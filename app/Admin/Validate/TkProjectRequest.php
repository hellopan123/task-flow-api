<?php

declare(strict_types=1);

namespace App\Admin\Validate;

use Hyperf\Validation\Request\FormRequest;

class TkProjectRequest extends FormRequest
{
    protected array $scenes = [
        'add' => ['project_name', 'project_code',  'status'],
        'update' => ['id', 'project_name', 'project_code', 'status'],
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
            'project_name' => 'required|string|max:100',
            'project_code' => 'required|string|max:50|alpha_dash',
            'description' => 'string|max:1000',
            'owner_id' => 'required|integer|min:0',
            'start_date' => 'date',
            'end_date' => 'date|after_or_equal:start_date',
            'status' => 'required|in:0,1,2,3',
            'progress' => 'integer|min:0|max:100',
            'sort' => 'integer|min:0',
            'remark' => 'string|max:500',
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => '项目ID不能为空',
            'id.integer' => '项目ID格式错误',
            'id.min' => '项目ID必须大于0',
            'project_name.required' => '项目名称不能为空',
            'project_name.max' => '项目名称最多100个字符',
            'project_code.required' => '项目编码不能为空',
            'project_code.max' => '项目编码最多50个字符',
            'project_code.alpha_dash' => '项目编码只能包含字母、数字、下划线和短横线',
            'owner_id.required' => '负责人不能为空',
            'owner_id.integer' => '负责人ID格式错误',
            'start_date.date' => '开始日期格式错误',
            'end_date.date' => '结束日期格式错误',
            'end_date.after_or_equal' => '结束日期必须大于等于开始日期',
            'status.required' => '状态不能为空',
            'status.in' => '状态值错误',
            'progress.integer' => '进度必须是整数',
            'progress.min' => '进度不能小于0',
            'progress.max' => '进度不能大于100',
        ];
    }
}
