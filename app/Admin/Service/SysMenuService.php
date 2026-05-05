<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Admin\Service;

use App\Common\Exception\AppException;
use App\Common\Model\SysMenuModel;
use Hyperf\Context\Context;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Psr\Log\LoggerInterface;

class SysMenuService
{
    #[Inject]
    protected LoggerInterface $logger;

    /**
     * Desc: 获取菜单列表
     * Auth: hello pan
     * Date: 2/11/26 10:44 PM.
     * @return mixed
     */
    public function getList()
    {
        $query = SysMenuModel::query()->orderBy('pid', 'asc')->orderBy('order', 'asc');
        $sys_menu = $query->get()->toArray();
        $menu_list = make_tree_menu_button($sys_menu, 'id', 'pid', 'children', 0);
        return $menu_list['menu_tree'];
    }

    /**
     * Desc: 获取菜单详情
     * Auth: hello pan
     * Date: 2/15/26 10:30 PM
     * @param int $id
     * @return array
     * @throws AppException
     */
    public function getInfo(int $id): array
    {
        $menu = SysMenuModel::find($id);
        if (! $menu) {
            throw new AppException('菜单不存在');
        }
        return $menu->toArray();
    }

    /**
     * Desc: 获取导航菜单
     * Auth: hello pan
     * Date: 2/15/26 10:30 PM
     * @return array
     */
    public function getNavMenu(): array
    {
        $userInfo = Context::get('user_info');
        $roleId = $userInfo['role_id'] ?? 0;

        if ($roleId === 1) {
            $menus = SysMenuModel::where('status', 1)
                ->orderBy('sort', 'asc')
                ->orderBy('id', 'desc')
                ->get()
                ->toArray();
        } else {
            $menuIds = $userInfo['menu_ids'] ?? [];
            if (empty($menuIds)) {
                return [];
            }

            $menus = SysMenuModel::whereIn('id', $menuIds)
                ->where('status', 1)
                ->orderBy('sort', 'asc')
                ->orderBy('id', 'desc')
                ->get()
                ->toArray();
        }

        return $this->buildMenuTree($menus);
    }

    /**
     * Desc: 添加菜单
     * Auth: hello pan
     * Date: 2/15/26 10:30 PM
     * @param array $params
     * @return int
     * @throws AppException
     */
    public function add(array $params): int
    {
        $id = SysMenuModel::insertGetId($params);
        if (! $id) {
            throw new AppException('添加菜单失败');
        }
        return $id;
    }

    /**
     * Desc: 更新菜单
     * Auth: hello pan
     * Date: 2/15/26 10:30 PM
     * @param array $params
     * @return bool
     * @throws AppException
     */
    public function update(array $params): bool
    {
        $id = $params['id'];
        unset($params['id']);
        
        $res = SysMenuModel::where('id', $id)->update($params);
        if ($res === false) {
            throw new AppException('更新菜单失败');
        }
        return true;
    }

    /**
     * Desc: 删除菜单操作
     * Auth: hello pan
     * Date: 12/19/25 4:58 PM
     * @param string $id 菜单id
     * @return true
     * @throws AppException
     */
    public static function delete($id)
    {
        // 获取当前删除菜单的下级所有的id
        $sub_menu_list = SysMenuModel::where([
            ['status', '=', 1],
        ])->get()->toArray();

        $del_menu_ids = make_tree_lower($sub_menu_list,$id);
        $del_menu_ids[] = $id;

        $res = SysMenuModel::where('id','in',$del_menu_ids)->delete();
        if(!$res){
            throw new AppException('删除菜单失败');
        }
        return true;
    }

    /**
     * Desc: 构建菜单树
     * Auth: hello pan
     * Date: 2/15/26 10:30 PM
     * @param array $menus
     * @return array
     */
    protected function buildMenuTree(array $menus): array
    {
        $tree = [];
        $map = [];

        foreach ($menus as &$menu) {
            $menu['children'] = [];
            $map[$menu['id']] = &$menu;
        }
        unset($menu);

        foreach ($map as &$item) {
            if (isset($map[$item['parent_id']]) && $item['parent_id'] > 0) {
                $map[$item['parent_id']]['children'][] = &$item;
            } else {
                $tree[] = &$item;
            }
        }

        return $tree;
    }
}
