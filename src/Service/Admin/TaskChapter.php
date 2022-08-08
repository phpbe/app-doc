<?php

namespace Be\App\Doc\Service\Admin;

use Be\App\ServiceException;
use Be\Be;

class TaskChapter
{

    /**
     * 同步到 ES
     *
     * @param array $chapters
     */
    public function syncEs(array $chapters)
    {
        if (count($chapters) === 0) return;

        $configEs = Be::getConfig('App.Doc.Es');
        if (!$configEs->enable) {
            return;
        }

        $es = Be::getEs();
        $db = Be::getDb();

        $batch = [];
        foreach ($chapters as $chapter) {

            $chapter->is_enable = (int)$chapter->is_enable;
            $chapter->is_delete = (int)$chapter->is_delete;

            $batch[] = [
                'index' => [
                    '_index' => $configEs->indexChapter,
                    '_id' => $chapter->id,
                ]
            ];

            if ($chapter->is_delete === 1) {
                $batch[] = [
                    'id' => $chapter->id,
                    'is_delete' => true
                ];
            } else {

                $batch[] = [
                    'id' => $chapter->id,
                    'project_id' => $chapter->project_id,
                    'title' => $chapter->title,
                    'summary' => $chapter->summary,
                    'description' => $chapter->description,
                    'url' => $chapter->url,
                    'ordering' => (int)$chapter->ordering,
                    'hits' => (int)$chapter->hits,
                    'is_enable' => $chapter->is_enable === 1,
                    'is_delete' => $chapter->is_delete === 1,
                    'create_time' => $chapter->create_time,
                    'update_time' => $chapter->update_time,
                ];
            }
        }

        $response = $es->bulk(['body' => $batch]);
        if ($response['errors'] > 0) {
            $reason = '';
            if (isset($response['items']) && count($response['items']) > 0) {
                foreach ($response['items'] as $item) {
                    if (isset($item['index']['error']['reason'])) {
                        $reason = $item['index']['error']['reason'];
                        break;
                    }
                }
            }
            throw new ServiceException('文章全量量同步到ES出错：' . $reason);
        }
    }

    /**
     * 文章同步到 Redis
     *
     * @param array $chapters
     */
    public function syncCache(array $chapters)
    {
        if (count($chapters) === 0) return;

        $db = Be::getDb();
        $cache = Be::getCache();
        $keyValues = [];
        foreach ($chapters as $chapter) {

            $chapter->is_enable = (int)$chapter->is_enable;
            $chapter->is_delete = (int)$chapter->is_delete;

            $key = 'Doc:Chapter:' . $chapter->id;

            if ($chapter->is_delete === 1) {
                $cache->delete($key);
            } else {

                $chapter->url_custom = (int)$chapter->url_custom;
                $chapter->seo_title_custom = (int)$chapter->seo_title_custom;
                $chapter->seo_description_custom = (int)$chapter->seo_description_custom;
                $chapter->ordering = (int)$chapter->ordering;

                $keyValues[$key] = $chapter;
            }
        }

        if (count($keyValues) > 0) {
            $cache->setMany($keyValues);
        }

    }


}
