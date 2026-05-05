<?php

declare(strict_types=1);

namespace App\Api\Event;

use Hyperf\Contract\Arrayable;

class TaskStatusChangedEvent implements Arrayable
{
    public int $taskId;
    public int $operatorId;
    public int $oldStatus;
    public int $newStatus;
    public array $assigneeIds;
    public string $title;
    public int $timestamp;

    public function __construct(
        int $taskId,
        int $operatorId,
        int $oldStatus,
        int $newStatus,
        array $assigneeIds,
        string $title
    ) {
        $this->taskId = $taskId;
        $this->operatorId = $operatorId;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->assigneeIds = $assigneeIds;
        $this->title = $title;
        $this->timestamp = time();
    }

    public function toArray(): array
    {
        return [
            'task_id' => $this->taskId,
            'operator_id' => $this->operatorId,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'assignee_ids' => $this->assigneeIds,
            'title' => $this->title,
            'timestamp' => $this->timestamp,
        ];
    }
}
