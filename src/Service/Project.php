<?php

namespace Be\App\Doc\Service;

use Be\App\ServiceException;
use Be\Be;

class Project
{

    /**
     * 获取页面伪静态页网址
     *
     * @param array $params
     * @return string
     * @throws ServiceException
     */
    public function getProjectUrl(array $params = []): string
    {
        $project = $this->getProject($params['id']);
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
            throw new ServiceException('项目不存在！');
        }

        return $project;
    }


}
