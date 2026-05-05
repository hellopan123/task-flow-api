<?php

declare(strict_types=1);

namespace App\Admin\Service;

use App\Common\Exception\AppException;
use App\Common\Model\TkProjectMemberModel;
use App\Common\Model\TkProjectModel;
use Hyperf\DbConnection\Db;
use Psr\Log\LoggerInterface;

class TkProjectService
{
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getList(array $params = []): array
    {
        $where = quickFilterWhereKey($params,['project_name|like','project_code|like','owner_id','status']);
        
        $query = TkProjectModel::query()->where($where);
        
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 15;
        
        $total = $query->count();
        $list = $query->forPage($page,$limit)
            ->orderBy('sort', 'asc')
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
        $project = TkProjectModel::find($id);
        
        if (!$project) {
            return null;
        }
        
        $data = $project->toArray();
        
        $members = TkProjectMemberModel::where('project_id', $id)
            ->pluck('user_id')
            ->toArray();
        $data['member_ids'] = $members;
        
        return $data;
    }

    public function create(array $data): int
    {
        $exists = TkProjectModel::where('project_code', $data['project_code'])->exists();
        
        if ($exists) {
            throw new AppException('项目编码已存在');
        }
        
        return TkProjectModel::insertGetId($data);
    }

    public function update(array $params): bool
    {
        $model = TkProjectModel::find($params['id']);
        
        if (!$model) {
            throw new AppException('项目不存在');
        }
        
        if ($model->project_code !== $params['project_code']) {
            $exists = TkProjectModel::where('project_code', $params['project_code'])
                ->where('id', '<>', $params['id'])
                ->exists();
            
            if ($exists) {
                throw new AppException('项目编码已存在');
            }
        }
        
        return $model->where('id',$params['id'])->update($params) > 0;
    }

    public function updateStatus(array $params): bool
    {
        $model = TkProjectModel::find($params['id']);
        
        if (!$model) {
            throw new AppException('项目不存在');
        }
        
        return $model->where('id',$params['id'])->update(['status' => $params['status']]) > 0;
    }

    public function updateProgress(array $params): bool
    {
        $model = TkProjectModel::find($params['id']);
        
        if (!$model) {
            throw new AppException('项目不存在');
        }
        
        return $model->where('id',$params['id'])->update(['progress' => $params['progress']]) > 0;
    }

    public function delete(int $id): bool
    {
        $model = TkProjectModel::find($id);
        
        if (!$model) {
            throw new AppException('项目不存在');
        }
        
        Db::beginTransaction();
        try {
            $model->delete();
            TkProjectMemberModel::where('project_id', $id)->delete();
            Db::commit();
            return true;
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error('删除项目失败: ' . $e->getMessage());
            throw new AppException('删除失败');
        }
    }

    public function getAllProjects(): array
    {
        return TkProjectModel::where('status', '<>', 3)
            ->orderBy('sort', 'asc')
            ->get()
            ->toArray();
    }
}
