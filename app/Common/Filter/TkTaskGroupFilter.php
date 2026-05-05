<?php

declare(strict_types=1);

namespace App\Common\Filter;

class TkTaskGroupFilter extends BaseFilter
{
    protected array $scene = [
        'list' => ['page', 'pageSize', 'group_name', 'status'],
        'info' => ['id'],
        'add' => ['group_name', 'description', 'cover_image', 'sort', 'status', 'creator_id' => '__OperateId', 'create_time' => '__Now'],
        'update' => ['id', 'group_name', 'description', 'cover_image', 'sort', 'status'],
        'update_status' => ['id', 'status'],
    ];
}
