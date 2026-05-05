<?php

declare(strict_types=1);

namespace App\Api\Filter;

use App\Common\Filter\BaseFilter;
use App\Common\Model\TkProjectModel;

class ProjectFilter extends BaseFilter
{
    protected array $scene = [
        'list' => ['page', 'limit', 'status','project_name'],
        'create' => [
            'project_name',
            'project_code',
            'description',
            'start_date',
            'member_ids',
            'end_date',
            'creator_id' => '__OperateId'
        ],
        'update' => [
            'id',
            'project_name',
            'description',
            'start_date',
            'end_date',
        ],
        'add_members' => [
            'id',
            'member_ids',
        ],
    ];

    /**
     * Desc:code
     * Auth: hello pan
     * Date: 12/18/25 7:17 PM
     * @param $value
     * @param $data
     * @param $result
     * @return string
     */
    protected function formatProjectCode($value = '', $data = [], $result = []):string
    {
        $prefix = 'P';
        $randomLength = 6;
        $date = date('Ymd');
        $random = strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, $randomLength));
        return $prefix . $date . $random;

    }

}
