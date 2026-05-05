<?php

declare(strict_types=1);

namespace App\Common\Filter;

class TkRequirementFilter extends BaseFilter
{
    protected array $scene = [
        'list' => ['page', 'pageSize', 'requirement_name', 'project_id', 'priority', 'status'],
        'info' => ['id'],
        'add' => ['requirement_name', 'requirement_code', 'description', 'source_type', 'source_file_id', 'source_file_name', 'project_id', 'priority', 'status', 'start_date', 'end_date', 'owner_id', 'sort', 'remark', 'create_by' => '__OperateId', 'create_time' => '__Now'],
        'update' => ['id', 'requirement_name', 'requirement_code', 'description', 'source_type', 'source_file_id', 'source_file_name', 'project_id', 'priority', 'status', 'start_date', 'end_date', 'actual_end_date', 'owner_id', 'sort', 'remark', 'update_by' => '__OperateId'],
        'update_status' => ['id', 'status'],
    ];
}
