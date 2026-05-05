<?php

declare(strict_types=1);

namespace App\Common\Filter;

class SysDictFilter extends BaseFilter
{
    protected array $scene = [
        'info' => ['id'],
        'add' => ['dict_name', 'dict_code', 'description', 'status', 'remark', 'create_by' => '__OperateId'],
        'update' => ['id', 'dict_name', 'dict_code', 'description', 'status', 'remark', 'update_by' => '__OperateId'],
        'update_status' => ['id', 'status'],
    ];
}
