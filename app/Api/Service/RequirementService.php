<?php

declare(strict_types=1);

namespace App\Api\Service;

use App\Common\Exception\AppException;
use App\Common\Model\TkProjectMemberModel;
use App\Common\Model\TkProjectModel;
use App\Common\Model\TkRequirementModel;
use App\Common\Model\TkTaskModel;
use Hyperf\Context\Context;
use Psr\Log\LoggerInterface;

class RequirementService
{
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getList(array $params = []): array
    {
        $userId = Context::get('user_id');

        $params['creator_id'] = $userId;
        $query = TkRequirementModel::query();
        $where =quickFilterWhereKey($params,['creator_id','project_id','requirement_name|like','status','priority','create_time-start_date-end_date|date_between']);
        $query->where($where)->where('deleted_at', null);
        
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 15;
        
        $count = $query->count();
        $list = $query->offset(($page - 1) * $limit)
            ->limit($limit)
            ->with(['project', 'owner', 'creator'])
            ->orderBy('priority', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();
            
        // 处理关联数据，将 project_name 提出来方便前端展示
        foreach ($list as &$item) {
            $item['project_name'] = $item['project']['project_name'] ?? '';
        }
        
        return ['list' => $list, 'count' => $count];
    }

    /**
     * Desc: 获取项目需求列表
     * Auth: hello pan
     * Date: 4/4/26 5:23 PM
     * @param int $projectId
     * @return array
     * @throws AppException
     */
    public function getByProject(int $projectId, array $params = []): array
    {
        $userId = Context::get('user_id');
        
        if (!$this->isProjectMember($projectId, $userId)) {
            throw new AppException('无权访问该项目需求');
        }
        $where = quickFilterWhereKey($params,['requirement_name|like','status']);
        
        return TkRequirementModel::where('project_id', $projectId)
            ->where('deleted_at', null)
            ->where($where)
            ->with(['project', 'owner', 'creator'])
            ->orderBy('priority', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();
    }

    public function getDetail(int $id): ?array
    {
        $userId = Context::get('user_id');
        
        $requirement = TkRequirementModel::where('id', $id)
            ->where('deleted_at', null)
            ->with(['project', 'owner', 'creator'])
            ->first();
        
        if (!$requirement) {
            return null;
        }
        
        if (!$this->isProjectMember($requirement->project_id, $userId)) {
            throw new AppException('无权访问该需求');
        }
        
        return $requirement->toArray();
    }

    /**
     * Desc: 添加需求
     * Auth: hello pan
     * Date: 4/4/26 4:36 PM
     * @param array $params
     * @return array
     * @throws AppException
     */
    public function create(array $params): array
    {
        $userId = Context::get('user_id');
        $params['creator_id'] = $userId;
        
        if (!$this->isProjectMember((int)$params['project_id'], $userId)) {
            throw new AppException('无权在该项目创建需求');
        }
        
        if (empty($params['requirement_code'])) {
            $params['requirement_code'] = self::generateCode((int)$params['project_id']);
        }
        
        $requirement = TkRequirementModel::create($params);
        
        return $requirement->toArray();
    }

    public function update(array $params): bool
    {
        $userId = Context::get('user_id');
        $id = $params['id'];
        
        $requirement = TkRequirementModel::where('id', $id)
            ->where('deleted_at', null)
            ->first();
        
        if (!$requirement) {
            throw new AppException('需求不存在');
        }
        
        if (!$this->isProjectMember($requirement->project_id, $userId)) {
            throw new AppException('无权修改该需求');
        }
        
        unset($params['id']);
        return $requirement->update($params) > 0;
    }

    /**
     * Desc: 更新需求状态
     * Auth: hello pan
     * Date: 4/4/26 5:21 PM
     * @param int $id
     * @param int $status
     * @return bool
     * @throws AppException
     */
    public function updateStatus(int $id, int $status): bool
    {
        $userId = Context::get('user_id');
        
        $requirement = TkRequirementModel::where('id', $id)
            ->where('deleted_at', null)
            ->first();
        
        if (!$requirement) {
            throw new AppException('需求不存在');
        }
        
        if (!$this->isProjectMember($requirement->project_id, $userId)) {
            throw new AppException('无权修改该需求');
        }
        
        return $requirement->update(['status' => $status]) > 0;
    }

    /**
     * Desc: 删除操作
     * Auth: hello pan
     * Date: 4/4/26 5:21 PM
     * @param int $id
     * @return bool
     * @throws AppException
     */
    public function delete(int $id): bool
    {
        $userId = Context::get('user_id');
        
        $requirement = TkRequirementModel::where('id', $id)
            ->where('deleted_at', null)
            ->first();
        
        if (!$requirement) {
            throw new AppException('需求不存在');
        }
        
        if (!$this->isProjectMember($requirement->project_id, $userId)) {
            throw new AppException('无权删除该需求');
        }
        
        return $requirement->update(['deleted_at' => date('Y-m-d H:i:s')]) > 0;
    }

    /**
     * Desc: 检查用户是否为项目成员
     * Auth: hello pan
     * Date: 4/4/26 5:20 PM
     * @param int $projectId
     * @param int $userId
     * @return bool
     */
    protected function isProjectMember(int $projectId, int $userId): bool
    {
        return TkProjectMemberModel::where('project_id', $projectId)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Desc: 生成需求编码
     * Auth: hello pan
     * Date: 4/4/26 5:20 PM
     * @param int $projectId
     * @return string
     */
    public static function generateCode(int $projectId): string
    {
        $project = TkProjectModel::find($projectId);
        $prefix = $project ? strtoupper($project->project_code) : 'REQ';

        $count = TkRequirementModel::where('project_id', $projectId)->count();

        return $prefix . '-' . str_pad((string) ($count + 1), 4, '0', STR_PAD_LEFT);
    }

    // ========== 需求导出 ==========
    public function export(int $requirementId, string $exportType = 'excel'): string
    {
        $requirement = TkRequirementModel::find($requirementId);
        if (!$requirement) {
            throw new AppException('需求不存在');
        }

        // 获取需求下的所有任务
        $tasks = TkTaskModel::where('requirement_id', $requirementId)
            ->where('deleted_at', null)
            ->get();

        // 生成导出文件
        $dir = BASE_PATH . '/runtime/exports/' . date('Ymd');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = 'requirement_' . $requirementId . '_' . time() . '.xlsx';
        $filePath = $dir . '/' . $filename;

        // 记录导出记录
        // 这里简化实现，实际需要生成 Excel 文件
        // 使用 PHPExcel 或 PhpSpreadsheet 生成

        // 返回文件路径（实际需要返回可访问的 URL）
        return '/runtime/exports/' . date('Ymd') . '/' . $filename;
    }

    public function getExportRecords(?int $requirementId): array
    {
        // 简化实现，返回空数组
        // 实际需要查询导出记录表
        return [];
    }
}
