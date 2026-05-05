<?php

declare(strict_types=1);

namespace App\Api\Event;

use Hyperf\Contract\Arrayable;

class TaskCreatedEvent implements Arrayable
{
    public int $taskId;
    public int $creatorId;
    public array $assigneeIds;
    public int $groupId;
    public string $title;
    public int $timestamp;

    public function __construct(
        int $taskId,
        int $creatorId,
        array $assigneeIds,
        int $groupId,
        string $title
    ) {
        $this->taskId = $taskId;
        $this->creatorId = $creatorId;
        $this->assigneeIds = $assigneeIds;
        $this->groupId = $groupId;
        $this->title = $title;
        $this->timestamp = time();
    }

    public function toArray(): array
    {
        return [
            'task_id' => $this->taskId,
            'creator_id' => $this->creatorId,
            'assignee_ids' => $this->assigneeIds,
            'group_id' => $this->groupId,
            'title' => $this->title,
            'timestamp' => $this->timestamp,
        ];
    }
}
