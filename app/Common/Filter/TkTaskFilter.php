<?php

declare(strict_types=1);

namespace App\Common\Filter;

class TkTaskFilter extends BaseFilter
{
    protected array $scene = [
        'list' => ['page', 'pageSize', 'title', 'group_id', 'requirement_id', 'status', 'priority'],
        'info' => ['id'],
        'add' => ['title', 'description', 'group_id', 'requirement_id', 'import_batch_id', 'import_row_num', 'creator_id', 'status', 'priority', 'progress', 'start_date', 'due_date', 'sort', 'create_time' => '__Now'],
        'update' => ['id', 'title', 'description', 'group_id', 'requirement_id', 'status', 'priority', 'progress', 'start_date', 'due_date', 'sort'],
        'update_status' => ['id', 'status'],
        'update_progress' => ['id', 'progress'],
    ];
}
