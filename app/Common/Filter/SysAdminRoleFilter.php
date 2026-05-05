<?php
namespace App\Common\Filter;


/**
 * 系统管理员角色过滤器类
 */
class SysAdminRoleFilter extends BaseFilter
{
    protected array $scene = [
        'info'      => ['id'],
        'add'       => ['name','status','remark','menu_ids'],
        'update'    => ['id','name','status','remark','menu_ids'],
        'update_status'    => ['id','status'],
    ];


    /**
     * Desc: 菜单ids处理
     * Auth: hello pan
     * Date: 12/18/25 7:17 PM
     * @param $value
     * @param $data
     * @param $result
     * @return string
     */
    protected function formatMenuIds($value = '', $data = [], $result = []):string
    {
        if (is_array($value)) {
            $value = empty($value) ? '' : implode(',', $value);
        }
        return $value;
    }
}