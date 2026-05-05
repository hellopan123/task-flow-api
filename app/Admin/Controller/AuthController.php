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

namespace App\Admin\Controller;

use App\Admin\Service\AdminService;
use App\Admin\Validate\AuthRequest;
use App\Common\Controller\AbstractController;
use App\Common\Exception\AppException;
use App\Common\Service\JwtService;
use App\Common\Utils\Result;
use Exception;
use Hyperf\Context\Context;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Log\LoggerInterface;

use function Hyperf\Support\make;

class AuthController extends AbstractController
{
    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected LoggerInterface $logger;

    #[Inject]
    protected AdminService $adminService;

    /**
     * 管理员登录.
     * @title 管理员登录
     * @method post
     * @url /admin/auth/login
     * @param verify 必选 string 验证码
     * @param request_id 必选 string 请求ID
     * @param username 必选 string 用户名
     * @param password 必选 string 密码
     */
    public function login(): array
    {
        $this->check(AuthRequest::class, 'login');

        $params = $this->request->all();

        return Result::success($this->adminService->login($params));
    }

    /**
     * 刷新token
     * 支持多设备刷新token.
     * @title 刷新token
     * @method post
     * @url /admin/auth/refresh_token
     * @param refresh_token 必选 string 刷新token
     */
    public function refreshToken(): array
    {
        $refreshToken = $this->request->input('refresh_token');
        if (empty($refreshToken)) {
            throw new AppException('刷新Token不能为空');
        }

        try {
            $deviceId = $this->request->input('device_id', '');

            $jwtService = make(JwtService::class);
            $tokenData = $jwtService->refreshToken($refreshToken, $deviceId);
            return Result::success($tokenData);
        } catch (Exception $e) {
            $this->logger->error('刷新Token失败: ' . $e->getMessage(), $this->request->all());
            throw new AppException($e->getMessage());
        }
    }

    /**
     * @title 退出登录
     * @method get|post
     * @url /admin/auth/logout
     * @return {"code":"200","msg":"操作成功","data":{}}
     */
    public function logout(): array
    {
        $jwtService = make(JwtService::class);
        $token = $this->request->getHeaderLine('Authorization');
        if (! $jwtService->logout($token)) {
            throw new AppException('退出登录失败');
        }
        return Result::success();
    }

    /**
     * @title 获取当前登录用户信息
     * @method get
     * @url /admin/user_info
     *
     */
    public function getUserInfo(): array
    {
        $userId = Context::get('user_id');
        $userInfo = $this->adminService->getUserInfo($userId);
        return Result::success($userInfo);
    }

    /**
     * Desc: 飞书登录
     * Auth: hello pan
     * Date: 2/12/26 1:08 PM
     * @return array
     * @throws AppException
     */
    public function loginByFsCode(): array
    {
        $code = $this->request->input('code');
        if (empty($code)) {
            throw new AppException('飞书授权码不能为空');
        }

        // TODO: 调用FeishuService获取用户信息并登录
        throw new AppException('飞书登录功能待实现');
    }
}
