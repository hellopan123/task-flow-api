<?php

declare(strict_types=1);

namespace App\Api\Filter;

use App\Api\Service\RequirementService;
use App\Common\Filter\BaseFilter;

class RequirementFilter extends BaseFilter
{
    protected array $scene = [
        'list' => ['page', 'limit', 'project_id', 'status', 'priority','requirement_name'],
        'create' => [
            'project_id',
            'requirement_name',
            'requirement_code',
            'description',
            'priority',
            'start_date',
            'end_date',
            'creator_id' => '__OperateId',
            'status',
            'create_time' => '__CurrentTime',
        ],
        'update' => [
            'id',
            'requirement_name',
            'description',
            'priority',
        ],
        'update_status' => [
            'id',
            'status',
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
    protected function formatRequirementCode($value = '', $data = [], $result = []):string
    {
        return  RequirementService::generateCode((int)$data['project_id']);

        $prefix = 'REQ';
        $randomLength = 6;
        $date = date('Ymd');
        $random = strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, $randomLength));
        return $prefix . $date . $random;

    }
}
