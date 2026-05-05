<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Api\Filter\AuthFilter;
use App\Api\Filter\UserFilter;
use App\Api\Service\UserService;
use App\Api\Validate\AuthRequest;
use App\Api\Validate\UserRequest;
use App\Common\Controller\AbstractController;
use App\Common\Exception\AppException;
use App\Common\Utils\Result;
use Hyperf\Context\Context;
use Hyperf\Di\Annotation\Inject;

class UserController extends AbstractController
{
    #[Inject]
    protected UserService $userService;

    public function info(): array
    {
        $userId = Context::get('user_id');
        if (!$userId) {
            throw new AppException('未登录');
        }
        
        $data = $this->userService->getUserInfo($userId);
        if (!$data) {
            throw new AppException('用户不存在');
        }
        
        return Result::success($data);
    }

    public function updateProfile(): array
    {
        $userId = Context::get('user_id');
        if (!$userId) {
            throw new AppException('未登录');
        }
        
        $this->check(UserRequest::class, 'update_profile');
        $params = $this->request->all();
        $this->filter(UserFilter::class, $params, 'update_profile');
        
        $this->userService->updateProfile($userId, $params);
        return Result::success();
    }

    public function resetPassword(): array
    {
        $this->check(AuthRequest::class, 'reset_password');
        $params = $this->request->all();
        $this->filter(AuthFilter::class, $params, 'reset_password');

        $this->userService->resetPassword($params);
        return Result::success();
    }

    public function changePassword(): array
    {
        $userId = Context::get('user_id');
        if (!$userId) {
            return Result::error('未登录', [],401);
        }

        $this->check(AuthRequest::class, 'change_password');
        $params = $this->request->all();
        $this->filter(AuthFilter::class, $params, 'change_password');

        $this->userService->updatePassword($userId, $params);
        return Result::success();
    }

    public function forgotPassword(): array
    {
        $this->check(AuthRequest::class, 'forgot_password');
        $params = $this->request->all();
        $this->filter(AuthFilter::class, $params, 'forgot_password');

        $this->userService->forgotPassword($params);
        return Result::success();
    }

}
