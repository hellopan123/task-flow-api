<?php

declare(strict_types=1);

namespace App\Api\Service;

use App\Common\Exception\AppException;
use App\Common\Model\TkNotificationModel;
use Hyperf\Context\Context;
use Psr\Log\LoggerInterface;

class NotificationService
{
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Desc: 获取列表数据
     * Auth: hello pan
     * Date: 5/2/26 PM3:48
     * @param array $params
     * @return array
     * @throws AppException
     */
    public function getList(array $params = []): array
    {
        $userId = Context::get('user_id');
        if (!$userId) {
            throw new AppException('未登录');
        }
        
        $query = TkNotificationModel::query()
            ->where('user_id', $userId);
        
        if (isset($params['is_read']) && $params['is_read'] !== '') {
            $query->where('is_read', $params['is_read']);
        }
        
        if (!empty($params['notification_type'])) {
            $query->where('notification_type', $params['notification_type']);
        }
        
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 15;
        
        $count = $query->count();
        $list = $query->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy('is_read', 'desc')
            ->orderBy('create_time', 'desc')
            ->get()
            ->toArray();
        
        return ['list' => $list, 'count' => $count];
    }

    /**
     * Desc: 获取未读数量
     * Auth: hello pan
     * Date: 5/2/26 PM3:49
     * @return int
     */
    public function getUnreadCount(): int
    {
        $userId = Context::get('user_id');
        
        return TkNotificationModel::where('user_id', $userId)
            ->where('is_read', 2)
            ->count();
    }

    /**
     * Desc: 执行读操作
     * Auth: hello pan
     * Date: 5/2/26 PM3:49
     * @param int $id
     * @return bool
     * @throws AppException
     */
    public function markRead(int $id): bool
    {
        $userId = Context::get('user_id');
        
        $notification = TkNotificationModel::where('id', $id)
            ->where('user_id', $userId)
            ->first();
        
        if (!$notification) {
            throw new AppException('通知不存在');
        }
        
        return $notification->update([
            'is_read' => 1,
            'read_time' => date('Y-m-d H:i:s'),
        ]) > 0;
    }

    /**
     * Desc: 已读全部
     * Auth: hello pan
     * Date: 5/2/26 PM3:49
     * @return int
     */
    public function markAllRead(): int
    {
        $userId = Context::get('user_id');
        
        return TkNotificationModel::where('user_id', $userId)
            ->where('is_read', 2)
            ->update([
                'is_read' => 1,
                'read_time' => date('Y-m-d H:i:s'),
            ]);
    }

    /**
     * Desc: 删除操作
     * Auth: hello pan
     * Date: 5/2/26 PM3:49
     * @param int $id
     * @return bool
     * @throws AppException
     */
    public function delete(int $id): bool
    {
        $userId = Context::get('user_id');
        
        $notification = TkNotificationModel::where('id', $id)
            ->where('user_id', $userId)
            ->first();
        
        if (!$notification) {
            throw new AppException('通知不存在');
        }
        
        return $notification->delete() > 0;
    }
}
