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

namespace App\Common\Middleware;

use App\Admin\Service\SysAdminRoleService;
use App\Common\Exception\AuthException;
use Hyperf\Context\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthCheckMiddleware implements MiddlewareInterface
{
    protected array $except = [
        '/admin/auth/login',
        '/admin/auth/fs_login',
        '/admin/auth/refresh_token',
        '/admin/auth/logout',
        '/admin/user_info',
        '/admin/sys_menu/nav_menu',
        '/admin/common/captcha',
        '/admin/common/qiniu_token',
        '/admin/common/fs_authorize_url',
    ];

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();

        if (shouldPassThrough($this->except,$path)) {
            return $handler->handle($request);
        }

        $userId = Context::get('user_id');
        $userInfo = Context::get('user_info');
        $roleIds = $userInfo['role_ids'] ?? null;

        if (! $userId || ! $roleIds) {
            throw new AuthException('请先登录');
        }

        if (in_array(1,$roleIds) || $userId === 1) {
            return $handler->handle($request);
        }

        $permissions = [];
        foreach ($roleIds as $roleId){
            $permissions = array_merge($permissions,SysAdminRoleService::getAdminRoleMenuAuthCode($roleId, 60));
        }

        if (! in_array($path, $permissions) && ! in_array('/' . $path, $permissions)) {
            throw new AuthException('权限不足');
        }

        return $handler->handle($request);
    }

}
