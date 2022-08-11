<be-head>
    <?php
    $wwwUrl = \Be\Be::getProperty('App.Doc')->getWwwUrl();
    ?>
    <link rel="stylesheet" href="<?php echo $wwwUrl; ?>/lib/highlight.js/11.5.1/default.min.css">
    <link rel="stylesheet" href="<?php echo $wwwUrl; ?>/lib/highlight.js/11.5.1/styles/atom-one-light.css">

    <script src="<?php echo $wwwUrl; ?>/lib/highlight.js/11.5.1/highlight.min.js"></script>

    <link rel="stylesheet" href="<?php echo $wwwUrl; ?>/lib/highlight.js/highlightjs-line-numbers.css">
    <script src="<?php echo $wwwUrl; ?>/lib/highlight.js/highlightjs-line-numbers.min.js"></script>

    <script src="<?php echo $wwwUrl; ?>/lib/clipboard/clipboard.min.js"></script>

    <link rel="stylesheet" href="<?php echo $wwwUrl; ?>/doc/css/chapter.css">
    <script src="<?php echo $wwwUrl; ?>/doc/js/chapter.js"></script>

    <style>
        .doc-menu {
            position: relative;
            top: 0;
            bottom: 0;
            width: 300px;
            height: calc(100vh - 60px);
            overflow-y: auto;
            background-color: #fafafa;
            user-select: none;
        }

        .doc-menu .menu-label {
            padding-right: 1rem;
            display: flex;
            align-items: center;
        }

        .doc-menu .menu-active>.menu-label {
            background-color: #eee;
        }

        .doc-menu .menu-label:hover {
            background-color: #f5f5f5;
            color: #333;
        }

        .doc-menu .menu-active .menu-label:hover {
            background-color: #eee;
        }

        .doc-menu .menu-label a {
            flex: 1;
            color: #666;
            text-decoration: none;
        }

        .doc-menu .icon {
            flex: 0 0 1rem;
            width: 1rem;
            height: 1rem;
            line-height: 1rem;
            vertical-align: middle;
            margin: .5rem;
        }

        .doc-menu .icon-open {
            border-left: .5em solid transparent;
            border-right: .5em solid transparent;
            border-top: .5em solid #999;
            border-bottom: .5em solid transparent;
            margin: .5rem .5rem 0 .5rem;
            cursor: pointer;
        }

        .doc-menu .icon-open:hover {
            border-top: .5em solid #666;
        }

        .doc-menu .icon-close {
            border-left: .5em solid #999;
            border-right: .5em solid transparent;
            border-top: .5em solid transparent;
            border-bottom: .5em solid transparent;
            margin: 0 0 0 1rem;
            cursor: pointer;
        }

        .doc-menu .icon-close:hover {
            border-left: .5em solid #666;
        }

        .doc-menu .menu-close ul {
            display: none;
        }

        .doc-menu ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .doc-menu li {
            line-height: 2rem;
            white-space: nowrap;
            overflow: hidden;
        }

        .doc-content {
            padding-left: 2rem;
        }

        .doc-content h4 {
            margin: .5rem 0;
        }

        .doc-content hr {
            border: 0;
            border-bottom: 1px solid #ddd;
        }
    </style>

    <script>

        let sessionStorageKey = "doc-menu-opened-node-ids-<?php echo $this->project->id; ?>"
        let docMenuOpenedNodeIds = sessionStorage.getItem(sessionStorageKey);
        if (docMenuOpenedNodeIds === null) {
            docMenuOpenedNodeIds = [];
        } else {
            docMenuOpenedNodeIds = docMenuOpenedNodeIds.split(",");
        }

        $(function (){
            //sessionStorage.setItem(sessionStorageKey, "");

            $(".doc-menu .icon").click(function () {
                let $li = $(this).closest("li");
                let chapterId = $li.data("id");
                if ($(this).hasClass("icon-close")) {
                    $(this).removeClass("icon-close").addClass("icon-open");
                    $li.removeClass("menu-close").addClass("menu-open");

                    if (docMenuOpenedNodeIds.indexOf(chapterId) === -1) {
                        docMenuOpenedNodeIds.push(chapterId);
                    }
                } else if ($(this).hasClass("icon-open")) {
                    $(this).removeClass("icon-open").addClass("icon-close");
                    $li.removeClass("menu-open").addClass("menu-close");

                    let pos = docMenuOpenedNodeIds.indexOf(chapterId);
                    if (pos > 0) {
                        docMenuOpenedNodeIds.splice(pos, 1);
                    }
                }

                sessionStorage.setItem(sessionStorageKey, docMenuOpenedNodeIds.join(","));
            })

            if (docMenuOpenedNodeIds.length > 0) {
                for(let docMenuOpenedNodeId of docMenuOpenedNodeIds) {
                    let $li = $("#node-"+docMenuOpenedNodeId);
                    if ($li.hasClass("menu-close")) {
                        $li.removeClass("menu-close").addClass("menu-open");

                        let $icon = $li.children(".menu-label").children(".icon");
                        if ($icon.hasClass("icon-close")) {
                            $icon.removeClass("icon-close").addClass("icon-open");
                        }
                    }
                }
            }


            let $docMenu = $("#doc-menu");
            let docMenuPosition1 = $docMenu.offset().top - 30;
            let docMenuHeight = $(window).height() - 60; // calc(100vh-60px)
            let docContainerHeight = $("#doc-container").height();
            let docMenuPosition2 = docMenuPosition1 + docContainerHeight - docMenuHeight;
            let docMenuFixed = false;

            $(window).scroll(function (){
                let scrollTop = $(this).scrollTop();
                if (scrollTop >= docMenuPosition1 && scrollTop <= docMenuPosition2) {
                    if (!docMenuFixed) {
                        $docMenu.css({
                            position: "fixed",
                            top: "30px",
                            bottom: "30px",
                            transform: ''
                        });
                        docMenuFixed = true;
                    }
                } else {
                    if (docMenuFixed) {
                        $docMenu.css({
                            position: "relative",
                            top: "0",
                            bottom: "0",
                            height: docMenuHeight + "px",
                            transform: ''
                        });

                        docMenuFixed = false;
                    }

                    if (scrollTop < docMenuPosition1) {
                        $docMenu.css({transform: ''});
                    } else  {
                        $docMenu.css({transform: 'translateY(' + (docMenuPosition2 - docMenuPosition1) + 'px)'});
                    }
                }
            });

            $(window).resize(function (){
                docMenuHeight = $(window).height() - 60;
                docMenuPosition2 = docMenuPosition1 + docContainerHeight - docMenuHeight;
            });
        });

    </script>
</be-head>



<be-page-content>
    <div class="be-row" id="doc-container">
        <div class="be-col-auto" style="width: 300px;">
            <div class="doc-menu" id="doc-menu">
                <div class="be-fw-bold be-p-50 be-bc-eee be-mb-50"><?php echo $this->project->title; ?></div>
                <?php
                if(isset($this->chapterTree) && is_array($this->chapterTree) && count($this->chapterTree) > 0) {
                    echo \Be\Be::getService('App.Doc.Section')->getDocMenu($this->chapterTree, $this->flatChapterTree, $this->chapter);
                }
                ?>
            </div>
        </div>

        <div class="be-col">
            <h1 class="be-h2 be-ta-center"><?php echo $this->chapter->title; ?></h1>

            <div class="doc-content be-mt-200  be-lh-<?php echo $this->configChapter->detailLineHeight; ?> be-fs-<?php echo $this->configChapter->detailFontSize; ?>">
                <?php echo $this->chapter->description; ?>
            </div>
        </div>
    </div>
</be-page-content>