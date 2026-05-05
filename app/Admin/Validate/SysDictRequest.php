<?php

declare(strict_types=1);

namespace App\Admin\Validate;

use Hyperf\Validation\Request\FormRequest;

class SysDictRequest extends FormRequest
{
    protected array $scenes = [
        'add' => ['dict_name', 'dict_code', 'status'],
        'update' => ['id', 'dict_name', 'dict_code', 'status'],
        'update_status' => ['id', 'status'],
        'delete' => ['id'],
        'info' => ['id'],
        'update_items' => ['dict_code', 'items'],
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|integer|min:1',
            'dict_name' => 'required|string|max:50',
            'dict_code' => 'required|string|max:50|alpha_dash',
            'description' => 'string|max:255',
            'status' => 'required|in:0,1',
            'remark' => 'string|max:500',
            'items' => 'array',
            'items.*.item_label' => 'required|string|max:100',
            'items.*.item_value' => 'required|string|max:100',
            'items.*.sort' => 'integer|min:0',
            'items.*.status' => 'in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => '字典ID不能为空',
            'id.integer' => '字典ID格式错误',
            'id.min' => '字典ID必须大于0',
            'dict_name.required' => '字典名称不能为空',
            'dict_name.max' => '字典名称最多50个字符',
            'dict_code.required' => '字典编码不能为空',
            'dict_code.max' => '字典编码最多50个字符',
            'dict_code.alpha_dash' => '字典编码只能包含字母、数字、下划线和短横线',
            'description.max' => '描述最多255个字符',
            'status.required' => '状态不能为空',
            'status.in' => '状态值错误',
            'items.array' => '字典项必须是数组',
            'items.*.item_label.required' => '字典项标签不能为空',
            'items.*.item_value.required' => '字典项值不能为空',
        ];
    }
}
