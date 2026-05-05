<?php

declare(strict_types=1);

namespace App\Common\Cache\db00;

use App\Common\Cache\Abstract\AbstractStringCache;

class SmsCodeStringCache extends AbstractStringCache
{
    protected static string $prefix = 'sms_code';

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
