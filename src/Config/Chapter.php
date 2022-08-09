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


}

