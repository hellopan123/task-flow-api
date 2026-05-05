<?php
/**
 * Created by PhpStorm.
 * User: Hello pan
 * Date: 12/18/25
 * Time: 7:37 PM
 */

namespace App\Common\Filter;

use App\Common\Model\SysMenuModel;

class SysMenuFilter extends BaseFilter
{
    protected array $scene = [
        'add'       => ['pid', 'title', 'name','icon', 'component','path', 'auth_code', 'type','active_icon','active_path', 'affix_tab','affix_tab_order','badge','badge_type','badge_variants','hide_children_in_menu','hide_in_breadcrumb','hide_in_menu','hide_in_tab','keep_alive','link','order','level','status','admin_id' => '__operateId'],
        'update'    => ['id','pid', 'title', 'name','icon', 'component','path', 'auth_code', 'type','active_icon','active_path', 'affix_tab','affix_tab_order','badge','badge_type','badge_variants','hide_children_in_menu','hide_in_breadcrumb','hide_in_menu','hide_in_tab','keep_alive','link','order','level','status','admin_id' => '__operateId'],
    ];

    /**
     * Desc: 层级
     * Auth: hello pan
     * Date: 2024-06-14
     * @param string $value
     * @param array $data
     * @param array $result
     * @return int
     */
    protected function formatLevel($value = '', $data = [], $result = []) {
        if($result['pid'] == 0) return 1;
        $sys_menu_level = SysMenuModel::where('id', $result['pid'])->value('level');
        return $sys_menu_level + 1;
    }

}