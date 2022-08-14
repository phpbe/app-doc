<?php

namespace Be\App\Doc\Service\Admin;

use Be\App\ServiceException;
use Be\Be;
use Be\Util\Str\Pinyin;

class Project
{

    public function getIdTitleKeyValues()
    {
        $sql = 'SELECT id, title FROM doc_project WHERE is_delete = 0';
        return Be::getDb()->getKeyValues($sql);
    }

    /**
     * 获取项目
     *
     * @param string $projectId
     * @return \stdClass
     * @throws ServiceException
     * @throws \Be\Db\DbException
     * @throws \Be\Runtime\RuntimeException
     */
    public function getProject(string $projectId): object
    {
        $sql = 'SELECT * FROM doc_project WHERE id=? AND is_delete = 0';
        $project = Be::getDb()->getObject($sql, [$projectId]);
        if (!$project) {
            throw new ServiceException('项目（# ' . $projectId . '）不存在！');
        }

        $project->url_custom = (int)$project->url_custom;
        $project->seo_title_custom = (int)$project->seo_title_custom;
        $project->seo_description_custom = (int)$project->seo_description_custom;
        $project->ordering = (int)$project->ordering;
        $project->hits = (int)$project->hits;
        $project->chapter_toggle_editor = (int)$project->chapter_toggle_editor;

        return $project;
    }

    /**
     * 编辑项目
     *
     * @param array $data 项目数据
     * @return object
     * @throws \Throwable
     */
    public function edit(array $data): object
    {
        $db = Be::getDb();

        $isNew = true;
        $projectId = null;
        if (isset($data['id']) && is_string($data['id']) && $data['id'] !== '') {
            $isNew = false;
            $projectId = $data['id'];
        }

        $tupleProject = Be::getTuple('doc_project');
        if (!$isNew) {
            try {
                $tupleProject->load($projectId);
            } catch (\Throwable $t) {
                throw new ServiceException('项目（# ' . $projectId . '）不存在！');
            }

            if ($tupleProject->is_delete === 1) {
                throw new ServiceException('项目（# ' . $projectId . '）不存在！');
            }
        }

        if (!isset($data['title']) || !is_string($data['title'])) {
            throw new ServiceException('项目标题未填写！');
        }
        $title = $data['title'];

        if (!isset($data['description']) || !is_string($data['description'])) {
            $data['description'] = '';
        }

        if (!isset($data['url_custom']) || $data['url_custom'] !== 1) {
            $data['url_custom'] = 0;
        }

        $url = null;
        if (!isset($data['url']) || !is_string($data['url'])) {
            $urlTitle = strtolower($title);
            $url = Pinyin::convert($urlTitle, '-');
            if (strlen($url) > 200) {
                $url = Pinyin::convert($urlTitle, '-', true);
                if (strlen($url) > 200) {
                    $url = Pinyin::convert($urlTitle, '', true);
                }
            }

            $data['url_custom'] = 0;

        } else {
            $url = $data['url'];
        }
        $urlUnique = $url;
        $urlIndex = 0;
        $urlExist = null;
        do {
            if ($isNew) {
                $urlExist = Be::getTable('doc_project')
                        ->where('url', $urlUnique)
                        ->getValue('COUNT(*)') > 0;
            } else {
                $urlExist = Be::getTable('doc_project')
                        ->where('url', $urlUnique)
                        ->where('id', '!=', $projectId)
                        ->getValue('COUNT(*)') > 0;
            }

            if ($urlExist) {
                $urlIndex++;
                $urlUnique = $url . '-' . $urlIndex;
            }
        } while ($urlExist);
        $url = $urlUnique;

        if (!isset($data['seo_title']) || !is_string($data['seo_title'])) {
            $data['seo_title'] = $title;
        }

        if (!isset($data['seo_title_custom']) || $data['seo_title_custom'] !== 1) {
            $data['seo_title_custom'] = 0;
        }

        if (!isset($data['seo_description']) || !is_string($data['seo_description'])) {
            $data['seo_description'] = $data['description'];
        }

        if (!isset($data['seo_description_custom']) || $data['seo_description_custom'] !== 1) {
            $data['seo_description_custom'] = 0;
        }

        if (!isset($data['seo_keywords']) || !is_string($data['seo_keywords'])) {
            $data['seo_keywords'] = '';
        }

        if (!isset($data['chapter_default_editor']) || !is_string($data['chapter_default_editor']) || !in_array($data['chapter_default_editor'], ['markdown', 'tinymce'])) {
            $data['chapter_default_editor'] = 'markdown';
        }

        if (!isset($data['chapter_toggle_editor']) || !is_numeric($data['chapter_default_editor']) || $data['chapter_default_editor'] !== 1) {
            $data['chapter_toggle_editor'] = 1;
        }

        if (!isset($data['ordering']) || !is_numeric($data['ordering'])) {
            $data['ordering'] = 0;
        }

        $db->startTransaction();
        try {
            $now = date('Y-m-d H:i:s');
            $tupleProject->title = $title;
            $tupleProject->description = $data['description'];
            $tupleProject->url = $url;
            $tupleProject->url_custom = $data['url_custom'];
            $tupleProject->seo_title = $data['seo_title'];
            $tupleProject->seo_title_custom = $data['seo_title_custom'];
            $tupleProject->seo_description = $data['seo_description'];
            $tupleProject->seo_description_custom = $data['seo_description_custom'];
            $tupleProject->chapter_default_editor = $data['chapter_default_editor'];
            $tupleProject->chapter_toggle_editor = $data['chapter_toggle_editor'];
            $tupleProject->seo_keywords = $data['seo_keywords'];
            $tupleProject->ordering = $data['ordering'];
            $tupleProject->update_time = $now;
            if ($isNew) {
                $tupleProject->hits = 0;
                $tupleProject->is_delete = 0;
                $tupleProject->create_time = $now;
                $tupleProject->insert();
            } else {
                $tupleProject->update();
            }

            $db->commit();

        } catch (\Throwable $t) {
            $db->rollback();
            Be::getLog()->error($t);

            throw new ServiceException(($isNew ? '新建' : '编辑') . '项目发生异常！');
        }

        Be::getService('App.System.Task')->trigger('Doc.ProjectSyncCache');

        return $tupleProject->toObject();
    }

    /**
     * 删除项目
     *
     * @param array $projectIds
     * @return void
     * @throws ServiceException
     * @throws \Be\Db\DbException
     * @throws \Be\Runtime\RuntimeException
     */
    public function delete(array $projectIds)
    {
        if (count($projectIds) === 0) return;

        $db = Be::getDb('shopfai');
        $db->startTransaction();
        try {
            $now = date('Y-m-d H:i:s');
            foreach ($projectIds as $projectId) {
                $tupleProject = Be::getTuple('doc_project');
                try {
                    $tupleProject->loadBy([
                        'id' => $projectId,
                        'is_delete' => 0
                    ]);
                } catch (\Throwable $t) {
                    throw new ServiceException('项目（# ' . $projectId . '）不存在！');
                }

                Be::getTable('doc_chapter')
                    ->where('project_id', $projectId)
                    ->update(['is_delete' => 0, 'update_time' => $now]);

                $tupleProject->url = $projectId;
                $tupleProject->is_delete = 1;
                $tupleProject->update_time = $now;
                $tupleProject->update();
            }

            $db->commit();

        } catch (\Throwable $t) {
            $db->rollback();
            Be::getLog()->error($t);

            throw new ServiceException('删除项目发生异常！');
        }

        Be::getService('App.System.Task')->trigger('Doc.ProjectSyncCache');
        Be::getService('App.System.Task')->trigger('Doc.ChapterSyncEsAndCache');
    }

    /**
     * 获取菜单参数选择器
     *
     * @return array
     */
    public function getProjectMenuPicker(): array
    {
        return [
            'name' => 'project_id',
            'field' => 'id',
            'value' => '项目：{title}',
            'table' => 'doc_project',
            'grid' => [
                'title' => '选择一个项目',

                'filter' => [
                    ['is_delete', '=', '0'],
                ],

                'form' => [
                    'items' => [
                        [
                            'name' => 'title',
                            'label' => '项目标题',
                        ],
                    ],
                ],

                'table' => [

                    // 未指定时取表的所有字段
                    'items' => [
                        [
                            'name' => 'title',
                            'label' => '项目标题',
                            'align' => 'left'
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
                ],
            ]
        ];
    }
}
