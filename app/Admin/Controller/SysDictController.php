<?php

declare(strict_types=1);

namespace App\Admin\Controller;

use App\Admin\Service\SysDictService;
use App\Admin\Validate\SysDictRequest;
use App\Common\Controller\AbstractController;
use App\Common\Exception\AppException;
use App\Common\Filter\SysDictFilter;
use App\Common\Utils\Result;
use Hyperf\Di\Annotation\Inject;

class SysDictController extends AbstractController
{
    #[Inject]
    protected SysDictService $dictService;

    public function getList(): array
    {
        $params = $this->request->all();
        $data = $this->dictService->getList($params);
        return Result::success($data);
    }

    public function getInfo(): array
    {
        $id = $this->request->input('id');
        if (empty($id)) {
            throw new AppException('字典ID不能为空');
        }

        $data = $this->dictService->getInfoById((int) $id);
        if (!$data) {
            throw new AppException('字典不存在');
        }

        return Result::success($data);
    }

    public function getItemsByCode(): array
    {
        $dictCode = $this->request->input('dict_code');
        if (empty($dictCode)) {
            throw new AppException('字典编码不能为空');
        }

        $data = $this->dictService->getItemsByCode($dictCode);
        return Result::success($data);
    }

    public function add(): array
    {
        $this->check(SysDictRequest::class, 'add');
        $params = $this->request->all();
        $this->filter(SysDictFilter::class, $params, 'add');
        $this->dictService->create($params);
        return Result::success();
    }

    public function update(): array
    {
        $this->check(SysDictRequest::class, 'update');
        $params = $this->request->all();
        $this->filter(SysDictFilter::class, $params, 'update');
        $this->dictService->update($params);
        return Result::success();
    }

    public function updateStatus(): array
    {
        $this->check(SysDictRequest::class, 'update_status');
        $params = $this->request->all();
        $this->filter(SysDictFilter::class, $params, 'update_status');
        $this->dictService->updateStatus($params);
        return Result::success();
    }

    public function delete(): array
    {
        $id = $this->request->input('id');
        if (empty($id)) {
            throw new AppException('字典ID不能为空');
        }

        $this->dictService->delete((int) $id);
        return Result::success();
    }

    public function updateItems(): array
    {
        $this->check(SysDictRequest::class, 'update_items');
        $params = $this->request->all();
        $this->dictService->updateItems($params['dict_code'], $params['items'] ?? []);
        return Result::success();
    }
}
