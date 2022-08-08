<?php
namespace Be\App\Doc\Task;

use Be\Be;
use Be\Task\Task;

/**
 * @BeTask("项目全量同步到Cache")
 */
class AllProjectSyncCache extends Task
{


    public function execute()
    {
        $service = Be::getService('App.Doc.Admin.TaskProject');

        $db = Be::getDb();
        $sql = 'SELECT * FROM doc_project';
        $categories = $db->getYieldObjects($sql);

        $batch = [];
        $i = 0;
        foreach ($categories as $category) {
            $batch[] = $category;

            $i++;
            if ($i >= 100) {
                $service->syncCache($batch);

                $batch = [];
                $i = 0;
            }
        }

        if ($i > 0) {
            $service->syncCache($batch);
        }

    }

}
