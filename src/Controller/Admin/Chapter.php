<?php

namespace Be\App\Doc\Controller\Admin;

use Be\AdminPlugin\Detail\Item\DetailItemHtml;
use Be\AdminPlugin\Detail\Item\DetailItemSwitch;
use Be\AdminPlugin\Table\Item\TableItemImage;
use Be\AdminPlugin\Table\Item\TableItemLink;
use Be\AdminPlugin\Table\Item\TableItemSelection;
use Be\App\ControllerException;
use Be\App\System\Controller\Admin\Auth;
use Be\Be;

/**
 * @BePermissionGroup("文档")
 */
class Chapter extends Auth
{

    /**
     * 指定项目下的项目文档管理
     *
     * @BePermission("项目文档管理", ordering="1.14")
     */
    public function chapters()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $projectId = $request->get('project_id', '');

        $project = Be::getService('App.Doc.Admin.Project')->getProject($projectId);
        $response->set('project', $project);

        $chapterTree = Be::getService('App.Doc.Admin.Chapter')->getChapterTree($projectId);
        $response->set('chapterTree', $chapterTree);

        $configChapter = Be::getConfig('App.Doc.Chapter');
        $response->set('configChapter', $configChapter);

        $response->set('title', '项目文档管理');

        $response->display();
    }

    /**
     * 获取文档
     *
     * @BePermission("项目文档管理")
     */
    public function getChapter()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        try {
            $chapterId = $request->json('chapter_id', '');
            $chapter = Be::getService('App.Doc.Admin.Chapter')->getChapter($chapterId);
            $response->set('success', true);
            $response->set('message', '获取文档成功！');
            $response->set('chapter', $chapter);
            $response->json();
        } catch (\Throwable $t) {
            $response->set('success', false);
            $response->set('message', $t->getMessage());
            $response->json();
        }
    }

    /**
     * 添加文档
     *
     * @BePermission("项目文档管理")
     */
    public function create()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        try {
            $chapter = Be::getService('App.Doc.Admin.Chapter')->create($request->json());
            $response->set('success', true);
            $response->set('message', '新建文档成功！');
            $response->set('chapter', $chapter);
            $response->json();
        } catch (\Throwable $t) {
            $response->set('success', false);
            $response->set('message', $t->getMessage());
            $response->json();
        }
    }

    /**
     * 保存文档
     *
     * @BePermission("项目文档管理")
     */
    public function edit()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        try {
            Be::getService('App.Doc.Admin.Chapter')->edit($request->json('formData'));
            $response->set('success', true);
            $response->set('message', '保存文档成功！');
            $response->json();
        } catch (\Throwable $t) {
            $response->set('success', false);
            $response->set('message', $t->getMessage());
            $response->json();
        }
    }

    /**
     * 文档排序
     *
     * @BePermission("项目文档管理")
     */
    public function sort()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        try {
            Be::getService('App.Doc.Admin.Chapter')->sort($request->json('formData'));
            $response->set('success', true);
            $response->set('message', '文档排序成功！');
            $response->json();
        } catch (\Throwable $t) {
            $response->set('success', false);
            $response->set('message', $t->getMessage());
            $response->json();
        }
    }

    /**
     * 删除文档
     *
     * @BePermission("项目文档管理")
     */
    public function delete()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();
        try {
            $chapterId = $request->json('chapter_id', '');
            Be::getService('App.Doc.Admin.Chapter')->delete($chapterId);
            $response->set('success', true);
            $response->set('message', '删除文档成功！');
            $response->json();
        } catch (\Throwable $t) {
            $response->set('success', false);
            $response->set('message', $t->getMessage());
            $response->json();
        }
    }

}
