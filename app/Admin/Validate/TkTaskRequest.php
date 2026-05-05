<?php

declare(strict_types=1);

namespace App\Admin\Validate;

use Hyperf\Validation\Request\FormRequest;

class TkTaskRequest extends FormRequest
{
    protected array $scenes = [
        'add' => ['title', 'group_id', 'status'],
        'update' => ['id', 'title', 'group_id', 'status'],
        'update_status' => ['id', 'status'],
        'update_progress' => ['id', 'progress'],
        'delete' => ['id'],
        'info' => ['id'],
        'list' => ['page', 'pageSize'],
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
            'description' => 'string|max:5000',
            'group_id' => 'required|integer|min:1',
            'requirement_id' => 'integer|min:1',
            'creator_id' => 'integer|min:1',
            'status' => 'required|in:0,1,2,3,4',
            'priority' => 'integer|in:1,2,3,4',
            'progress' => 'integer|min:0|max:100',
            'start_date' => 'date',
            'due_date' => 'date|after_or_equal:start_date',
            'sort' => 'integer|min:0',
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => '任务ID不能为空',
            'id.integer' => '任务ID格式错误',
            'id.min' => '任务ID必须大于0',
            'title.required' => '任务标题不能为空',
            'title.max' => '任务标题最多200个字符',
            'group_id.required' => '所属分组不能为空',
            'group_id.integer' => '分组ID格式错误',
            'status.required' => '状态不能为空',
            'status.in' => '状态值错误',
            'priority.in' => '优先级值错误',
            'progress.integer' => '进度必须是整数',
            'progress.min' => '进度不能小于0',
            'progress.max' => '进度不能大于100',
            'start_date.date' => '开始日期格式错误',
            'due_date.date' => '截止日期格式错误',
            'due_date.after_or_equal' => '截止日期必须大于等于开始日期',
        ];
    }
}
