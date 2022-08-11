<?php

namespace Be\App\Doc\Service\Admin;


use Be\App\ServiceException;
use Be\Be;
use Be\Util\Str\Pinyin;

class Chapter
{

    /**
     * 获取项目文档
     *
     * @param string $chapterId
     * @return object
     * @throws ServiceException
     * @throws \Be\Db\DbException
     * @throws \Be\Runtime\RuntimeException
     */
    public function getChapter(string $chapterId): object
    {
        $sql = 'SELECT * FROM doc_chapter WHERE id=? AND is_delete = 0';
        $chagpter = Be::getDb()->getObject($sql, [$chapterId]);
        if (!$chagpter) {
            throw new ServiceException('文档（# ' . $chapterId . '）不存在！');
        }

        $chagpter->url_custom = (int)$chagpter->url_custom;
        $chagpter->seo_title_custom = (int)$chagpter->seo_title_custom;
        $chagpter->seo_description_custom = (int)$chagpter->seo_description_custom;
        $chagpter->ordering = (int)$chagpter->ordering;
        $chagpter->hits = (int)$chagpter->hits;
        $chagpter->is_enable = (int)$chagpter->is_enable;

        return $chagpter;
    }

    /**
     * 获取章节树
     * @param string $projectId
     * @return void
     */
    public function getChapterTree(string $projectId): array
    {
        $chapters = Be::getTable('doc_chapter')
            ->where('project_id', $projectId)
            ->where('is_delete', 0)
            ->orderBy('ordering ASC')
            ->getObjects('id, parent_id, title as label');

        return $this->createChapterTree($chapters);
    }

    /**
     * 生成章节树
     *
     * @param array $chapters
     * @param string $parentId
     * @return array
     */
    private function createChapterTree(array $chapters, string $parentId = '')
    {
        $children = [];
        foreach ($chapters as $chapter) {
            if ($chapter->parent_id === $parentId) {
                $chapter->children = $this->createChapterTree($chapters, $chapter->id);
                $children[] = $chapter;
            }
        }
        return $children;
    }

    /**
     * 新建项目文档
     *
     * @param array $data
     */
    public function addChapter(array $data): object
    {
        if (!isset($data['project_id']) || !is_string($data['project_id'])) {
            throw new ServiceException('参数（项目ID）缺失!');
        }

        $tupleProject = Be::getTuple('doc_project');
        try {
            $tupleProject->load($data['project_id']);
        } catch (\Throwable $t) {
            throw new ServiceException('项目（#' . $data['project_id'] . '）不存在!');
        }

        if (!isset($data['parent_id']) || !is_string($data['parent_id'])) {
            $data['parent_id'] = '';
        }

        if (!isset($data['title']) || !is_string($data['title'])) {
            $data['title'] = '新建文档';
        }

        if ($data['parent_id'] !== '') {
            $tupleParent = Be::getTuple('doc_chapter');
            try {
                $tupleParent->load($data['parent_id']);
            } catch (\Throwable $t) {
                throw new ServiceException('父文档（#' . $data['parent_id'] . '）不存在!');
            }

            if ($tupleParent->project_id !== $data['project_id']) {
                throw new ServiceException('父文档（#' . $data['parent_id'] . '）不需于当前项目!');
            }
        }

        $ordering = Be::getTable('doc_chapter')
            ->where('project_id', $data['project_id'])
            ->where('parent_id', $data['parent_id'])
            ->max('ordering');
        if ($ordering === null) {
            $ordering = 0;
        } else {
            $ordering = (int)$ordering;
        }
        $ordering++;

        $tuple = Be::getTuple('doc_chapter');
        $tuple->project_id = $data['project_id'];
        $tuple->parent_id = $data['parent_id'];
        $tuple->title = $data['title'];
        $tuple->description = "\n\n";
        $tuple->description_markdown = "\n\n";
        $tuple->editor = $tupleProject->chapter_default_editor;
        $tuple->url = $data['parent_id'] . '-' . $ordering;
        $tuple->url_custom = 0;
        $tuple->seo_title = $data['title'];
        $tuple->seo_title_custom = 0;
        $tuple->seo_description = '';
        $tuple->seo_description_custom = 0;
        $tuple->seo_keywords = '';
        $tuple->ordering = $ordering;
        $tuple->hits = 0;
        $tuple->is_enable = 0;
        $tuple->is_delete = 0;
        $tuple->create_time = date('Y-m-d H:i:s');
        $tuple->update_time = date('Y-m-d H:i:s');
        $tuple->insert();

        return $tuple->toObject();
    }

    /**
     * 新建项目文档
     *
     * @param array $data
     */
    public function saveChapter(array $data): object
    {
        if (!isset($data['id']) || !is_string($data['id'])) {
            throw new ServiceException('参数（id）缺失!');
        }

        $tuple = Be::getTuple('doc_chapter');
        try {
            $tuple->load($data['id']);
        } catch (\Throwable $t) {
            throw new ServiceException('文档（#' . $data['id'] . '）不存在!');
        }

        if (!isset($data['project_id']) || !is_string($data['project_id'])) {
            throw new ServiceException('参数（项目ID）缺失!');
        }

        $tupleProject = Be::getTuple('doc_project');
        try {
            $tupleProject->load($data['project_id']);
        } catch (\Throwable $t) {
            throw new ServiceException('项目（#' . $data['project_id'] . '）不存在!');
        }

        if (!isset($data['title']) || !is_string($data['title'])) {
            $data['title'] = '未填写';
        }

        if (!isset($data['description']) || !is_string($data['description'])) {
            $data['description'] = '';
        }

        if (!isset($data['description_markdown']) || !is_string($data['description_markdown'])) {
            $data['description_markdown'] = '';
        }

        if (!isset($data['editor']) || !is_string($data['editor'])) {
            $data['editor'] = $tupleProject->chapter_default_editor;
        }

        if (!isset($data['url_custom']) || $data['url_custom'] !== 1) {
            $data['url_custom'] = 0;
        }

        $url = null;
        if (!isset($data['url']) || !is_string($data['url'])) {
            $urlTitle = strtolower($data['title']);
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
            $urlExist = Be::getTable('doc_chapter')
                    ->where('project_id', '=', $data['project_id'])
                    ->where('id', '!=', $data['id'])
                    ->where('url', $urlUnique)
                    ->getValue('COUNT(*)') > 0;

            if ($urlExist) {
                $urlIndex++;
                $urlUnique = $url . '-' . $urlIndex;
            }
        } while ($urlExist);
        $url = $urlUnique;

        if (!isset($data['seo_title']) || !is_string($data['seo_title'])) {
            $data['seo_title'] = $data['title'];
        }

        if (!isset($data['seo_title_custom']) || !is_numeric($data['seo_title_custom']) || $data['seo_title_custom'] !== 1) {
            $data['seo_title_custom'] = 0;
        }

        if (!isset($data['seo_description']) || !is_string($data['seo_description'])) {
            $data['seo_description'] = '';
        }

        if (!isset($data['seo_description_custom']) || !is_numeric($data['seo_description_custom']) || $data['seo_description_custom'] !== 1) {
            $data['seo_description_custom'] = 0;
        }

        if (!isset($data['seo_keywords']) || !is_string($data['seo_keywords'])) {
            $data['seo_keywords'] = '';
        }

        if (!isset($data['is_enable']) || !is_numeric($data['is_enable'])) {
            $data['is_enable'] = 0;
        }

        $tuple->project_id = $data['project_id'];
        $tuple->title = $data['title'];
        $tuple->description = $data['description'];
        $tuple->description_markdown = $data['description_markdown'];
        $tuple->editor = $data['editor'];
        $tuple->url = $url;
        $tuple->url_custom = $data['url_custom'];
        $tuple->seo_title = $data['seo_title'];
        $tuple->seo_title_custom = $data['seo_title_custom'];
        $tuple->seo_description = $data['seo_description'];
        $tuple->seo_description_custom = $data['seo_description_custom'];
        $tuple->seo_keywords = $data['seo_keywords'];
        $tuple->is_enable = $data['is_enable'];
        $tuple->is_delete = 0;
        $tuple->update_time = date('Y-m-d H:i:s');
        $tuple->update();

        Be::getService('App.System.Task')->trigger('Doc.ChapterSyncEsAndCache');

        return $tuple->toObject();
    }

    /**
     * 项目文档排序
     *
     * @param array $data
     */
    public function sortChapter(array $data)
    {
        $db = Be::getDb();
        $db->startTransaction();
        try {
            $i = 1;
            foreach ($data as $x) {

                if (!isset($x['id'])) {
                    throw new ServiceException('第' . $i . '组数据的参数（id）缺失!');
                }

                if (!isset($x['ordering']) || !is_numeric($x['ordering'])) {
                    throw new ServiceException('第' . $i . '组数据的参数（ordering）缺失!');
                }

                $tuple = Be::getTuple('doc_chapter');
                try {
                    $tuple->load($x['id']);
                } catch (\Throwable $t) {
                    throw new ServiceException('文档（#' . $x['id'] . '）不存在!');
                }

                if (isset($x['parent_id'])) {
                    $tuple->parent_id = $x['parent_id'];
                }

                $tuple->ordering = $x['ordering'];
                $tuple->update_time = date('Y-m-d H:i:s');
                $tuple->update();

                $i++;
            }

            $db->commit();
        } catch (\Throwable $t) {
            $db->rollback();

            throw $t;
        }

        Be::getService('App.System.Task')->trigger('Doc.ChapterSyncEsAndCache');
    }

    /**
     * 删除文档
     *
     * @param string $chapterId 文档ID
     * @return bool
     */
    public function deleteChapter(string $chapterId): bool
    {
        $tuple = Be::getTuple('doc_chapter');
        try {
            $tuple->load($chapterId);
        } catch (\Throwable $t) {
            throw new ServiceException('文档（#' . $chapterId . '）不存在!');
        }

        if (Be::getTable('doc_chapter')
                ->where('project_id', $tuple->project_id)
                ->where('parent_id', $chapterId)
                ->where('is_delete', 0)
                ->count() > 0) {
            throw new ServiceException('删除子文档后方可删除父文档!');
        }

        $tuple->url = $tuple->id;
        $tuple->is_delete = 1;
        $tuple->update_time = date('Y-m-d H:i:s');
        $tuple->update();

        Be::getService('App.System.Task')->trigger('Doc.ChapterSyncEsAndCache');

        return true;
    }

}
