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

use App\Common\Cache\db00\ApiIdempotencyStringCache;
use App\Common\Utils\Result;
use Hyperf\Context\Context;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class IdempotencyMiddleware implements MiddlewareInterface
{
    protected array $except = [
        '/admin/auth/login',
        '/admin/auth/fs_login',
        '/admin/auth/refresh_token',
        '/admin/auth/logout',
        '/admin/user_info',
        '/admin/sys_menu/nav_menu',
        '/admin/common/captcha',

        '/api/auth/login',
        '/api/auth/refresh_token',
        '/api/common/upload_file',
    ];

    #[Inject]
    protected LoggerInterface $logger;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $method = $request->getMethod();

        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'])) {
            return $handler->handle($request);
        }

        $path = $request->getUri()->getPath();

        if (shouldPassThrough($this->except, $path)) {
            return $handler->handle($request);
        }

        $key = $request->getHeaderLine('Request-Id');
        if (empty($key)) {
            return jsonResponse(Result::error('请传递Request-Id header参数'));
        }

        try {
            $userId = Context::get('user_id') ?? 'guest';
            $cache = ApiIdempotencyStringCache::make([$userId, $key]);
            if (! $cache->setNx(1)) {
                return jsonResponse(Result::error('请求处理中，请勿重复提交', [], Result::CODE_SYSTEM_API_LIMIT));
            }
        } catch (Throwable $e) {
            $this->logger->error('Redis操作失败: ' . $e->getMessage());
        }
        return $handler->handle($request);
    }

}
