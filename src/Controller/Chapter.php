<?php

namespace Be\App\Doc\Controller;

use Be\App\ControllerException;
use Be\Be;

/**
 * 文档
 */
class Chapter
{

    /**
     * 章节详情
     *
     * @BeMenu("指定文档", picker="return \Be\Be::getService('App.Doc.Admin.Chapter')->getMenuPicker()")
     * @BeRoute("\Be\Be::getService('App.Doc.Chapter')->getChapterUrl($params)")
     */
    public function detail()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        try {
            $serviceChapter = Be::getService('App.Doc.Chapter');
            $chapterId = $request->get('chapter_id', '');
            if ($chapterId === '') {
                throw new ControllerException('文档不存在！');
            }

            $chapter = $serviceChapter->hit($chapterId);
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
