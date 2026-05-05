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

namespace App\Common\Exception\Handler;

use App\Common\Exception\AppException;
use App\Common\Exception\AuthException;
use App\Common\Exception\ValidationException;
use App\Common\Utils\Result;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\Validation\ValidationException as HyperfValidationException;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use function Hyperf\Support\env;

class AppExceptionHandler extends ExceptionHandler
{
    #[Inject]
    protected StdoutLoggerInterface $logger;

    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        //$this->logToSentry($throwable);

        if ($throwable instanceof AuthException) {
            return jsonResponse(Result::error($throwable->getMessage(), [], Result::CODE_AUTH_FAIL),Result::CODE_AUTH_FAIL);
        }

        if ($throwable instanceof ValidationException) {
            return jsonResponse(Result::error($throwable->getMessage(), [], Result::CODE_ERROR));
        }

        if ($throwable instanceof HyperfValidationException) {
            return jsonResponse(Result::error($throwable->validator->errors()->first(), [], Result::CODE_ERROR));
        }

        if ($throwable instanceof AppException) {
            return jsonResponse(Result::error($throwable->getMessage()));
        }

        $this->logger->error(sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
        $this->logger->error($throwable->getTraceAsString());

        $isDebug = env('APP_DEBUG', false);
        return jsonResponse(
            Result::error($isDebug ? $throwable->getMessage() : null, [], Result::CODE_SYSTEM_ERROR)
        );
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }

    protected function logToSentry(Throwable $throwable): void
    {
        if (extension_loaded('sentry') && env('SENTRY_DSN')) {
            \Sentry\captureException($throwable);
        }
    }
}
