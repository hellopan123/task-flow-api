<?php

declare(strict_types=1);

namespace App\Api\Filter;

use App\Common\Filter\BaseFilter;

class TaskFilter extends BaseFilter
{
    protected array $scene = [
        'list' => ['page', 'limit', 'project_id', 'requirement_id', 'status', 'priority', 'type', 'title'],
        'create' => ['title', 'description', 'project_id', 'requirement_id', 'owner_id', 'priority', 'start_date', 'due_date'],
        'update' => ['id', 'title', 'description', 'owner_id', 'priority', 'start_date', 'due_date'],
        'assign' => ['id', 'owner_id'],
        'update_status' => ['id', 'status','remark'],
        'batch_create' => ['tasks'],
        'batch_assign' => ['task_ids', 'owner_id'],
    ];
}
