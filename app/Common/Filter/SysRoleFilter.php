<?php

declare(strict_types=1);

namespace App\Common\Filter;

class SysRoleFilter extends BaseFilter
{
    protected array $scene = [
        'info' => ['id'],
        'add' => ['role_name', 'role_code', 'sort', 'status', 'remark','menu_ids', 'create_by' => '__OperateId'],
        'update' => ['id', 'role_name', 'role_code', 'sort', 'status', 'remark','menu_ids', 'update_by' => '__OperateId'],
        'update_status' => ['id', 'status'],
    ];
}
