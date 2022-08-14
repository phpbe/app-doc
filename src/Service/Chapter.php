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
            throw new ServiceException('文档（#' . $chapterId . '）不存在！');
        }

        return $chapter;
    }

    /**
     * 获取文档树
     * @param string $projectId
     * @return array
     */
    public function getChapterTree(string $projectId): array
    {
        $cache = Be::getCache();

        $key = 'Doc:ChapterTree:' . $projectId;
        if (!$cache->has($key)) {
            return [];
        }

        return $cache->get($key);
    }

    /**
     * 获取文档树
     * @param string $projectId
     * @return array
     */
    public function getFlatChapterTree(string $projectId): array
    {
        $cache = Be::getCache();

        $key = 'Doc:FlatChapterTree:' . $projectId;
        if (!$cache->has($key)) {
            return [];
        }

        return $cache->get($key);
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
        $chapter = $this->getChapter($params['chapter_id']);
        $project = Be::getService('App.Doc.Project')->getProject($chapter->project_id);
        return '/doc/' . $project->url . '/' . $chapter->url;
    }


    /**
     * 获取文档树菜单
     *
     * @param array $chapterTree
     * @param array $flatChapterTree
     * @param object $chapter
     * @return string
     */
    public function getChapterTreeMenu(array $chapterTree, array $flatChapterTree, object $chapter): string
    {
        $parentIdMapping = [];
        foreach ($flatChapterTree as $x) {
            $parentIdMapping[$x->id] = $x->parent_id;
        }

        $openNodeIds = [];
        $openNodeIds[] = $chapter->id;

        $parentId = $chapter->parent_id;
        while ($parentId !== '') {
            $openNodeIds[] = $parentId;
            $parentId = $parentIdMapping[$parentId] ?? '';
        }

        return $this->makeChapterTreeMenu($chapterTree, $openNodeIds, $chapter->id, 0);
    }

    /**
     * 生成文档树菜单
     *
     * @param array $chapterTree
     * @param array $openNodeIds
     * @param string $chapterId
     * @param int $level
     * @return string
     */
    public function makeChapterTreeMenu(array $chapterTree, array $openNodeIds, string $chapterId, int $level = 0): string
    {
        $html = '<ul class="doc-menu-ul">';
        foreach ($chapterTree as $chapter) {
            $childrenCount = count($chapter->children);

            $html .= '<li class="';
            if ($childrenCount > 0) {
                $html .= in_array($chapter->id, $openNodeIds) ? ' menu-open' : ' menu-close';
            }

            if ($chapterId === $chapter->id) {
                $html .= ' menu-active';
            }

            $html .= '">';

            $html .= '<div class="menu-label"';
            if ($level > 0) {
                $html .= 'style="padding-left: ' . $level . 'rem"';
            }
            $html .= '>';

            $html .= '<i class="icon';
            if ($childrenCount > 0) {
                $html .= in_array($chapter->id, $openNodeIds) ? ' icon-open' : ' icon-close';
            }
            $html .= '"></i>';

            $html .= '<a href="' . $chapter->url . '">';
            $html .= $chapter->title;
            $html .= '</a>';

            $html .= '</div>';

            if ($childrenCount > 0) {
                $html .= $this->makeChapterTreeMenu($chapter->children, $openNodeIds, $chapterId, $level + 1);
            }

            $html .= '</li>';
        }
        $html .= '</ul>';

        return $html;
    }


}
