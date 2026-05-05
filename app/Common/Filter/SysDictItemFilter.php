<?php

declare(strict_types=1);

namespace App\Common\Filter;

class SysDictItemFilter extends BaseFilter
{
    protected array $scene = [
        'list' => ['dict_id'],
        'add' => ['dict_id', 'item_label', 'item_value', 'item_style', 'sort', 'status', 'remark', 'create_by' => '__OperateId'],
        'update' => ['id', 'item_label', 'item_value', 'item_style', 'sort', 'status', 'remark','update_by' => '__OperateId'],
        'update_status' => ['id', 'status'],
    ];
}
