<?php

declare(strict_types=1);

namespace App\Api\Validate;

use Hyperf\Validation\Request\FormRequest;

class RequirementRequest extends FormRequest
{
    protected array $scenes = [
        'list' => ['page', 'limit'],
        'create' => ['project_id', 'requirement_name'],
        'update' => ['id', 'requirement_name'],
        'delete' => ['id'],
        'detail' => ['id'],
        'update_status' => ['id', 'status'],
        'by_project' => ['project_id'],
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|integer|min:1',
            'project_id' => 'required|integer|min:1',
            'requirement_name' => 'required|string|max:200',
            'requirement_code' => 'string|max:50',
            'description' => 'string',
            'priority' => 'in:1,2,3,4',
            'status' => 'required|in:1,2,3,4,5',
            'start_date' => 'date',
            'end_date' => 'date',
            'page' => 'integer|min:1',
            'limit' => 'integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => '需求ID不能为空',
            'project_id.required' => '项目ID不能为空',
            'requirement_name.required' => '需求标题不能为空',
            'requirement_name.max' => '需求标题最多200个字符',
            'status.required' => '状态不能为空',
            'status.in' => '状态值错误',
        ];
    }
}
