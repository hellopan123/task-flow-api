<?php

declare(strict_types=1);

namespace App\Admin\Validate;

use Hyperf\Validation\Request\FormRequest;

class TkUserRequest extends FormRequest
{
    protected array $scenes = [
        'list' => ['page', 'limit'],
        'add' => ['phone', 'password', 'nickname', 'email', 'gender'],
        'update_status' => ['id', 'status'],
        'delete' => ['id'],
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|integer|min:1',
            'phone' => 'required|regex:/^1[3-9]\d{9}$/|unique:tk_user,phone',
            'password' => 'required|string|min:6|max:20',
            'nickname' => 'string|max:50',
            'email' => 'email|max:100',
            'gender' => 'in:0,1,2',
            'position' => 'string|max:50',
            'status' => 'required|in:0,1',
            'page' => 'integer|min:1',
            'limit' => 'integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => '用户ID不能为空',
            'id.integer' => '用户ID格式错误',
            'id.min' => '用户ID必须大于0',
            'phone.required' => '手机号不能为空',
            'phone.regex' => '手机号格式不正确',
            'phone.unique' => '手机号已存在',
            'password.required' => '密码不能为空',
            'password.min' => '密码最少6个字符',
            'password.max' => '密码最多20个字符',
            'nickname.max' => '昵称最多50个字符',
            'email.email' => '邮箱格式不正确',
            'email.max' => '邮箱最多100个字符',
            'gender.in' => '性别值错误',
            'position.max' => '职位最多50个字符',
            'status.required' => '状态不能为空',
            'status.in' => '状态值错误',
        ];
    }
}
