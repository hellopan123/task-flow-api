<?php

declare(strict_types=1);

namespace App\Api\Service;

use App\Common\Exception\AppException;
use App\Common\Model\TkUserModel;
use App\Common\Model\TkUserRelationModel;
use Hyperf\Context\Context;
use Hyperf\DbConnection\Db;

class MemberService
{

    /**
     * Desc: 添加员工
     * Auth: hello pan
     * Date: 2/28/26 11:04 PM
     * @param array $params
     * @return int
     * @throws AppException
     */
    public function addUser(array $params)
    {
        $userId = Context::get('user_id');
        $user_info = TkUserModel::query()
            ->where('phone', $params['phone'])->first();

        if(!empty($user_info)){
            $relation_id = TkUserRelationModel::where('user_id',$userId)->where('related_user_id',$user_info->id ?? 0)->first();
            if(!empty($relation_id)){
                throw new AppException('手机号用户已存在');
            }
        }

        $insert_data = [
            'user_id' => $userId,
            'related_user_id' => $user_info->id ?? 0,
            'relation_type' => 1,
        ];
        Db::beginTransaction();
        try {
            if(empty($user_info)){
                $related_user_id = TkUserModel::insertGetId($params);
                if(!$related_user_id){
                    throw new AppException('创建用户失败');
                }
                $insert_data['related_user_id'] = $related_user_id;
            }
            $relation_id = TkUserRelationModel::insertGetId($insert_data);
            if(!$relation_id){
                throw new AppException('创建用户关系失败');
            }
            Db::commit();
            return $relation_id;
        }catch ( \Exception $e){
            Db::rollBack();
            throw $e;
        }
    }


    /**
     * Desc: 获取员工详情
     * Auth: hello pan
     * Date: 3/29/26 8:24 PM
     * @param $id
     * @return array
     * @throws AppException
     */
    public function getInfoById($id):array
    {
        $userId = Context::get('user_id');
        $relatedUserIds = TkUserRelationModel::where('user_id', $userId)->where('related_user_id',$id)
            ->get();

        if (empty($relatedUserIds)) {
            throw new AppException('用户不存在');
        }

        $user_info = TkUserModel::query()
            ->where('id', $id)
            ->first(['id', 'nickname', 'avatar', 'phone', 'email', 'position', 'gender','status','create_time']);
        if (empty($user_info)) {
            throw new AppException('用户不存在');
        }

        $info = $user_info->toArray();
        if (!empty($info['phone'])) {
            $info['phone'] = substr_replace($info['phone'], '****', 3, 4);
        }
        return $info;
    }


    /**
     * Desc: 获取员工列表
     * Auth: hello pan
     * Date: 3/29/26 8:25 PM
     * @param array $params
     * @return array
     */
    public function getList(array $params = []): array
    {
        $userId = Context::get('user_id');
        
        $relatedUserIds = TkUserRelationModel::where('user_id', $userId)
            ->pluck('related_user_id')
            ->toArray();
        
        if (empty($relatedUserIds)) {
            return ['list' => [], 'count' => 0];
        }
        
        $query = TkUserModel::query()
            ->whereIn('id', $relatedUserIds)
            ->where('status', 1);
        
        if (!empty($params['keyword'])) {
            $keyword = $params['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('nickname', 'like', "%{$keyword}%")
                  ->orWhere('phone', 'like', "%{$keyword}%")
                  ->orWhere('email', 'like', "%{$keyword}%");
            });
        }
        
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 20;
        
        $count = $query->count();
        $list = $query->offset(($page - 1) * $limit)
            ->limit($limit)
            ->orderBy('id', 'desc')
            ->get(['id', 'nickname', 'avatar', 'phone', 'email', 'position', 'gender','status','create_time'])
            ->toArray();
            
        // 手机号脱敏处理：保留前三后四
        foreach ($list as &$item) {
            if (!empty($item['phone'])) {
                $item['phone'] = substr_replace($item['phone'], '****', 3, 4);
            }
        }
        
        return ['list' => $list, 'count' => $count];
    }

    public function search(string $keyword = ''): array
    {
        $userId = Context::get('user_id');
        
        $relatedUserIds = TkUserRelationModel::where('user_id', $userId)
            ->pluck('related_user_id')
            ->toArray();
        
        if (empty($relatedUserIds)) {
            return [];
        }
        
        $query = TkUserModel::query()
            ->whereIn('id', $relatedUserIds)
            ->where('status', 1);
        
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('nickname', 'like', "%{$keyword}%")
                  ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }
        
        $list = $query->limit(20)
            ->orderBy('id', 'desc')
            ->get(['id', 'nickname', 'avatar', 'phone', 'position'])
            ->toArray();
        
        return $list;
    }

    /**
     * Desc: 添加员工
     * Auth: hello pan
     * Date: 4/3/26 9:54 PM
     * @param int $relatedUserId
     * @return array
     * @throws AppException
     */
    public function addMember(int $relatedUserId): array
    {
        $userId = Context::get('user_id');
        
        if ($userId === $relatedUserId) {
            throw new AppException('不能添加自己为员工');
        }
        
        $user = TkUserModel::find($relatedUserId);
        if (!$user || $user->status !== 1) {
            throw new AppException('用户不存在或已禁用');
        }
        
        $exists = TkUserRelationModel::where('user_id', $userId)
            ->where('related_user_id', $relatedUserId)
            ->exists();
        
        if ($exists) {
            throw new AppException('该员工已存在');
        }
        
        $relation = TkUserRelationModel::create([
            'user_id' => $userId,
            'related_user_id' => $relatedUserId,
            'relation_type' => 1,
        ]);
        
        return [
            'id' => $user->id,
            'nickname' => $user->nickname,
            'avatar' => $user->avatar,
            'phone' => $user->phone,
            'position' => $user->position,
        ];
    }

    public function addMemberByPhone(string $phone): array
    {
        $userId = Context::get('user_id');
        
        $user = TkUserModel::where('phone', $phone)
            ->where('status', 1)
            ->first();
        
        if (!$user) {
            throw new AppException('该手机号用户不存在');
        }
        
        return $this->addMember($user->id);
    }

    public function removeMember(int $relatedUserId): bool
    {
        $userId = Context::get('user_id');
        
        $relation = TkUserRelationModel::where('user_id', $userId)
            ->where('related_user_id', $relatedUserId)
            ->first();
        
        if (!$relation) {
            throw new AppException('员工关系不存在');
        }
        
        return $relation->delete();
    }

    public function searchUser(string $keyword = ''): array
    {
        $userId = Context::get('user_id');
        
        $existUserIds = TkUserRelationModel::where('user_id', $userId)
            ->pluck('related_user_id')
            ->toArray();
        
        $existUserIds[] = $userId;
        
        $query = TkUserModel::query()
            ->whereNotIn('id', $existUserIds)
            ->where('status', 1);
        
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('nickname', 'like', "%{$keyword}%")
                  ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }
        
        $list = $query->limit(20)
            ->orderBy('id', 'desc')
            ->get(['id', 'nickname', 'avatar', 'phone', 'position'])
            ->toArray();
        
        return $list;
    }
}
