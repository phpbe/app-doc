<?php

namespace Be\App\Doc\Controller\Admin;

use Be\App\System\Controller\Admin\Auth;
use Be\Be;

/**
 * @BeMenuGroup("控制台", icon="el-icon-monitor", ordering="3")
 * @BePermissionGroup("控制台", icon="el-icon-monitor", ordering="3")
 */
class Task extends Auth
{
    /**
     * @BeMenu("计划任务", icon="el-icon-timer", ordering="3.2")
     * @BePermission("计划任务", ordering="3.2")
     */
    public function dashboard()
    {
        Be::getAdminPlugin('Task')->setting(['appName' => 'Doc'])->execute();
    }

}
