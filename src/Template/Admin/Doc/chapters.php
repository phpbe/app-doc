<be-head>
    <?php
    $wwwUrl = \Be\Be::getProperty('App.Doc')->getWwwUrl();
    ?>
    <script src="<?php echo $wwwUrl; ?>/admin/js/pinyin.js"></script>

    <?php
    if ($this->project->chapter_toggle_editor || $this->project->chapter_default_editor === 'tinymce') {
        ?>
        <script src="<?php echo $wwwUrl; ?>/admin/js/turndown.js"></script>
        <script src="<?php echo $wwwUrl; ?>/admin/js/turndown-plugin-gfm.js"></script>
        <?php
    }
    ?>

    <link rel="stylesheet" href="<?php echo $wwwUrl; ?>/admin/css/chapters.css">
</be-head>


<be-body>
    <?php
    $formData = [];
    $uiItems = new \Be\AdminPlugin\UiItem\UiItems();
    $rootUrl = \Be\Be::getRequest()->getRootUrl();
    ?>

    <div id="app" v-cloak>
        <div class="left-side" :style="{width: leftWidth + 'px'}">
            <div class="left-side-tree">
                <el-tree
                        ref="chapterTree"
                        :data="chapterTree"
                        node-key="id"
                        @node-click="(data) => editChapter(data.id)"
                        @node-drag-end="sortChapter"
                        :expand-on-click-node="false"
                        default-expand-all
                        highlight-current
                        draggable>
                <span class="custom-tree-node" slot-scope="{ node, data }">
                    <span>{{ node.label }}</span>
                    <span>
                      <el-link
                              type="primary"
                              size="mini"
                              icon="el-icon-plus"
                              @click.stop="() => addChapter(data.id)">
                      </el-link>
                      <el-link
                              type="danger"
                              size="mini"
                              icon="el-icon-close"
                              :disabled="data.children.length > 0"
                              @click.stop="() => deleteChapter(data.id)">
                      </el-link>
                    </span>
                  </span>
                </el-tree>
            </div>
            <div class="left-side-op be-ta-center">
                <el-button type="primary" size="mini" icon="el-icon-plus" @click="addChapter('')">新建文档</el-button>
            </div>
        </div>

        <div id="left-resize" :style="{left: leftWidth + 'px'}"></div>

        <div class="right-side" :style="{marginLeft: (leftWidth + 10) + 'px'}">
            <el-form ref="formRef" :model="formData">
                <div class="be-row">
                    <div class="be-col">
                        <el-input
                                type="text"
                                placeholder="请输入名称"
                                v-model = "formData.title"
                                size="medium"
                                maxlength="120"
                                show-word-limit
                                @change="titleUpdate">
                        </el-input>
                    </div>
                    <div class="be-col-auto">
                        <div class="be-pl-200 be-pt-50">
                            发布：
                        </div>
                    </div>
                    <div class="be-col-auto">
                        <div class="be-pt-50">
                            <el-switch v-model.number="formData.is_enable" :active-value="1" :inactive-value="0" size="medium"></el-switch>
                        </div>
                    </div>
                     <div class="be-col-auto">
                        <div class="be-pl-200 be-pt-50">
                            <el-link type="primary" @click="drawerSeo=true">SEO设置</el-link>
                        </div>
                    </div>

                    <?php
                    if ($this->project->chapter_toggle_editor) {
                        ?>
                        <div class="be-col-auto">
                            <div class="be-pl-200 be-pt-50">
                                <el-switch
                                        v-model="formData.editor"
                                        active-text="Markdown"
                                        active-color="#13ce66"
                                        active-value="markdown"
                                        inactive-text="Tinymce"
                                        inactive-color="#409eff"
                                        inactive-value="tinymce"
                                        @change="toggleEditor">
                                </el-switch>
                            </div>
                        </div>
                        <?php
                    }
                    ?>

                    <div class="be-col-auto">
                        <div class="be-pl-200">
                            <el-button size="medium" type="primary" :disabled="loading || formData.id === ''" @click="saveChapter(false)">保存</el-button>
                        </div>
                    </div>
                </div>

                <?php
                if ($this->project->chapter_toggle_editor || $this->project->chapter_default_editor === 'tinymce') {
                    $driver = new \Be\AdminPlugin\Form\Item\FormItemTinymce([
                        'name' => 'description',
                        'ui' => [
                            'form-item' => [
                                'class' => 'be-mt-50',
                                'v-show' => 'formData.editor === \'tinymce\''
                            ],
                            '@change' => 'descriptionUpdate'
                        ],
                        'option' => [
                            'toolbar_sticky' => false, // 工具栏浮动
                            'height' => 500,
                            'min_height' => 500,
                            'max_height' => 500,
                        ],

                        'layout' => 'full',
                    ]);
                    echo $driver->getHtml();

                    $uiItems->add($driver);
                }

                if ($this->project->chapter_toggle_editor || $this->project->chapter_default_editor === 'markdown') {
                    $driver = new \Be\AdminPlugin\Form\Item\FormItemMarkdown([
                        'name' => 'description_markdown',
                        'ui' => [
                            'form-item' => [
                                'class' => 'be-mt-50',
                                'v-show' => 'formData.editor === \'markdown\''
                            ],
                            '@change' => 'descriptionMarkdownUpdate'
                        ],
                        'option' => [
                                'saveHTMLToTextarea' => true,
                        ]
                    ]);
                    echo $driver->getHtml();

                    $uiItems->add($driver);
                }
                ?>
            </el-form>
        </div>

        <el-drawer
                :visible.sync="drawerSeo"
                title="搜索引擎优化"
                size="40%"
                :wrapper-closable="false"
                :destroy-on-close="true">

            <div class="be-px-150">
                <div class="be-row">
                    <div class="be-col-auto">
                        SEO标题
                        <el-tooltip effect="dark" content="标题是SEO最重要的部分，该标题会显示在搜索引擎的搜索结果中。" placement="top">
                            <i class="el-icon-fa fa-question-circle-o"></i>
                        </el-tooltip>：
                    </div>
                    <div class="be-col">
                        <div class="be-pl-100">
                            <el-switch v-model.number="formData.seo_title_custom" :active-value="1" :inactive-value="0" inactive-text="自动生成" active-text="自定义" size="medium" @change="seoUpdate"></el-switch>
                        </div>
                    </div>
                </div>
                <el-input
                        class="be-mt-50"
                        type="text"
                        placeholder="请输入SEO标题"
                        v-model = "formData.seo_title"
                        size="medium"
                        maxlength="120"
                        show-word-limit
                        :disabled="formData.seo_title_custom === 0">
                </el-input>

                <div class="be-row be-mt-150">
                    <div class="be-col-auto">
                        SEO描述
                        <el-tooltip effect="dark" content="这是该文章分类的整体SEO描述，使文章分类在搜索引擎中获得更高的排名。" placement="top">
                            <i class="el-icon-fa fa-question-circle-o"></i>
                        </el-tooltip>：
                    </div>
                    <div class="be-col">
                        <div class="be-pl-100">
                            <el-switch v-model.number="formData.seo_description_custom" :active-value="1" :inactive-value="0" inactive-text="自动生成" active-text="自定义" size="medium" @change="seoUpdate"></el-switch>
                        </div>
                    </div>
                </div>
                <el-input
                        class="be-mt-50"
                        type="textarea"
                        :rows="6"
                        placeholder="请输入SEO描述"
                        v-model = "formData.seo_description"
                        size="medium"
                        maxlength="500"
                        show-word-limit
                        :disabled="formData.seo_description_custom === 0">
                </el-input>

                <div class="be-row be-mt-150">
                    <div class="be-col-auto">
                        SEO友好链接：
                    </div>
                    <div class="be-col">
                        <div class="be-pl-100">
                            <el-switch v-model.number="formData.url_custom" :active-value="1" :inactive-value="0" inactive-text="自动生成" active-text="自定义" size="medium" @change="seoUpdate"></el-switch>
                        </div>
                    </div>
                </div>
                <el-input
                        class="be-mt-50"
                        type="text"
                        placeholder="请输入SEO友好链接"
                        v-model = "formData.url"
                        size="medium"
                        maxlength="200"
                        show-word-limit
                        :disabled="formData.url_custom === 0">
                    <template slot="prepend"><?php echo $rootUrl; ?>/doc/</template>
                </el-input>

                <div class="be-mt-150">
                    SEO关键词
                    <el-tooltip effect="dark" content="关键词可以提高搜索结果排名，建议1-2个关键词即可，堆砌关键词可能会降低排名！" placement="top">
                        <i class="el-icon-fa fa-question-circle-o"></i>
                    </el-tooltip>
                </div>
                <el-input
                        class="be-mt-50"
                        type="text"
                        placeholder="请输入SEO关键词，多个关键词以逗号分隔。"
                        v-model = "formData.seo_keywords"
                        size="medium"
                        maxlength="60">
                </el-input>

                <div class="be-mt-150 be-ta-right">
                    <el-button size="medium" type="primary" @click="drawerSeo=false">确定</el-button>
                </div>

            </div>

        </el-drawer>

    </div>

    <?php
    echo $uiItems->getJs();
    echo $uiItems->getCss();
    ?>

    <script>
        let vueCenter = new Vue({
            el: '#app',
            data: {
                leftWidth: 200,
                leftWidthLoaded: false,
                chapterTreeCurrentNodeKey: "",

                project: <?php echo json_encode($this->project); ?>,

                chapterTree: <?php echo json_encode($this->chapterTree); ?>,

                formData: null,

                loading: false,

                drawerSeo: false,

                turndownService: null,

                t: false
                <?php
                echo $uiItems->getVueData();
                ?>
            },
            methods: {
                addChapter(parentId) {
                    let _this = this;
                    _this.loading = true;
                    _this.$http.post("<?php echo beAdminUrl('Doc.Doc.addChapter'); ?>", {
                        project_id: _this.project.id,
                        parent_id: parentId,
                        title: "新建文档"
                    }).then(function (response) {
                        _this.loading = false;
                        if (response.status === 200) {
                            var responseData = response.data;
                            if (responseData.success) {

                                _this.addChapterTreeNode(parentId, {
                                    id: responseData.chapter.id,
                                    parent_id: responseData.chapter.parent_id,
                                    label: responseData.chapter.title,
                                    children: []
                                }, false);

                                // 选中新添加的文档
                                _this.$nextTick(function (){
                                    _this.$refs.chapterTree.setCurrentKey(responseData.chapter.id);
                                });

                                _this.formData = responseData.chapter;
                            } else {
                                if (responseData.message) {
                                    _this.$message.error(responseData.message);
                                } else {
                                    _this.$message.error("服务器返回数据异常！");
                                }
                            }
                        }
                    }).catch(function (error) {
                        _this.loading = false;
                        _this.$message.error(error);
                    });
                },
                addChapterTreeNode(parentId, node, children = false) {
                    if (parentId === "") {
                        this.chapterTree.push(node);
                        return;
                    }

                    if (children === false) {
                        children = this.chapterTree;
                    }

                    for (let i in children) {
                        if (children[i].id === parentId) {
                            children[i].children.push(node);
                            return true;
                        } else {
                            if (children[i].children.length > 0) {
                                if (this.addChapterTreeNode(parentId, node, children[i].children)) {
                                    return true;
                                }
                            }
                        }
                    }

                    return false;
                },
                editChapter(chapterId) {
                    let _this = this;
                    _this.loading = true;
                    _this.$http.post("<?php echo beAdminUrl('Doc.Doc.getChapter'); ?>", {
                        chapter_id: chapterId
                    }).then(function (response) {
                        _this.loading = false;
                        if (response.status === 200) {
                            var responseData = response.data;
                            if (responseData.success) {
                                _this.formData = responseData.chapter;

                                if (_this.formData.editor === 'tinymce') {
                                    if (_this.formItems.description.instance) {
                                        _this.formItems.description.instance.setContent(_this.formData.description);
                                    } else {
                                        let timerTinymce = setInterval(function () {
                                            if (_this.formItems.description.instance) {
                                                _this.formItems.description.instance.setContent(_this.formData.description);
                                                clearInterval(timerTinymce)
                                            }
                                        }, 200);
                                    }
                                }

                                if (_this.formData.editor === 'markdown') {
                                    if (_this.formItems.description_markdown.instance) {
                                        _this.formItems.description_markdown.instance.setMarkdown(_this.formData.description_markdown);
                                    } else {
                                        let timerMarkdown = setInterval(function () {
                                            if (_this.formItems.description_markdown.instance) {
                                                _this.formItems.description_markdown.instance.setMarkdown(_this.formData.description_markdown);
                                                clearInterval(timerMarkdown)
                                            }
                                        }, 200);
                                    }
                                }
                            } else {
                                if (responseData.message) {
                                    _this.$message.error(responseData.message);
                                } else {
                                    _this.$message.error("服务器返回数据异常！");
                                }
                            }
                        }
                    }).catch(function (error) {
                        _this.loading = false;
                        _this.$message.error(error);
                    });
                },
                saveChapter: function (isAutoSave = false) {
                    let _this = this;
                    _this.loading = true;
                    _this.$http.post("<?php echo beAdminUrl('Doc.Doc.saveChapter'); ?>", {
                        formData: _this.formData
                    }).then(function (response) {
                        _this.loading = false;
                        //console.log(response);
                        if (response.status === 200) {
                            var responseData = response.data;
                            if (responseData.success) {

                                let node = _this.getChapterTreeNode(_this.formData.id);
                                if (node) {
                                    node.label = _this.formData.title;
                                }

                                if (!isAutoSave) {
                                    _this.$message.success(responseData.message);
                                }
                            } else {
                                if (responseData.message) {
                                    _this.$message.error(responseData.message);
                                } else {
                                    _this.$message.error("服务器返回数据异常！");
                                }
                            }
                        }
                    }).catch(function (error) {
                        _this.loading = false;
                        _this.$message.error(error);
                    });
                },
                deleteChapter(chapterId) {
                    let _this = this;
                    _this.$confirm("确认要岫除么？", "删除确认", {
                        confirmButtonText: "确定",
                        cancelButtonText: "取消",
                        type: "warning"
                    }).then(function(){
                        _this.loading = true;
                        _this.$http.post("<?php echo beAdminUrl('Doc.Doc.deleteChapter'); ?>", {
                            chapter_id: chapterId
                        }).then(function (response) {
                            _this.loading = false;
                            if (response.status === 200) {
                                var responseData = response.data;
                                if (responseData.success) {
                                    _this.$message.success(responseData.message);
                                    _this.deleteChapterTreeNode(data.id);
                                } else {
                                    if (responseData.message) {
                                        _this.$message.error(responseData.message);
                                    } else {
                                        _this.$message.error("服务器返回数据异常！");
                                    }
                                }
                            }
                        }).catch(function (error) {
                            _this.loading = false;
                            _this.$message.error(error);
                        });

                    }).catch(function(){});
                },
                deleteChapterTreeNode(chapterId, children = false) {
                    if (children === false) {
                        children = this.chapterTree;
                    }

                    for (let i in children) {
                        if (children[i].id === chapterId) {
                            children.splice(i, 1);

                            if (chapterId === this.formData.id) {
                                this.initFormData();
                            }
                            return true;
                        } else {
                            if (children[i].children.length > 0) {
                                if (this.deleteChapterTreeNode(chapterId, children[i].children)) {
                                    return true;
                                }
                            }
                        }
                    }

                    return false;
                },
                sortChapter(draggingNode, dropNode, dropType, ev) {

                    let formData = [];

                    if (dropType === "inner") {
                        for(let i in dropNode.childNodes) {
                            if (dropNode.childNodes[i].data.id  === draggingNode.data.id) {
                                draggingNode.data.parent_id = dropNode.data.id;
                                formData.push({
                                    id: draggingNode.data.id,
                                    parent_id: dropNode.data.id,
                                    ordering: i
                                });
                            } else {
                                formData.push({
                                    id: dropNode.childNodes[i].data.id,
                                    ordering: i
                                });
                            }
                        }
                    } else {
                        // 目标节点所在组重新排序
                        for(let i in dropNode.parent.childNodes) {
                            if (dropNode.parent.childNodes[i].data.id  === draggingNode.data.id) {
                                draggingNode.data.parent_id = dropNode.parent.data.id;

                                formData.push({
                                    id: draggingNode.data.id,
                                    parent_id: dropNode.parent.level > 0 ? dropNode.parent.data.id : "",
                                    ordering: i
                                });
                            } else {
                                formData.push({
                                    id: dropNode.parent.childNodes[i].data.id,
                                    ordering: i
                                });
                            }
                        }
                    }

                    let _this = this;
                    _this.loading = true;
                    _this.$http.post("<?php echo beAdminUrl('Doc.Doc.sortChapter'); ?>", {
                        formData: formData
                    }).then(function (response) {
                        _this.loading = false;
                        //console.log(response);
                        if (response.status === 200) {
                            var responseData = response.data;
                            if (responseData.success) {
                                //_this.$message.success(responseData.message);
                            } else {
                                if (responseData.message) {
                                    _this.$message.error(responseData.message);
                                } else {
                                    _this.$message.error("服务器返回数据异常！");
                                }
                            }
                        }
                    }).catch(function (error) {
                        _this.loading = false;
                        _this.$message.error(error);
                    });
                },
                getChapterTreeNode(chapterId, children = false) {
                    if (children === false) {
                        children = this.chapterTree;
                    }

                    let node;
                    for (let i in children) {
                        if (children[i].id === chapterId) {
                            return children[i];
                        } else {
                            if (children[i].children.length > 0) {
                                node = this.getChapterTreeNode(chapterId, children[i].children);
                                if (node !== false) {
                                    return node;
                                }
                            }
                        }
                    }

                    return false;
                },
                titleUpdate: function () {
                    /*
                    if (this.formData.id !== "") {
                        let node = this.getChapterTreeNode(this.formData.id);
                        if (node) {
                            node.label = this.formData.title;
                        }
                    }
                    */

                    this.seoUpdate();
                },
                descriptionUpdate() {
                    if (this.turndownService === null) {
                        this.turndownService = new TurndownService();
                        this.turndownService.use(turndownPluginGfm.gfm);
                    }

                    this.formData.description_markdown = this.turndownService.turndown(this.formData.description)
                    this.seoUpdate();
                },
                descriptionMarkdownUpdate() {
                    this.formData.description = this.formItems.description_markdown.instance.getHTML();
                    this.seoUpdate();
                },
                seoUpdate: function () {
                    if (this.formData.seo_title_custom === 0) {
                        this.formData.seo_title = this.formData.title;
                    }

                    if (this.formData.seo_description_custom === 0) {
                        let seoDescription = this.formData.description;
                        seoDescription = seoDescription.replace(/<[^>]+>/g,"");
                        seoDescription = seoDescription.replace("\r", " ");
                        seoDescription = seoDescription.replace("\n", " ");
                        if (seoDescription.length > 500) {
                            seoDescription = seoDescription.substr(0, 500);
                        }
                        this.formData.seo_description = seoDescription;
                    }

                    if (this.formData.url_custom === 0) {
                        let title = this.formData.title.toLowerCase();
                        let url = Pinyin.convert(title, "-");
                        if (url.length > 200) {
                            url = Pinyin.convert(title, "-", true);
                            if (url.length > 200) {
                                url = Pinyin.convert(title, "", true);
                            }
                        }
                        this.formData.url = url;
                    }
                },
                toggleEditor() {
                    if (this.formData.editor === 'tinymce') {
                        this.formItems.description.instance.setContent(this.formData.description);
                    } else {
                        this.formItems.description_markdown.instance.setMarkdown(this.formData.description_markdown);
                    }

                    this.$forceUpdate();
                    //this.resizeRight();
                },
                resizeLeft() {
                    let cookieKey = 'doc-chapter-left-width';

                    if (!this.leftWidthLoaded) {
                        if (this.$cookies.isKey(cookieKey)) {
                            let cookieLeftWidth = this.$cookies.get(cookieKey);
                            if (!isNaN(cookieLeftWidth)) {
                                cookieLeftWidth = Number(cookieLeftWidth);
                                if (cookieLeftWidth >= 150 && cookieLeftWidth <= 600) {
                                    this.leftWidth = cookieLeftWidth;
                                }
                            }
                        }
                        this.$cookies.set(cookieKey, this.leftWidth, 86400 * 180);

                        this.leftWidthLoaded = true;
                    }

                    let _this = this;
                    let resize = document.getElementById('left-resize');
                    resize.onmousedown = function (e) {
                        resize.className = 'left-resize-on';
                        resize.left = resize.offsetLeft;
                        let x0 = e.clientX;
                        document.onmousemove = function (e) {
                            let x1 = e.clientX;
                            let letfWidth = resize.left + (x1 - x0);
                            if (letfWidth < 150) letfWidth = 150;
                            if (letfWidth > 600) letfWidth = 600;
                            _this.leftWidth = letfWidth;
                            _this.resizeRight();
                        };
                        document.onmouseup = function (evt) {
                            resize.className = '';
                            document.onmousemove = null;
                            document.onmouseup = null;
                            resize.releaseCapture && resize.releaseCapture();
                            _this.$cookies.set(cookieKey, _this.leftWidth, 86400 * 180);
                        };
                        resize.setCapture && resize.setCapture();
                        return false;
                    };
                },
                resizeRight: function () {
                    let width = document.documentElement.clientWidth - this.leftWidth - 20;
                    let height = Math.max(document.documentElement.clientHeight - 60, 300);

                    let _this = this;

                    <?php
                    if ($this->project->chapter_toggle_editor || $this->project->chapter_default_editor === 'tinymce') {
                    ?>
                    if (this.formData.editor === 'tinymce') {
                        if (this.formItems.description.instance) {
                            this.formItems.description.instance.settings.height = height;
                            this.formItems.description.instance.settings.max_height = height;
                            this.formItems.description.instance.settings.min_height = height;
                        } else {
                            let timerTinymce = setInterval(function () {
                                if (_this.formItems.description.instance) {
                                    _this.formItems.description.instance.settings.height = height;
                                    _this.formItems.description.instance.settings.max_height = height;
                                    _this.formItems.description.instance.settings.min_height = height;
                                    clearInterval(timerTinymce)
                                }
                            }, 200);
                        }
                    }
                    <?php
                    }

                    if ($this->project->chapter_toggle_editor || $this->project->chapter_default_editor === 'markdown') {
                    ?>
                    if (this.formData.editor === 'markdown') {
                        if (this.formItems.description_markdown.instance) {
                            this.formItems.description_markdown.instance.resize(width, height);
                        } else {
                            let timerMarkdown = setInterval(function () {
                                if (_this.formItems.description_markdown.instance) {
                                    _this.formItems.description_markdown.instance.resize(width, height);
                                    clearInterval(timerMarkdown)
                                }
                            }, 200);
                        }
                    }
                    <?php
                    }
                    ?>
                },
                initFormData: function () {
                    this.formData = {
                        id: "",
                        project_id: "<?php echo $this->project->id; ?>",
                        parent_id: "",
                        title: "",
                        description: "",
                        description_markdown: "",
                        editor: "<?php echo $this->project->chapter_default_editor ?>",
                        is_enable: 0,
                        seo_title: "",
                        seo_title_custom: 0,
                        seo_description: "",
                        seo_description_custom: 0,
                        url: "",
                        url_custom: 0,
                        seo_keywords: "",
                    };
                },
                autoSave() {
                    <?php
                    if ($this->configChapter->autoSave) {
                    ?>
                    var _this = this;
                    setInterval(function () {
                        if (_this.formData.id !== "") {
                            _this.saveChapter(true);
                        }
                    }, <?php echo $this->configChapter->autoSaveInterval * 1000 ?>);
                    <?php
                    }
                    ?>
                },
                autoLoad() {
                    if (this.chapterTree.length > 0) {

                        let chapterId = this.chapterTree[0].id;

                        this.editChapter(chapterId);

                        let _this = this;

                        // 选中新添加的文档
                        _this.$nextTick(function (){
                            _this.$refs.chapterTree.setCurrentKey(chapterId);
                        });
                    }
                }
                <?php
                echo $uiItems->getVueMethods();
                ?>
            }
            <?php
            $uiItems->setVueHook('created', '
                this.initFormData();
                this.autoLoad();
                this.autoSave();
            ');

            $uiItems->setVueHook('mounted', '
                this.resizeLeft();

                let _thisResizeRight = this;
                window.onresize = function () {
                    _thisResizeRight.resizeRight();
                };

                this.$nextTick(function () {
                    _thisResizeRight.resizeRight();
                });
            ');

            $uiItems->setVueHook('updated', '
                let _this = this;
                this.$nextTick(function () {
                    _this.resizeRight();
                });
            ');

            echo $uiItems->getVueHooks();
            ?>
        });
    </script>

</be-body>