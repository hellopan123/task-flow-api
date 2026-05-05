<?php
namespace App\Common\Filter;


/**
 * 系统管理员过滤器类
 *
 * @Author hello pan
 * @DateTime 2025-12-19
 */
class SysAdminFilter extends BaseFilter
{
    protected array $scene = [
        'info'      => ['id'],
        'add'       => ['username','salt'=>'__Salt','password' => '__NewPassword','gender','nickname','email','phone','avatar','role_ids','status','is_super','remark','create_by' => '__OperateId'],
        'update'    => ['id','username','gender','nickname','email','phone','avatar','role_ids','status','is_super','remark','update_by'=>'__OperateId'],
        'update_status' => ['id','status'],
    ];
}