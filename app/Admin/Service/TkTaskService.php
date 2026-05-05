<?php

declare(strict_types=1);

namespace App\Admin\Service;

use App\Common\Exception\AppException;
use App\Common\Model\TkTaskAssigneeModel;
use App\Common\Model\TkTaskModel;
use Hyperf\DbConnection\Db;
use Psr\Log\LoggerInterface;

class TkTaskService
{
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getList(array $params = []): array
    {
        $where = [];
        
        if (!empty($params['title'])) {
            $where[] = ['title', 'like', '%' . $params['title'] . '%'];
        }
        
        if (!empty($params['group_id'])) {
            $where[] = ['group_id', '=', (int) $params['group_id']];
        }
        
        if (!empty($params['requirement_id'])) {
            $where[] = ['requirement_id', '=', (int) $params['requirement_id']];
        }
        
        if (isset($params['status']) && $params['status'] !== '') {
            $where[] = ['status', '=', (int) $params['status']];
        }
        
        if (isset($params['priority']) && $params['priority'] !== '') {
            $where[] = ['priority', '=', (int) $params['priority']];
        }
        
        $query = TkTaskModel::query()->where($where);
        
        $page = $params['page'] ?? 1;
        $pageSize = $params['pageSize'] ?? 15;
        
        $total = $query->count();
        $list = $query->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->orderBy('priority', 'asc')
            ->orderBy('sort', 'asc')
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();
        
        foreach ($list as &$item) {
            $item['assignees'] = TkTaskAssigneeModel::where('task_id', $item['id'])
                ->pluck('user_id')
                ->toArray();
        }
        
        return [
            'list' => $list,
            'total' => $total,
        ];
    }

    public function getInfoById(int $id): ?array
    {
        $task = TkTaskModel::find($id);
        
        if (!$task) {
            return null;
        }
        
        $data = $task->toArray();
        
        $assignees = TkTaskAssigneeModel::where('task_id', $id)
            ->pluck('user_id')
            ->toArray();
        $data['assignee_ids'] = $assignees;
        
        return $data;
    }

    public function create(array $data): TkTaskModel
    {
        return TkTaskModel::create($data);
    }

    public function update(array $params): bool
    {
        $model = TkTaskModel::find($params['id']);
        
        if (!$model) {
            throw new AppException('任务不存在');
        }
        
        return $model->update($params) > 0;
    }

    public function updateStatus(array $params): bool
    {
        $model = TkTaskModel::find($params['id']);
        
        if (!$model) {
            throw new AppException('任务不存在');
        }
        
        $updateData = ['status' => $params['status']];
        
        if ($params['status'] == 3) {
            $updateData['completed_time'] = date('Y-m-d H:i:s');
            $updateData['progress'] = 100;
        }
        
        return $model->update($updateData) > 0;
    }

    public function updateProgress(array $params): bool
    {
        $model = TkTaskModel::find($params['id']);
        
        if (!$model) {
            throw new AppException('任务不存在');
        }
        
        $updateData = ['progress' => $params['progress']];
        
        if ($params['progress'] == 100) {
            $updateData['status'] = 3;
            $updateData['completed_time'] = date('Y-m-d H:i:s');
        }
        
        return $model->update($updateData) > 0;
    }

    public function delete(int $id): bool
    {
        $model = TkTaskModel::find($id);
        
        if (!$model) {
            throw new AppException('任务不存在');
        }
        
        Db::beginTransaction();
        try {
            $model->delete();
            TkTaskAssigneeModel::where('task_id', $id)->delete();
            Db::commit();
            return true;
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error('删除任务失败: ' . $e->getMessage());
            throw new AppException('删除失败');
        }
    }

    public function getByGroupId(int $groupId): array
    {
        return TkTaskModel::where('group_id', $groupId)
            ->orderBy('priority', 'asc')
            ->orderBy('sort', 'asc')
            ->get()
            ->toArray();
    }

    public function getByRequirementId(int $requirementId): array
    {
        return TkTaskModel::where('requirement_id', $requirementId)
            ->orderBy('priority', 'asc')
            ->orderBy('sort', 'asc')
            ->get()
            ->toArray();
    }
}
