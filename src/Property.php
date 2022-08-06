<?php

namespace Be\App\Doc;


class Property extends \Be\App\Property
{

    protected string $label = '文档';
    protected string $icon = 'el-icon-notebook-2';
    protected string $description = '文档管理系统';

    public function __construct() {
        parent::__construct(__FILE__);
    }

}
