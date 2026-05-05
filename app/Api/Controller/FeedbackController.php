<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Api\Filter\FeedbackFilter;
use App\Api\Service\FeedbackService;
use App\Api\Validate\FeedbackRequest;
use App\Common\Controller\AbstractController;
use App\Common\Exception\AppException;
use App\Common\Utils\Result;
use Hyperf\Di\Annotation\Inject;

class FeedbackController extends AbstractController
{
    #[Inject]
    protected FeedbackService $feedbackService;

    public function getList(): array
    {
        $params = $this->request->all();
        $this->filter(FeedbackFilter::class, $params, 'list');
        $data = $this->feedbackService->getList($params);
        return Result::success($data);
    }

    public function getDetail(): array
    {
        $id = (int) $this->request->input('id');
        if (!$id) {
            throw new AppException('反馈ID不能为空');
        }
        
        $data = $this->feedbackService->getDetail($id);
        if (!$data) {
            throw new AppException('反馈不存在');
        }
        
        return Result::success($data);
    }

    public function create(): array
    {
        $this->check(FeedbackRequest::class, 'create');
        $params = $this->request->all();
        $this->filter(FeedbackFilter::class, $params, 'create');
        
        $data = $this->feedbackService->create($params);
        return Result::success($data);
    }
}
