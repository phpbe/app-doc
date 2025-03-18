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
     * @return array
     * @throws ServiceException
     */
    public function getProjectUrl(array $params = []): array
    {
        $project = $this->getProject($params['project_id']);

        $params1 = ['project_id' => $params['project_id']];
        unset($params['project_id']);
        return ['/doc/' . $project->url, $params1, $params];
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
        if ($project) {
            if (!is_object($project)) {
                throw new ServiceException('doc - 项目（#' . $projectId . '）不存在！');
            }
        } else {
            $configProject = Be::getConfig('App.Doc.Project');
            if (!isset($configProject->cacheNotExistsLoadFromDb)) {
                $configProject->cacheNotExistsLoadFromDb = 1;
                $configProject->cacheNotExistsLoadFromDbExceptionLockTime = 600;
            }

            if ($configProject->cacheNotExistsLoadFromDb === 1) {
                try {
                    # 从数据库中加载
                    $project = $this->getProjectFromDb($projectId);
                    $cache->set($key, $project);
                } catch (Throwable $t) {
                    # 数据库中不存在，缓存锁定一段时间
                    if ($configProject->cacheNotExistsLoadFromDbExceptionLockTime > 0) {
                        $cache->set($key, '', $configProject->cacheNotExistsLoadFromDbExceptionLockTime);
                    }
                }
            } else {
                throw new ServiceException('doc - 项目（#' . $projectId . '）不存在！');
            }
        }

        return $project;
    }

    /**
     * 获取项目 - 从数据库读取
     *
     * @param string $projectId 项目ID
     * @return object
     */
    public function getProjectFromDb(string $projectId): object
    {
        $db = Be::getDb();
        $sql = 'SELECT * FROM doc_project WHERE id = ? AND is_delete = 0';
        $project = $db->getObject($sql, [$projectId]);
        if (!$project) {
            throw new ServiceException('doc - 项目（#' . $projectId . '）不存在！');
        }

        $project->url_custom = (int)$project->url_custom;
        $project->seo_title_custom = (int)$project->seo_title_custom;
        $project->seo_description_custom = (int)$project->seo_description_custom;
        $project->ordering = (int)$project->ordering;
        $project->hits = (int)$project->hits;
        $project->is_delete = (int)$project->is_delete;
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
            $sql = 'UPDATE doc_project SET hits=?, update_time=? WHERE id=?';
            Be::getDb()->query($sql, [$hits, date('Y-m-d H:i:s'), $projectId]);
        }

        $product->hits = $hits;

        return $product;
    }

}
