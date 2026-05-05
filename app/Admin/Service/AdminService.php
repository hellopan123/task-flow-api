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
use App\Common\Model\SysAdminModel;
use App\Common\Model\SysAdminRoleModel;
use App\Common\Service\JwtService;
use Hyperf\Context\Context;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Log\LoggerInterface;
use function Hyperf\Support\make;

class AdminService
{
    #[Inject]
    protected LoggerInterface $logger;


    public function __construct(protected RequestInterface $request)
    {
    }

    /**
     * Desc: 登录操作
     * Auth: hello pan
     * Date: 2/8/26 3:18 PM
     * @param array $params
     * @return array
     * @throws AppException
     */
    public function login(array $params): array
    {
        return $this->loginHandle([['username', '=', $params['username']]], $params['password']);
    }

    /**
     * Desc: 使用id获取详情
     * Auth: hello pan
     * Date: 2/22/26 11:13 AM
     * @param int $id
     * @return array|null
     * @throws AppException
     */
    public function getInfoById(int $id): ?array
    {
        $admin = SysAdminModel::with('roles:admin_id,role_id')->find($id);
        if (! $admin) {
           throw new AppException('用户不存在');
        }
        $data = $admin->toArray();
        //提取 role_id 数组
        $data['role_ids'] = array_column($data['roles'], 'role_id');
        unset($data['password'], $data['salt'],$data['roles']);

        return $data;
    }

    /**
     * Desc: 条件查询获取详情
     * Auth: hello pan
     * Date: 2/22/26 11:13 AM
     * @param array $where
     * @return array|null
     */
    public function getInfoByWhere(array $where): ?array
    {
        $admin = SysAdminModel::where($where)->first();
        if (! $admin) {
            return null;
        }
        return $admin->toArray();
    }


    /**
     * Desc: 获取用户信息
     * Auth: hello pan
     * Date: 2/22/26 11:14 AM
     * @param int $userId
     * @return array|null
     * @throws AppException
     */
    public function getUserInfo(int $userId): ?array
    {
        return $this->getInfoById($userId);
    }

    /**
     * Desc: 更新管理员状态
     * Auth: hello pan
     * Date: 2/14/26 10:02 PM
     * @param $params
     * @return true
     * @throws AppException
     */
    public function updateStatus($params)
    {
        $id = $params['id'];
        if ($id == 1) {
            throw new \Exception('顶级管理员不可操作！');
        }
        $result = SysAdminModel::where('id', $id)->update(['status' => $params['status']]);
        if (!$result) throw new AppException('操作失败');
        return true;
    }

    /**
     * Desc: 更新管理员信息
     * Auth: hello pan
     * Date: 2/14/26 9:58 PM
     * @param array $params
     * @return bool
     * @throws AppException
     */
    public function update(array $params): bool
    {
        // 判断用户是否存在
        $model = SysAdminModel::find($params['id']);
        if (!$model) {
            throw new AppException('管理员不存在');
        }

        Db::beginTransaction();
        try {
            $role_ids = $params['role_ids'];
            unset($params['role_ids']);
            SysAdminModel::where('id', $params['id'])->update($params);
            // 更新关联角色
            SysAdminRoleModel::where('admin_id',$params['id'])->delete();
            if(!empty($role_ids)){
                $role_data = [];
                foreach ($role_ids as $_role){
                    $role_data[] = [
                        'admin_id' => $params['id'],
                        'role_id' => $_role ,
                    ];
                }
                SysAdminRoleModel::insert($role_data);
            }
            Db::commit();
        }catch (\Throwable $e){
            Db::rollBack();
            $this->logger->info('更新失败：'.$e->getMessage());
            throw new AppException('操作失败');
        }

        return true;
    }

    /**
     * Desc: 软删除操作
     * Auth: hello pan
     * Date: 2/22/26 11:15 AM
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        if ($id == 1){
            throw new AppException('管理员不支持删除');
        }
        return SysAdminModel::destroy($id) > 0;
    }

    /**
     * Desc: 获取管理员列表
     * Auth: hello pan
     * Date: 2/15/26 7:03 PM
     * @param array $params
     * @return array
     */
    public function getList(array $params = []): array
    {

        $where = quickFilterWhereKey($params, ['username|like','status']);

        $query = SysAdminModel::query()->with('roles:admin_id,role_id');

        $query->where($where);
        $page = $params['page'] ?? 1;
        $pageSize = $params['page_size'] ?? 15;

        $total = $query->count();
        $list = $query->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();

        foreach ($list as &$item) {
            $item['role_ids'] = array_column($item['roles'], 'role_id');
            unset($item['password'], $item['salt'],$item['roles']);
        }

        return [
            'list' => $list,
            'count' => $total,
        ];
    }

    /**
     * Desc: 登录处理
     * Auth: hello pan
     * Date: 2/8/26 3:16 PM.
     * @throws AppException
     */
    private function loginHandle(array $where, string $password = ''): array
    {
        $login_msg = '';
        try {
            $userInfo = $this->getInfoByWhere($where);

            if (! $userInfo) {
                throw new AppException('用户不存在或已被禁用');
            }

            if ($userInfo['login_fail'] > 8) {
                throw new AppException('您登录失败的次数过多，请联系管理员解封');
            }

            if (! empty($password) && $userInfo['password'] !=  password($password, $userInfo['salt'])) {
                $this->incrementLoginFail($userInfo['id']);
                throw new AppException('用户名或密码错误');
            }


            if ($userInfo['status'] !== 1) {
                throw new AppException('账号已被禁用');
            }

            $this->updateLoginInfo($userInfo['id'], get_real_ip());

            $appId = Context::get('app_id');
            $userInfo['user_type'] = Context::get('user_type');
            unset($userInfo['password'], $userInfo['salt']);
            $status = 1;
            return (new JwtService())->generateToken($userInfo, $appId);
        }catch (\Throwable $e){
            $status = 2;
            $login_msg = $e->getMessage();
            throw new AppException($e->getMessage());
        } finally {
            // 增加登录日志
            $agent_info = parseUserAgent($this->request->header('user-agent'));
            $log_data = [
                'username'  => $this->request->input('username',''),
                'ip'        => get_real_ip(),
                'location'  => '',
                'browser'   => $agent_info['browser'],
                'os'        => $agent_info['os'],
                'login_type' => 'password',
                'login_status' => $status,
                'login_msg'     => $login_msg,
            ];
            $login_log = make(SysLoginLogService::class);
            $login_log->create($log_data);
        }

    }


    /**
     * Desc: 增加登录失败次数
     * Auth: hello pan
     * Date: 2/22/26 11:15 AM
     * @param int $id
     * @return int
     */
    public function incrementLoginFail(int $id): int
    {
        return SysAdminModel::where('id', $id)->increment('login_fail', 1);
    }

    /**
     * Desc: 更新登录信息
     * Auth: hello pan
     * Date: 2/22/26 11:15 AM
     * @param int $id
     * @param string $ip
     * @return int
     */
    public function updateLoginInfo(int $id, string $ip): int
    {
        return SysAdminModel::where('id', $id)->update([
            'login_fail' => 0,
            'last_login_time' => date('Y-m-d H:i:s'),
            'last_login_ip' => $ip,
        ]);
    }

    /**
     * Desc: 创建操作
     * Auth: hello pan
     * Date: 2/22/26 11:15 AM
     * @param array $data
     * @return SysAdminModel
     */
    public function create(array $data): int
    {
        $role_ids = $data['role_ids'];
        unset($data['role_ids']);
        $admin_id = SysAdminModel::insertGetId($data);
        // 更新关联角色
        if(!empty($role_ids)){
            $role_data = [];
            foreach ($role_ids as $_role){
                $role_data[] = [
                    'admin_id' => $admin_id,
                    'role_id' => $_role ,
                ];
            }
            SysAdminRoleModel::insert($role_data);
        }
        return $admin_id;
    }

}
