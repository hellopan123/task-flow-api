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
use App\Common\Middleware\AuthCheckMiddleware;
use App\Common\Middleware\AuthMiddleware;
use App\Common\Middleware\IdempotencyMiddleware;
use App\Common\Middleware\RateLimiterMiddleware;
use App\Common\Middleware\SignatureMiddleware;
use App\Common\Middleware\WriteLogMiddleware;
use Hyperf\Validation\Middleware\ValidationMiddleware;

return [
    'http' => [
        WriteLogMiddleware::class,
        RateLimiterMiddleware::class,
        SignatureMiddleware::class,
        AuthMiddleware::class,
        //AuthCheckMiddleware::class,
        IdempotencyMiddleware::class,
        // 验证中间件 FormRequest
        ValidationMiddleware::class
    ],
];
