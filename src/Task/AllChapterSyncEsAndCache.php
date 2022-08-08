<?php
namespace Be\App\Doc\Task;

use Be\Be;
use Be\Task\Task;

/**
 * 文档全量量同步到ES和Cache
 *
 * @BeTask("文档全量量同步到ES和Cache")
 */
class AllChapterSyncEsAndCache extends Task
{

    public function execute()
    {
        $configEs = Be::getConfig('App.Doc.Es');

        $service = Be::getService('App.Doc.Admin.TaskChapter');

        $db = Be::getDb();
        $sql = 'SELECT * FROM doc_chapter WHERE is_enable != -1';
        $blogs = $db->getYieldObjects($sql);

        $batch = [];
        $i = 0;
        foreach ($blogs as $blog) {
            $batch[] = $blog;

            $i++;
            if ($i >= 100) {
                if ($configEs->enable) {
                    $service->syncEs($batch);
                }

                $service->syncCache($batch);

                $batch = [];
                $i = 0;
            }
        }

        if ($i > 0) {
            if ($configEs->enable) {
                $service->syncEs($batch);
            }

            $service->syncCache($batch);
        }

    }


}
