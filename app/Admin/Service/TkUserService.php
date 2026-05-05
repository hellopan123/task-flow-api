<?php

declare(strict_types=1);

namespace App\Admin\Service;

use App\Common\Exception\AppException;
use App\Common\Model\TkUserModel;
use Psr\Log\LoggerInterface;

class TkUserService
{
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getList(array $params = []): array
    {
        $where = quickFilterWhereKey($params,['nickname|like','phone|like','email|like','status']);
        
        $query = TkUserModel::query()->where($where);
        
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 15;
        
        $total = $query->count();
        $list = $query->forPage($page,$limit)
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();
        
        return [
            'list' => $list,
            'count' => $total,
        ];
    }

    public function getInfoById(int $id): ?array
    {
        $user = TkUserModel::find($id);
        
        if (!$user) {
            return null;
        }
        
        return $user->toArray();
    }

    /**
     * Desc: 创建用户
     * Auth: hello pan
     * Date: 2/24/26 10:07 PM
     * @param array $data
     * @return int
     * @throws AppException
     */
    public function create(array $data): int
    {
        $exists = TkUserModel::where('phone', $data['phone'])->exists();
        if ($exists) {
            throw new AppException('手机号已存在');
        }
        
        return TkUserModel::insertGetId($data);
    }

    public function updateStatus(array $params): bool
    {
        $model = TkUserModel::find($params['id']);
        
        if (!$model) {
            throw new AppException('用户不存在');
        }
        
        return $model->where('id',$params['id'])->update(['status' => $params['status']]) > 0;
    }

    public function delete(int $id): bool
    {
        $model = TkUserModel::find($id);
        
        if (!$model) {
            throw new AppException('用户不存在');
        }
        
        return $model->delete() > 0;
    }

    public function getAllUsers(): array
    {
        return TkUserModel::where('status', 1)
            ->select(['id', 'nickname', 'avatar', 'phone', 'email'])
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();
    }
}
