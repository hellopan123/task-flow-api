<?php

declare(strict_types=1);

namespace App\Api\Filter;

use App\Common\Filter\BaseFilter;

class AuthFilter extends BaseFilter
{
    protected array $scene = [
        'register' => [
            'phone',
            'password',
            'salt' => '__Salt',
            'nickname',
            'status' => '__TurnOn',
            'create_time' => '__CurrentTime',
        ],
        'login_password' => [
            'phone',
            'password',
        ],
        'login_sms' => [
            'phone',
            'code',
        ],
        'send_code' => [
            'phone',
            'type',
        ],
        'reset_password' => [
            'phone',
            'password',
        ],
        'update_password' => [
            'old_password',
            'new_password',
        ],
        'update_profile' => [
            'nickname',
            'avatar',
            'email',
            'gender',
            'position',
        ],
    ];
}
