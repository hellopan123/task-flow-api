<?php

declare(strict_types=1);

namespace App\Admin\Controller;

use App\Admin\Service\TkFaqService;
use App\Admin\Validate\TkFaqRequest;
use App\Common\Controller\AbstractController;
use App\Common\Exception\AppException;
use App\Common\Filter\TkFaqFilter;
use App\Common\Utils\Result;
use Hyperf\Di\Annotation\Inject;

class TkFaqController extends AbstractController
{
    #[Inject]
    protected TkFaqService $faqService;

    public function getList(): array
    {
        $params = $this->request->all();
        $this->filter(TkFaqFilter::class, $params, 'list');
        $data = $this->faqService->getList($params);
        return Result::success($data);
    }

    public function getInfo(): array
    {
        $id = $this->request->input('id');
        if (empty($id)) {
            throw new AppException('FAQ ID不能为空');
        }

        $data = $this->faqService->getInfoById((int) $id);
        if (!$data) {
            throw new AppException('FAQ不存在');
        }

        return Result::success($data);
    }

    public function add(): array
    {
        $this->check(TkFaqRequest::class, 'add');
        $params = $this->request->all();
        $this->filter(TkFaqFilter::class, $params, 'add');
        $this->faqService->create($params);
        return Result::success();
    }

    public function update(): array
    {
        $this->check(TkFaqRequest::class, 'update');
        $params = $this->request->all();
        $this->filter(TkFaqFilter::class, $params, 'update');
        $this->faqService->update($params);
        return Result::success();
    }

    public function updateStatus(): array
    {
        $this->check(TkFaqRequest::class, 'update_status');
        $params = $this->request->all();
        $this->filter(TkFaqFilter::class, $params, 'update_status');
        $this->faqService->updateStatus($params);
        return Result::success();
    }

    public function delete(): array
    {
        $this->check(TkFaqRequest::class, 'delete');
        $id = $this->request->input('id');
        $this->faqService->delete((int) $id);
        return Result::success();
    }

    public function getCategories(): array
    {
        $data = $this->faqService->getCategories();
        return Result::success($data);
    }
}
