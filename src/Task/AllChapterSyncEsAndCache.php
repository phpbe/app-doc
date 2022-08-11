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
        $chapters = $db->getYieldObjects($sql);

        $projectIds = [];

        $batch = [];
        $i = 0;
        foreach ($chapters as $chapter) {
            $batch[] = $chapter;

            if (!in_array($chapter->project_id, $projectIds)) {
                $projectIds[] = $chapter->project_id;
            }

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

        if (count($projectIds) > 0) {
            $service->syncCacheChapterTree($projectIds);
        }

    }


}
