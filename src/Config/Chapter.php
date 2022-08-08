<?php
namespace Be\App\Doc\Config;

/**
 * @BeConfig("文档")
 */
class Chapter
{

    /**
     * @BeConfigItem("自动保存文档",
     *     description="启用后，编辑文档时可自动保存",
     *     driver="FormItemSwitch"
     * )
     */
    public int $autoSave = 1;

    /**
     * @BeConfigItem("自动保存文档间隔时间（秒）",
     *     driver="FormItemInputNumberInt"
     * )
     */
    public int $autoSaveInterval = 15;

    /**
     * @BeConfigItem("默认编辑器",
     *     description="启用后，文档变更将同步到ES搜索引擎，检索相关的功能将由ES接管",
     *     driver="FormItemSwitch"
     * )
     */
    public string $defaultEditor = 'markdown';

    /**
     * @BeConfigItem("单个文档是否可切换编辑器",
     *     description="启用后，文档可单独控制编辑器",
     *     driver="FormItemSwitch"
     * )
     */
    public int $toggleEditor = 1;

}

