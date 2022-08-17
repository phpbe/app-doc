<?php

namespace Be\App\Doc\Controller;

use Be\App\ControllerException;
use Be\Be;

/**
 * 文档
 */
class Project
{

    /**
     * 项目首页
     *
     * @BeMenu("指定项目", picker="return \Be\Be::getService('App.Doc.Admin.Project')->getProjectMenuPicker()")
     * @BeRoute("\Be\Be::getService('App.Doc.Project')->getProjectUrl($params)")
     */
    public function detail()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        try {
            $serviceProject = Be::getService('App.Doc.Project');
            $projectId = $request->get('project_id', '');
            if ($projectId === '') {
                throw new ControllerException('项目不存在！');
            }

            $project = $serviceProject->hit($projectId);

            $response->set('title', $project->seo_title);
            $response->set('metaKeywords', $project->seo_keywords);
            $response->set('metaDescription', $project->seo_description);

            $response->set('pageTitle', $project->title);

            $response->set('project', $project);

            $serviceChapter = Be::getService('App.Doc.Chapter');
            $chapterTree =  $serviceChapter->getChapterTree($project->id);
            if (count($chapterTree) === 0) {
                throw new ControllerException('该项目下暂无文档！');
            }
            $response->set('chapterTree', $chapterTree);

            $flatChapterTree =  $serviceChapter->getFlatChapterTree($project->id);
            $response->set('flatChapterTree', $flatChapterTree);

            $configChapter = Be::getConfig('App.Doc.Chapter');
            $response->set('configChapter', $configChapter);

            $project->parent_id = '';
            $response->set('chapter', $project);

            $response->display('App.Doc.Chapter.detail');
        } catch (\Throwable $t) {
            $response->error($t->getMessage());
        }
    }

}
