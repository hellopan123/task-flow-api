<?php

declare(strict_types=1);

namespace App\Api\Service;

use App\Api\Event\TaskAssignedEvent;
use App\Api\Event\TaskCreatedEvent;
use App\Api\Event\TaskStatusChangedEvent;
use App\Common\Exception\AppException;
use App\Common\Model\TkNotificationModel;
use App\Common\Model\TkProjectMemberModel;
use App\Common\Model\TkProjectModel;
use App\Common\Model\TkRequirementModel;
use App\Common\Model\TkTaskAssigneeModel;
use App\Common\Model\TkTaskModel;
use App\Common\Model\TkTaskLogModel;
use App\Common\Model\TkTaskAttachmentModel;
use App\Common\Model\TkTaskCommentModel;
use App\Common\Model\TkUserModel;
use App\Common\Utils\UploadFile;
use Hyperf\Context\Context;
use Hyperf\DbConnection\Db;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

class TaskService
{
    protected LoggerInterface $logger;
    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(LoggerInterface $logger, EventDispatcherInterface $eventDispatcher)
    {
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Desc: 获取任务统计
     * Auth: hello pan
     * Date: 3/29/26 8:52 PM
     * @param int $owner_id 任务所有者ID
     * @return array
     */
    public function getTaskStats(int $owner_id): array
    {
        $userId = Context::get('user_id');
        $query = TkTaskModel::query()
            ->where('creator_id', $userId)
            ->where('owner_id', $owner_id)
            ->where('deleted_at', null);

        $res = $query->select(['status',Db::raw('count(id) as count')])->groupBy('status')->get()->toArray();
        $res = array_column($res,'count','status');
        // 1-待处理 2-进行中 3-已完成 4-已关闭
        return [
            // 待处理
            'pending' => $res['1'] ?? 0,
            // 进行中
            'in_progress' => $res['2'] ?? 0,
            // 已完成
            'completed' => $res['3'] ?? 0,
            // 已关闭
            'closed' => $res['4'] ?? 0,
        ];

    }

    /**
     * Desc: 获取任务统计
     * Auth: hello pan
     * Date: 3/29/26 8:52 PM
     * @param int $owner_id 任务所有者ID
     * @return array
     */
    public function getTaskListStats(array $params = []): array
    {
        $userId = Context::get('user_id');

        $query = TkTaskModel::query();
        // 如果没有明确指定类型，默认查询我创建的或指派给我的
        if (!isset($params['type']) || $params['type'] == 'all') {
            $query->where(function ($q) use ($userId) {
                $q->where('creator_id', $userId)
                    ->orWhere('owner_id', $userId);
            });
        } else {
            // 1: 我发布的
            if ($params['type'] == 1) {
                $query->where('creator_id', $userId);
            }
            // 2: 指派给我的
            elseif ($params['type'] == 2) {
                $query->where('owner_id', $userId);
            }
        }
        $where = quickFilterWhereKey($params,['project_id','requirement_id','status','priority','title|like']);
        $query->where($where)->where('deleted_at', null);

        $res = $query->select(['status',Db::raw('count(id) as count')])->groupBy('status')->get()->toArray();
        $res = array_column($res,'count','status');
        // 1-待处理 2-进行中 3-已完成 4-已关闭
        return [
            // 待处理
            'pending' => $res['1'] ?? 0,
            // 进行中
            'in_progress' => $res['2'] ?? 0,
            // 已完成
            'completed' => $res['3'] ?? 0,
            // 已关闭
            'closed' => $res['4'] ?? 0,
            // 总任务
            'total' => ($res['1'] ?? 0) + ($res['2'] ?? 0) + ($res['3'] ?? 0) + ($res['4'] ?? 0),
        ];

    }

    /**
     * Desc: 任务列表
     * Auth: hello pan
     * Date: 5/1/26 PM10:15
     * @param array $params
     * @return array
     */
    public function getList(array $params = []): array
    {
        $userId = Context::get('user_id');
        
        $query = TkTaskModel::query()
            ->where('deleted_at', null);
            
        // 如果没有明确指定类型，默认查询我创建的或指派给我的
        if (!isset($params['type']) || $params['type'] == 'all') {
            $query->where(function ($q) use ($userId) {
                $q->where('creator_id', $userId)
                  ->orWhere('owner_id', $userId);
            });
        } else {
            // 1: 我发布的
            if ($params['type'] == 1) {
                $query->where('creator_id', $userId);
            }
            // 2: 指派给我的
            elseif ($params['type'] == 2) {
                $query->where('owner_id', $userId);
            }
        }

        $where = quickFilterWhereKey($params,['project_id','requirement_id','status','priority','title|like']);
        $query->where($where);
        
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 15;
        
        $count = $query->count();
        $list = $query->offset(($page - 1) * $limit)
            ->limit($limit)
            ->with(['assignees.user'])
            ->orderBy('id', 'desc')
            ->orderBy('due_date', 'asc')
            ->get()
            ->toArray();
        
        $this->appendRelatedInfo($list);
        
        return ['list' => $list, 'count' => $count];
    }

    /**
     * Desc: 获取我的任务
     * Auth: hello pan
     * Date: 5/2/26 PM3:53
     * @param array $params
     * @return array
     */
    public function getMyTasks(array $params = []): array
    {
        $userId = Context::get('user_id');
        
        $query = TkTaskModel::query()
            ->where('deleted_at', null)
            ->where('owner_id', $userId);
        
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', $params['status']);
        }
        
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 15;
        
        $count = $query->count();
        $list = $query->offset(($page - 1) * $limit)
            ->limit($limit)
            ->with(['assignees.user'])
            ->orderBy('priority', 'desc')
            ->orderBy('due_date', 'asc')
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();
        
        $this->appendRelatedInfo($list);
        
        return ['list' => $list, 'count' => $count];
    }

    /**
     * Desc: 获取任务详情
     * Auth: hello pan
     * Date: 5/2/26 PM3:53
     * @param int $id
     * @return array|null
     * @throws AppException
     */
    public function getDetail(int $id): ?array
    {
        $userId = Context::get('user_id');
        
        $task = TkTaskModel::where('id', $id)
            ->where('deleted_at', null)
            ->with(['assignees.user'])
            ->first();
        
        if (!$task) {
            return null;
        }
        
        if (!$this->canAccess($task, $userId)) {
            throw new AppException('无权访问该任务');
        }
        
        $data = $task->toArray();
        $data['owner_info'] = $this->getUserInfo($task->owner_id);
        $data['creator_info'] = $this->getUserInfo($task->creator_id);
        $data['project_info'] = $this->getProjectInfo($task->project_id);
        $data['requirement_info'] = $this->getRequirementInfo($task->requirement_id);
        $data['comments'] = $this->getComments($id);
        $data['attachments'] = $this->getAttachments($id);
        
        return $data;
    }

    /**
     * Desc: 创建任务
     * Auth: hello pan
     * Date: 5/2/26 PM3:54
     * @param array $params
     * @return array
     * @throws AppException
     */
    public function create(array $params): array
    {
        $userId = Context::get('user_id');
        $params['creator_id'] = $userId;
        
        if (empty($params['owner_id']) && !empty($params['assignee_ids'])) {
            $params['owner_id'] = $params['assignee_ids'][0];
        }
        
        if (empty($params['owner_id'])) {
            $params['owner_id'] = $userId;
        }
        
        if (!empty($params['project_id']) && !$this->isProjectMember($params['project_id'], $userId)) {
            throw new AppException('无权在该项目创建任务');
        }
        
        $assigneeIds = $params['assignee_ids'] ?? [];
        unset($params['assignee_ids']);

        Db::beginTransaction();
        try {
            $task = TkTaskModel::create($params);

            // 触发任务创建事件
            $this->eventDispatcher->dispatch(new TaskCreatedEvent(
                taskId: $task->id,
                creatorId: $userId,
                assigneeIds: $assigneeIds,
                groupId: $params['project_id'] ?? 0,
                title: $task->title
            ));

            // 维护需求统计
            if (!empty($params['requirement_id'])) {
                $requirement = TkRequirementModel::find($params['requirement_id']);
                if ($requirement) {
                    $requirement->increment('task_count');
                }
            }

            if (!empty($assigneeIds)) {
                $assignees = [];
                foreach ($assigneeIds as $assigneeId) {
                    $assignees[] = [
                        'task_id' => $task->id,
                        'user_id' => $assigneeId,
                        'create_time' => date('Y-m-d H:i:s'),
                    ];
                }
                TkTaskAssigneeModel::insert($assignees);
            }

            Db::commit();
            return $task->toArray();
        }catch (\Throwable $e){
            Db::rollback();
            throw new AppException('创建任务失败');
        }

    }

    /**
     * Desc: 批量创建
     * Auth: hello pan
     * Date: 5/2/26 PM3:54
     * @param array $tasks
     * @return int
     * @throws AppException
     */
    public function batchCreate(array $tasks): int
    {
        $userId = Context::get('user_id');
        $count = 0;
        $requirement_id = $tasks[0]['requirement_id'];
        
        Db::beginTransaction();
        try {
            foreach ($tasks as $task) {
                $task['creator_id'] = $userId;
                
                if (empty($task['owner_id'])) {
                    $task['owner_id'] = $userId;
                }
                
                if (!empty($task['project_id']) && !$this->isProjectMember($task['project_id'], $userId)) {
                    continue;
                }
                
                TkTaskModel::create($task);
                $count++;
            }
            // 更新需求数据
            TkRequirementModel::where('id',$requirement_id)->update(['task_count' => Db::raw('task_count + '.$count)]);
            
            Db::commit();
            return $count;
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error('批量创建任务失败: ' . $e->getMessage());
            throw new AppException('批量创建任务失败');
        }
    }

    /**
     * Desc: 更新操作
     * Auth: hello pan
     * Date: 5/2/26 PM3:52
     * @param array $params
     * @return array
     * @throws AppException
     */
    public function update(array $params): array
    {
        $userId = Context::get('user_id');
        $id = $params['id'];
        
        $task = TkTaskModel::where('id', $id)
            ->where('deleted_at', null)
            ->first();
        
        if (!$task) {
            throw new AppException('任务不存在');
        }
        
        if (!$this->canEdit($task, $userId)) {
            throw new AppException('无权修改该任务');
        }
        
        if (empty($params['owner_id']) && !empty($params['assignee_ids'])) {
            $params['owner_id'] = $params['assignee_ids'][0];
        }
        
        unset($params['id']);
        
        $assigneeIds = null;
        if (isset($params['assignee_ids'])) {
            $assigneeIds = $params['assignee_ids'];
            unset($params['assignee_ids']);
        }
        
        $task->update($params);
        
        if ($assigneeIds !== null) {
            TkTaskAssigneeModel::where('task_id', $task->id)->delete();
            if (!empty($assigneeIds)) {
                $assignees = [];
                foreach ($assigneeIds as $assigneeId) {
                    $assignees[] = [
                        'task_id' => $task->id,
                        'user_id' => $assigneeId,
                        'create_time' => date('Y-m-d H:i:s'),
                    ];
                }
                TkTaskAssigneeModel::insert($assignees);
            }
        }
        
        return $task->toArray();
    }

    /**
     * Desc: 更新状态
     * Auth: hello pan
     * Date: 4/26/26 PM6:55
     * @param int $id
     * @param int $status
     * @param string $remark
     * @return bool
     * @throws AppException
     */
    public function updateStatus(int $id, int $status, string $remark=''): bool
    {
        $userId = Context::get('user_id');

        $task = TkTaskModel::where('id', $id)
            ->where('deleted_at', null)
            ->first();

        if (!$task) {
            throw new AppException('任务不存在');
        }

        $oldStatus = $task->status;
        if($task->status == $status){
            throw new AppException('状态已更新');
        }

        if (!$this->canAccess($task, $userId)) {
            throw new AppException('无权修改该任务');
        }

        $updateData = ['status' => $status, 'remark' => $remark];
        if ($status === 3) {
            $updateData['completed_time'] = date('Y-m-d H:i:s');
            // 更新需求完成任务数
            TkRequirementModel::where('id',$task->requirement_id)->update(['completed_task_count' => Db::raw('completed_task_count+1')]);
        }

        $result = $task->update($updateData) > 0;

        // 触发任务状态变更事件
        if ($result) {
            $assigneeIds = TkTaskAssigneeModel::where('task_id', $id)->pluck('user_id')->toArray();
            $this->eventDispatcher->dispatch(new TaskStatusChangedEvent(
                taskId: $id,
                operatorId: $userId,
                oldStatus: $oldStatus,
                newStatus: $status,
                assigneeIds: $assigneeIds,
                title: $task->title
            ));
        }

        return $result;
    }


    /**
     * Desc: 删除操作
     * Auth: hello pan
     * Date: 4/26/26 PM6:56
     * @param int $id
     * @return bool
     * @throws AppException
     */
    public function delete(int $id): bool
    {
        $userId = Context::get('user_id');
        
        $task = TkTaskModel::where('id', $id)
            ->where('deleted_at', null)
            ->first();
        
        if (!$task) {
            throw new AppException('任务不存在');
        }
        
        if (!$this->canEdit($task, $userId)) {
            throw new AppException('无权删除该任务');
        }
        
        return $task->update(['deleted_at' => date('Y-m-d H:i:s')]) > 0;
    }

    /**
     * Desc:分派任务
     * Auth: hello pan
     * Date: 4/6/26 8:35 PM
     * @param int $taskId
     * @param int $ownerId
     * @return bool
     * @throws AppException
     *
     */
    public function assign(int $taskId, int $ownerId): bool
    {
        $userId = Context::get('user_id');
        
        $task = TkTaskModel::find($taskId);
        if (!$task) {
            throw new AppException('任务不存在');
        }
        
        if (!$this->canEdit($task, $userId)) {
            throw new AppException('无权分配任务');
        }

        $assignees = [
            'task_id' => $task->id,
            'user_id' => $ownerId,
            'is_primary' => 1,
        ];

        TkTaskAssigneeModel::insert($assignees);

        $this->eventDispatcher->dispatch(new TaskAssignedEvent(
            taskId: $taskId,
            operatorId: $userId,
            newAssigneeIds: [$ownerId],
            oldAssigneeIds: [$task->owner_id],
            title: $task->title
        ));

        return $task->update(['owner_id' => $ownerId]);
    }

    /**
     * Desc: 批量分派任务
     * Auth: hello pan
     * Date: 4/6/26 8:35 PM
     * @param array $taskIds
     * @param int $ownerId
     * @return int
     * @throws AppException
     */
    public function batchAssign(array $taskIds, int $ownerId): int
    {
        $userId = Context::get('user_id');
        $count = 0;
        
        Db::beginTransaction();
        try {
            foreach ($taskIds as $taskId) {
                $task = TkTaskModel::find($taskId);
                if ($task && $this->canEdit($task, $userId)) {
                    $task->update(['owner_id' => $ownerId]);
                    $count++;
                }
            }
            
            Db::commit();
            return $count;
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error('批量分配任务失败: ' . $e->getMessage());
            throw new AppException('批量分配任务失败');
        }
    }

    public function getMetrics(): array
    {
        $userId = Context::get('user_id');
        
        $baseQuery = function () use ($userId) {
            return TkTaskModel::where('deleted_at', null)
                ->where(function ($q) use ($userId) {
                    $q->where('creator_id', $userId)
                      ->orWhere('owner_id', $userId);
                });
        };
        
        $total = $baseQuery()->count();
        $pending = $baseQuery()->where('status', 1)->count();
        $inProgress = $baseQuery()->where('status', 2)->count();
        $completed = $baseQuery()->where('status', 3)->count();
        $overdue = $baseQuery()
            ->where('status', '!=', 3)
            ->where('due_date', '<', date('Y-m-d'))
            ->count();
        
        return [
            'total' => $total,
            'pending' => $pending,
            'in_progress' => $inProgress,
            'completed' => $completed,
            'overdue' => $overdue,
        ];
    }

    // ========== 任务日志 ==========
    public function getLogs(int $taskId): array
    {
        $logs = \App\Common\Model\TkTaskLogModel::where('task_id', $taskId)
            ->with(['user'])
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();

        return array_map(function ($log) {
            return [
                'id' => $log['id'],
                'task_id' => $log['task_id'],
                'user_id' => $log['user_id'],
                'action' => $log['action'],
                'content' => $log['content'],
                'old_value' => $log['old_value'],
                'new_value' => $log['new_value'],
                'create_time' => $log['create_time'],
                'user_info' => $log['user'] ? [
                    'id' => $log['user']['id'],
                    'nickname' => $log['user']['nickname'],
                ] : null,
            ];
        }, $logs);
    }


    /**
     * Desc: 上传任务附件资源
     * Auth: hello pan
     * Date: 4/25/26 PM10:28
     * @param int $taskId
     * @param $file
     * @return array
     * @throws \League\Flysystem\FilesystemException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function uploadAttachment(int $taskId, $file): array
    {
        $userId = Context::get('user_id');

        $file_type = $file->getMimeType();
        // 保存文件
        $upload = UploadFile::upload($file);

        $inset_data = [
            'task_id' => $taskId,
            'file_name' => $upload['original_name'],
            'file_path' => $upload['save_name'],
            'file_size' => $upload['file_size'],
            'file_type' => $file_type,
            'uploader_id' => $userId,
            'create_time' => date('Y-m-d H:i:s'),
        ];
        $id = TkTaskAttachmentModel::insertGetId($inset_data);

        // 记录日志
        $this->addLog($taskId, 'upload_attachment', '上传附件: ' . $file->getClientFilename());

        $inset_data['id'] = $id;
        $inset_data['file_url'] = get_image_url($upload['save_name']);
        return $inset_data;
    }

    /**
     * Desc: 删除资源
     * Auth: hello pan
     * Date: 4/25/26 PM10:27
     * @param int $id
     * @return bool
     * @throws AppException
     */
    public function deleteAttachment(int $id): bool
    {
        $attachment = TkTaskAttachmentModel::find($id);
        if (!$attachment) {
            throw new AppException('附件不存在');
        }

        $taskId = $attachment->task_id;
        $fileName = $attachment->file_name;

        $attachment->destroy($id);

        // 记录日志
        $this->addLog($taskId, 'delete_attachment', '删除附件: ' . $fileName);

        return true;
    }

    // ========== 任务评论 ==========
    public function getComments(int $taskId): array
    {
        $comments = \App\Common\Model\TkTaskCommentModel::where('task_id', $taskId)
            ->where('parent_id', 0)
            ->with(['user', 'replies.user'])
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();

        return array_map(function ($comment) {
            $replies = array_map(function ($reply) {
                return [
                    'id' => $reply['id'],
                    'task_id' => $reply['task_id'],
                    'user_id' => $reply['user_id'],
                    'content' => $reply['content'],
                    'parent_id' => $reply['parent_id'],
                    'create_time' => $reply['create_time'],
                    'user_info' => $reply['user'] ? [
                        'id' => $reply['user']['id'],
                        'nickname' => $reply['user']['nickname'],
                    ] : null,
                ];
            }, $comment['replies'] ?? []);

            return [
                'id' => $comment['id'],
                'task_id' => $comment['task_id'],
                'user_id' => $comment['user_id'],
                'content' => $comment['content'],
                'parent_id' => $comment['parent_id'],
                'create_time' => $comment['create_time'],
                'user_info' => $comment['user'] ? [
                    'id' => $comment['user']['id'],
                    'nickname' => $comment['user']['nickname'],
                ] : null,
                'replies' => $replies,
            ];
        }, $comments);
    }

    public function createComment(array $params): array
    {
        $userId = Context::get('user_id');

        $comment = \App\Common\Model\TkTaskCommentModel::create([
            'task_id' => $params['task_id'],
            'user_id' => $userId,
            'content' => $params['content'],
            'parent_id' => $params['parent_id'] ?? 0,
            'create_time' => date('Y-m-d H:i:s'),
        ]);

        // 记录日志
        $action = empty($params['parent_id']) ? 'add_comment' : 'reply_comment';
        $this->addLog($params['task_id'], $action, '添加评论: ' . mb_substr($params['content'], 0, 50));

        return [
            'id' => $comment->id,
            'task_id' => $comment->task_id,
            'user_id' => $comment->user_id,
            'content' => $comment->content,
            'parent_id' => $comment->parent_id,
            'create_time' => $comment->create_time,
            'user_info' => [
                'id' => $userId,
                'nickname' => Context::get('user_nickname') ?? '',
            ],
        ];
    }

    public function deleteComment(int $id): bool
    {
        $comment = \App\Common\Model\TkTaskCommentModel::find($id);
        if (!$comment) {
            throw new AppException('评论不存在');
        }

        $userId = Context::get('user_id');
        if ($comment->user_id != $userId) {
            throw new AppException('无权限删除此评论');
        }

        $comment->delete();
        return true;
    }

    // ========== 任务导入 ==========
    public function importPreview($file): array
    {
        // 这里简单实现，实际需要使用 PhpSpreadsheet 解析 Excel
        // 返回预览数据
        return [
            'headers' => ['标题', '描述', '优先级', '截止日期'],
            'rows' => [
                ['任务1', '任务描述1', '中', '2024-12-31'],
                ['任务2', '任务描述2', '高', '2024-12-30'],
            ],
            'total_rows' => 2,
        ];
    }

    public function importExecute(array $params): array
    {
        $userId = Context::get('user_id');
        $rows = $params['rows'] ?? [];
        $projectId = $params['project_id'] ?? null;
        $requirementId = $params['requirement_id'] ?? null;

        $successCount = 0;
        $failCount = 0;

        foreach ($rows as $row) {
            try {
                TkTaskModel::create([
                    'title' => $row[0] ?? '',
                    'description' => $row[1] ?? '',
                    'priority' => $this->parsePriority($row[2] ?? '中'),
                    'due_date' => $row[3] ?? null,
                    'project_id' => $projectId,
                    'requirement_id' => $requirementId,
                    'creator_id' => $userId,
                    'status' => 1,
                    'create_time' => date('Y-m-d H:i:s'),
                ]);
                $successCount++;
            } catch (\Throwable $e) {
                $failCount++;
            }
        }

        return [
            'success_count' => $successCount,
            'fail_count' => $failCount,
        ];
    }

    public function getImportRecords(?int $requirementId): array
    {
        // 简化实现，返回空数组
        return [];
    }

    // ========== 辅助方法 ==========
    protected function addLog(int $taskId, string $action, string $content): void
    {
        $userId = Context::get('user_id');

        TkTaskLogModel::insertGetId([
            'task_id' => $taskId,
            'user_id' => $userId,
            'action' => $action,
            'action_desc' => $content,
            'create_time' => date('Y-m-d H:i:s'),
        ]);
    }

    protected function parsePriority(string $priority): int
    {
        $map = ['低' => 1, '中' => 2, '高' => 3, '紧急' => 4];
        return $map[$priority] ?? 2;
    }

    protected function getUserInfo(?int $userId): ?array
    {
        if (!$userId) {
            return null;
        }
        
        $user = TkUserModel::find($userId);
        if (!$user) {
            return null;
        }
        
        return [
            'id' => $user->id,
            'nickname' => $user->nickname,
            'avatar' => $user->avatar,
            'phone' => $user->phone,
        ];
    }

    protected function getProjectInfo(?int $projectId): ?array
    {
        if (!$projectId) {
            return null;
        }
        
        $project = TkProjectModel::find($projectId);
        if (!$project) {
            return null;
        }
        
        return [
            'id' => $project->id,
            'project_name' => $project->project_name,
            'project_code' => $project->project_code,
        ];
    }

    protected function getRequirementInfo(?int $requirementId): ?array
    {
        if (!$requirementId) {
            return null;
        }
        
        $requirement = TkRequirementModel::find($requirementId);
        if (!$requirement) {
            return null;
        }
        
        return [
            'id' => $requirement->id,
            'requirement_name' => $requirement->requirement_name,
            'requirement_code' => $requirement->requirement_code,
        ];
    }

    public function getAttachments(int $taskId): array
    {
        $list = TkTaskAttachmentModel::where('task_id', $taskId)
            ->where('deleted_at', null)
            ->orderBy('create_time', 'desc')
            ->get()
            ->toArray();
        foreach ($list as &$item) {
            $item['file_url'] = get_image_url($item['file_path'] ?? '');
        }

        return $list;
    }

    protected function appendRelatedInfo(array &$list): void
    {
        $ownerIds = array_unique(array_filter(array_column($list, 'owner_id')));
        $creatorIds = array_unique(array_filter(array_column($list, 'creator_id')));
        $userIds = array_unique(array_merge($ownerIds, $creatorIds));
        
        $users = TkUserModel::whereIn('id', $userIds)
            ->get(['id', 'nickname', 'avatar'])
            ->keyBy('id')
            ->toArray();
        
        $projectIds = array_unique(array_filter(array_column($list, 'project_id')));
        $projects = TkProjectModel::whereIn('id', $projectIds)
            ->get(['id', 'project_name', 'project_code'])
            ->keyBy('id')
            ->toArray();
        
        $requirementIds = array_unique(array_filter(array_column($list, 'requirement_id')));
        $requirements = TkRequirementModel::whereIn('id', $requirementIds)
            ->get(['id', 'requirement_name', 'requirement_code'])
            ->keyBy('id')
            ->toArray();
        
        foreach ($list as &$item) {
            $item['owner_info'] = isset($item['owner_id']) ? ($users[$item['owner_id']] ?? null) : null;
            $item['creator_info'] = isset($item['creator_id']) ? ($users[$item['creator_id']] ?? null) : null;
            $item['project_info'] = isset($item['project_id']) ? ($projects[$item['project_id']] ?? null) : null;
            $item['requirement_info'] = isset($item['requirement_id']) ? ($requirements[$item['requirement_id']] ?? null) : null;
        }
    }

    protected function canAccess($task, int $userId): bool
    {
        if (!$task) {
            return false;
        }
        
        if ($task->creator_id === $userId || $task->owner_id === $userId) {
            return true;
        }
        
        if ($task->project_id) {
            return $this->isProjectMember($task->project_id, $userId);
        }
        
        return false;
    }

    protected function canEdit($task, int $userId): bool
    {
        if (!$task) {
            return false;
        }
        
        if ($task->creator_id === $userId) {
            return true;
        }
        
        if ($task->project_id) {
            $isProjectOwner = TkProjectMemberModel::where('project_id', $task->project_id)
                ->where('user_id', $userId)
                ->where('role', 1)
                ->exists();
            
            if ($isProjectOwner) {
                return true;
            }
        }
        
        return false;
    }

    protected function isProjectMember(int $projectId, int $userId): bool
    {
        return TkProjectMemberModel::where('project_id', $projectId)
            ->where('user_id', $userId)
            ->exists();
    }
}
