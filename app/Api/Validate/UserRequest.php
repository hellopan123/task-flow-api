<?php

declare(strict_types=1);

namespace App\Api\Validate;

use Hyperf\Validation\Request\FormRequest;

class UserRequest extends FormRequest
{
    protected array $scenes = [
        'info' => ['id'],
        'update_profile' => ['nickname', 'avatar', 'email', 'gender', 'position'],
        'update_password' => ['old_password', 'new_password'],
        'add' => ['phone','email','nickname', 'avatar'],
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|integer|min:1',
            'phone' => 'required|regex:/^1[3-9]\d{9}$/',
            'nickname' => 'string|max:50',
            'avatar' => 'string|max:500',
            'email' => 'email|max:100',
            'gender' => 'in:0,1,2',
            'position' => 'string|max:50',
            'old_password' => 'required|string|min:6|max:20',
            'new_password' => 'required|string|min:6|max:20|different:old_password',
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => '用户ID不能为空',
            'phone.required' => '手机号不能为空',
            'phone.regex' => '手机号格式不正确',
            'nickname.max' => '昵称最多50个字符',
            'email.email' => '邮箱格式不正确',
            'email.max' => '邮箱最多100个字符',
            'gender.in' => '性别值错误',
            'position.max' => '职位最多50个字符',
            'old_password.required' => '原密码不能为空',
            'new_password.required' => '新密码不能为空',
            'new_password.different' => '新密码不能与原密码相同',
        ];
    }
}
