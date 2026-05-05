<?php

declare(strict_types=1);

namespace App\Admin\Validate;

use Hyperf\Validation\Request\FormRequest;

class TkTaskGroupRequest extends FormRequest
{
    protected array $scenes = [
        'add' => ['group_name', 'description', 'sort', 'status'],
        'update' => ['id', 'group_name', 'description', 'sort', 'status'],
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
            'group_name' => 'required|string|max:100',
            'description' => 'string|max:500',
            'cover_image' => 'string|max:255',
            'sort' => 'integer|min:0',
            'status' => 'required|in:0,1',
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => '分组ID不能为空',
            'id.integer' => '分组ID格式错误',
            'id.min' => '分组ID必须大于0',
            'group_name.required' => '分组名称不能为空',
            'group_name.max' => '分组名称最多100个字符',
            'description.max' => '描述最多500个字符',
            'sort.integer' => '排序必须是整数',
            'sort.min' => '排序不能小于0',
            'status.required' => '状态不能为空',
            'status.in' => '状态值错误',
        ];
    }
}
