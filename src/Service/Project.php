<?php

namespace Be\App\Doc\Service;

use Be\App\ServiceException;
use Be\Be;

class Project
{

    /**
     * 获取项目详情页伪静态页网址
     *
     * @param array $params
     * @return string
     * @throws ServiceException
     */
    public function getProjectUrl(array $params = []): string
    {
        $project = $this->getProject($params['project_id']);
        return '/doc/' . $project->url;
    }

    /**
     * 获取项目
     *
     * @param string $projectId 项目ID
     * @return object
     */
    public function getProject(string $projectId): object
    {
        $cache = Be::getCache();
        $key = 'Doc:Project:' . $projectId;
        $project = $cache->get($key);
        if (!$project) {
            throw new ServiceException('项目（#' . $projectId . '）不存在！');
        }

        return $project;
    }

    /**
     * 查看项目并更新点击
     *
     * @param string $projectId 项目ID
     * @return object
     */
    public function hit(string $projectId): object
    {
        $cache = Be::getCache();

        $product = $this->getProject($projectId);

        // 点击量 使用缓存 存放
        $hits = (int)$product->hits;
        $hitsKey = 'Doc:Project:hits:' . $projectId;
        $cacheHits = $cache->get($hitsKey);
        if ($cacheHits !== false) {
            if (is_numeric($cacheHits)) {
                $cacheHits = (int)$cacheHits;
                if ($cacheHits > $product->hits) {
                    $hits = $cacheHits;
                }
            }
        }

        $hits++;

        $cache->set($hitsKey, $hits);

        // 每 100 次访问，更新到数据库
        if ($hits % 100 === 0) {
            $sql = 'UPDATE doc_product SET hits=?, update_time=? WHERE id=?';
            Be::getDb()->query($sql, [$hits, date('Y-m-d H:i:s'), $projectId]);
        }

        $product->hits = $hits;

        return $product;
    }

}
