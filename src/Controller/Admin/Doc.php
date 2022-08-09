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
class Doc extends Auth
{

    /**
     * 文档
     *
     * @BeMenu("文档", icon="el-icon-document-copy", ordering="1.1")
     * @BePermission("文档", ordering="1.1")
     */
    public function projects()
    {
        Be::getAdminPlugin('Curd')->setting([

            'label' => '项目列表',
            'table' => 'doc_project',

            'grid' => [
                'title' => '项目列表',

                'filter' => [
                    ['is_delete', '=', '0'],
                ],

                'orderBy' => 'ordering',
                'orderByDir' => 'ASC',

                'form' => [
                    'items' => [
                        [
                            'name' => 'title',
                            'label' => '项目标题',
                        ],
                    ],
                ],

                'titleRightToolbar' => [
                    'items' => [
                        [
                            'label' => '新建项目',
                            'action' => 'createProject',
                            'target' => 'self', // 'ajax - ajax请求 / dialog - 对话框窗口 / drawer - 抽屉 / self - 当前页面 / blank - 新页面'
                            'ui' => [
                                'icon' => 'el-icon-plus',
                                'type' => 'primary',
                            ]
                        ],
                    ]
                ],

                'tableToolbar' => [
                    'items' => [
                        [
                            'label' => '批量删除',
                            'task' => 'fieldEdit',
                            'target' => 'ajax',
                            'confirm' => '确认要删除吗？',
                            'postData' => [
                                'field' => 'is_delete',
                                'value' => '1',
                            ],
                            'ui' => [
                                'icon' => 'el-icon-delete',
                                'type' => 'danger'
                            ]
                        ],
                    ]
                ],


                'table' => [

                    // 未指定时取表的所有字段
                    'items' => [
                        [
                            'driver' => TableItemSelection::class,
                            'width' => '50',
                        ],
                        [
                            'name' => 'title',
                            'label' => '项目标题',
                            'align' => 'left',
                        ],
                        [
                            'name' => 'chapter_count',
                            'label' => '章节数量',
                            'align' => 'center',
                            'width' => '120',
                        ],
                        [
                            'name' => 'ordering',
                            'label' => '排序',
                            'width' => '120',
                            'sortable' => true,
                        ],
                        [
                            'name' => 'create_time',
                            'label' => '创建时间',
                            'width' => '180',
                            'sortable' => true,
                        ],
                        [
                            'name' => 'update_time',
                            'label' => '更新时间',
                            'width' => '180',
                            'sortable' => true,
                        ],
                    ],
                    'operation' => [
                        'label' => '操作',
                        'width' => '240',
                        'items' => [
                            [
                                'label' => '',
                                'tooltip' => '编辑文档',
                                'action' => 'goChapters',
                                'target' => 'blank',
                                'ui' => [
                                    'type' => 'success',
                                    ':underline' => 'false',
                                    'style' => 'font-size: 20px;',
                                ],
                                'icon' => 'el-icon-document-copy',
                            ],
                            [
                                'label' => '',
                                'tooltip' => '编辑项目',
                                'action' => 'editProject',
                                'target' => 'self',
                                'ui' => [
                                    ':underline' => 'false',
                                    'style' => 'font-size: 20px;',
                                ],
                                'icon' => 'el-icon-edit',
                            ],
                            [
                                'label' => '',
                                'tooltip' => '删除',
                                'action' => 'deleteProject',
                                'confirm' => '确认要删除么？',
                                'target' => 'ajax',
                                'ui' => [
                                    'type' => 'danger',
                                    ':underline' => 'false',
                                    'style' => 'font-size: 20px;',
                                ],
                                'icon' => 'el-icon-delete',
                            ],
                        ]
                    ],
                ],
            ],

            'detail' => [
                'title' => '项目详情',
                'theme' => 'DocBlank',
                'form' => [
                    'items' => [
                        [
                            'name' => 'id',
                            'label' => 'ID',
                        ],
                        [
                            'name' => 'title',
                            'label' => '标题',
                        ],
                        [
                            'name' => 'description',
                            'label' => '描述',
                            'driver' => DetailItemHtml::class,
                        ],
                        [
                            'name' => 'url',
                            'label' => '网址',
                            'value' => function ($row) {
                                return beUrl('Doc.Project.chapters', ['id' => $row['id']]);
                            }
                        ],
                        [
                            'name' => 'seo_title',
                            'label' => 'SEO 标题',
                        ],
                        [
                            'name' => 'seo_description',
                            'label' => 'SEO 描述',
                        ],
                        [
                            'name' => 'seo_keywords',
                            'label' => 'SEO 关键词',
                        ],
                        [
                            'name' => 'chapter_default_editor',
                            'label' => '文档默认编辑器',
                        ],
                        [
                            'name' => 'chapter_toggle_editor',
                            'label' => '文档是否可切换编辑器',
                            'driver' => DetailItemSwitch::class,
                        ],
                        [
                            'name' => 'ordering',
                            'label' => '排序',
                        ],
                        [
                            'name' => 'create_time',
                            'label' => '创建时间',
                        ],
                        [
                            'name' => 'update_time',
                            'label' => '更新时间',
                        ],
                    ]
                ],
            ],
        ])->execute();
    }

    /**
     * 新建项目
     *
     * @BePermission("新建项目", ordering="1.11")
     */
    public function createProject()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        if ($request->isAjax()) {
            try {
                Be::getService('App.Doc.Admin.Project')->edit($request->json('formData'));
                $response->set('success', true);
                $response->set('message', '新建项目成功！');
                $response->json();
            } catch (\Throwable $t) {
                $response->set('success', false);
                $response->set('message', $t->getMessage());
                $response->json();
            }
        } else {
            $response->set('project', false);
            $response->set('title', '新建项目');
            $response->display('App.Doc.Admin.Doc.editProject');
        }
    }

    /**
     * 编辑项目
     *
     * @BePermission("编辑项目", ordering="1.12")
     */
    public function editProject()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        if ($request->isAjax()) {
            try {
                Be::getService('App.Doc.Admin.Project')->edit($request->json('formData'));
                $response->set('success', true);
                $response->set('message', '编辑项目成功！');
                $response->json();
            } catch (\Throwable $t) {
                $response->set('success', false);
                $response->set('message', $t->getMessage());
                $response->json();
            }
        } elseif ($request->isPost()) {
            $postData = $request->post('data', '', '');
            if ($postData) {
                $postData = json_decode($postData, true);
                if (isset($postData['row']['id']) && $postData['row']['id']) {
                    $response->redirect(beAdminUrl('Doc.Doc.editProject', ['id' => $postData['row']['id']]));
                }
            }
        } else {
            $projectId = $request->get('id', '');
            $project = Be::getService('App.Doc.Admin.Project')->getProject($projectId);
            $response->set('project', $project);
            $response->set('title', '编辑项目');
            $response->display('App.Doc.Admin.Doc.editProject');
        }
    }

    /**
     * 删除项目
     *
     * @BePermission("删除项目", ordering="1.13")
     */
    public function deleteProject()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        try {
            $postData = $request->json();

            $categoryIds = [];
            if (isset($postData['selectedRows'])) {
                foreach ($postData['selectedRows'] as $row) {
                    $categoryIds[] = $row['id'];
                }
            } elseif (isset($postData['row'])) {
                $categoryIds[] = $postData['row']['id'];
            }

            if (count($categoryIds) > 0) {
                Be::getService('App.Doc.Admin.Project')->delete($categoryIds);
            }

            $response->set('success', true);
            $response->set('message', '删除项目成功！');
            $response->json();
        } catch (\Throwable $t) {
            $response->set('success', false);
            $response->set('message', $t->getMessage());
            $response->json();
        }
    }

    /**
     * 指定项目下的项目文档管理
     *
     * @BePermission("项目文档管理", ordering="1.14")
     */
    public function goChapters()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $postData = $request->post('data', '', '');
        if ($postData) {
            $postData = json_decode($postData, true);
            if (isset($postData['row']['id']) && $postData['row']['id']) {
                $response->redirect(beAdminUrl('Doc.Doc.chapters', ['id' => $postData['row']['id']]));
            }
        }
    }

    /**
     * 指定项目下的项目文档管理
     *
     * @BePermission("项目文档管理")
     */
    public function chapters()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $projectId = $request->get('id', '');

        $project = Be::getService('App.Doc.Admin.Project')->getProject($projectId);
        $response->set('project', $project);

        $chapterTree = Be::getService('App.Doc.Admin.Chapter')->getChapterTree($projectId);
        $response->set('chapterTree', $chapterTree);

        $configChapter = Be::getConfig('App.Doc.Chapter');
        $response->set('configChapter', $configChapter);

        $response->set('title', '项目文档管理');

        $response->display('App.Doc.Admin.Doc.chapters');
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
    public function addChapter()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        try {
            $chapter = Be::getService('App.Doc.Admin.Chapter')->addChapter($request->json());
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
    public function saveChapter()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        try {
            Be::getService('App.Doc.Admin.Chapter')->saveChapter($request->json('formData'));
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
    public function sortChapter()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        try {
            Be::getService('App.Doc.Admin.Chapter')->sortChapter($request->json('formData'));
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
    public function deleteChapter()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();
        try {
            $chapterId = $request->json('chapter_id', '');
            $chapter = Be::getService('App.Doc.Admin.Chapter')->deleteChapter($chapterId);
            $response->set('success', true);
            $response->set('message', '删除文档成功！');
            $response->set('chapter', $chapter);
            $response->json();
        } catch (\Throwable $t) {
            $response->set('success', false);
            $response->set('message', $t->getMessage());
            $response->json();
        }
    }

}
