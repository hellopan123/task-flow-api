<?php

declare(strict_types=1);

namespace App\Admin\Validate;

use Hyperf\Validation\Request\FormRequest;

class SysDictItemRequest extends FormRequest
{
    protected array $scenes = [
        'add' => ['dict_id', 'item_label', 'item_value'],
        'update' => ['id', 'item_label', 'item_value'],
        'update_status' => ['id', 'status'],
        'delete' => ['id'],
        'list' => ['dict_id'],
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|integer|min:1',
            'dict_id' => 'required|integer|min:1',
            'item_label' => 'required|string|max:100',
            'item_value' => 'required|string|max:100',
            'item_style' => 'string|max:200',
            'sort' => 'integer|min:0',
            'status' => 'required|in:0,1',
            'remark' => 'string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => '字典项ID不能为空',
            'id.integer' => '字典项ID格式错误',
            'id.min' => '字典项ID必须大于0',
            'dict_id.required' => '字典ID不能为空',
            'dict_id.integer' => '字典ID格式错误',
            'item_label.required' => '字典项标签不能为空',
            'item_label.max' => '字典项标签最多100个字符',
            'item_value.required' => '字典项值不能为空',
            'item_value.max' => '字典项值最多100个字符',
            'sort.integer' => '排序必须是整数',
            'sort.min' => '排序不能小于0',
            'status.required' => '状态不能为空',
            'status.in' => '状态值错误',
        ];
    }
}
