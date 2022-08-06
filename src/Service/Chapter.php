<?php

namespace Be\App\Doc\Service;

use Be\App\ServiceException;
use Be\Be;

class Chapter
{

    /**
     * 获取文档
     *
     * @param string $chapterId 文档ID
     * @return object 文档对象
     * @throws ServiceException
     */
    public function getChapter(string $chapterId): object
    {
        $cache = Be::getCache();

        $key = 'Doc:Chapter:' . $chapterId;
        $chapter = $cache->get($key);
        if (!$chapter) {
            throw new ServiceException('文档不存在！');
        }

        return $chapter;
    }

    /**
     * 获取文档
     *
     * @param string $chapterId 文档ID
     * @return object 文档对象
     * @throws ServiceException
     */
    public function getChapterFromDb(string $chapterId): object
    {
        $db = Be::getDb();

        $sql = 'SELECT * FROM `doc_chapter` WHERE id=?';
        $chapter = $db->getObject($sql, [$chapterId]);
        if (!$chapter) {
            throw new ServiceException('文档不存在！');
        }

        $chapter->url_custom = (int)$chapter->url_custom;
        $chapter->seo_title_custom = (int)$chapter->seo_title_custom;
        $chapter->seo_description_custom = (int)$chapter->seo_description_custom;
        $chapter->ordering = (int)$chapter->ordering;
        $chapter->is_enable = (int)$chapter->is_enable;
        $chapter->is_delete = (int)$chapter->is_delete;

        return $chapter;
    }

    /**
     * 获取章节树
     * @param string $projectId
     * @return void
     */
    public function getChapterTree(string $projectId): array
    {

    }


    /**
     * 查看文档并更新点击
     *
     * @param string $chapterId 文档ID
     * @return object
     */
    public function hit(string $chapterId): object
    {
        $cache = Be::getCache();

        $chapter = $this->getChapter($chapterId);

        // 点击量 使用缓存 存放
        $hits = (int)$chapter->hits;
        $hitsKey = 'Doc:Chapter:hits:' . $chapterId;
        $cacheHits = $cache->get($hitsKey);
        if ($cacheHits !== false) {
            if (is_numeric($cacheHits)) {
                $cacheHits = (int)$cacheHits;
                if ($cacheHits > $chapter->hits) {
                    $hits = $cacheHits;
                }
            }
        }

        $hits++;

        $cache->set($hitsKey, $hits);

        // 每 100 次访问，更新到数据库
        if ($hits % 100 === 0) {
            $sql = 'UPDATE doc_chapter SET hits=?, update_time=? WHERE id=?';
            Be::getDb()->query($sql, [$hits, date('Y-m-d H:i:s'), $chapterId]);
        }

        $chapter->hits = $hits;

        return $chapter;
    }

    /**
     * 按关銉词搜索
     *
     * @param string $keywords 关銉词
     * @param array $params
     * @return array
     */
    public function search(string $keywords, array $params = []): array
    {
        $configChapter = Be::getConfig('App.Doc.Chapter');
        $configEs = Be::getConfig('App.Doc.Es');
        if (!$configEs->enable) {
            return $this->searchFromDb($keywords, $params);
        }

        $cache = Be::getCache();
        $es = Be::getEs();

        $keywords = trim($keywords);
        if ($keywords !== '') {
            // 将本用户搜索的关键词写入ES search_history
            $counterKey = 'Doc:Chapter:SearchHistory';
            $counter = (int)$cache->get($counterKey);
            $query = [
                'index' => $configEs->indexChapterSearchHistory,
                'id' => $counter,
                'body' => [
                    'keyword' => $keywords,
                ]
            ];
            $es->index($query);

            // 累计写入1千个
            $counter++;
            if ($counter >= $configChapter->searchHistory) {
                $counter = 0;
            }

            $cache->set($counterKey, $counter);
        }

        $query = [
            'index' => $configEs->indexChapter,
            'body' => [
                'query' => [
                    'bool' => [
                        'filter' => [
                            [
                                'term' => [
                                    'is_enable' => true,
                                ],
                            ],
                            [
                                'term' => [
                                    'is_delete' => false,
                                ],
                            ],
                        ]
                    ]
                ]
            ]
        ];

        if ($keywords === '') {
            $query['body']['min_score'] = 0;
        } else {
            $query['body']['min_score'] = 0.01;
            $query['body']['query']['bool']['should'] = [
                [
                    'match' => [
                        'title' => $keywords
                    ],
                ],
            ];
        }

        if (isset($params['orderBy'])) {
            if (is_array($params['orderBy'])) {
                $len1 = count($params['orderBy']);
                if ($len1 > 0 && is_array($params['orderByDir'])) {
                    $len2 = count($params['orderByDir']);
                    if ($len1 === $len2) {
                        $query['body']['sort'] = [];
                        for ($i = 0; $i < $len1; $i++) {
                            $orderByDir = 'desc';
                            if (in_array($params['orderByDir'][$i], ['asc', 'desc'])) {
                                $orderByDir = $params['orderByDir'][$i];
                            }
                            $query['body']['sort'][] = [
                                $params['orderBy'][$i] => [
                                    'order' => $orderByDir
                                ]
                            ];
                        }
                    }
                }
            } elseif (is_string($params['orderBy']) && in_array($params['orderBy'], ['hits', 'publish_time'])) {
                $orderByDir = 'desc';
                if (in_array($params['orderByDir'], ['asc', 'desc'])) {
                    $orderByDir = $params['orderByDir'];
                }

                $query['body']['sort'] = [
                    [
                        $params['orderBy'] => [
                            'order' => $orderByDir
                        ]
                    ],
                ];
            }
        }

        // 分页
        $pageSize = null;
        if (isset($params['pageSize']) && is_numeric($params['pageSize']) && $params['pageSize'] > 0) {
            $pageSize = $params['pageSize'];
        } else {
            $pageSize = $configChapter->pageSize;
        }

        if ($pageSize > 200) {
            $pageSize = 200;
        }

        $page = null;
        if (isset($params['page']) && is_numeric($params['page']) && $params['page'] > 0) {
            $page = $params['page'];
        } else {
            $page = 1;
        }

        $query['body']['size'] = $pageSize;
        $query['body']['from'] = ($page - 1) * $pageSize;

        $results = $es->search($query);

        $total = 0;
        if (isset($results['hits']['total']['value'])) {
            $total = $results['hits']['total']['value'];
        }

        $rows = [];
        foreach ($results['hits']['hits'] as $x) {
            $rows[] = $this->formatEsChapter($x['_source']);
        }

        return [
            'total' => $total,
            'pageSize' => $pageSize,
            'page' => $page,
            'rows' => $rows,
        ];
    }

    /**
     * 按关銉词搜索
     *
     * @param string $keywords 关銉词
     * @param array $params
     * @return array
     */
    public function searchFromDb(string $keywords, array $params = []): array
    {
        $configChapter = Be::getConfig('App.Doc.Chapter');
        $tableChapter = Be::getTable('doc_chapter');

        $tableChapter->where('is_enable', 1);
        $tableChapter->where('is_delete', 0);

        if ($keywords !== '') {
            $tableChapter->where('title', 'like', '%' . $keywords . '%');
        }

        $total = $tableChapter->count();

        if (isset($params['orderBy']) && in_array($params['orderBy'], ['hits', 'publish_time'])) {
            $orderByDir = 'desc';
            if (isset($params['orderByDir']) && in_array($params['orderByDir'], ['asc', 'desc'])) {
                $orderByDir = $params['orderByDir'];
            }

            $tableChapter->orderBy($params['orderBy'], $orderByDir);
        } else {
            $tableChapter->orderBy('ordering DESC');
        }


        if (isset($params['orderBy'])) {
            if (is_array($params['orderBy'])) {
                $len1 = count($params['orderBy']);
                if ($len1 > 0 && is_array($params['orderByDir'])) {
                    $len2 = count($params['orderByDir']);
                    if ($len1 === $len2) {
                        $orderByStrings = [];
                        for ($i = 0; $i < $len1; $i++) {
                            $orderByDir = 'desc';
                            if (in_array($params['orderByDir'][$i], ['asc', 'desc'])) {
                                $orderByDir = $params['orderByDir'][$i];
                            }
                            $orderByStrings[] = $params['orderBy'][$i] . ' ' . strtoupper($orderByDir);
                        }

                        $tableChapter->orderBy(implode(', ', $orderByStrings));
                    }
                }
            } elseif (is_string($params['orderBy']) && in_array($params['orderBy'], ['hits', 'publish_time'])) {
                $orderByDir = 'desc';
                if (in_array($params['orderByDir'], ['asc', 'desc'])) {
                    $orderByDir = $params['orderByDir'];
                }

                $tableChapter->orderBy($params['orderBy'], strtoupper($orderByDir));
            }
        }


        // 分页
        $pageSize = null;
        if (isset($params['pageSize']) && is_numeric($params['pageSize']) && $params['pageSize'] > 0) {
            $pageSize = $params['pageSize'];
        } else {
            $pageSize = $configChapter->pageSize;
        }

        if ($pageSize > 200) {
            $pageSize = 200;
        }
        $tableChapter->limit($pageSize);

        $page = null;
        if (isset($params['page']) && is_numeric($params['page']) && $params['page'] > 0) {
            $page = $params['page'];
        } else {
            $page = 1;
        }

        $tableChapter->offset(($page - 1) * $pageSize);

        $rows = $tableChapter->getObjects();

        return [
            'total' => $total,
            'pageSize' => $pageSize,
            'page' => $page,
            'rows' => $rows,
        ];
    }

    /**
     * 格式化ES查询出来的文档
     *
     * @param array $row
     * @return object
     */
    private function formatEsChapter(array $row): object
    {
        $chapter = (object)$row;
        return $chapter;
    }

    /**
     * 获取文档伪静态页网址
     *
     * @param array $params
     * @return string
     * @throws ServiceException
     */
    public function getChapterUrl(array $params = []): string
    {
        $chapter = $this->getChapter($params['id']);
        $project = $this->getChapter($chapter->project_id);
        return '/doc/' . $project->url . '/' . $chapter->url;
    }


}
