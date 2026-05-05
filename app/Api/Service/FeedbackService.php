<?php

declare(strict_types=1);

namespace App\Api\Service;

use App\Common\Exception\AppException;
use App\Common\Model\TkFeedbackModel;
use Hyperf\Context\Context;
use Psr\Log\LoggerInterface;

class FeedbackService
{
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function create(array $params): array
    {
        $userId = Context::get('user_id');
        
        $feedback = TkFeedbackModel::create([
            'user_id' => $userId,
            'feedback_type' => $params['feedback_type'],
            'content' => $params['content'],
            'images' => $params['images'] ?? '',
            'contact' => $params['contact'] ?? '',
            'status' => 1,
        ]);
        
        return $feedback->toArray();
    }

    public function getList(array $params = []): array
    {
        $userId = Context::get('user_id');
        
        $query = TkFeedbackModel::query()
            ->where('user_id', $userId);
        
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', $params['status']);
        }
        
        if (!empty($params['feedback_type'])) {
            $query->where('feedback_type', $params['feedback_type']);
        }
        
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 15;
        
        $count = $query->count();
        $list = $query->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy('create_time', 'desc')
            ->get()
            ->toArray();
        
        return ['list' => $list, 'count' => $count];
    }

    public function getDetail(int $id): ?array
    {
        $userId = Context::get('user_id');
        
        $feedback = TkFeedbackModel::where('id', $id)
            ->where('user_id', $userId)
            ->first();
        
        if (!$feedback) {
            return null;
        }
        
        return $feedback->toArray();
    }
}
