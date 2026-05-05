<?php

declare(strict_types=1);

namespace App\Api\Service;

use App\Common\Exception\AppException;
use App\Common\Model\TkProjectMemberModel;
use App\Common\Model\TkProjectModel;
use App\Common\Model\TkRequirementModel;
use App\Common\Model\TkUserModel;
use App\Common\Model\TkUserRelationModel;
use Hyperf\Context\Context;
use Hyperf\DbConnection\Db;
use Psr\Log\LoggerInterface;
use function Hyperf\Coroutine\parallel;

class ProjectService
{
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Desc: 获取dict数据
     * Auth: hello pan
     * Date: 2/28/26 10:18 PM
     * @param array $params
     * @return array
     */
    public function getDict(array $params = []): array
    {
        $userId = Context::get('user_id');
        return TkProjectModel::query()
            ->where('creator_id', $userId)
            ->where('deleted_at', null)
            ->whereIn('status',[1,2])
            ->orderBy('id', 'desc')
            ->get(['id','project_name','project_code','start_date','end_date','status'])
            ->toArray();
    }


    /**
     * Desc: 获取项目列表
     * Auth: hello pan
     * Date: 4/4/26 1:37 PM
     * @param array $params
     * @return array
     */
    public function getList(array $params = []): array
    {
        $userId = Context::get('user_id');

        $params['creator_id'] = $userId;
        $where = quickFilterWhereKey($params,['status','project_name|like','creator_id']);
        $query = TkProjectModel::query()
            ->where($where)->where('deleted_at', null);
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 15;

        $count = $query->count();
        $list = $query->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();

        if (empty($list)) {
            return ['list' => [], 'count' => $count];
        }

        $projectIds = array_column($list, 'id');


        $option_data = parallel([
            'member_counts' => function ()use ($projectIds) {
                return TkProjectMemberModel::whereIn('project_id', $projectIds)
                    ->groupBy('project_id')
                    ->selectRaw('project_id, COUNT(*) as count')
                    ->pluck('count', 'project_id')
                    ->toArray();
            },
            'requirement_counts' => function ()use ($projectIds) {
                return TkRequirementModel::whereIn('project_id', $projectIds)
                    ->groupBy('project_id')
                    ->selectRaw('project_id, COUNT(*) as count')
                    ->pluck('count', 'project_id')
                    ->toArray();
            },
            'members_project' => function ()use ($projectIds) {
                // 批量获取成员信息
                $allMembers = TkProjectMemberModel::whereIn('project_id', $projectIds)
                    ->orderBy('role', 'asc')
                    ->get()
                    ->toArray();

                $userIds = array_column($allMembers, 'user_id');
                $users = TkUserModel::whereIn('id', $userIds)
                    ->where('status', 1)
                    ->get(['id', 'nickname', 'avatar', 'phone', 'email'])
                    ->toArray();
                $users = array_column($users, null, 'id');
                $membersByProject = [];
                foreach ($allMembers as $member) {
                    $member['user_info'] = $users[$member['user_id']] ?? null;
                    $membersByProject[$member['project_id']][] = $member;
                }
                return $membersByProject;
            }

        ]);

        foreach ($list as &$item) {
            $item['member_count'] = $option_data['member_counts'][$item['id']] ?? 0;
            $item['requirement_count'] = $option_data['requirement_counts'][$item['id']] ?? 0;
            $item['members'] = $option_data['members_project'][$item['id']] ?? [];
        }

        return ['list' => $list, 'count' => $count];
    }

    public function getDetail(int $id): ?array
    {
        $userId = Context::get('user_id');
        
        $project = TkProjectModel::where('id', $id)
            ->where('deleted_at', null)
            ->first();
        
        if (!$project) {
            return null;
        }
        
        if (!$this->isProjectMember($id, $userId)) {
            throw new AppException('无权访问该项目');
        }
        
        $data = $project->toArray();
        $data['members'] = $this->getMembers($id);
        $data['member_count'] = count($data['members']);
        $data['requirement_count'] = $this->getRequirementCount($id);
        
        return $data;
    }

    public function create(array $params): int
    {
        $userId = Context::get('user_id');
        $params['creator_id'] = $userId;
        
        $exists = TkProjectModel::where('project_code', $params['project_code'])
            ->where('deleted_at', null)
            ->exists();
        if ($exists) {
            throw new AppException('项目编码已存在');
        }
        $member = $params['member_ids'] ?? [];
        unset($params['member_ids']);
        $member[] = $userId;

        Db::beginTransaction();
        try {
            $project_id = TkProjectModel::insertGetId($params);

            $pro_member = [];
            foreach ($member as $_m){
                $pro_member[] = [
                    'project_id' => $project_id,
                    'user_id' => $_m,
                    'role' => 1,
                ];
            }

            $res = TkProjectMemberModel::insert($pro_member);
            if(!$res){
                throw new AppException('创建失败');
            }
            Db::commit();
            
            return $project_id;
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error('创建项目失败: ' . $e->getMessage());
            throw new AppException('创建项目失败');
        }
    }

    public function update(array $params): bool
    {
        $userId = Context::get('user_id');
        $id = $params['id'];
        
        $project = TkProjectModel::where('id', $id)
            ->where('deleted_at', null)
            ->first();
        
        if (!$project) {
            throw new AppException('项目不存在');
        }
        
        if (!$this->isProjectAdmin($id, $userId)) {
            throw new AppException('无权修改该项目');
        }
        
        unset($params['id']);
        return $project->update($params) > 0;
    }

    public function delete(int $id): bool
    {
        $userId = Context::get('user_id');
        
        $project = TkProjectModel::where('id', $id)
            ->where('deleted_at', null)
            ->first();
        
        if (!$project) {
            throw new AppException('项目不存在');
        }
        
        if (!$this->isProjectAdmin($id, $userId)) {
            throw new AppException('无权删除该项目');
        }
        
        Db::beginTransaction();
        try {
            $project->update(['deleted_at' => date('Y-m-d H:i:s')]);
            
            Db::commit();
            return true;
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error('删除项目失败: ' . $e->getMessage());
            throw new AppException('删除项目失败');
        }
    }

    /**
     * Desc: 添加项目成员
     * Auth: hello pan
     * Date: 4/4/26 3:25 PM
     * @param int $projectId
     * @param array $memberIds
     * @return bool
     * @throws AppException
     */
    public function addMembers(int $projectId, array $memberIds): bool
    {
        $userId = Context::get('user_id');
        
        if (!$this->isProjectAdmin($projectId, $userId)) {
            throw new AppException('无权添加成员');
        }

        $myEmployeeIds = TkUserRelationModel::where('user_id', $userId)
            ->pluck('related_user_id')
            ->toArray();
        
        $invalidIds = array_diff($memberIds, $myEmployeeIds);
        if (!empty($invalidIds)) {
            throw new AppException('只能添加您的员工到项目中');
        }
        
        Db::beginTransaction();
        try {
            foreach ($memberIds as $memberId) {
                $exists = TkProjectMemberModel::where('project_id', $projectId)
                    ->where('user_id', $memberId)
                    ->exists();
                
                if (!$exists) {
                    TkProjectMemberModel::create([
                        'project_id' => $projectId,
                        'user_id' => $memberId,
                        'role' => 2,
                    ]);
                }
            }
            
            Db::commit();
            return true;
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error('添加成员失败: ' . $e->getMessage());
            throw new AppException('添加成员失败');
        }
    }

    public function removeMember(int $projectId, int $memberId): bool
    {
        $userId = Context::get('user_id');
        
        if (!$this->isProjectAdmin($projectId, $userId)) {
            throw new AppException('无权移除成员');
        }
        
        $member = TkProjectMemberModel::where('project_id', $projectId)
            ->where('user_id', $memberId)
            ->first();
        
        if (!$member) {
            throw new AppException('成员不存在');
        }
        
        if ($member->role === 1) {
            throw new AppException('不能移除管理员');
        }
        
        return $member->delete() > 0;
    }

    public function getMembers(int $projectId): array
    {
        $members = TkProjectMemberModel::where('project_id', $projectId)
            ->orderBy('role', 'asc')
            ->get()
            ->toArray();
        
        $userIds = array_column($members, 'user_id');
        $users = TkUserModel::whereIn('id', $userIds)
            ->where('status', 1)
            ->get(['id', 'nickname', 'avatar', 'email','position'])
            ->keyBy('id')
            ->toArray();
        
        foreach ($members as &$member) {
            $member['user_info'] = $users[$member['user_id']] ?? null;
        }
        
        return $members;
    }

    /**
     * Desc: 获取项目可添加成员列表
     * Auth: hello pan
     * Date: 4/4/26 3:24 PM
     * @param int $projectId
     * @return array
     */
    public function getAvailableMembers(int $projectId): array
    {
        $userId = Context::get('user_id');
        
        $myEmployeeIds = TkUserRelationModel::where('user_id', $userId)
            ->pluck('related_user_id')
            ->toArray();
        
        if (empty($myEmployeeIds)) {
            return [];
        }
        
        $projectMemberIds = TkProjectMemberModel::where('project_id', $projectId)
            ->pluck('user_id')
            ->toArray();
        
        $availableIds = array_diff($myEmployeeIds, $projectMemberIds);
        
        if (empty($availableIds)) {
            return [];
        }
        
        $users = TkUserModel::whereIn('id', $availableIds)
            ->where('status', 1)
            ->get(['id', 'nickname', 'avatar', 'phone', 'position'])
            ->toArray();
        
        return $users;
    }

    protected function isProjectMember(int $projectId, int $userId): bool
    {
        return TkProjectMemberModel::where('project_id', $projectId)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Desc: 验证当前登录人是否为项目管理员创建者
     * Auth: hello pan
     * Date: 4/4/26 3:26 PM
     * @param int $projectId
     * @param int $userId
     * @return bool
     */
    protected function isProjectAdmin(int $projectId, int $userId): bool
    {
        $project = TkProjectModel::where('id', $projectId)
            ->where('creator_id', $userId)
            ->where('deleted_at', null)
            ->first();
        
        if ($project) {
            return true;
        }
        
        return TkProjectMemberModel::where('project_id', $projectId)
            ->where('user_id', $userId)
            ->where('role', 1)
            ->exists();
    }


    protected function getMemberCount(int $projectId): int
    {
        return TkProjectMemberModel::where('project_id', $projectId)
            ->count();
    }

    protected function getRequirementCount(int $projectId): int
    {
        return \App\Common\Model\TkRequirementModel::where('project_id', $projectId)
            ->count();
    }
}
