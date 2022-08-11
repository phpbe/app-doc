<?php
namespace Be\App\Doc\Task;

use Be\Be;
use Be\Task\TaskInterval;

/**
 * 间隔一段时间晨，定时执行 文档同步到ES和Cache
 *
 * @BeTask("文档境量同步到ES和Cache", schedule="* * * * *")
 */
class ChapterSyncEsAndCache extends TaskInterval
{

    // 时间间隔：1天
    protected $step = 86400;


    public function execute()
    {
        if (!$this->breakpoint) {
            $this->breakpoint = date('Y-m-d h:i:s', time() - $this->step);
        }

        $configEs = Be::getConfig('App.Doc.Es');

        $t0 = time();
        $t1 = strtotime($this->breakpoint);
        $t2 = $t1 + $this->step;

        if ($t1 >= $t0) return;
        if ($t2 > $t0) {
            $t2 = $t0;
        }

        $d1 = date('Y-m-d H:i:s', $t1 - 60);
        $d2 = date('Y-m-d H:i:s', $t2);

        $service = Be::getService('App.Doc.Admin.TaskChapter');
        $db = Be::getDb();
        $sql = 'SELECT * FROM doc_chapter WHERE is_enable != -1 AND update_time >= ? AND update_time < ?';
        $chapters = $db->getYieldObjects($sql, [$d1, $d2]);

        $productIds = [];

        $batch = [];
        $i = 0;
        foreach ($chapters as $chapter) {
            $batch[] = $chapter;

            if (in_array($chapter->project_id, $productIds)) {
                $productIds[] = $chapter->project_id;
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

        if (count($productIds) > 0) {
            $service->syncCacheChapterTree($productIds);
        }

        $this->breakpoint = $d2;
    }


}
