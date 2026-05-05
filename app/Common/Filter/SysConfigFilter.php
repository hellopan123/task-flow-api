<?php

declare(strict_types=1);

namespace App\Common\Filter;

class SysConfigFilter extends BaseFilter
{
    protected array $scene = [
        'list' => ['page', 'limit', 'config_group', 'config_key', 'config_name', 'status'],
        'info' => ['id'],
        'add' => ['config_group', 'config_key', 'config_value', 'config_name', 'config_type', 'sort', 'status', 'remark', 'create_by' => '__OperateId', 'create_time' => '__Now'],
        'update' => ['id', 'config_group', 'config_key', 'config_value', 'config_name', 'config_type', 'sort', 'status', 'remark', 'update_by' => '__OperateId'],
        'update_status' => ['id', 'status'],
    ];
}
