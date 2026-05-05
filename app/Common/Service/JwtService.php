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

namespace App\Common\Service;

use App\Admin\Service\AdminService;
use App\Admin\Service\TkUserService;
use App\Common\Cache\Abstract\AbstractHashCache;
use App\Common\Cache\db01\AdminAccessTokenHashCache;
use App\Common\Cache\db01\AgentAccessTokenHashCache;
use App\Common\Cache\db01\LoginTokenStringCache;
use App\Common\Cache\db01\RefreshTokenStringCache;
use App\Common\Cache\db01\H5AccessTokenHashCache;
use App\Common\Cache\db01\StaffAccessTokenHashCache;
use App\Common\Exception\AppException;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use function Hyperf\Config\config;
use Hyperf\Di\Annotation\Inject;
use Psr\Log\LoggerInterface;
use function Hyperf\Support\make;

class JwtService
{
    protected array $userTypeCacheClass = [
        'agent' => AgentAccessTokenHashCache::class,
        'h5'    => H5AccessTokenHashCache::class,
        'staff' => StaffAccessTokenHashCache::class,
        'admin' => AdminAccessTokenHashCache::class,
    ];

    #[Inject]
    protected LoggerInterface $logger;

    #[Inject]
    protected AdminService $adminService;

    protected string $appName = 'Hyperf';


    /**
     * Desc: 创建token
     * Auth: hello pan
     * Date: 2/11/26 9:41 PM
     * @param array $userInfo
     * @param string $appId
     * @param array $deviceInfo
     * @return array
     * @throws AppException
     */
    public function generateToken(array $userInfo, string $appId, array $deviceInfo = []): array
    {
        $appConfig = $this->getAppConfig($appId);
        if (! $appConfig) {
            throw new AppException('应用配置不存在');
        }

        $userType = $userInfo['user_type'] ?? '';
        if (! isset($this->userTypeCacheClass[$userType])) {
            throw new AppException('不支持的用户类型');
        }

        $this->checkMaxDevices($userType, $userInfo['id'], $appId, $appConfig['max_devices']);
        $tokenId = $this->generateTokenId($userType, $userInfo['id'], $appId);

        $payload = [
            'iss' => $this->appName,
            'aud' => $appId,
            'sub' => $userInfo['id'],
            'typ' => $userType,
            'jti' => $tokenId,
            'iat' => time(),
            'exp' => time() + $appConfig['jwt_expire'],
            'nbf' => time(),
            'data' => [
                'user_type' => $userType,
                'user_id' => $userInfo['id'],
                'username' => $userInfo['username'] ?? '',
                'app_id' => $appId,
                'device_id' => $deviceInfo['device_id'] ?? '',
            ],
        ];

        $accessToken = JWT::encode($payload, $appConfig['jwt_secret'], 'HS256');
        $refreshToken = $this->generateRefreshToken($tokenId, $appConfig['jwt_secret']);

        $this->cacheToken($tokenId, $payload, $appConfig['jwt_expire']);
        $this->recordLoginToken($userType, $userInfo['id'], $appId, $tokenId);
        $this->cacheRefreshToken($refreshToken, $payload, $appConfig['refresh_expire']);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => $appConfig['jwt_expire'],
            'user_info' => $userInfo,
        ];
    }

    /**
     * Desc: 验证token
     * Auth: hello pan
     * Date: 2/12/26 10:56 AM
     * @param string $token
     * @param string|null $appId
     * @return array
     */
    public function verifyToken(string $token, ?string $appId = null): array
    {
        try {
            $payload = $this->decodeToken($token);

            if ($appId && $payload['aud'] !== $appId) {
                throw new AppException('Token与应用不匹配');
            }

            if (! $this->isTokenExist($payload)) {
                throw new AppException('Token已失效');
            }

            return [
                'valid' => true,
                'payload' => $payload,
                'user_info' => $this->getUserInfo($payload['typ'], $payload['sub']),
            ];
        } catch (Exception $e) {
            $this->logger->error('JWT验证失败: ' . $e->getMessage(), [
                'token' => substr($token, 0, 50) . '...',
                'app_id' => $appId,
            ]);

            return [
                'valid' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Desc: 刷新token
     * Auth: hello pan
     * Date: 2/12/26 10:56 AM
     * @param string $refreshToken
     * @param string $deviceId
     * @return array
     * @throws AppException
     */
    public function refreshToken(string $refreshToken, string $deviceId = ''): array
    {
        if (! str_starts_with($refreshToken, 'rt_')) {
            throw new AppException('无效的刷新令牌');
        }

        $parts = explode('.', substr($refreshToken, 3));
        if (count($parts) !== 2) {
            throw new AppException('刷新令牌格式错误');
        }

        $tokenId = $parts[0];
        $signature = $parts[1];

        $cache = RefreshTokenStringCache::make([$refreshToken]);
        $cached = $cache->get();
        if (! $cached) {
            throw new AppException('刷新令牌已失效');
        }

        $appConfig = $this->getAppConfig($cached['app_id']);
        $expectedSignature = hash_hmac('sha256', $tokenId, $appConfig['jwt_secret']);
        if (! hash_equals($expectedSignature, $signature)) {
            throw new AppException('刷新令牌签名错误');
        }

        $this->checkRefreshTokenExpire($tokenId, $appConfig);
        $userInfo = $this->getUserInfo($cached['user_type'], $cached['user_id']);

        $tokenInfo = $this->generateToken($userInfo, $cached['app_id'], [
            'device_id' => $deviceId ?: $cached['device_id'],
        ]);
        unset($tokenInfo['user_info']);

        $cache->del();
        return $tokenInfo;
    }

    /**
     * Desc: 退出登录
     * Auth: hello pan
     * Date: 2/12/26 10:57 AM
     * @param string $token
     * @param bool $allDevices
     * @return bool
     */
    public function logout(string $token, bool $allDevices = false): bool
    {
        try {
            $payload = $this->decodeToken($token);
            $tokenId = $payload['jti'];

            $this->delToken($tokenId, $payload);

            if ($allDevices) {
                $this->logoutAllDevices($payload['typ'], $payload['sub'], $payload['aud']);
            }

            return true;
        } catch (Exception $e) {
            $this->logger->error('退出登录失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Desc: 获取配置数据
     * Auth: hello pan
     * Date: 2/12/26 10:57 AM
     * @param string $appId
     * @return array|null
     */
    protected function getAppConfig(string $appId): ?array
    {
        $config = config('jwt.app_type', []);
        $prefix = explode('_', $appId)[0];

        return $config[$prefix] ?? config('jwt.default');
    }

    /**
     * Desc: 创建tokenId
     * Auth: hello pan
     * Date: 2/12/26 10:57 AM
     * @param string $userType
     * @param int $userId
     * @param string $appId
     * @return string
     * @throws \Random\RandomException
     */
    protected function generateTokenId(string $userType, int $userId, string $appId): string
    {
        $random = bin2hex(random_bytes(16));
        return "{$userType}_{$userId}_{$appId}_" . time() . "_{$random}";
    }

    /**
     * Desc: 创建刷新token
     * Auth: hello pan
     * Date: 2/12/26 10:56 AM
     * @param string $tokenId
     * @param string $secret
     * @return string
     */
    protected function generateRefreshToken(string $tokenId, string $secret): string
    {
        $signature = hash_hmac('sha256', $tokenId, $secret);
        return 'rt_' . $tokenId . '.' . $signature;
    }

    /**
     * Desc: 解析token
     * Auth: hello pan
     * Date: 2/12/26 10:55 AM
     * @param string $token
     * @return array
     * @throws AppException
     */
    protected function decodeToken(string $token): array
    {
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }

        $tks = explode('.', $token);
        if (count($tks) != 3) {
            throw new AppException('Token格式错误');
        }

        $payload = JWT::jsonDecode(JWT::urlsafeB64Decode($tks[1]));
        $appConfig = $this->getAppConfig($payload->aud);

        if (! $appConfig) {
            throw new AppException('应用不存在或已禁用');
        }

        $decoded = JWT::decode($token, new Key($appConfig['jwt_secret'], 'HS256'));
        return (array) $decoded;
    }

    /**
     * Desc: 缓存登录token
     * Auth: hello pan
     * Date: 2/12/26 10:55 AM
     * @param string $tokenId
     * @param array $payload
     * @param int $expire
     */
    protected function cacheToken(string $tokenId, array $payload, int $expire): void
    {
        $cache = LoginTokenStringCache::make([$tokenId]);
        $cache->setEx([
            'user_type' => $payload['typ'],
            'user_id' => $payload['sub'],
            'app_id' => $payload['aud'],
            'device_id' => $payload['data']['device_id'] ?? '',
            'payload' => $payload,
        ], $expire);
    }

    /**
     * Desc: 缓存刷新token
     * Auth: hello pan
     * Date: 2/12/26 10:54 AM
     * @param string $refreshToken
     * @param array $payload
     * @param int $expire
     */
    protected function cacheRefreshToken(string $refreshToken, array $payload, int $expire): void
    {
        $cache = RefreshTokenStringCache::make([$refreshToken]);
        $cache->setEx([
            'user_type' => $payload['typ'],
            'user_id' => $payload['sub'],
            'app_id' => $payload['aud'],
            'device_id' => $payload['data']['device_id'] ?? '',
        ], $expire);
    }

    /**
     * Desc: 设置登录token
     * Auth: hello pan
     * Date: 2/12/26 10:54 AM
     * @param string $userType
     * @param int $userId
     * @param string $appId
     * @param string $tokenId
     */
    protected function recordLoginToken(string $userType, int $userId, string $appId, string $tokenId): void
    {
        $cache = $this->getUserTypeCache($userType, $userId, $appId);
        $cache->set($tokenId, time());
    }

    /**
     * Desc: 删除token
     * Auth: hello pan
     * Date: 2/12/26 10:54 AM
     * @param string $tokenId
     * @param array $payload
     */
    protected function delToken(string $tokenId, array $payload): void
    {
        $cache = $this->getUserTypeCache($payload['typ'], $payload['sub'], $payload['aud']);
        $cache->hDel($tokenId);

        $tokenCache = LoginTokenStringCache::make([$tokenId]);
        $tokenCache->del();
    }

    /**
     * Desc: 退出所有的设备
     * Auth: hello pan
     * Date: 2/12/26 10:53 AM
     * @param string $userType
     * @param int $userId
     * @param string $appId
     */
    protected function logoutAllDevices(string $userType, int $userId, string $appId): void
    {
        $cache = $this->getUserTypeCache($userType, $userId, $appId);
        $tokens = $cache->getAll();

        foreach ($tokens as $tokenId => $timestamp) {
            $this->delToken($tokenId, [
                'typ' => $userType,
                'sub' => $userId,
                'aud' => $appId,
            ]);
        }
    }

    /**
     * Desc: 验证token是否存在
     * Auth: hello pan
     * Date: 2/12/26 10:53 AM
     * @param array $payload
     * @return bool
     */
    protected function isTokenExist(array $payload): bool
    {
        $cache = LoginTokenStringCache::make([$payload['jti']]);
        if (! $cache->exists()) {
            return false;
        }

        $accessCache = $this->getUserTypeCache($payload['typ'], $payload['sub'], $payload['aud']);
        if (! $accessCache->fieldExists($payload['jti'])) {
            return false;
        }

        return true;
    }

    /**
     * Desc: 验证最大设备数
     * Auth: hello pan
     * Date: 2/12/26 10:52 AM
     * @param string $userType
     * @param int $userId
     * @param string $appId
     * @param int $maxDevices
     */
    protected function checkMaxDevices(string $userType, int $userId, string $appId, int $maxDevices): void
    {
        $cache = $this->getUserTypeCache($userType, $userId, $appId);
        $devices = $cache->getAll();

        if (count($devices) >= $maxDevices) {
            arsort($devices);
            $i = 0;
            foreach ($devices as $deviceId => $timestamp) {
                if (++$i < $maxDevices) {
                    continue;
                }
                $cache->hDel($deviceId);
            }
        }
    }

    /**
     * Desc: 验证刷新token有效期
     * Auth: hello pan
     * Date: 2/12/26 10:52 AM
     * @param string $tokenId   token
     * @param array $appConfig  配置文件
     * @throws AppException
     */
    protected function checkRefreshTokenExpire(string $tokenId, array $appConfig): void
    {
        $parts = explode('_', $tokenId);
        if (! isset($parts[4])) {
            throw new AppException('刷新令牌格式错误');
        }

        $createTime = (int) $parts[4];
        if (time() - $createTime > $appConfig['refresh_expire']) {
            throw new AppException('刷新令牌已过期');
        }
    }

    /**
     * Desc: 获取用户信息
     * Auth: hello pan
     * Date: 2/12/26 10:51 AM
     * @param string $userType
     * @param int $userId
     * @return array
     */
    protected function getUserInfo(string $userType, int $userId): array
    {
        $userInfo = null;

        switch ($userType) {
            case 'agent':
                $tableMap[$userType] = 'agent_users';
                break;
            case 'staff':
                $tableMap[$userType] = 'staff_users';
                break;
            case 'h5':
                $userInfo = make(TkUserService::class)->getInfoById($userId);
                if ($userInfo) {
                    unset($userInfo['password']);
                }
                break;
            case 'admin':
            default:
                $userInfo = $this->adminService->getUserInfo($userId);
                break;
        }

        if (! empty($userInfo)) {
            $userInfo['user_type'] = $userType;
        }

        return $userInfo;
    }

    /**
     * 获取用户类型对应的缓存类
     * Auth: hello pan
     * Date: 2/12/26 10:51 AM
     * @param string $userType 用户类型
     * @param $userId
     * @param string $appId
     * @return AbstractHashCache
     */
    protected function getUserTypeCache(string $userType, $userId, string $appId): AbstractHashCache
    {
        $class = $this->userTypeCacheClass[$userType];
        return $class::make(['user_id' => $userId, 'app_id' => $appId]);
    }
}
