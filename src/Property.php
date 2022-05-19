<?php

namespace Be\App\Doc;


class Property extends \Be\App\Property
{

    protected $label = '文档';
    protected $icon = 'el-icon-notebook-2';
    protected $description = '文档管理系统';

    public function __construct() {
        parent::__construct(__FILE__);
    }

}
