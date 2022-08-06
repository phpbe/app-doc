<?php
namespace Be\App\Doc\Config;

/**
 * @BeConfig("ES搜索引擎")
 */
class Es
{

    /**
     * @BeConfigItem("是否启用ES搜索引擎",
     *     description="启用后，文档变更将同步到ES搜索引擎，检索相关的功能将由ES接管",
     *     driver="FormItemSwitch"
     * )
     */
    public int $enable = 0;

    /**
     * @BeConfigItem("存储文档的索引名",
     *     driver="FormItemInput",
     *     ui="return ['form-item' => ['v-show' => 'formData.enable === 1']];"
     * )
     */
    public string $indexChapter = 'doc.chapter';

}

