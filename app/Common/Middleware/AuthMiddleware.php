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

use App\Common\Exception\AppException;
use App\Common\Exception\AuthException;
use App\Common\Service\JwtService;
use App\Common\Utils\Result;
use Hyperf\Context\Context;
use Hyperf\Contract\TranslatorInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware implements MiddlewareInterface
{
    protected array $appIdList = [
        'pc_admin',
        'app_h5',
        'app_sales',
        'app_staff',
    ];

    protected array $except = [
        '/admin/auth/login',
        '/admin/auth/fs_login',
        '/admin/auth/refresh_token',
        '/admin/common/captcha',
        '/api/auth/login',
        '/api/auth/register',
        '/api/auth/send_code',
        '/api/auth/reset_password',
        '/api/auth/forgot_password',
        '/api/auth/refresh_token',
        '/api/app/info',
        '/api/app/check_update',
        '/api/faq',
        '/api/common/dict_items',
        '/api/common/dicts',
        '/api/common/config',
        '/api/common/upload_file',
    ];

    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected JwtService $jwtService;


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();

        $appId = $request->getHeaderLine('App-ID');
        if (! in_array($appId, $this->appIdList)) {
            return jsonResponse(Result::error('无效的应用ID'));
        }


        Context::set('app_id', $appId);
        Context::set('user_type', explode('_', $appId)[1] ?? '');

        if (shouldPassThrough($this->except, $path)) {
            return $handler->handle($request);
        }

        $token = $this->getBearerToken($request);
        if (empty($token)) {
            throw new AppException('未提供认证Token');
        }

        $result = $this->jwtService->verifyToken($token);

        if (!$result['valid']) {
            throw new AuthException($result['error'] ?? '认证失败');
        }

        $this->bindUserToContext($result);

        return $handler->handle($request);
    }

    protected function getBearerToken(ServerRequestInterface $request): string
    {
        $authHeader = $request->getHeaderLine('Authorization');
        if (str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7);
        }
        return $authHeader;
    }

    /**
     * Desc: 绑定数据到上下文中
     * Auth: hello pan
     * Date: 2/12/26 11:04 AM
     * @param array $result
     */
    protected function bindUserToContext(array $result): void
    {
        $payload = $result['payload'];
        $userInfo = $result['user_info'];

        Context::set('user_id', $payload['sub']);
        Context::set('user_name', $userInfo['username'] ?? '');
        Context::set('token_id', $payload['jti']);
        Context::set('user_info', $userInfo);
    }

}
