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

use App\Common\Cache\db00\IpPathLimitCache;
use App\Common\Exception\AppException;
use App\Common\Utils\Result;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * 限流中间件.
 */
class RateLimiterMiddleware implements MiddlewareInterface
{
    protected int $defaultTimeNum = 150;

    protected array $pathTimeNum = [];

    #[Inject]
    protected LoggerInterface $logger;


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $this->checkIpPathLimit($request);
        } catch (Throwable $e) {
            $this->logger->error('限流检查失败: ' . $e->getMessage());
            return jsonResponse(Result::error($e->getMessage(), [], Result::CODE_SYSTEM_API_LIMIT));
        }
        return $handler->handle($request);
    }

    protected function checkIpPathLimit(ServerRequestInterface $request): void
    {
        $appId = $request->getHeaderLine('App-ID');
        $ip = get_real_ip();
        $path = $request->getUri()->getPath();

        $field = [$appId, $ip, $path];
        $cache = IpPathLimitCache::make($field);
        $time = $cache->increase();

        if ($time === 1) {
            $cache->expire(60);
        }

        $maxTime = $this->pathTimeNum[$path] ?? $this->defaultTimeNum;
        if ($maxTime >= 0 && $time >= $maxTime) {
            $this->limitOutput($appId, $ip, $path);
        }
    }

    protected function limitOutput(string $appId, string $ip, string $path): void
    {
        $msg = sprintf('time:%s, ip:%s, appId:%s, path:%s', date('Y-m-d H:i:s'), $ip, $appId, $path);
        $this->logger->warning('访问限制-操作频繁: ' . $msg);
        throw new AppException('操作频繁，请稍后重试');
    }

}
