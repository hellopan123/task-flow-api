<?php

declare(strict_types=1);

namespace App\Api\Filter;

use App\Common\Filter\BaseFilter;

class UserFilter extends BaseFilter
{
    protected array $scene = [
        'update_profile' => [
            'nickname',
            'avatar',
            'email',
            'gender',
            'position',
        ],
        'update_password' => [
            'old_password',
            'new_password',
        ],
        'add' => ['phone','salt' => '__Salt','password' => 'InitPassword','nickname','avatar','email','gender','position','dept_id','status']
    ];
}
