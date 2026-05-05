<?php

declare(strict_types=1);

namespace App\Api\Service;

use App\Common\Cache\db00\SmsCodeStringCache;
use App\Common\Exception\AppException;
use App\Common\Model\SysConfigModel;
use App\Common\Model\TkSmsCodeModel;
use App\Common\Model\TkUserModel;
use App\Common\Service\JwtService;
use Hyperf\Context\Context;
use Hyperf\DbConnection\Db;
use Psr\Log\LoggerInterface;
use function Hyperf\Support\make;

class UserService
{
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Desc: 用户注册操作
     * Auth: hello pan
     * Date: 5/2/26 PM7:48
     * @param array $params
     * @return array
     * @throws AppException
     */
    public function register(array $params): array
    {
        $phone = $params['phone'];
        
        $exists = TkUserModel::where('phone', $phone)->exists();
        if ($exists) {
            throw new AppException('手机号已注册');
        }
        
        Db::beginTransaction();
        try {
            $user = TkUserModel::create([
                'phone' => $phone,
                'password' => password($params['password'], $params['salt']),
                'nickname' => $params['nickname'] ?? '',
                'status' => 1,
            ]);
            
            $this->deleteSmsCode($phone, 'register');
            
            Db::commit();
            
            return $this->generateToken($user->toArray());
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error('用户注册失败: ' . $e->getMessage());
            throw new AppException('注册失败');
        }
    }

    /**
     * Desc: 密码登录
     * Auth: hello pan
     * Date: 2/26/26 9:56 PM
     * @param array $params
     * @return array
     * @throws AppException
     */
    public function loginByPassword(array $params): array
    {
        $phone = $params['phone'];
        
        $user = TkUserModel::where('phone', $phone)->first();
        if (!$user) {
            throw new AppException('用户不存在');
        }
        
        if ($user->password != password($params['password'], $user->salt)) {
            throw new AppException('密码错误');
        }
        
        if ($user->status !== 1) {
            throw new AppException('账号已被禁用');
        }
        
        $this->updateLoginInfo($user->id);
        $user_info = $user->toArray();
        unset($user_info['password'],$user_info['salt']);

        return $this->generateToken($user_info);
    }

    /**
     * Desc: 验证码登录
     * Auth: hello pan
     * Date: 5/2/26 PM7:48
     * @param array $params
     * @return array
     * @throws AppException
     */
    public function loginBySms(array $params): array
    {
        $phone = $params['phone'];
        
        $user = TkUserModel::where('phone', $phone)->first();
        if (!$user) {
            throw new AppException('用户不存在');
        }
        
        if ($user->status !== 1) {
            throw new AppException('账号已被禁用');
        }
        
        $this->updateLoginInfo($user->id);
        $this->deleteSmsCode($phone, 'login');

        $user_info = $user->toArray();
        unset($user_info['password'],$user_info['salt']);

        return $this->generateToken($user_info);
    }

    /**
     * Desc: 发送验证码
     * Auth: hello pan
     * Date: 5/2/26 PM7:49
     * @param string $phone
     * @param string $type
     * @return bool
     * @throws AppException
     * @throws \Random\RandomException
     */
    public function sendSmsCode(string $phone, string $type): bool
    {
        $config = $this->getSmsConfig();
        if (!$config['enabled']) {
            throw new AppException('短信功能未开启');
        }
        
        if ($type === 'register') {
            $exists = TkUserModel::where('phone', $phone)->exists();
            if ($exists) {
                throw new AppException('手机号已注册');
            }
        } elseif ($type === 'login' || $type === 'reset') {
            $exists = TkUserModel::where('phone', $phone)->exists();
            if (!$exists) {
                throw new AppException('用户不存在');
            }
        }
        
        $code = $this->generateCode();

        // 增加短信记录
        TkSmsCodeModel::insert([
            'phone' => $phone,
            'code' => $code,
            'code_type' => $type === 'register' ? 2 : ($type === 'login' ? 1 : 3),
            'expire_time' => date('Y-m-d H:i:s',time() + ($config['expire_minutes'] * 60)),
        ]);
        
        $cache = SmsCodeStringCache::make([$phone, $type]);
        $cache->setEx($code, $config['expire_minutes'] * 60);
        
        $this->logger->info("发送短信验证码: phone={$phone}, type={$type}, code={$code}");
        var_dump($code);
        return true;
    }

    /**
     * Desc: 重置密码
     * Auth: hello pan
     * Date: 5/2/26 PM7:49
     * @param array $params
     * @return bool
     * @throws AppException
     */
    public function resetPassword(array $params): bool
    {
        $phone = $params['phone'];
        
        $user = TkUserModel::where('phone', $phone)->first();
        if (!$user) {
            throw new AppException('用户不存在');
        }
        
        $user->password = password($params['password'], $user->salt);
        $user->save();
        
        $this->deleteSmsCode($phone, 'reset');
        
        return true;
    }

    /**
     * Desc: 更新密码
     * Auth: hello pan
     * Date: 5/2/26 PM7:49
     * @param int $userId
     * @param array $params
     * @return bool
     * @throws AppException
     */
    public function updatePassword(int $userId, array $params): bool
    {
        $user = TkUserModel::find($userId);
        if (!$user) {
            throw new AppException('用户不存在');
        }
        
        if (password($params['old_password'], $user->salt) != $user->password) {
            throw new AppException('原密码错误');
        }
        
        $user->password = password($params['new_password'], $user->salt);
        return $user->save();
    }

    /***
     * Desc: 更新个人信息
     * Auth: hello pan
     * Date: 5/2/26 PM7:49
     * @param int $userId
     * @param array $params
     * @return bool
     * @throws AppException
     */
    public function updateProfile(int $userId, array $params): bool
    {
        $user = TkUserModel::find($userId);
        if (!$user) {
            throw new AppException('用户不存在');
        }
        
        $allowFields = ['nickname', 'avatar', 'email', 'gender', 'position'];
        $updateData = array_intersect_key($params, array_flip($allowFields));

        if (empty($updateData)) {
            return true;
        }
        
        return $user->update($updateData) > 0;
    }

    /**
     * Desc: 获取用户信息
     * Auth: hello pan
     * Date: 5/2/26 PM7:50
     * @param int $userId
     * @return array|null
     */
    public function getUserInfo(int $userId): ?array
    {
        $user = TkUserModel::find($userId);
        if (!$user) {
            return null;
        }
        
        $data = $user->toArray();
        unset($data['password'],$data['salt']);
        
        return $data;
    }

    /**
     * Desc: 退出
     * Auth: hello pan
     * Date: 5/2/26 PM7:50
     * @param string $token
     * @return bool
     */
    public function logout(string $token): bool
    {
        $jwtService = make(JwtService::class);
        return $jwtService->logout($token);
    }

    /**
     * Desc: 忘记密码
     * Auth: hello pan
     * Date: 5/2/26 PM7:50
     * @param array $params
     * @return bool
     * @throws AppException
     */
    public function forgotPassword(array $params): bool
    {
        $phone = $params['phone'];
        $code = $params['code'];
        $newPassword = $params['new_password'];
        
        $this->validateSmsCode($phone, $code, 'reset');
        
        $user = TkUserModel::where('phone', $phone)->first();
        if (!$user) {
            throw new AppException('用户不存在');
        }
        
        $user->password = password($newPassword, $user->salt);
        $result = $user->save();
        
        $this->deleteSmsCode($phone, 'reset');
        
        return $result;
    }

    /**
     * Desc: 刷新token
     * Auth: hello pan
     * Date: 5/2/26 PM7:50
     * @param string $refreshToken
     * @return array
     * @throws AppException
     */
    public function refreshToken(string $refreshToken): array
    {
        $jwtService = make(JwtService::class);
        return $jwtService->refreshToken($refreshToken);
    }

    /**
     * Desc: 验证验证码
     * Auth: hello pan
     * Date: 5/2/26 PM7:50
     * @param string $phone
     * @param string $code
     * @param string $type
     * @throws AppException
     */
    protected function validateSmsCode(string $phone, string $code, string $type): void
    {
        $cache = SmsCodeStringCache::make([$phone, $type]);
        $cachedCode = $cache->get();
        
        if (!$cachedCode) {
            throw new AppException('验证码已过期');
        }
        
        if ($cachedCode !== $code) {
            throw new AppException('验证码错误');
        }
    }

    /**
     * Desc: 构建登录token
     * Auth: hello pan
     * Date: 5/2/26 PM7:51
     * @param array $userInfo
     * @return array
     * @throws AppException
     */
    protected function generateToken(array $userInfo): array
    {

        $appId = Context::get('app_id');
        $userInfo['user_type'] = Context::get('user_type');

        $jwtService = make(JwtService::class);
        return $jwtService->generateToken($userInfo, $appId);
    }

    /**
     * Desc: 更新登录信息
     * Auth: hello pan
     * Date: 5/2/26 PM7:51
     * @param int $userId
     */
    protected function updateLoginInfo(int $userId): void
    {
        TkUserModel::where('id', $userId)->update([
            'last_login_time' => date('Y-m-d H:i:s'),
            'last_login_ip' => get_real_ip(),
        ]);
    }

    /**
     * Desc: 创建code
     * Auth: hello pan
     * Date: 5/2/26 PM7:47
     * @return string
     * @throws \Random\RandomException
     */
    protected function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Desc: 删除验证码
     * Auth: hello pan
     * Date: 5/2/26 PM7:47
     * @param string $phone
     * @param string $type
     */
    protected function deleteSmsCode(string $phone, string $type): void
    {
        $cache = SmsCodeStringCache::make([$phone, $type]);
        $cache->del();
    }

    /**
     * Desc: 获取系统设置
     * Auth: hello pan
     * Date: 5/2/26 PM7:47
     * @return array
     */
    protected function getSmsConfig(): array
    {
        $config = SysConfigModel::where('config_group', 'sms')
            ->where('status', 1)
            ->get(['config_key', 'config_value'])
            ->pluck('config_value', 'config_key')
            ->toArray();
        
        return [
            'enabled' => ($config['sms_enabled'] ?? 'false') === 'true',
            'expire_minutes' => (int) ($config['sms_expire_minutes'] ?? 5),
        ];
    }
}
