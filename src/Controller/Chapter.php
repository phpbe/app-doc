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
            $response->set('metaKeywords', $chapter->seo_keywords);
            $response->set('metaDescription', $chapter->seo_description);

            $response->set('pageTitle', $chapter->title);

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

            $northMenu = Be::getMenu('North');
            $menuActiveId = null;
            foreach ($northMenu->getItems() as $item) {
                if ($item->route === 'Doc.Project.detail' && $project->id === $item->params['project_id']) {
                    $menuActiveId = $item->id;
                    break;
                }
            }

            if ($menuActiveId !== null) {
                $northMenu->setActiveId($menuActiveId);
            }

            $response->display();
        } catch (\Throwable $t) {
            $response->error($t->getMessage());
        }
    }
}
