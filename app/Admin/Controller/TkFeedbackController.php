<?php

declare(strict_types=1);

namespace App\Admin\Controller;

use App\Admin\Service\TkFeedbackService;
use App\Admin\Validate\TkFeedbackRequest;
use App\Common\Controller\AbstractController;
use App\Common\Exception\AppException;
use App\Common\Filter\TkFeedbackFilter;
use App\Common\Utils\Result;
use Hyperf\Di\Annotation\Inject;

class TkFeedbackController extends AbstractController
{
    #[Inject]
    protected TkFeedbackService $feedbackService;

    public function getList(): array
    {
        $params = $this->request->all();
        $this->filter(TkFeedbackFilter::class, $params, 'list');
        $data = $this->feedbackService->getList($params);
        return Result::success($data);
    }

    public function getInfo(): array
    {
        $id = $this->request->input('id');
        if (empty($id)) {
            throw new AppException('反馈ID不能为空');
        }

        $data = $this->feedbackService->getInfoById((int) $id);
        if (!$data) {
            throw new AppException('反馈不存在');
        }

        return Result::success($data);
    }

    public function add(): array
    {
        $this->check(TkFeedbackRequest::class, 'add');
        $params = $this->request->all();
        $this->filter(TkFeedbackFilter::class, $params, 'add');
        $this->feedbackService->create($params);
        return Result::success();
    }

    public function reply(): array
    {
        $this->check(TkFeedbackRequest::class, 'reply');
        $params = $this->request->all();
        $this->filter(TkFeedbackFilter::class, $params, 'reply');
        $this->feedbackService->reply($params);
        return Result::success();
    }

    public function delete(): array
    {
        $this->check(TkFeedbackRequest::class, 'delete');
        $id = $this->request->input('id');
        $this->feedbackService->delete((int) $id);
        return Result::success();
    }
}
