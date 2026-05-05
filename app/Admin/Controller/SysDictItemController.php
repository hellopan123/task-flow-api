<?php

declare(strict_types=1);

namespace App\Admin\Controller;

use App\Admin\Service\SysDictItemService;
use App\Admin\Validate\SysDictItemRequest;
use App\Common\Controller\AbstractController;
use App\Common\Exception\AppException;
use App\Common\Filter\SysDictItemFilter;
use App\Common\Utils\Result;
use Hyperf\Di\Annotation\Inject;

class SysDictItemController extends AbstractController
{
    #[Inject]
    protected SysDictItemService $dictItemService;

    public function getList(): array
    {
        $dictId = $this->request->input('dict_id');
        if (empty($dictId)) {
            throw new AppException('字典ID不能为空');
        }

        $data = $this->dictItemService->getList((int) $dictId);
        return Result::success($data);
    }

    public function add(): array
    {
        $this->check(SysDictItemRequest::class, 'add');
        $params = $this->request->all();
        $this->filter(SysDictItemFilter::class, $params, 'add');
        $this->dictItemService->create($params);
        return Result::success();
    }

    public function update(): array
    {
        $this->check(SysDictItemRequest::class, 'update');
        $params = $this->request->all();
        $this->filter(SysDictItemFilter::class, $params, 'update');
        $this->dictItemService->update($params);
        return Result::success();
    }

    public function updateStatus(): array
    {
        $this->check(SysDictItemRequest::class, 'update_status');
        $params = $this->request->all();
        $this->filter(SysDictItemFilter::class, $params, 'update_status');
        $this->dictItemService->updateStatus($params);
        return Result::success();
    }

    public function delete(): array
    {
        $this->check(SysDictItemRequest::class, 'delete');
        $id = $this->request->input('id');
        $this->dictItemService->delete((int) $id);
        return Result::success();
    }
}
