<?php

declare(strict_types=1);

namespace App\Common\Filter;

class TkFaqFilter extends BaseFilter
{
    protected array $scene = [
        'list' => ['page', 'pageSize', 'question', 'category', 'status'],
        'info' => ['id'],
        'add' => ['question', 'answer', 'category', 'sort', 'status', 'view_count', 'create_by' => '__OperateId', 'create_time' => '__Now'],
        'update' => ['id', 'question', 'answer', 'category', 'sort', 'status', 'update_by' => '__OperateId'],
        'update_status' => ['id', 'status'],
    ];
}
