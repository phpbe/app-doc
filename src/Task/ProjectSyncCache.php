<?php
namespace Be\App\Doc\Task;

use Be\Be;
use Be\Task\TaskInterval;

/**
 * @BeTask("项目增量同步到Cache", schedule="* * * * *")
 */
class ProjectSyncCache extends TaskInterval
{

    // 时间间隔：1天
    protected $step = 86400;

    public function execute()
    {
        if (!$this->breakpoint) {
            $this->breakpoint = date('Y-m-d h:i:s', time() - $this->step);
        }

        $t0 = time();
        $t1 = strtotime($this->breakpoint);
        $t2 = $t1 + $this->step;

        if ($t1 >= $t0) return;
        if ($t2 > $t0) {
            $t2 = $t0;
        }

        $d1 = date('Y-m-d H:i:s', $t1 - 60);
        $d2 = date('Y-m-d H:i:s', $t2);

        $service = Be::getService('App.Doc.Admin.TaskProject');
        $db = Be::getDb();
        $sql = 'SELECT * FROM doc_project WHERE update_time >= ? AND update_time < ?';
        $categories = $db->getYieldObjects($sql, [$d1, $d2]);

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

        $this->breakpoint = $d2;
    }

}
