<?php

declare(strict_types=1);

namespace App\Admin\Validate;

use App\Common\Exception\AppException;
use App\Common\Model\SysRoleModel;
use Hyperf\Validation\Request\FormRequest;

class SysRoleRequest extends FormRequest
{
    protected array $scenes = [
        'add' => ['role_name', 'role_code', 'sort', 'status','menu_ids'],
        'update' => ['id', 'role_name', 'role_code', 'sort', 'status','menu_ids'],
        'update_status' => ['id', 'status'],
        'delete' => ['id'],
        'info' => ['id'],
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|integer|min:1',
            'role_name' => 'required|string|max:50',
            'role_code' => ['required','string','max:50','alpha_dash',
                function ($attribute, $value, $fail) {
//                    $exists = SysRoleModel::where('role_code', $value)
//                        ->exists();
//                    if ($exists) {
//                        $fail('角色编码已存在11');
//                    }
                },
            ],
            'sort' => 'integer|min:0',
            'status' => 'required|in:2,1',
            'menu_ids' => 'array',
            'menu_ids.*' => 'integer',
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => '角色ID不能为空',
            'id.integer' => '角色ID格式错误',
            'id.min' => '角色ID必须大于0',
            'role_name.required' => '角色名称不能为空',
            'role_name.max' => '角色名称最多50个字符',
            'role_code.required' => '角色编码不能为空',
            'role_code.max' => '角色编码最多50个字符',
            'role_code.alpha_dash' => '角色编码只能包含字母、数字、下划线和短横线',
            'sort.integer' => '排序必须是整数',
            'sort.min' => '排序不能小于0',
            'status.required' => '状态不能为空',
            'status.in' => '状态值错误',
            'menu_ids.array' => '菜单ID必须是数组',
            'menu_ids.*.integer' => '菜单ID必须是整数',
        ];
    }
}
