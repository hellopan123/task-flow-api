<?php

declare(strict_types=1);

namespace App\Common\Filter;

class TkProjectFilter extends BaseFilter
{
    protected array $scene = [
        'list' => ['page', 'pageSize', 'project_name', 'project_code', 'owner_id', 'status'],
        'info' => ['id'],
        'add' => ['project_name', 'project_code', 'description', 'owner_id', 'start_date', 'end_date', 'status', 'progress', 'sort', 'remark', 'create_by' => '__OperateId'],
        'update' => ['id', 'project_name', 'project_code', 'description', 'owner_id', 'start_date', 'end_date', 'status', 'progress', 'sort', 'remark', 'update_by' => '__OperateId'],
        'update_status' => ['id', 'status'],
        'update_progress' => ['id', 'progress'],
    ];
}
