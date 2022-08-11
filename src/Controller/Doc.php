<?php

namespace Be\App\Doc\Controller;

use Be\App\ControllerException;
use Be\Be;

/**
 * 文档
 */
class Doc
{

    /**
     * 项目首页
     *
     * @BeMenu("指定项目", picker="return \Be\Be::getService('App.Doc.Admin.Project')->getProjectMenuPicker()")
     * @BeRoute("\Be\Be::getService('App.Doc.Project')->getProjectUrl($params)")
     */
    public function project()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        try {
            $serviceProject = Be::getService('App.Doc.Project');
            $id = $request->get('id', '');
            if ($id === '') {
                throw new ControllerException('项目不存在！');
            }

            $project = $serviceProject->getProject($id);

            $response->set('title', $project->seo_title);
            $response->set('meta_keywords', $project->seo_keywords);
            $response->set('meta_description', $project->seo_description);
            $response->set('project', $project);

            $serviceChapter = Be::getService('App.Doc.Chapter');
            $chapterTree =  $serviceChapter->getChapterTree($project->id);
            $response->set('chapterTree', $chapterTree);

            $flatChapterTree =  $serviceChapter->getFlatChapterTree($project->id);
            $response->set('flatChapterTree', $flatChapterTree);
            /*
            $configProject = Be::getConfig('App.Doc.Project');
            $response->set('configProject', $configProject);
            */

            $configChapter = Be::getConfig('App.Doc.Chapter');
            $response->set('configChapter', $configChapter);


            $response->display('App.Doc.Doc.chapter');
        } catch (\Throwable $t) {
            $response->error($t->getMessage());
        }
    }

    /**
     * 章节详情
     *
     * @BeRoute("\Be\Be::getService('App.Doc.Chapter')->getChapterUrl($params)")
     */
    public function chapter()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        try {
            $serviceChapter = Be::getService('App.Doc.Chapter');
            $id = $request->get('id', '');
            if ($id === '') {
                throw new ControllerException('文档不存在！');
            }

            $chapter = $serviceChapter->hit($id);
            $response->set('title', $chapter->seo_title);
            $response->set('meta_keywords', $chapter->seo_keywords);
            $response->set('meta_description', $chapter->seo_description);
            $response->set('chapter', $chapter);

            $serviceProject = Be::getService('App.Doc.Project');
            $project = $serviceProject->getProject($chapter->project_id);
            $response->set('project', $project);

            $chapterTree =  $serviceChapter->getChapterTree($chapter->project_id);
            $response->set('chapterTree', $chapterTree);

            $flatChapterTree =  $serviceChapter->getFlatChapterTree($chapter->project_id);
            $response->set('flatChapterTree', $flatChapterTree);

            $configChapter = Be::getConfig('App.Doc.Chapter');
            $response->set('configChapter', $configChapter);

            $response->display();
        } catch (\Throwable $t) {
            $response->error($t->getMessage());
        }
    }
}
