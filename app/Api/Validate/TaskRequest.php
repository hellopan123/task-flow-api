<?php

declare(strict_types=1);

namespace App\Api\Validate;

use Hyperf\Validation\Request\FormRequest;

class TaskRequest extends FormRequest
{
    protected array $scenes = [
        'list' => ['page', 'limit'],
        'create' => ['title', 'project_id', 'requirement_id', 'group_id', 'description', 'priority', 'status', 'start_date', 'due_date', 'owner_id', 'assignee_ids'],
        'update' => ['id', 'title', 'project_id', 'requirement_id', 'group_id', 'description', 'priority', 'status', 'start_date', 'due_date', 'owner_id', 'assignee_ids'],
        'delete' => ['id'],
        'detail' => ['id'],
        'update_status' => ['id', 'status'],
        'update_progress' => ['id', 'progress'],
        'assign' => ['id', 'owner_id'],
        'batch_create' => ['tasks'],
        'batch_assign' => ['task_ids', 'owner_id'],
        'my_tasks' => ['page', 'limit'],
        'user_task_stats' => ['owner_id'],
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|integer|min:1',
            'title' => 'required|string|max:200',
            'description' => 'string',
            'project_id' => 'required|integer|min:1',
            'requirement_id' => 'integer|min:1',
            'group_id' => 'integer|min:1',
            'owner_id' => 'integer|min:1',
            'assignee_ids' => 'array',
            'assignee_ids.*' => 'integer|min:1',
            'status' => 'in:1,2,3,4',
            'priority' => 'in:1,2,3,4',
            'progress' => 'required|integer|min:0|max:100',
            'start_date' => 'date',
            'due_date' => 'date',
            'page' => 'integer|min:1',
            'limit' => 'integer|min:1|max:100',
            'task_ids' => 'required|array',
            'task_ids.*' => 'integer|min:1',
            'tasks' => 'required|array',
            'tasks.*.title' => 'required|string|max:200',
            'tasks.*.project_id' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => '任务ID不能为空',
            'title.required' => '任务标题不能为空',
            'title.max' => '任务标题最多200个字符',
            'project_id.required' => '项目ID不能为空',
            'owner_id.required' => '负责人ID不能为空',
            'status.required' => '状态不能为空',
            'status.in' => '状态值错误',
            'progress.required' => '进度不能为空',
            'progress.min' => '进度不能小于0',
            'progress.max' => '进度不能大于100',
            'task_ids.required' => '任务ID不能为空',
            'tasks.required' => '任务数据不能为空',
        ];
    }
}
