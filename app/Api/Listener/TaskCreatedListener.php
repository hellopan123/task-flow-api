<?php

declare(strict_types=1);

namespace App\Api\Listener;

use App\Api\Event\TaskCreatedEvent;
use App\Common\Model\TkNotificationModel;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Psr\Log\LoggerInterface;

#[Listener]
class TaskCreatedListener implements ListenerInterface
{
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function listen(): array
    {
        return [
            TaskCreatedEvent::class,
        ];
    }

    public function process(object $event): void
    {
        if (!$event instanceof TaskCreatedEvent) {
            return;
        }

        $notifications = [];
        $now = date('Y-m-d H:i:s');

        foreach ($event->assigneeIds as $assigneeId) {
            if ($assigneeId === $event->creatorId) {
                continue;
            }

            $notifications[] = [
                'user_id' => $assigneeId,
                'notification_type' => '1',
                'title' => '新任务分配',
                'content' => "您有新的任务：{$event->title}",
                'related_id' => $event->taskId,
                'related_type' => 'task',
                'is_read' => 2,
                'create_time' => $now,
            ];
        }

        if (!empty($notifications)) {
            TkNotificationModel::insert($notifications);
            $this->logger->info('TaskCreatedEvent: 创建通知成功', [
                'task_id' => $event->taskId,
                'notification_count' => count($notifications),
            ]);
        }
    }
}
