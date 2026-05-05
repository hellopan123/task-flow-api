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

use App\Common\Cache\db02\SysAdminRoleStringCache;
use App\Common\Exception\AppException;
use App\Common\Model\SysAdminRoleModel;
use App\Common\Model\SysMenuModel;
use Hyperf\Di\Annotation\Inject;
use Psr\Log\LoggerInterface;

class SysAdminRoleService
{

    #[Inject]
    protected LoggerInterface $logger;


    /**
     * Desc: 获取管理员角色列表
     * Auth: hello pan
     * Date: 2/15/26 8:33 PM
     * @param array $params
     * @return array
     */
    public function getList(array $params)
    {

        $where = quickFilterWhereKey($params,['name|like','status']);
        $query = SysAdminRoleModel::query();
        $query->where($where);

        $page = $params['page'] ?? 1;
        $pageSize = $params['page_size'] ?? 15;

        $total = $query->count();
        $list = $query->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();

        return[
            'list' => $list,
            'count' => $total,
        ];
    }


    /**
     * Desc: 获取详情
     * Auth: hello pan
     * Date: 2/15/26 10:16 PM
     * @param array $params
     * @return array|mixed[]
     * @throws AppException
     */
    public function getInfoById($id)
    {
        $role = SysAdminRoleModel::find($id);
        if (! $role) {
            throw new AppException('角色不存在');
        }
        return $role->toArray();
    }


    /**
     * Desc: 添加操作
     * Auth: hello pan
     * Date: 2/15/26 10:19 PM
     * @param array $params
     * @return int
     * @throws AppException
     */
    public function add(array $params)
    {
        $id = SysAdminRoleModel::insertGetId($params);
        if (!$id) {
            throw new AppException('添加角色失败');
        }
        return $id;
    }


    /**
     * Desc: 更新角色
     * Auth: hello pan
     * Date: 12/23/25 1:56 PM
     * @param array $params
     * @throws AppException
     */
    public function update(array $params)
    {
        // 验证角色ID是否为默认角色
        if($params['id'] == 1){
            throw new AppException('默认角色不能更新!');
        }
        $res = SysAdminRoleModel::where('id',$params['id'])->update($params);
        if ($res === false) {
            throw new AppException('更新角色失败');
        }

        // 清除角色缓存
        //SysAdminRoleStringCache::clearCache($params['id']);
        return $res;
    }



    /**
     * Desc: 获取管理员角色菜单
     * Auth: hello pan
     * Date: 2/15/26 8:23 PM
     * @param int $roleId
     * @param int $ttl
     * @return array
     */
    public static function getAdminRoleMenuAuthCode(int $roleId, int $ttl = 60): array
    {
        $instance = SysAdminRoleStringCache::make([$roleId]);
        if ($instance->exists()) {
            $data = $instance->get();
            return is_array($data) ? $data['menu_codes'] ?? [] : [];
        }

        $role = SysAdminRoleModel::find($roleId);
        if (! $role) {
            return [];
        }

        $menuIds = $role->menu_ids ?? [];
        if (empty($menuIds)) {
            return [];
        }

        $menus = SysMenuModel::whereIn('id', $menuIds)
            ->where('status', 1)
            ->select('auth_code', 'path')
            ->get()
            ->toArray();

        $menuCodes = array_column($menus, 'auth_code');
        $menuPaths = array_column($menus, 'path');

        $cacheData = [
            'menu_ids' => $menuIds,
            'menu_codes' => $menuCodes,
            'menu_paths' => $menuPaths,
        ];

        $instance->set($cacheData);
        $instance->expire($ttl);

        return $menuCodes;
    }

    /**
     * Desc: 获取管理员角色数据
     * Auth: hello pan
     * Date: 2/15/26 8:26 PM
     * @param int $id
     * @param array $fields
     * @return array|null
     */
    public static function getSmallData(int $id, array $fields = ['*']): ?array
    {
        $instance = SysAdminRoleStringCache::make([$id]);
        if ($instance->exists()) {
            $data = $instance->get();
            if ($fields !== ['*']) {
                return array_intersect_key($data, array_flip($fields));
            }
            return $data;
        }

        return null;
    }

    /**
     * Desc: 获取管理员角色菜单
     * Auth: hello pan
     * Date: 2/15/26 8:26 PM
     * @param int $roleId
     * @param int $ttl
     * @return array
     */
    public static function getAdminRoleMenus(int $roleId, int $ttl = 60): array
    {
        $cacheKey = 'menus:' . $roleId;
        $instance = SysAdminRoleStringCache::make([$cacheKey]);
        if ($instance->exists()) {
            return $instance->get();
        }

        $role = SysAdminRoleModel::find($roleId);
        if (! $role) {
            return [];
        }

        $menuIds = $role->menu_ids ?? [];
        if (empty($menuIds)) {
            return [];
        }

        $menus = SysMenuModel::whereIn('id', $menuIds)
            ->where('status', 1)
            ->orderBy('sort', 'asc')
            ->get()
            ->toArray();

        $result = self::buildMenuTree($menus, 0);

        $instance->set($result);
        $instance->expire($ttl);

        return $result;
    }

    /**
     * Desc: 组装菜单树
     * Auth: hello pan
     * Date: 2/15/26 8:25 PM
     * @param array $menus
     * @param int $parentId
     * @return array
     */
    protected static function buildMenuTree(array $menus, int $parentId = 0): array
    {
        $tree = [];

        foreach ($menus as $menu) {
            if ($menu['parent_id'] == $parentId) {
                $children = self::buildMenuTree($menus, $menu['id']);
                if (! empty($children)) {
                    $menu['children'] = $children;
                }
                $tree[] = $menu;
            }
        }

        return $tree;
    }


    
}
