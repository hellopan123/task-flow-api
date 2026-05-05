<?php

declare(strict_types=1);

namespace App\Api\Validate;

use Hyperf\Validation\Request\FormRequest;

class ProjectRequest extends FormRequest
{
    protected array $scenes = [
        'list' => ['page', 'limit'],
        'create' => ['project_name'],
        'update' => ['id', 'project_name'],
        'delete' => ['id'],
        'detail' => ['id'],
        'add_members' => ['id', 'member_ids'],
        'remove_member' => ['id', 'member_id'],
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|integer|min:1',
            'project_name' => 'required|string|max:200',
            'description' => 'string|max:1000',
            'start_date' => 'date',
            'end_date' => 'date',
            'page' => 'integer|min:1',
            'limit' => 'integer|min:1|max:100',
            'member_ids' => 'required|array',
            'member_ids.*' => 'integer|min:1',
            'member_id' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => '项目ID不能为空',
            'project_name.required' => '项目名称不能为空',
            'project_name.max' => '项目名称最多200个字符',
            'description.max' => '项目描述最多1000个字符',
            'member_ids.required' => '成员ID不能为空',
            'member_ids.array' => '成员ID格式错误',
            'member_id.required' => '成员ID不能为空',
        ];
    }
}
