<be-head>
    <?php
    $wwwUrl = \Be\Be::getProperty('App.Doc')->getWwwUrl();
    ?>
    <link rel="stylesheet" href="<?php echo $wwwUrl; ?>/lib/highlight.js/11.5.1/default.min.css">
    <link rel="stylesheet" href="<?php echo $wwwUrl; ?>/lib/highlight.js/11.5.1/styles/atom-one-light.css?v=20220814">

    <script src="<?php echo $wwwUrl; ?>/lib/highlight.js/11.5.1/highlight.min.js"></script>

    <link rel="stylesheet" href="<?php echo $wwwUrl; ?>/lib/highlight.js/highlightjs-line-numbers.css">
    <script src="<?php echo $wwwUrl; ?>/lib/highlight.js/highlightjs-line-numbers.min.js"></script>

    <script src="<?php echo $wwwUrl; ?>/lib/clipboard/clipboard.min.js"></script>

    <link rel="stylesheet" href="<?php echo $wwwUrl; ?>/css/chapter/detail.css?v=20220814">
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
    <script src="<?php echo $wwwUrl; ?>/js/chapter/detail.js?v=20220814"></script>
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-compact-right" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M6.776 1.553a.5.5 0 0 1 .671.223l3 6a.5.5 0 0 1 0 .448l-3 6a.5.5 0 1 1-.894-.448L9.44 8 6.553 2.224a.5.5 0 0 1 .223-.671z"/>
                    </svg>
                </div>

                <div class="doc-menu-toggle-off" id="doc-menu-toggle-off">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-compact-left" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M9.224 1.553a.5.5 0 0 1 .223.67L6.56 8l2.888 5.776a.5.5 0 1 1-.894.448l-3-6a.5.5 0 0 1 0-.448l3-6a.5.5 0 0 1 .67-.223z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="be-col doc-content-container">
            <h1 class="be-h2 be-ta-center"><?php echo $this->chapter->title; ?></h1>

            <div class="doc-content be-mt-200 be-lh-<?php echo $this->configChapter->detailLineHeight; ?> be-fs-<?php echo $this->configChapter->detailFontSize; ?>">
                <?php echo $this->chapter->description; ?>
            </div>
        </div>
    </div>
</be-page-content>