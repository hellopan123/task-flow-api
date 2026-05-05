<?php

declare(strict_types=1);

namespace App\Admin\Controller;

use App\Admin\Service\TkUserService;
use App\Admin\Validate\TkUserRequest;
use App\Common\Controller\AbstractController;
use App\Common\Exception\AppException;
use App\Common\Filter\TkUserFilter;
use App\Common\Utils\Result;
use Hyperf\Di\Annotation\Inject;

class TkUserController extends AbstractController
{
    #[Inject]
    protected TkUserService $userService;

    public function getList(): array
    {
        $params = $this->request->all();
        $this->filter(TkUserFilter::class, $params, 'list');
        $data = $this->userService->getList($params);
        return Result::success($data);
    }

    public function getInfo(): array
    {
        $id = $this->request->input('id');
        if (empty($id)) {
            throw new AppException('用户ID不能为空');
        }

        $data = $this->userService->getInfoById((int) $id);
        if (!$data) {
            throw new AppException('用户不存在');
        }

        return Result::success($data);
    }

    /**
     * Desc: 添加用户
     * Auth: hello pan
     * Date: 2/24/26 10:00 PM
     * @return array
     * @throws AppException
     */
    public function add(): array
    {
        $this->check(TkUserRequest::class, 'add');
        $params = $this->request->all();
        $this->filter(TkUserFilter::class, $params, 'add');
        $this->userService->create($params);
        return Result::success();
    }

    public function updateStatus(): array
    {
        $this->check(TkUserRequest::class, 'update_status');
        $params = $this->request->all();
        $this->filter(TkUserFilter::class, $params, 'update_status');
        $this->userService->updateStatus($params);
        return Result::success();
    }

    public function delete(): array
    {
        $this->check(TkUserRequest::class, 'delete');
        $id = $this->request->input('id');
        $this->userService->delete((int) $id);
        return Result::success();
    }

    public function getAll(): array
    {
        $data = $this->userService->getAllUsers();
        return Result::success($data);
    }
}
