<be-page-content>
    <div class="be-bc-fff be-p-150" id="app" v-cloak>
        <?php
        $formDatas = [];

        if (!$this->configEs->enable) {
            ?>
            <div class="be-fw-bold">
                ES搜索引擎未启用！
                <el-link class="be-ml-100" type="primary" href="<?php echo beAdminUrl('Doc.Config.dashboard', ['configName' => 'Es']); ?>">修改</el-link>
            </div>
            <?php
        } else {
            if (count($this->indexes) > 0) {
                $i = 0;
                foreach ($this->indexes as $index) {
                    ?>
                    <div class="be-bb-eee be-pb-50">
                        <div class="be-row be-fs-110 <?php echo $i > 0 ? 'be-mt-300' : ''; ?>">
                            <div class="be-col-auto">
                                <div class="be-pr-100">
                                    <?php echo $index['label']; ?>：
                                </div>
                            </div>
                            <div class="be-col">
                                <?php echo $index['value']; ?>
                                <el-link class="be-ml-100" type="primary" href="<?php echo beAdminUrl('Doc.Config.dashboard', ['configName' => 'Es']); ?>">修改</el-link>
                            </div>
                        </div>
                    </div>

                    <div class="be-mt-150">
                    <?php
                    if ($index['exists']) {
                        ?>
                        <div class="be-row be-mt-50">
                            <div class="be-col-auto be-c-999">UUID：</div>
                            <div class="be-col">
                                <?php echo $index['settings']['index']['uuid'] ?? ''; ?>
                            </div>
                        </div>
                        <div class="be-row be-mt-50">
                            <div class="be-col-auto be-c-999">分片数：</div>
                            <div class="be-col">
                                <?php echo $index['settings']['index']['number_of_shards'] ?? ''; ?>
                            </div>
                        </div>
                        <div class="be-row be-mt-50">
                            <div class="be-col-auto be-c-999">副本数：</div>
                            <div class="be-col">
                                <?php echo $index['settings']['index']['number_of_replicas'] ?? ''; ?>
                            </div>
                        </div>
                        <div class="be-row be-mt-50">
                            <div class="be-col-auto be-c-999">数据量：</div>
                            <div class="be-col">
                                <?php echo $index['count'] ?? ''; ?>
                            </div>
                        </div>
                        <div class="be-row be-mt-50">
                            <div class="be-col-auto be-c-999">创建于：</div>
                            <div class="be-col">
                                <?php echo isset($index['settings']['index']['creation_date']) ? date('Y-m-d H:i:s', (int)$index['settings']['index']['creation_date'] / 1000) : ''; ?>
                            </div>
                        </div>

                        <div class="be-bt-eee be-mt-100 be-pt-50">
                            <el-button type="danger" size="medium" @click="deleteIndex('<?php echo $index['name']; ?>')">删除索引</el-button>
                        </div>
                        <?php
                    } else {
                        ?>
                        <el-form ref="<?php echo $index['name']; ?>FormRef" :model="<?php echo $index['name']; ?>FormData">
                            <el-form-item class="be-mt-50" prop="number_of_replicas" label="分片数" :rules="[{required: true, message: '请输入分片数', trigger: 'change' }]">
                                <el-input-number
                                        :min="1"
                                        :step="1"
                                        placeholder="请输入分片数"
                                        v-model = "<?php echo $index['name']; ?>FormData.number_of_shards"
                                        size="medium">
                                </el-input-number>
                            </el-form-item>

                            <el-form-item class="be-mt-50" prop="number_of_replicas" label="副本数" :rules="[{required: true, message: '请输入分片数', trigger: 'change' }]">
                                <el-input-number
                                        :min="0"
                                        :step="1"
                                        placeholder="请输入副本数"
                                        v-model = "<?php echo $index['name']; ?>FormData.number_of_replicas"
                                        size="medium">
                                </el-input-number>
                            </el-form-item>
                        </el-form>

                        <el-button type="primary" size="medium" @click="createIndex('<?php echo $index['name']; ?>FormRef', '<?php echo $index['name']; ?>FormData')">创建索引</el-button>
                        <?php
                        $formDatas[$index['name'] . 'FormData'] = [
                            'name' => $index['name'],
                            'number_of_shards' => 2,
                            'number_of_replicas' => 1,
                        ];
                    }
                    ?>
                    </div>

                    <?php
                    $i++;
                }
            }
        }

        if (count($formDatas) > 0) {
            ?>
            <div class="be-mt-200 be-c-999 be-bb-eee be-pb-50 be-mb-50">参考算法</div>
            <ul>
                <li class="be-c-999">副本数 <=  ES集群的服务器个数 - 1</li>
                <li class="be-c-999">预估要存入ES总数据量 * (副本数+1)  <= ES集群的总内存 / 2</li>
                <li class="be-c-999">分片数 * (副本数 + 1) <= ES集群的总CPU核心个数</li>
                <li class="be-c-999">参考：<el-link type="primary" href="https://www.liu12.com/article/es-number-of-rshards-and-eplicas" target="_blank">https://www.liu12.com/article/es-number-of-rshards-and-eplicas</el-link></li>
            </ul>
            <?php
        }
        ?>

    </div>
    <script>
        let vueCenter = new Vue({
            el: '#app',
            data: {
                loading: false,
                <?php
                if (count($formDatas) > 0) {
                    foreach ($formDatas as $key => $formData) {
                        echo $key . ':' . json_encode($formData) . ',';
                    }
                }
                ?>
                t: false
            },
            methods: {
                createIndex: function (formRef, formData) {
                    let _this = this;
                    this.$refs[formRef].validate(function (valid) {
                        if (valid) {
                            _this.loading = true;
                            _this.$http.post("<?php echo beAdminUrl('Doc.Es.createIndex'); ?>", {
                                formData: _this[formData]
                            }).then(function (response) {
                                _this.loading = false;
                                if (response.status === 200) {
                                    var responseData = response.data;
                                    if (responseData.success) {
                                        _this.$message.success(responseData.message);
                                        setTimeout(function () {
                                            window.location.reload();
                                        }, 1000);
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
                        } else {
                            return false;
                        }
                    });
                },
                deleteIndex: function (indexname) {

                    let _this = this;
                    _this.$confirm("本操作将删除索引中的所有数据，确认要岫除么？", "删除确认", {
                        confirmButtonText: "确定",
                        cancelButtonText: "取消",
                        type: "warning"
                    }).then(function(){

                        _this.loading = true;
                        _this.$http.post("<?php echo beAdminUrl('Doc.Es.deleteIndex'); ?>", {
                            formData: {
                                name: indexname
                            }
                        }).then(function (response) {
                            _this.loading = false;
                            if (response.status === 200) {
                                var responseData = response.data;
                                if (responseData.success) {
                                    _this.$message.success(responseData.message);
                                    setTimeout(function () {
                                        window.location.reload();
                                    }, 1000);
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
                t: function () {
                }
            },
        });
    </script>
</be-page-content>