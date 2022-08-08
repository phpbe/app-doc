<?php

namespace Be\App\Doc\Service\Admin;


use Be\Be;

class TaskProject
{

    /**
     * 分类同步到 Redis
     *
     * @param array $categories
     */
    public function syncCache(array $categories)
    {
        if (count($categories) === 0) return;

        $cache = Be::getCache();
        $keyValues = [];
        foreach ($categories as $project) {
            $key = 'Doc:Project:' . $project->id;

            $project->is_delete = (int)$project->is_delete;

            if ($project->is_delete === 1) {
                $cache->delete($key);
            } else {
                $project->url_custom = (int)$project->url_custom;
                $project->seo_title_custom = (int)$project->seo_title_custom;
                $project->seo_description_custom = (int)$project->seo_description_custom;
                $project->ordering = (int)$project->ordering;
                $project->is_enable = (int)$project->is_enable;

                $keyValues[$key] = $project;
            }
        }

        if (count($keyValues) > 0) {
            $cache->setMany($keyValues);
        }
    }

}
