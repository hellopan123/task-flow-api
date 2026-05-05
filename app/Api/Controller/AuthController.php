<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Api\Filter\AuthFilter;
use App\Api\Service\UserService;
use App\Api\Validate\AuthRequest;
use App\Common\Controller\AbstractController;
use App\Common\Utils\Result;
use Hyperf\Context\Context;
use Hyperf\Di\Annotation\Inject;

class AuthController extends AbstractController
{
    #[Inject]
    protected UserService $userService;

    public function register(): array
    {
        $this->check(AuthRequest::class, 'register');
        $params = $this->request->all();
        $this->filter(AuthFilter::class, $params, 'register');
        $data = $this->userService->register($params);
        return Result::success($data);
    }

    public function login(): array
    {
        $params = $this->request->all();
        
        if (!empty($params['code'])) {
            $this->check(AuthRequest::class, 'login_sms');
            $this->filter(AuthFilter::class, $params, 'login_sms');
            $data = $this->userService->loginBySms($params);
        } else {
            $this->check(AuthRequest::class, 'login_password');
            $this->filter(AuthFilter::class, $params, 'login_password');
            $data = $this->userService->loginByPassword($params);
        }
        
        return Result::success($data);
    }

    public function sendCode(): array
    {
        $this->check(AuthRequest::class, 'send_code');
        $params = $this->request->all();
        $this->filter(AuthFilter::class, $params, 'send_code');
        
        $this->userService->sendSmsCode($params['phone'], $params['type']);
        return Result::success();
    }

    public function refreshToken(): array
    {
        $refreshToken = $this->request->input('refresh_token');
        if (!$refreshToken) {
            return Result::error('refresh_token不能为空', 400);
        }
        
        $data = $this->userService->refreshToken($refreshToken);
        return Result::success($data);
    }

    public function logout(): array
    {
        $token = $this->request->getHeaderLine('Authorization');
        if ($token) {
            $this->userService->logout($token);
        }
        return Result::success();
    }
}
