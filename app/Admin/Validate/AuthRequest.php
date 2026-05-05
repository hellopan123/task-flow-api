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
use Hyperf\Validation\Validator;

class AuthRequest extends FormRequest
{
    protected array $scenes = [
        'login' => ['username', 'password',
            //'verify', 'request_id'
        ],
        'test' => ['username' => 'string|required', 'password'],
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
            'request_id' => 'required|string',
            'username' => [
                'required',
                'string',
//                function ($attribute, $value, $fail) {
//                    if ($value === 'admin') {
//                        $fail('用户名不能为 admin');
//                    }
//                },
            ],
            'password' => [
                'required',
                'string',
            ],
            'verify' => 'required|string|size:5',
        ];
    }

    public function messages(): array
    {
        return [
            'request_id.required' => '请传递Request-Id header参数',
            'username.required' => '请输入用户名',
            'password.required' => '请输入密码',
            'verify.required' => '请输入验证码',
            'verify.size' => '验证码长度必须为5位',
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
