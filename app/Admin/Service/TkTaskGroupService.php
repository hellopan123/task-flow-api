<?php

declare(strict_types=1);

namespace App\Admin\Service;

use App\Common\Exception\AppException;
use App\Common\Model\TkGroupMemberModel;
use App\Common\Model\TkTaskGroupModel;
use Hyperf\DbConnection\Db;
use Psr\Log\LoggerInterface;

class TkTaskGroupService
{
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getList(array $params = []): array
    {
        $where = [];
        
        if (!empty($params['group_name'])) {
            $where[] = ['group_name', 'like', '%' . $params['group_name'] . '%'];
        }
        
        if (isset($params['status']) && $params['status'] !== '') {
            $where[] = ['status', '=', (int) $params['status']];
        }
        
        $query = TkTaskGroupModel::query()->where($where);
        
        $page = $params['page'] ?? 1;
        $pageSize = $params['pageSize'] ?? 15;
        
        $total = $query->count();
        $list = $query->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->orderBy('sort', 'asc')
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();
        
        return [
            'list' => $list,
            'total' => $total,
        ];
    }

    public function getInfoById(int $id): ?array
    {
        $group = TkTaskGroupModel::find($id);
        
        if (!$group) {
            return null;
        }
        
        $data = $group->toArray();
        
        $members = TkGroupMemberModel::where('group_id', $id)
            ->pluck('user_id')
            ->toArray();
        $data['member_ids'] = $members;
        
        return $data;
    }

    public function create(array $data): TkTaskGroupModel
    {
        return TkTaskGroupModel::create($data);
    }

    public function update(array $params): bool
    {
        $model = TkTaskGroupModel::find($params['id']);
        
        if (!$model) {
            throw new AppException('分组不存在');
        }
        
        return $model->update($params) > 0;
    }

    public function updateStatus(array $params): bool
    {
        $model = TkTaskGroupModel::find($params['id']);
        
        if (!$model) {
            throw new AppException('分组不存在');
        }
        
        return $model->update(['status' => $params['status']]) > 0;
    }

    public function delete(int $id): bool
    {
        $model = TkTaskGroupModel::find($id);
        
        if (!$model) {
            throw new AppException('分组不存在');
        }
        
        Db::beginTransaction();
        try {
            $model->delete();
            TkGroupMemberModel::where('group_id', $id)->delete();
            Db::commit();
            return true;
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error('删除任务分组失败: ' . $e->getMessage());
            throw new AppException('删除失败');
        }
    }

    public function getAllGroups(): array
    {
        return TkTaskGroupModel::where('status', 1)
            ->orderBy('sort', 'asc')
            ->get()
            ->toArray();
    }
}
