<?php

declare(strict_types=1);

namespace App\Admin\Service;

use App\Common\Model\SysOperationLogModel;
use Psr\Log\LoggerInterface;

class SysOperationLogService
{
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getList(array $params = []): array
    {
        $query = SysOperationLogModel::query();
        
        if (!empty($params['module'])) {
            $query->where('module', 'like', '%' . $params['module'] . '%');
        }
        
        if (!empty($params['operator_name'])) {
            $query->where('operator_name', 'like', '%' . $params['operator_name'] . '%');
        }
        
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', '=', (int) $params['status']);
        }
        
        if (!empty($params['start_time'])) {
            $query->where('create_time', '>=', $params['start_time'] . ' 00:00:00');
        }
        
        if (!empty($params['end_time'])) {
            $query->where('create_time', '<=', $params['end_time'] . ' 23:59:59');
        }
        
        $page = $params['page'] ?? 1;
        $pageSize = $params['pageSize'] ?? 15;
        
        $total = $query->count();
        $list = $query->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();
        
        return ['list' => $list, 'total' => $total];
    }

    public function getInfoById(int $id): ?array
    {
        $log = SysOperationLogModel::find($id);
        
        if (!$log) {
            return null;
        }
        
        return $log->toArray();
    }

    public function delete(int $id): bool
    {
        $log = SysOperationLogModel::find($id);
        
        if (!$log) {
            return false;
        }
        
        return $log->delete() > 0;
    }

    public function clear(): int
    {
        return SysOperationLogModel::query()->delete();
    }
}
