<?php

declare(strict_types=1);

namespace App\Admin\Service;

use App\Common\Exception\AppException;
use App\Common\Model\SysRoleMenuModel;
use App\Common\Model\SysRoleModel;
use Hyperf\DbConnection\Db;
use Psr\Log\LoggerInterface;

class SysRoleService
{
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Desc: 获取列表
     * Auth: hello pan
     * Date: 2/23/26 1:25 PM
     * @param array $params
     * @return array
     */
    public function getList(array $params = []): array
    {
        $where = quickFilterWhereKey($params, ['role_name|like','role_code|like','status']);
        $query = SysRoleModel::query()->where($where);
        $page = $params['page'] ?? 1;
        $pageSize = $params['limit'] ?? 15;
        
        $total = $query->count();
        $list = $query->with('menuIds:role_id,menu_id')
            ->forPage($page,$pageSize)
            ->orderBy('sort', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->toArray();

        foreach ($list as &$v){
            $v['menu_ids'] = array_column($v['menu_ids'],'menu_id');
        }
        
        return [
            'list' => $list,
            'count' => $total,
        ];
    }

    /**
     * Desc: 获取详情
     * Auth: hello pan
     * Date: 2/23/26 1:26 PM
     * @param int $id
     * @return array|null
     */
    public function getInfoById(int $id): ?array
    {
        $role = SysRoleModel::where('id', $id)
            ->first();
        
        if (!$role) {
            return null;
        }
        
        $data = $role->toArray();
        
        $menuIds = SysRoleMenuModel::where('role_id', $id)
            ->pluck('menu_id')
            ->toArray();
        $data['menu_ids'] = $menuIds;
        
        return $data;
    }

    /**
     * Desc: 创建角色
     * Auth: hello pan
     * Date: 2/23/26 1:26 PM
     * @param array $data
     * @return int
     * @throws AppException
     */
    public function create(array $data): int
    {
        $exists = SysRoleModel::where('role_code', $data['role_code'])
            ->exists();
        if ($exists) {
            throw new AppException('角色编码已存在');
        }

        $menu_ids = $data['menu_ids'];
        unset($data['menu_ids']);
        $role_id = SysRoleModel::insertGetId($data);
        if (!$role_id){
            throw new AppException('添加角色失败');
        }

        $role_menu_data = [];
        foreach ($menu_ids as $menu_id){
            $role_menu_data[] = [
                'role_id' => $role_id,
                'menu_id' => $menu_id,
            ];
        }
        if(!empty($role_menu_data)){
            SysRoleMenuModel::insert($role_menu_data);
        }

        return $role_id;

    }


    /**
     * Desc: 更新
     * Auth: hello pan
     * Date: 2/23/26 2:31 PM
     * @param array $params
     * @return bool
     * @throws AppException
     */
    public function update(array $params): bool
    {
        $model = SysRoleModel::with('menuIds:role_id,menu_id')->where('id', $params['id'])
            ->first();
        
        if (!$model) {
            throw new AppException('角色不存在');
        }
        
        if ($model->role_code !== $params['role_code']) {
            $exists = SysRoleModel::where('role_code', $params['role_code'])
                ->where('id', '<>', $params['id'])
                ->exists();
            
            if ($exists) {
                throw new AppException('角色编码已存在');
            }
        }

        $role_info = $model->toArray();

        $old_menu_ids = array_column($role_info['menu_ids'],'menu_id');
        sort($old_menu_ids);
        $menu_ids = $params['menu_ids'];
        sort($menu_ids);
        unset($params['menu_ids']);

        Db::beginTransaction();
        try {

            SysRoleModel::where('id', $params['id'])->update($params);
            if($old_menu_ids != $menu_ids){

                if(!empty($old_menu_ids)){
                    SysRoleMenuModel::where('role_id',$params['id'])->delete();
                }

                $role_menu_data = [];
                foreach ($menu_ids as $_id){
                    $role_menu_data[] = [
                        'role_id' => $params['id'],
                        'menu_id' => $_id,
                    ];
                }

                SysRoleMenuModel::insert($role_menu_data);
            }
            Db::commit();
        }catch (\Throwable $e){
            Db::rollBack();
            throw new AppException('更新失败:'.$e->getMessage());
        }

        return true;
    }

    /**
     * Desc: 更新状态
     * Auth: hello pan
     * Date: 2/23/26 2:31 PM
     * @param array $params
     * @return bool
     * @throws AppException
     */
    public function updateStatus(array $params): bool
    {
        $model = SysRoleModel::where('id', $params['id'])
            ->first();
        if (!$model) {
            throw new AppException('角色不存在');
        }
        
        return $model->where('id', $params['id'])->update(['status' => $params['status']]) > 0;
    }

    /**
     * Desc: 删除操作
     * Auth: hello pan
     * Date: 2/23/26 2:32 PM
     * @param int $id
     * @return bool
     * @throws AppException
     */
    public function delete(int $id): bool
    {
        $model = SysRoleModel::where('id', $id)
            ->first();
        
        if (!$model) {
            throw new AppException('角色不存在');
        }
        
        Db::beginTransaction();
        try {
            $model->destroy($id);
            SysRoleMenuModel::where('role_id', $id)->delete();
            Db::commit();
            return true;
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error('删除角色失败: ' . $e->getMessage());
            throw new AppException('删除失败');
        }
    }

    /**
     * Desc: 获取菜单id
     * Auth: hello pan
     * Date: 2/23/26 2:33 PM
     * @param int $roleId
     * @return array
     */
    public function getMenuIdsByRoleId(int $roleId): array
    {
        return SysRoleMenuModel::where('role_id', $roleId)
            ->pluck('menu_id')
            ->toArray();
    }

    /**
     * Desc: 获取所有的角色
     * Auth: hello pan
     * Date: 2/23/26 2:33 PM
     * @return array
     */
    public function getAllRoles(): array
    {
        return SysRoleModel::where('status', 1)
            ->orderBy('sort', 'asc')
            ->get()
            ->toArray();
    }
}
