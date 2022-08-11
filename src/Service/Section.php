<?php

namespace Be\App\Doc\Service;


class Section
{


    /**
     * 生成文档树界面
     *
     * @param array $chapterTree
     * @return string
     */
    public function getDocMenu(array $chapterTree, array $flatChapterTree, object $chapter): string
    {
        $parentIdMapping = [];
        foreach ($flatChapterTree as $x) {
            $parentIdMapping[$x->id] = $x->parent_id;
        }

        $openNodeIds = [];
        $parentId = $chapter->parent_id;
        while ($parentId !== '') {
            $openNodeIds[] = $parentId;
            $parentId = $parentIdMapping[$parentId] ?? '';
        }

        return $this->makeDocMenu($chapterTree, $openNodeIds, $chapter->id, 0);
    }

    /**
     * 生成文档树界面
     *
     * @param array $chapterTree
     * @param array $openNodeIds
     * @param string $chapterId
     * @param int $level
     * @return string
     */
    public function makeDocMenu(array $chapterTree, array $openNodeIds, string $chapterId, int $level = 0): string
    {
        $html = '<ul class="doc-menu-ul">';
        foreach ($chapterTree as $chapter) {
            $childrenCount = count($chapter->children);

            $html .= '<li class="';
            if ($childrenCount > 0) {
                $html .= in_array($chapter->id, $openNodeIds) ? ' menu-open' : ' menu-close';
            }

            if ($chapterId === $chapter->id) {
                $html .= ' menu-active';
            }

            $html .= '" id="node-' . $chapter->id . '" data-id="' . $chapter->id . '">';

            $html .= '<div class="menu-label"';
            if ($level > 0) {
                $html .= 'style="padding-left: ' . $level . 'rem"';
            }
            $html .= '>';

            $html .= '<i class="icon';
            if ($childrenCount > 0) {
                $html .= in_array($chapter->id, $openNodeIds) ? ' icon-open' : ' icon-close';
            }
            $html .= '"></i>';

            $html .= '<a href="' . $chapter->url . '">';
            $html .= $chapter->title;
            $html .= '</a>';

            $html .= '</div>';

            if ($childrenCount > 0) {
                $html .= $this->makeDocMenu($chapter->children, $openNodeIds, $chapterId, $level + 1);
            }

            $html .= '</li>';
        }
        $html .= '</ul>';

        return $html;
    }


}
