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

namespace App\Common\Cache\db00;

use App\Common\Cache\Abstract\AbstractStringCache;

/**
 * 验证码缓存.
 */
class CaptchaStringCache extends AbstractStringCache
{
    protected static string $prefix = 'captcha';

    protected static bool $json = false;

    protected int $expire = 300;

    protected static function key(array $parameter): string
    {
        return implode(':', $parameter);
    }

    public function getKeyPrefix(): string
    {
        return self::$prefix;
    }
}
