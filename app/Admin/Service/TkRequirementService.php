<?php

declare(strict_types=1);

namespace App\Admin\Service;

use App\Common\Exception\AppException;
use App\Common\Model\TkRequirementModel;
use Psr\Log\LoggerInterface;

class TkRequirementService
{
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getList(array $params = []): array
    {
        $where = [];
        
        if (!empty($params['requirement_name'])) {
            $where[] = ['requirement_name', 'like', '%' . $params['requirement_name'] . '%'];
        }
        
        if (!empty($params['project_id'])) {
            $where[] = ['project_id', '=', (int) $params['project_id']];
        }
        
        if (isset($params['priority']) && $params['priority'] !== '') {
            $where[] = ['priority', '=', (int) $params['priority']];
        }
        
        if (isset($params['status']) && $params['status'] !== '') {
            $where[] = ['status', '=', (int) $params['status']];
        }
        
        $query = TkRequirementModel::query()->where($where);
        
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
        
        return [
            'list' => $list,
            'total' => $total,
        ];
    }

    public function getInfoById(int $id): ?array
    {
        $requirement = TkRequirementModel::find($id);
        
        if (!$requirement) {
            return null;
        }
        
        return $requirement->toArray();
    }

    public function create(array $data): TkRequirementModel
    {
        $exists = TkRequirementModel::where('requirement_code', $data['requirement_code'])->exists();
        
        if ($exists) {
            throw new AppException('需求编码已存在');
        }
        
        return TkRequirementModel::create($data);
    }

    public function update(array $params): bool
    {
        $model = TkRequirementModel::find($params['id']);
        
        if (!$model) {
            throw new AppException('需求不存在');
        }
        
        if ($model->requirement_code !== $params['requirement_code']) {
            $exists = TkRequirementModel::where('requirement_code', $params['requirement_code'])
                ->where('id', '<>', $params['id'])
                ->exists();
            
            if ($exists) {
                throw new AppException('需求编码已存在');
            }
        }
        
        return $model->update($params) > 0;
    }

    public function updateStatus(array $params): bool
    {
        $model = TkRequirementModel::find($params['id']);
        
        if (!$model) {
            throw new AppException('需求不存在');
        }
        
        $updateData = ['status' => $params['status']];
        
        if ($params['status'] == 3) {
            $updateData['actual_end_date'] = date('Y-m-d H:i:s');
        }
        
        return $model->update($updateData) > 0;
    }

    public function delete(int $id): bool
    {
        $model = TkRequirementModel::find($id);
        
        if (!$model) {
            throw new AppException('需求不存在');
        }
        
        return $model->delete() > 0;
    }

    public function getByProjectId(int $projectId): array
    {
        return TkRequirementModel::where('project_id', $projectId)
            ->where('status', '<>', 3)
            ->orderBy('priority', 'asc')
            ->orderBy('sort', 'asc')
            ->get()
            ->toArray();
    }
}
