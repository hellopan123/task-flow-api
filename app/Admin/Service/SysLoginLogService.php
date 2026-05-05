<?php

declare(strict_types=1);

namespace App\Admin\Service;

use App\Common\Model\SysLoginLogModel;
use Psr\Log\LoggerInterface;

class SysLoginLogService
{
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getList(array $params = []): array
    {
        $where = quickFilterWhereKey($params,['username|like','login_status','login_time-start_time-end_time|date_between']);
        $query = SysLoginLogModel::query();
        $query->where($where);

        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 15;
        
        $total = $query->count();
        $list = $query->forPage($page,$limit)
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();
        
        return ['list' => $list, 'count' => $total];
    }

    public function getInfoById(int $id): ?array
    {
        $log = SysLoginLogModel::find($id);
        
        if (!$log) {
            return null;
        }
        
        return $log->toArray();
    }

    public function delete(int $id): bool
    {
        $log = SysLoginLogModel::find($id);
        
        if (!$log) {
            return false;
        }
        
        return $log->delete() > 0;
    }

    public function clear(): int
    {
        return SysLoginLogModel::query()->delete();
    }

    /**
     * Desc: 添加登录记录
     * Auth: hello pan
     * Date: 2/23/26 9:10 PM
     * @param array $data
     * @return int
     */
    public function create(array $data)
    {
        return SysLoginLogModel::insertGetId($data);
    }
}
