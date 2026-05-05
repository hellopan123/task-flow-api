<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Api\Filter\UserFilter;
use App\Api\Service\MemberService;
use App\Api\Validate\UserRequest;
use App\Common\Controller\AbstractController;
use App\Common\Exception\AppException;
use App\Common\Utils\Result;
use Hyperf\Di\Annotation\Inject;

class MemberController extends AbstractController
{
    #[Inject]
    protected MemberService $memberService;

    public function getList(): array
    {
        $params = $this->request->all();
        $data = $this->memberService->getList($params);
        return Result::success($data);
    }

    public function getInfo(): array
    {
        $id = (int) $this->request->input('id', 0);
        $data = $this->memberService->getInfoById($id);
        return Result::success($data);
    }

    public function search(): array
    {
        $keyword = $this->request->input('keyword', '');
        $data = $this->memberService->search($keyword);
        return Result::success($data);
    }

    /**
     * Desc: 添加成员
     * Auth: hello pan
     * Date: 2/28/26 10:42 PM
     * @return array
     * @throws AppException
     */
    public function add(): array
    {
        $this->check(UserRequest::class,'add');
        $params = $this->request->all();
        $this->filter(UserFilter::class,$params,'add');
        $this->memberService->addUser($params);
        return Result::success();
    }

    public function remove(): array
    {
        $userId = (int) $this->request->input('user_id', 0);
        
        if ($userId <= 0) {
            throw new AppException('用户ID不能为空');
        }
        
        $this->memberService->removeMember($userId);
        return Result::success();
    }

    public function searchUser(): array
    {
        $keyword = $this->request->input('keyword', '');
        $data = $this->memberService->searchUser($keyword);
        return Result::success($data);
    }
}
