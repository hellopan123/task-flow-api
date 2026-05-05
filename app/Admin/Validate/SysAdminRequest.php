<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Admin\Validate;


use App\Common\Service\CaptchaService;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rules\Password;
use Hyperf\Validation\Validator;

class SysAdminRequest extends FormRequest
{
    protected array $scenes = [
        'login'         => ['request_id','username', 'password', 'verify'],
        'add'           => ['username', 'nickname', 'email', 'role_ids', 'password'],
        'update'        => ['id','username', 'nickname', 'email', 'role_ids'],
        'info'          => ['id'],
        'delete'        => ['id'],
        'stop'          => ['id'],
        'start'         => ['id'],
        'update_status' => ['id','status'],
        'feishu_login' => ['code'],
    ];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id'           => 'required|integer',
            'request_id'   => 'required',
            'verify'       => 'required|size:5|checkCaptchaCode',
            'password'     => ['required',Password::min(6)->max(30)->numbers()],
            'username'    => 'required|max:25',
            'nickname'    => 'required|max:25',
            'email'        => 'required|email',
            'role_ids'      => 'required',
            'password_two' => 'required|confirmed:password',
            'status'        => 'required|in:1,2',
            'code'        => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'id.required'              => '管理员ID不能为空!',
            'id.integer'              => '管理员ID格式错误!',
            'request_id.required'      => '请输入请求ID',
            'verify.required'          => '请输入验证码',
            'verify.size'           => '验证码长度错误',
            'verify.checkCaptchaCode' => '验证码错误',
            'username.required'        => '请输入姓名',
            'password.required'        => '请输入密码',
            'password.*'               => '密码格式不正确',
            'username'               => '账号不能为空！',
            'nickname'               => '姓名不能为空！',
            'email.required'           => '邮箱不能为空！',
            'email.email'             => '邮箱格式错误！',
            'role_ids.required'         => '请选择角色！',
            'role_ids.integer'         => '角色ID格式错误!',
            'password_two.required'    => '确认密码不能为空！',
            'password_two.confirmed'    => '两次密码不一致！',
            'status.required'          => '状态不能为空!',
            'status.in'               => '状态格式错误!',
            'code.required'            => '飞书授权码不能为空!',
        ];
    }

    /**
     * 配置验证器实例
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            // 获取当前场景
            $scene = $this->getScene();

            // 仅在 login 场景下验证验证码
            if ($scene === 'login') {
                $data = $validator->getData();
                $verify = $data['verify'] ?? '';
                $requestId = $data['request_id'] ?? '';

                if ($verify && $requestId) {
                    $captchaService = $this->container->get(CaptchaService::class);
                    if (! $captchaService->verifyCode($verify, $requestId)) {
                        //$validator->errors()->add('verify', '验证码错误');
                    }
                }
            }
        });
    }
    

}
