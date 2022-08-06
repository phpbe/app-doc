<?php

namespace Be\App\Doc\Controller\Admin;

use Be\AdminPlugin\Detail\Item\DetailItemHtml;
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

            'label' => '项目',
            'table' => 'doc_category',

            'grid' => [
                'title' => '项目',

                'filter' => [
                    ['is_delete', '=', '0'],
                ],

                'orderBy' => 'ordering',
                'orderByDir' => 'ASC',

                'form' => [
                    'items' => [
                        [
                            'name' => 'name',
                            'label' => '名称',
                        ],
                    ],
                ],

                'titleRightToolbar' => [
                    'items' => [
                        [
                            'label' => '新建项目',
                            'action' => 'create',
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
                            'label' => '名称',
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
                        'width' => '180',
                        'items' => [
                            [
                                'label' => '',
                                'tooltip' => '编辑文档',
                                'action' => 'chapters',
                                'target' => 'self',
                                'ui' => [
                                    ':underline' => 'false',
                                    'style' => 'font-size: 20px;',
                                ],
                                'icon' => 'el-icon-edit',
                            ],
                            [
                                'label' => '',
                                'tooltip' => '编辑项目',
                                'action' => 'edit',
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
                                'task' => 'fieldEdit',
                                'confirm' => '确认要删除么？',
                                'target' => 'ajax',
                                'postData' => [
                                    'field' => 'is_delete',
                                    'value' => 1,
                                ],
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

            'fieldEdit' => [
                'events' => [
                    'before' => function ($tuple) {
                        $postData = Be::getRequest()->json();
                        $field = $postData['postData']['field'];
                        if ($field === 'is_delete') {
                            $value = $postData['postData']['value'];
                            if ($value === 1) {
                                $tuple->url = $tuple->url . '-' . $tuple->id;
                            }
                        }

                        $tuple->update_time = date('Y-m-d H:i:s');
                    },
                    'success' => function () {
                        $postData = Be::getRequest()->json();

                        $categoryIds = [];
                        if (isset($postData['selectedRows'])) {
                            foreach ($postData['selectedRows'] as $row) {
                                $categoryIds[] = $row['id'];
                            }
                        } elseif (isset($postData['row'])) {
                            $categoryIds[] = $postData['row']['id'];
                        }

                        $chapterIds = Be::getTable('doc_chapter_category')
                            ->where('category_id', 'IN',  $categoryIds)
                            ->getValues('chapter_id');
                        if (count($chapterIds) > 0) {
                            Be::getService('App.Doc.Admin.Chapter')->onUpdate($chapterIds);
                        }

                        Be::getService('App.Doc.Admin.Project')->onUpdate($categoryIds);
                    },
                ],
            ],

        ])->execute();
    }

    /**
     * 新建项目
     *
     * @BePermission("新建", ordering="1.21")
     */
    public function create()
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
            $response->set('category', false);
            $response->set('title', '新建项目');
            $response->display('App.Doc.Admin.Project.edit');
        }
    }

    /**
     * 编辑
     *
     * @BePermission("编辑", ordering="1.32")
     */
    public function edit()
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
                    $response->redirect(beAdminUrl('Doc.Project.edit', ['id' => $postData['row']['id']]));
                }
            }
        } else {
            $pageId = $request->get('id', '');
            $category = Be::getService('App.Doc.Admin.Project')->getProject($pageId);
            $response->set('category', $category);
            $response->set('title', '编辑项目');
            $response->display('App.Doc.Admin.Project.edit');
        }
    }

    /**
     * 指定项目下的项目章节管理
     *
     * @BePermission("项目章节管理", ordering="1.33")
     */
    public function goChapters()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $postData = $request->post('data', '', '');
        if ($postData) {
            $postData = json_decode($postData, true);
            if (isset($postData['row']['id']) && $postData['row']['id']) {
                $response->redirect(beAdminUrl('Doc.Project.chapters', ['id' => $postData['row']['id']]));
            }
        }
    }

    /**
     * 指定项目下的项目章节管理
     *
     * @BePermission("项目章节管理")
     */
    public function chapters()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $categoryId = $request->get('id', '');
        $category = Be::getService('App.Doc.Admin.Project')->getProject($categoryId);

        $filter = [
            ['is_delete', '=', '0'],
        ];

        $chapterIds = Be::getTable('doc_chapter_category')
            ->where('category_id', $categoryId)
            ->getValues('chapter_id');
        if (count($chapterIds) > 0) {
            $filter[] = [
                'id', 'IN', $chapterIds
            ];
        } else {
            $filter[] = [
                'id', '=', ''
            ];
        }

        Be::getAdminPlugin('Curd')->setting([
            'label' => '项目 ' . $category->name . ' 下的章节',
            'table' => 'doc_chapter',
            'grid' => [
                'title' => '项目 ' . $category->name . ' 下的章节管理',

                'filter' => $filter,

                'titleRightToolbar' => [
                    'items' => [
                        [
                            'label' => '返回',
                            'url' => beAdminUrl('Doc.Project.categories'),
                            'target' => 'self',
                            'ui' => [
                                'icon' => 'el-icon-back'
                            ]
                        ],
                        [
                            'label' => '添加章节',
                            'url' => beAdminUrl('Doc.Project.addChapter', ['id' => $categoryId]),
                            'target' => 'drawer', // 'ajax - ajax请求 / dialog - 对话框窗口 / drawer - 抽屉 / self - 当前页面 / blank - 新页面'
                            'drawer' => [
                                'width' => '60%',
                            ],
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
                            'label' => '批量从此项目中移除',
                            'task' => 'fieldEdit',
                            'target' => 'ajax',
                            'confirm' => '确认要从此项目中移除吗？',
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

                    'items' => [
                        [
                            'driver' => TableItemSelection::class,
                            'width' => '50',
                        ],
                        [
                            'name' => 'image',
                            'label' => '封面图片',
                            'width' => '90',
                            'driver' => TableItemImage::class,
                            'ui' => [
                                'style' => 'max-width: 60px; max-height: 60px'
                            ],
                            'value' => function($row) {
                                if ($row['image'] === '') {
                                    return Be::getProperty('App.Doc')->getWwwUrl() . '/Template/Chapter/images/no-image.jpg';
                                }
                                return $row['image'];
                            },
                        ],
                        [
                            'name' => 'title',
                            'label' => '章节标题',
                            'driver' => TableItemLink::class,
                            'align' => 'left',
                            'url' => beAdminUrl('Doc.Chapter.chapters', ['task'=>'detail']),
                            'drawer' => [
                                'width' => '80%'
                            ],
                        ],
                    ],

                    'operation' => [
                        'label' => '操作',
                        'width' => '150',
                        'items' => [
                            [
                                'label' => '',
                                'tooltip' => '预览',
                                'url' => beAdminUrl('Doc.Chapter.preview'),
                                'target' => '_blank',
                                'ui' => [
                                    'type' => 'success',
                                    ':underline' => 'false',
                                    'style' => 'font-size: 20px;',
                                ],
                                'icon' => 'el-icon-view',
                            ],
                            [
                                'label' => '',
                                'tooltip' => '从此项目中移除',
                                'url' => beAdminUrl('Doc.Project.deleteChapter', ['id' => $categoryId]),
                                'confirm' => '确认要从此项目中移除么？',
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
        ])->execute();
    }

    /**
     * 指定项目下的章节 - 添加
     *
     * @BePermission("项目章节管理")
     */
    public function addChapter()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $categoryId = $request->get('id', '');
        $category = Be::getService('App.Doc.Admin.Project')->getProject($categoryId);

        $filter = [
            ['is_delete', '=', '0'],
        ];

        $chapterIds = Be::getTable('doc_chapter_category')
            ->where('category_id', $categoryId)
            ->getValues('chapter_id');
        if (count($chapterIds) > 0) {
            $filter[] = [
                'id', 'NOT IN', $chapterIds
            ];
        }

        Be::getAdminPlugin('Curd')->setting([
            'label' => '向项目 ' . $category->name . ' 添加章节',
            'table' => 'doc_chapter',
            'opLog' => false,
            'grid' => [
                'title' => '向项目 ' . $category->name . ' 添加章节',
                'theme' => 'Blank',

                'filter' => $filter,

                'form' => [
                    'items' => [
                        [
                            'name' => 'title',
                            'label' => '章节标题',
                        ],
                    ],
                ],

                'tableToolbar' => [
                    'items' => [
                        [
                            'label' => '添加到项目 ' . $category->name . ' 中',
                            'url' => beAdminUrl('Doc.Project.addChapterSave', ['id' => $categoryId]),
                            'target' => 'ajax',
                            'ui' => [
                                'icon' => 'el-icon-plus',
                                'type' => 'primary'
                            ]
                        ],
                    ]
                ],

                'table' => [

                    'items' => [
                        [
                            'driver' => TableItemSelection::class,
                            'width' => '50',
                        ],
                        [
                            'name' => 'title',
                            'label' => '章节标题',
                            'align' => 'left',
                        ],
                    ],
                ],
            ],
        ])->execute();
    }

    /**
     * 指定项目下的章节 - 添加
     *
     * @BePermission("项目章节管理")
     */
    public function addChapterSave()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        try {
            $categoryId = $request->get('id', '');
            $selectedRows = $request->json('selectedRows');
            if (!is_array($selectedRows) || count($selectedRows) == 0) {
                throw new ControllerException('请选择章节！');
            }

            $chapterIds = [];
            foreach ($selectedRows as $selectedRow) {
                $chapterIds[] = $selectedRow['id'];
            }

            Be::getService('App.Doc.Admin.Project')->addChapter($categoryId, $chapterIds);
            $response->set('success', true);
            $response->set('message', '编辑项目成功！');
            $response->set('callback', 'parent.closeDrawerAndReload();');
            $response->json();
        } catch (\Throwable $t) {
            $response->set('success', false);
            $response->set('message', $t->getMessage());
            $response->json();
        }
    }

    /**
     * 指定项目下的章节 - 删除
     *
     * @BePermission("项目章节管理")
     */
    public function deleteChapter()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();
        try {
            $categoryId = $request->get('id', '');
            $chapterIds = [];
            $postData = $request->json();
            if (isset($postData['selectedRows'])) {
                if (is_array($postData['selectedRows']) && count($postData['selectedRows']) > 0) {
                    foreach ($postData['selectedRows'] as $selectedRow) {
                        $chapterIds[] = $selectedRow['id'];
                    }
                }
            } elseif (isset($postData['row'])) {
                $chapterIds[] = $postData['row']['id'];
            }

            if (count($chapterIds) == 0) {
                throw new ControllerException('请选择章节！');
            }

            Be::getService('App.Doc.Admin.Project')->deleteChapter($categoryId, $chapterIds);
            $response->set('success', true);
            $response->set('message', '编辑项目成功！');
            $response->json();
        } catch (\Throwable $t) {
            $response->set('success', false);
            $response->set('message', $t->getMessage());
            $response->json();
        }
    }

}
