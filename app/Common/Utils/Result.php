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

namespace App\Common\Utils;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\TranslatorInterface;
use Throwable;

class Result
{
    public const CODE_SUCCESS = 0;

    public const CODE_ERROR = 400;

    public const CODE_AUTH_FAIL = 401;

    public const CODE_AUTH_ROLE_DENIED = 403;

    public const CODE_NOT_FOUND = 404;

    public const CODE_SYSTEM_API_LIMIT = 429;

    public const CODE_SYSTEM_ERROR = 500;

    public const CODE_SYSTEM_BUSY = 503;

    protected const CODE_MAP = [
        self::CODE_SUCCESS => '操作成功',
        self::CODE_ERROR => '操作失败',
        self::CODE_AUTH_FAIL => '认证失败',
        self::CODE_AUTH_ROLE_DENIED => '权限不足',
        self::CODE_NOT_FOUND => '资源不存在',
        self::CODE_SYSTEM_API_LIMIT => '请求频率过快，请稍后重试',
        self::CODE_SYSTEM_ERROR => '系统繁忙，请稍后重试',
        self::CODE_SYSTEM_BUSY => '服务不可用',
    ];

    public static function success(array $data = []): array
    {
        return [
            'code' => self::CODE_SUCCESS,
            'msg' => self::translate(self::getInfo(self::CODE_SUCCESS)),
            'data' => $data,
        ];
    }

    public static function error(?string $msg = null, array $data = [], null|int|string $code = null): array
    {
        $code = empty($code) ? self::CODE_ERROR : (int) $code;
        return [
            'code' => $code,
            'msg' => self::translate(self::getInfo($code, $msg)),
            'data' => $data,
        ];
    }

    public static function getInfo(int $code, ?string $msg = null): string
    {
        return $msg ?? (self::CODE_MAP[$code] ?? '未知错误');
    }

    protected static function translate(string $text): string
    {
        try {
            $translator = ApplicationContext::getContainer()->get(TranslatorInterface::class);
            return $translator->trans($text);
        } catch (Throwable $e) {
            return $text;
        }
    }
}
