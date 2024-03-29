<be-head>
    <script src="<?php echo \Be\Be::getProperty('App.Doc')->getWwwUrl(); ?>/admin/js/pinyin.js"></script>
    <style>
        .el-form-item {
            margin-bottom: inherit;
        }

        .el-form-item__content {
            line-height: inherit;
        }

        .el-tooltip {
            cursor: pointer;
        }

        .el-tooltip:hover {
            color: #409EFF;
        }
    </style>
</be-head>


<be-north>
    <div id="app-north">
        <div class="be-row">
            <div class="be-col">
                <div style="padding: 1.25rem 0 0 2rem;">
                    <el-link icon="el-icon-back" href="<?php echo beAdminUrl('Doc.Project.projects'); ?>">返回文档列表</el-link>
                </div>
            </div>
            <div class="be-col-auto">
                <div style="padding: .75rem 2rem 0 0;">
                    <el-button size="medium" :disabled="loading" @click="vueCenter.cancel();">取消</el-button>
                    <el-dropdown type="primary" size="medium" split-button :disabled="loading" @click="vueCenter.save('')" @command="save">
                        保存
                        <el-dropdown-menu slot="dropdown">
                            <el-dropdown-item command="stay">保存并继续编辑</el-dropdown-item>
                        </el-dropdown-menu>
                    </el-dropdown>
                </div>
            </div>
        </div>
    </div>
    <script>
        let vueNorth = new Vue({
            el: '#app-north',
            data: {
                loading: false,
            },
            methods: {
                save: function (command) {
                    vueCenter.save(command)
                }
            }
        });
    </script>
</be-north>


<be-page-content>
    <?php
    $formData = [];
    $uiItems = new \Be\AdminPlugin\UiItem\UiItems();
    $rootUrl = \Be\Be::getRequest()->getRootUrl();
    ?>

    <div id="app" v-cloak>
        <el-form ref="formRef" :model="formData" class="be-mb-400">
            <?php
            $formData['id'] = ($this->project ? $this->project->id : '');
            ?>

            <div class="be-row">
                <div class="be-col-24 be-md-col-18">
                    <div class="be-p-150 be-bc-fff">
                        <div><span class="be-c-red">*</span> 项目标题：</div>
                        <el-form-item class="be-mt-50" prop="title" :rules="[{required: true, message: '请输入项目标题', trigger: 'change' }]">
                            <el-input
                                    type="text"
                                    placeholder="请输入名称"
                                    v-model = "formData.title"
                                    size="medium"
                                    maxlength="120"
                                    show-word-limit
                                    @change="seoUpdate">
                            </el-input>
                        </el-form-item>
                        <?php $formData['title'] = ($this->project ? $this->project->title : ''); ?>

                        <div class="be-mt-100">描述：</div>
                        <?php
                        $driver = new \Be\AdminPlugin\Form\Item\FormItemTinymce([
                            'name' => 'description',
                            'ui' => [
                                'form-item' => [
                                    'class' => 'be-mt-50'
                                ],
                                '@change' => 'seoUpdate',
                            ]
                        ]);
                        echo $driver->getHtml();

                        $formData['description'] = ($this->project ? $this->project->description : '');

                        $uiItems->add($driver);
                        ?>
                    </div>
                </div>


                <div class="be-col-24 be-md-col-6 be-pl-150">

                    <div class="be-p-150 be-bc-fff">
                        <div class="be-row">
                            <div class="be-col be-lh-250">文档默认编辑器：</div>
                            <div class="be-col-auto">
                                <el-form-item prop="chapter_default_editor">
                                    <el-select v-model = "formData.chapter_default_editor" size="medium">
                                        <el-option value="markdown" label="Markdown"></el-option>
                                        <el-option value="tinymce" label="Tinymce"></el-option>
                                    </el-select>
                                </el-form-item>
                            </div>
                        </div>
                        <?php $formData['chapter_default_editor'] = ($this->project ? $this->project->chapter_default_editor : 'markdown'); ?>

                        <div class="be-row be-mt-150">
                            <div class="be-col ">文档是否可切换编辑器：</div>
                            <div class="be-col-auto">
                                <el-form-item prop="chapter_toggle_editor">
                                    <el-switch v-model.number = "formData.chapter_toggle_editor" :active-value="1" :inactive-value="0" size="medium"></el-switch>
                                </el-form-item>
                            </div>
                        </div>
                        <?php $formData['chapter_toggle_editor'] = ($this->project ? $this->project->chapter_toggle_editor : 1); ?>

                        <div class="be-row be-mt-150">
                            <div class="be-col be-lh-250">排序：</div>
                            <div class="be-col-auto">
                                <el-form-item prop="ordering">
                                    <el-input-number
                                            v-model = "formData.ordering"
                                            size="medium">
                                    </el-input-number>
                                </el-form-item>
                            </div>
                        </div>
                        <?php $formData['ordering'] = ($this->project ? $this->project->ordering : ''); ?>
                    </div>

                    <div class="be-p-150 be-bc-fff be-mt-150">
                        <div class="be-row">
                            <div class="be-col">
                                <div class="be-center-box-title">
                                    SEO（搜索引擎优化）
                                </div>
                            </div>
                            <div class="be-col-auto">
                                <el-link type="primary" @click="drawerSeo=true">编辑</el-link>
                            </div>
                        </div>

                        <div class="be-mt-100 be-t-break be-c-999 be-fs-80"><?php echo $rootUrl; ?>/doc/{{formData.url}}</div>
                        <div class="be-mt-100">{{formData.seo_title}}</div>
                        <div class="be-mt-100 be-t-ellipsis-2">{{formData.seo_description}}</div>
                    </div>
                </div>
            </div>


        </el-form>

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
                <?php
                $formData['seo_title'] = ($this->project ? $this->project->seo_title : '');
                $formData['seo_title_custom'] = ($this->project ? $this->project->seo_title_custom : 0);
                ?>

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
                <?php
                $formData['seo_description'] = ($this->project ? $this->project->seo_description : '');
                $formData['seo_description_custom'] = ($this->project ? $this->project->seo_description_custom : 0);
                ?>

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
                <?php
                $formData['url'] = ($this->project ? $this->project->url : '');
                $formData['url_custom'] = ($this->project ? $this->project->url_custom : 0);
                ?>

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
                <?php
                $formData['seo_keywords'] = ($this->project ? $this->project->seo_keywords : '');
                ?>

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
                formData: <?php echo json_encode($formData); ?>,
                loading: false,

                drawerSeo: false,

                t: false
                <?php
                echo $uiItems->getVueData();
                ?>
            },
            methods: {
                save: function (command) {
                    let _this = this;
                    this.$refs["formRef"].validate(function (valid) {
                        if (valid) {
                            _this.loading = true;
                            vueNorth.loading = true;
                            _this.$http.post("<?php echo beAdminUrl('Doc.Project.' . ($this->project ? 'edit' :'create')); ?>", {
                                formData: _this.formData
                            }).then(function (response) {
                                _this.loading = false;
                                vueNorth.loading = false;
                                //console.log(response);
                                if (response.status === 200) {
                                    var responseData = response.data;
                                    if (responseData.success) {
                                        _this.$message.success(responseData.message);
                                        if (command === 'stay') {
                                            _this.formData.id = responseData.project.id;
                                        } else {
                                            setTimeout(function () {
                                                window.onbeforeunload = null;
                                                window.location.href = "<?php echo beAdminUrl('Doc.Project.projects'); ?>";
                                            }, 1000);
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
                                vueNorth.loading = false;
                                _this.$message.error(error);
                            });
                        } else {
                            return false;
                        }
                    });
                },
                cancel: function () {
                    window.onbeforeunload = null;
                    window.location.href = "<?php echo beAdminUrl('Doc.Project.projects'); ?>";
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
                }

                <?php
                echo $uiItems->getVueMethods();
                ?>
            }
            <?php
            $uiItems->setVueHook('mounted', 'window.onbeforeunload = function(e) {e = e || window.event; if (e) { e.returnValue = ""; } return ""; };');
            echo $uiItems->getVueHooks();
            ?>
        });
    </script>

</be-page-content>