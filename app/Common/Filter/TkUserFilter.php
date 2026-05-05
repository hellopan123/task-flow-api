<?php

declare(strict_types=1);

namespace App\Common\Filter;

class TkUserFilter extends BaseFilter
{
    protected array $scene = [
        'list' => ['page', 'limit', 'nickname', 'phone', 'email', 'status'],
        'info' => ['id'],
        'update_status' => ['id', 'status'],
        'add'  => ['salt'=>'__Salt','password' => '__NewPassword','gender','nickname','email','phone','avatar','status'],
    ];
}
