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
     * @BeConfigItem("文章详情页文字大小",
     *     driver="FormItemSelect",
     *     keyValues = "return ['100' => '1rem', '110' => '1.1rem', '120' => '1.2rem', '125' => '1.25rem', '150' => '1.5rem', '175' => '1.75rem', '200' => '2rem'];")
     * )
     */
    public string $detailFontSize = '110';

    /**
     * @BeConfigItem("文章详情页行高",
     *     driver="FormItemSelect",
     *     keyValues = "return ['150' => '1.5rem', '175' => '1.75rem', '200' => '2rem', '250' => '2.5rem', '300' => '3rem', '400' => '4rem'];")
     * )
     */
    public string $detailLineHeight = '200';

}

