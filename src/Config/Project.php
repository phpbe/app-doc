<?php
namespace Be\App\Doc\Config;

/**
 * @BeConfig("项目")
 */
class Project
{
    
    /**
     * @BeConfigItem("缓存不存在时，是否从数据库加载",
     *     driver="FormItemSwitch"
     * )
     */
    public int $cacheNotExistsLoadFromDb = 1;

    /**
     * @BeConfigItem("缓存不存在时，从数据库加载异常锁定时长（秒）",
     *     description="缓存不存在时，从数据库加载发生异常，缓存一段时间，防止重复堂试",
     *     driver="FormItemInputNumberInt"
     * )
     */
    public int $cacheNotExistsLoadFromDbExceptionLockTime = 600;

}

