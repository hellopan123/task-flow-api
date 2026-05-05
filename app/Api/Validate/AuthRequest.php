<?php

declare(strict_types=1);

namespace App\Api\Validate;

use App\Common\Cache\db00\SmsCodeStringCache;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Validator;

class AuthRequest extends FormRequest
{
    protected array $scenes = [
        'register' => ['phone', 'password', 'code', 'nickname'],
        'login_password' => ['phone', 'password'],
        'login_sms' => ['phone', 'code'],
        'send_code' => ['phone', 'type'],
        'reset_password' => ['phone', 'password', 'code'],
        'change_password' => ['old_password', 'new_password'],
        'forgot_password' => ['phone', 'code', 'new_password'],
        'update_profile' => ['nickname', 'avatar', 'email', 'gender', 'position'],
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => [
                'required',
                'regex:/^1[3-9]\d{9}$/',
            ],
            'password' => [
                'required',
                'string',
                'min:6',
                'max:20',
            ],
            'code' => [
                'required',
                'string',
                'size:6',
            ],
            'nickname' => [
                'string',
                'max:50',
            ],
            'avatar' => [
                'string',
                'max:500',
            ],
            'email' => [
                'email',
                'max:100',
            ],
            'gender' => [
                'in:0,1,2',
            ],
            'position' => [
                'string',
                'max:50',
            ],
            'type' => [
                'required',
                'in:register,login,reset',
            ],
            'old_password' => [
                'required',
                'string',
                'min:6',
                'max:20',
            ],
            'new_password' => [
                'required',
                'string',
                'min:6',
                'max:20',
                'different:old_password',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => '手机号不能为空',
            'phone.regex' => '手机号格式不正确',
            'password.required' => '密码不能为空',
            'password.min' => '密码最少6个字符',
            'password.max' => '密码最多20个字符',
            'code.required' => '验证码不能为空',
            'code.size' => '验证码必须是6位',
            'nickname.max' => '昵称最多50个字符',
            'email.email' => '邮箱格式不正确',
            'email.max' => '邮箱最多100个字符',
            'gender.in' => '性别值错误',
            'position.max' => '职位最多50个字符',
            'type.required' => '验证码类型不能为空',
            'type.in' => '验证码类型错误',
            'old_password.required' => '原密码不能为空',
            'new_password.required' => '新密码不能为空',
            'new_password.different' => '新密码不能与原密码相同',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $scene = $this->getScene();
            $data = $validator->getData();
            
            if (in_array($scene, ['register', 'login_sms', 'reset_password', 'forgot_password', 'send_code'])) {
                $phone = $data['phone'] ?? '';
                $code = $data['code'] ?? '';
                $type = $data['type'] ?? '';
                
                if ($scene === 'send_code' && $phone && $type) {
                    $this->validateSendFrequency($phone, $type, $validator);
                } elseif ($code && $phone) {
                    $codeType = $this->getCodeType($scene);
                    $this->validateSmsCode($phone, $code, $codeType, $validator);
                }
            }
        });
    }

    protected function validateSendFrequency(string $phone, string $type, $validator): void
    {
        $cache = SmsCodeStringCache::make([$phone, $type]);
        if ($cache->exists()) {
            $validator->errors()->add('phone', '验证码发送过于频繁，请稍后再试');
        }
    }

    protected function validateSmsCode(string $phone, string $code, string $type, $validator): void
    {
        $cache = SmsCodeStringCache::make([$phone, $type]);
        $cachedCode = $cache->get();
        
        if (!$cachedCode) {
            $validator->errors()->add('code', '验证码已过期');
            return;
        }
        
        if ($cachedCode !== $code) {
            $validator->errors()->add('code', '验证码错误');
        }
    }

    protected function getCodeType(string $scene): string
    {
        return match ($scene) {
            'register' => 'register',
            'login_sms' => 'login',
            'reset_password', 'forgot_password' => 'reset',
            default => 'login',
        };
    }
}
