<?php

declare(strict_types=1);

namespace App\Admin\Validate;

use Hyperf\Validation\Request\FormRequest;

class SysConfigRequest extends FormRequest
{
    protected array $scenes = [
        'add' => ['config_group', 'config_key', 'config_value', 'config_name', 'config_type'],
        'update' => ['id', 'config_group', 'config_key', 'config_value', 'config_name', 'config_type'],
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
            'config_group' => 'required|string|max:50',
            'config_key' => 'required|string|max:100',
            'config_value' => 'string',
            'config_name' => 'required|string|max:100',
            'config_type' => 'required|string|in:string,number,boolean,json,array',
            'sort' => 'integer|min:0',
            'status' => 'required|in:0,1',
            'remark' => 'string|max:500',
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => '配置ID不能为空',
            'id.integer' => '配置ID格式错误',
            'id.min' => '配置ID必须大于0',
            'config_group.required' => '配置分组不能为空',
            'config_group.max' => '配置分组最多50个字符',
            'config_key.required' => '配置键不能为空',
            'config_key.max' => '配置键最多100个字符',
            'config_name.required' => '配置名称不能为空',
            'config_name.max' => '配置名称最多100个字符',
            'config_type.required' => '配置类型不能为空',
            'config_type.in' => '配置类型值错误',
            'sort.integer' => '排序必须是整数',
            'sort.min' => '排序不能小于0',
            'status.required' => '状态不能为空',
            'status.in' => '状态值错误',
        ];
    }
}
