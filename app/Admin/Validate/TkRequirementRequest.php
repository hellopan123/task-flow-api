<?php

declare(strict_types=1);

namespace App\Admin\Validate;

use Hyperf\Validation\Request\FormRequest;

class TkRequirementRequest extends FormRequest
{
    protected array $scenes = [
        'add' => ['requirement_name', 'requirement_code', 'project_id', 'status'],
        'update' => ['id', 'requirement_name', 'requirement_code', 'project_id', 'status'],
        'update_status' => ['id', 'status'],
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
            'requirement_name' => 'required|string|max:100',
            'requirement_code' => 'required|string|max:50|alpha_dash',
            'description' => 'string|max:2000',
            'source_type' => 'integer|in:1,2,3',
            'project_id' => 'required|integer|min:1',
            'priority' => 'integer|in:1,2,3,4',
            'status' => 'required|in:0,1,2,3',
            'start_date' => 'date',
            'end_date' => 'date|after_or_equal:start_date',
            'owner_id' => 'integer|min:1',
            'sort' => 'integer|min:0',
            'remark' => 'string|max:500',
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => '需求ID不能为空',
            'id.integer' => '需求ID格式错误',
            'id.min' => '需求ID必须大于0',
            'requirement_name.required' => '需求名称不能为空',
            'requirement_name.max' => '需求名称最多100个字符',
            'requirement_code.required' => '需求编码不能为空',
            'requirement_code.max' => '需求编码最多50个字符',
            'requirement_code.alpha_dash' => '需求编码只能包含字母、数字、下划线和短横线',
            'project_id.required' => '所属项目不能为空',
            'project_id.integer' => '项目ID格式错误',
            'priority.in' => '优先级值错误',
            'status.required' => '状态不能为空',
            'status.in' => '状态值错误',
            'start_date.date' => '开始日期格式错误',
            'end_date.date' => '结束日期格式错误',
            'end_date.after_or_equal' => '结束日期必须大于等于开始日期',
        ];
    }
}
