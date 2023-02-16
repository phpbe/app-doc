<be-head>
    <?php
    $wwwUrl = \Be\Be::getProperty('App.Doc')->getWwwUrl();

    if (strpos($this->chapter->description, '<pre ') !== false && strpos($this->chapter->description, '<code ') !== false) {
        ?>
        <link rel="stylesheet" href="<?php echo $wwwUrl; ?>/lib/highlight.js/11.5.1/default.min.css">
        <link rel="stylesheet" href="<?php echo $wwwUrl; ?>/lib/highlight.js/11.5.1/styles/atom-one-light.css?v=20220814">

        <script src="<?php echo $wwwUrl; ?>/lib/highlight.js/11.5.1/highlight.min.js"></script>

        <link rel="stylesheet" href="<?php echo $wwwUrl; ?>/lib/highlight.js/highlightjs-line-numbers.css">
        <script src="<?php echo $wwwUrl; ?>/lib/highlight.js/highlightjs-line-numbers.min.js"></script>

        <script src="<?php echo $wwwUrl; ?>/lib/clipboard/clipboard.min.js"></script>

        <link rel="stylesheet" href="<?php echo $wwwUrl; ?>/css/chapter/detail.code.css">
        <script src="<?php echo $wwwUrl; ?>/js/chapter/detail.code.js"></script>
        <?php
    }

    if (strpos($this->chapter->description, '<img ') !== false) {
        ?>
        <link rel="stylesheet" href="<?php echo $wwwUrl; ?>/lib/lightbox/2.11.3/css/lightbox.min.css">
        <script src="<?php echo $wwwUrl; ?>/lib/lightbox/2.11.3/js/lightbox.min.js"></script>
        <script>
            lightbox.option({
                albumLabel: "图像 %1 / %2"
            })
        </script>
        <?php
    }
    ?>

    <link rel="stylesheet" href="<?php echo $wwwUrl; ?>/css/chapter/detail.css?v=20220923">
    <style>
        <?php
        if ($this->configChapter->stickyMenuTopOffset > 0 || $this->configChapter->stickyMenuBottomOffset > 0) {
            $docMenuHeight = 'height: calc(100vh - ' . ($this->configChapter->stickyMenuTopOffset + $this->configChapter->stickyMenuBottomOffset) . 'px);';
        } else {
            $docMenuHeight = 'height: 100vh;';
        }
        echo '.doc-menu {'.$docMenuHeight.'}';
        echo '.doc-menu-toggle {'.$docMenuHeight.'}';
        ?>
    </style>

    <script>
        let stickyMenuTopOffset = <?php echo $this->configChapter->stickyMenuTopOffset; ?>;
        let stickyMenuBottomOffset = <?php echo $this->configChapter->stickyMenuBottomOffset; ?>;
    </script>
    <script src="<?php echo $wwwUrl; ?>/js/chapter/detail.js?v=20220923"></script>
</be-head>



<be-page-content>
    <div class="be-row doc-container" id="doc-container">

        <div class="be-col-auto doc-menu-container">
            <div class="doc-menu" id="doc-menu">
                <div class="doc-menu-title"><?php echo $this->project->title; ?></div>
                <?php
                if(isset($this->chapterTree) && is_array($this->chapterTree) && count($this->chapterTree) > 0) {
                    echo \Be\Be::getService('App.Doc.Chapter')->getChapterTreeMenu($this->chapterTree, $this->flatChapterTree, $this->chapter);
                }
                ?>
            </div>
        </div>

        <div class="be-col-auto doc-menu-toggle-container">
            <div class="be-col-auto doc-menu-toggle" id="doc-menu-toggle">
                <div class="doc-menu-toggle-on" id="doc-menu-toggle-on">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M6.776 1.553a.5.5 0 0 1 .671.223l3 6a.5.5 0 0 1 0 .448l-3 6a.5.5 0 1 1-.894-.448L9.44 8 6.553 2.224a.5.5 0 0 1 .223-.671z"/>
                    </svg>
                </div>

                <div class="doc-menu-toggle-off" id="doc-menu-toggle-off">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M9.224 1.553a.5.5 0 0 1 .223.67L6.56 8l2.888 5.776a.5.5 0 1 1-.894.448l-3-6a.5.5 0 0 1 0-.448l3-6a.5.5 0 0 1 .67-.223z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="be-col doc-content-container">
            <h1 class="be-h2 be-ta-center"><?php echo $this->chapter->title; ?></h1>

            <div class="be-mt-200 be-ta-center be-c-font-5">
                <span class="be-ml-100">更新时间：<?php echo date('Y年n月j日 H:i', strtotime($this->chapter->update_time)); ?></span>
                <span class="be-ml-100">浏览：<?php echo $this->chapter->hits; ?></span>
            </div>

            <div class="doc-content be-mt-200 be-lh-<?php echo $this->configChapter->detailLineHeight; ?> be-fs-<?php echo $this->configChapter->detailFontSize; ?>">
                <?php
                $hasAnchor = strpos($this->chapter->description, '<a href="#');
                if ($hasAnchor !== false) {
                    $url = \Be\Be::getRequest()->getUrl();
                    $pos = strpos($url, '#');
                    if ($pos !== false) {
                        $url = substr($url, 0, $pos);
                    }
                    $this->chapter->description = str_replace('<a href="#', '<a href="' . $url . '#', $this->chapter->description);
                }


                $hasImg = strpos($this->chapter->description, '<img ');
                if ($hasImg !== false) {
                    preg_match_all("/<img.*?src=\"(.*?)\".*?[\/]?>/", $this->chapter->description, $matches);
                    $i = 0;
                    foreach ($matches[0] as $image) {

                        $src = $matches[1][$i];

                        $alt = '';
                        if (preg_match("/alt=\"(.*?)\"/", $image, $match)) {
                            $alt = $match[1];
                        }

                        $replace = '<a href="'.$src.'" data-lightbox="doc-images" data-title="'.$alt.'">' . $image . '</a>';

                        $this->chapter->description = str_replace($image, $replace, $this->chapter->description);
                        $i++;
                    }
                }

                $hasChildrenTag = strpos($this->chapter->description, '{{children}}');
                if ($this->chapter->description === '' || $hasChildrenTag !== false ) {
                    $childrenHtml =  '<ul>';
                    foreach ($this->flatChapterTree as $chapter) {
                        if ($chapter->parent_id === $this->chapter->id) {
                            $childrenHtml .= '<li>';
                            $childrenHtml .= '<a href="' . $chapter->url . '">';
                            $childrenHtml .= $chapter->title;
                            $childrenHtml .= '</a>';
                            $childrenHtml .= '</li>';
                        }
                    }
                    $childrenHtml .= '</ul>';

                    if ($this->chapter->description === '') {
                        echo $childrenHtml;
                    } else {
                        echo str_replace('{{children}}', $childrenHtml, $this->chapter->description);
                    }
                } else {
                    echo $this->chapter->description;
                }
                ?>
            </div>
        </div>
    </div>
</be-page-content>