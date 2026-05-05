<?php

declare(strict_types=1);

namespace App\Admin\Service;

use App\Common\Exception\AppException;
use App\Common\Model\TkNotificationModel;
use Psr\Log\LoggerInterface;

class TkNotificationService
{
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getList(array $params = []): array
    {
        $where = [];
        
        if (!empty($params['user_id'])) {
            $where[] = ['user_id', '=', (int) $params['user_id']];
        }
        
        if (isset($params['notification_type']) && $params['notification_type'] !== '') {
            $where[] = ['notification_type', '=', (int) $params['notification_type']];
        }
        
        if (isset($params['is_read']) && $params['is_read'] !== '') {
            $where[] = ['is_read', '=', (int) $params['is_read']];
        }
        
        $query = TkNotificationModel::query()->where($where);
        
        $page = $params['page'] ?? 1;
        $pageSize = $params['pageSize'] ?? 15;
        
        $total = $query->count();
        $list = $query->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();
        
        return [
            'list' => $list,
            'total' => $total,
        ];
    }

    public function getInfoById(int $id): ?array
    {
        $notification = TkNotificationModel::find($id);
        
        if (!$notification) {
            return null;
        }
        
        return $notification->toArray();
    }

    public function create(array $data): TkNotificationModel
    {
        return TkNotificationModel::create($data);
    }

    public function markAsRead(int $id): bool
    {
        $model = TkNotificationModel::find($id);
        
        if (!$model) {
            throw new AppException('通知不存在');
        }
        
        return $model->update([
            'is_read' => 1,
            'read_time' => date('Y-m-d H:i:s'),
        ]) > 0;
    }

    public function markAllAsRead(int $userId): int
    {
        return TkNotificationModel::where('user_id', $userId)
            ->where('is_read', 0)
            ->update([
                'is_read' => 1,
                'read_time' => date('Y-m-d H:i:s'),
            ]);
    }

    public function delete(int $id): bool
    {
        $model = TkNotificationModel::find($id);
        
        if (!$model) {
            throw new AppException('通知不存在');
        }
        
        return $model->delete() > 0;
    }

    public function getUnreadCount(int $userId): int
    {
        return TkNotificationModel::where('user_id', $userId)
            ->where('is_read', 0)
            ->count();
    }
}
