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
use function Hyperf\Support\env;

return [
    'default' => [
        'jwt_secret' => env('JWT_SECRET', 'your-secret-key-here'),
        'jwt_expire' => 7200,
        'refresh_expire' => 86400,
        'max_devices' => 5,
    ],
    'app_type' => [
        'pc' => [
            'jwt_secret' => env('JWT_SECRET_PC', env('JWT_SECRET', 'your-secret-key-here')),
            'jwt_expire' => 7200,
            'refresh_expire' => 86400,
            'max_devices' => 5,
        ],
        'app' => [
            'jwt_secret' => env('JWT_SECRET_WEAPP', env('JWT_SECRET', 'your-secret-key-here')),
            'jwt_expire' => 7200,
            'refresh_expire' => 86400,
            'max_devices' => 3,
        ],
    ],
];
