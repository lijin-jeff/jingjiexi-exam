<?php
// +----------------------------------------------------------------------
// | likeadmin快速开发前后端分离管理后台（PHP版）
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// +----------------------------------------------------------------------
// | 开源版本可自由商用，可去除界面版权logo
// +----------------------------------------------------------------------
// | gitee下载：https://gitee.com/likeshop_gitee/likeadmin
// | github下载：https://github.com/likeshop-github/likeadmin
// | 访问官网：https://www.likeadmin.cn
// | likeadmin团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeadminTeam
// +----------------------------------------------------------------------

namespace app\api\logic\exam\countdown;

use app\common\logic\BaseLogic;
use app\common\model\exam\countdown\TenantExamCountdown;
use app\common\model\user\UserSubscribe;

/**
 * 倒计时逻辑层
 * Class CountdownLogic
 * @package app\api\logic\exam\countdown    
 */
class CountdownLogic extends BaseLogic
{
    /**
     * 获取倒计时列表
     * @param array $params
     * @return array
     */
    public static function lists(array $params): array
    {
        try {
            $page = (int)($params['page_no'] ?? 1);
            $limit = (int)($params['page_size'] ?? 10);
            $userId = (int)($params['user_id'] ?? 0);
            
            $where = [];
            
            // 搜索条件
            if (!empty($params['keyword'])) {
                $where[] = ['title', 'like', '%' . $params['keyword'] . '%'];
            }
            
            // 状态筛选,status=1为进行中,status=0为已结束
            if (isset($params['status']) && $params['status'] == 1) {
                $where[] = ['target_date', '>', time()];
            } else if (isset($params['status']) && $params['status'] == 0) {
                $where[] = ['target_date', '<', time()];
            }
            
            // 排序规则
            $order = [];
            $isFollowCountSort = false;
            $query = TenantExamCountdown::where($where);
            
            switch ($params['order_by'] ?? '') {
                case 'follow_count':
                    // 按订阅人数降序排序，在PHP代码中处理
                    $isFollowCountSort = true;
                    break;
                case 'target_date':
                    // 按目标日期降序排序
                    $order = ['target_date' => 'desc'];
                    break;
                case 'days':
                    // 计算剩余天数,并按剩余天数升序排序
                    $order = ['target_date' => 'asc'];
                    break;
                case 'create_time':
                    $order = ['create_time' => 'desc'];
                    break;
                default:
                    $order = ['sort' => 'desc', 'create_time' => 'desc'];
                    break;
            }
            
            // 获取总记录数
            $count = $query->count();
            
            // 获取列表数据
            $list = $query->order($order)
                ->page($page, $limit)
                ->select()
                ->toArray();
            
            // 如果是按订阅人数排序，在PHP代码中进行排序
            if ($isFollowCountSort) {
                // 先获取所有倒计时的ID
                $countdownIds = array_column($list, 'id');
                
                // 批量获取订阅人数，避免多次数据库查询
                $followCounts = [];
                if (!empty($countdownIds)) {
                    // 查询所有订阅记录，按related_id分组统计
                    $followRecords = UserSubscribe::whereIn('related_id', $countdownIds)
                        ->where('type', '=', 'countdown')
                        ->group('related_id')
                        ->field('related_id, count(*) as follow_count')
                        ->select()
                        ->toArray();
                    
                    // 转换为countdown_id => follow_count的关联数组
                    foreach ($followRecords as $record) {
                        $followCounts[$record['related_id']] = $record['follow_count'];
                    }
                }
                
                // 为每个倒计时添加订阅人数
                foreach ($list as &$item) {
                    $item['follow_count'] = $followCounts[$item['id']] ?? 0;
                }
                
                // 按订阅人数降序排序
                usort($list, function($a, $b) {
                    return $b['follow_count'] <=> $a['follow_count'];
                });
            }
            
            $result = [
                'count' => $count,
                'list' => $list
            ];
            
            // 计算实时倒计时并添加订阅信息
            $now = time();
            foreach ($result['list'] as &$item) {
                // 检查target_date是否存在且不为空
                if (!isset($item['target_date']) || empty($item['target_date'])) {
                    $item['days'] = 0;
                    $item['status_text'] = '已结束';
                } else {
                    $targetTimestamp = strtotime($item['target_date']);
                    $days = ceil(($targetTimestamp - $now) / 86400);
                    $item['days'] = max(0, $days);
                    $item['status_text'] = $days > 0 ? '进行中' : '已结束';
                }
                
                // 添加订阅状态
                $item['is_followed'] = $userId > 0 ? (UserSubscribe::where([
                    'user_id' => $userId,
                    'type' => 'countdown',
                    'related_id' => $item['id'],
                    'subscribe_status' => 1,
                    'is_pushed' => 0
                ])->find() ? 1 : 0) : 0;
                // 添加订阅人数
                $item['follow_count'] = UserSubscribe::where([
                    'type' => 'countdown',
                    'related_id' => $item['id']
                ])->count();
            }
            
            return ['success' => true, 'msg' => '获取倒计时列表成功', 'data' => $result];
        } catch (\Exception $e) {
            return ['success' => false, 'msg' => $e->getMessage(), 'error' => $e->getCode(), 'data' => []];
        }
    }
    
    /**
     * 获取倒计时详情
     * @param array $params
     * @return array
     */
    public static function detail(array $params): array
    {
        try {
            $categoryUid = isset($params['category_uid']) ? $params['category_uid'] : '';
            $id = isset($params['id']) ? (int)$params['id'] : 0;
            $userId = (int)($params['user_id'] ?? 0);
            $where = [];
            
            if($id > 0){
                $where[] = ['id', '=', $id];
            } else if($categoryUid){
                $where[] = ['category_uid', '=', $categoryUid];
            } else {
                return ['success' => false, 'msg' => '倒计时ID或分类UID不能为空', 'error' => 400];
            }
            
            $model = TenantExamCountdown::where($where)->where('status', '=', 1)->findOrEmpty();
            
            if ($model->isEmpty()) {
                return ['success' => false, 'msg' => '倒计时不存在', 'error' => 404];
            }
            
            $detail = $model->toArray();
            
            // 计算实时倒计时信息
            $now = time();
            $targetTimestamp = (strtotime($detail['target_date']) ?: $now);
            $days = ceil(($targetTimestamp - $now) / 86400);
            
            // 详细倒计时信息
            $detail['days'] = max(0, (int)$days);
            $detail['hours'] = floor((($targetTimestamp - $now) % 86400) / 3600);
            $detail['minutes'] = floor((($targetTimestamp - $now) % 3600) / 60);
            $detail['seconds'] = floor(($targetTimestamp - $now) % 60);
            $detail['is_end'] = $days <= 0 ? 1 : 0;
            $detail['status_text'] = $days > 0 ? '进行中' : '已结束';
            $detail['target_timestamp'] = $targetTimestamp;
            $detail['current_timestamp'] = $now;
            
            // 获取订阅数量
            $detail['follow_count'] = UserSubscribe::where([
                'type' => 'countdown',
                'related_id' => $detail['id']
            ])->count();
            
            // 检查当前用户是否已订阅
            $detail['is_followed'] = $userId > 0 ? (UserSubscribe::where([
                'user_id' => $userId,
                'type' => 'countdown',
                'related_id' => $detail['id']
            ])->find() ? 1 : 0) : 0;
            
            return ['success' => true, 'data' => $detail, 'msg' => '获取倒计时详情成功'];
        } catch (\Exception $e) {
            return ['success' => false, 'msg' => $e->getMessage(), 'error' => $e->getCode(), 'data' => []];
        }
    }
    
    /**
     * 创建倒计时
     * @param array $params
     * @return array
     */
    public static function add(array $params): array
    {
        try {
            // 参数验证
            if (empty($params['title'])) {
                return ['success' => false, 'msg' => '请输入倒计时标题', 'error' => 400, 'data' => []];
            }
            
            if (empty($params['target_date'])) {
                return ['success' => false, 'msg' => '请选择目标日期', 'error' => 400, 'data' => []];
            }
            
            // 验证目标日期是否为有效日期
            if (!strtotime($params['target_date'])) {
                return ['success' => false, 'msg' => '无效的目标日期格式', 'error' => 400, 'data' => []];
            }
            
            // 验证目标日期是否大于当前日期
            if (strtotime($params['target_date']) <= time()) {
                return ['success' => false, 'msg' => '目标日期必须大于当前日期', 'error' => 400, 'data' => []];
            }
            
            // 验证标题长度
            if (mb_strlen($params['title']) > 50) {
                return ['success' => false, 'msg' => '标题长度不能超过50个字符', 'error' => 400, 'data' => []];
            }
            
            // 验证描述长度
            if (isset($params['description']) && mb_strlen($params['description']) > 500) {
                return ['success' => false, 'msg' => '描述长度不能超过500个字符', 'error' => 400, 'data' => []];
            }
            
            // 创建倒计时
            $TenantExamCountdown = new TenantExamCountdown();
            $TenantExamCountdown->tenant_id = $params['tenant_id'];

            $TenantExamCountdown->title = $params['title'];
            $TenantExamCountdown->target_date = $params['target_date'];
            $TenantExamCountdown->description = $params['description'] ?? '';
            $TenantExamCountdown->user_id = $params['user_id'];
            $TenantExamCountdown->sort = (int)($params['sort'] ?? 0);
            $TenantExamCountdown->status = (int)($params['status'] ?? 1);
            $TenantExamCountdown->create_time = time();
            
            if ($TenantExamCountdown->save()) {
                return ['success' => true, 'data' => $TenantExamCountdown->toArray(), 'msg' => '创建倒计时成功'];
            }
            
            return ['success' => false, 'msg' => '创建倒计时失败', 'error' => 500, 'data' => []];
        } catch (\Exception $e) {
            return ['success' => false, 'msg' => $e->getMessage(), 'error' => $e->getCode(), 'data' => []];
        }
    }
    
    /**
     * 更新倒计时
     * @param array $params
     * @return array
     */
    public static function update(array $params): array
    {
        try {
            $id = (int)($params['id'] ?? 0);
            
            if ($id <= 0) {
                return ['success' => false, 'msg' => '倒计时ID不能为空', 'error' => 400, 'data' => []];
            }
            
            // 查找倒计时
            $TenantExamCountdown = TenantExamCountdown::findOrEmpty($id);
            
            if ($TenantExamCountdown->isEmpty()) {
                return ['success' => false, 'msg' => '倒计时不存在', 'error' => 404, 'data' => []];
            }
            
            // 只有创建者可以更新
            if ($TenantExamCountdown->user_id != $params['user_id']) {
                return ['success' => false, 'msg' => '无权限修改该倒计时', 'error' => 403, 'data' => []];
            }
            
            // 参数验证
            if (isset($params['title'])) {
                if (empty($params['title'])) {
                    return ['success' => false, 'msg' => '请输入倒计时标题', 'error' => 400];
                }
                if (mb_strlen($params['title']) > 50) {
                    return ['success' => false, 'msg' => '标题长度不能超过50个字符', 'error' => 400];
                }
                $TenantExamCountdown->title = $params['title'];
            }
            
            if (isset($params['target_date'])) {
                // 验证目标日期是否为有效日期
                if (!strtotime($params['target_date'])) {
                    return ['success' => false, 'msg' => '无效的目标日期格式', 'error' => 400];
                }
                $TenantExamCountdown->target_date = $params['target_date'];
            }
            
            if (isset($params['description'])) {
                if (mb_strlen($params['description']) > 500) {
                    return ['success' => false, 'msg' => '描述长度不能超过500个字符', 'error' => 400];
                }
                $TenantExamCountdown->description = $params['description'];
            }
            
            if (isset($params['sort'])) {
                $TenantExamCountdown->sort = (int)$params['sort'];
            }
            
            if (isset($params['status'])) {
                $TenantExamCountdown->status = (int)$params['status'];
            }
            
            $TenantExamCountdown->update_time = time();
            
            if ($TenantExamCountdown->save()) {
                return ['success' => true, 'data' => $TenantExamCountdown->toArray(), 'msg' => '更新倒计时成功'];
            }
            
            return ['success' => false, 'msg' => '更新倒计时失败', 'error' => 500, 'data' => []];
        } catch (\Exception $e) {
            return ['success' => false, 'msg' => $e->getMessage(), 'error' => $e->getCode(), 'data' => []];
        }
    }
    
    /**
     * 删除倒计时
     * @param array $params
     * @return array
     */
    public static function delete(array $params): array
    {
        try {
            $id = (int)($params['id'] ?? 0);
            $userId = (int)($params['user_id'] ?? 0);

            if ($id <= 0) {
                return ['success' => false, 'msg' => '倒计时ID不能为空', 'error' => 400];
            }
            
            // 查找倒计时
            $TenantExamCountdown = TenantExamCountdown::findOrEmpty($id);
            
            if ($TenantExamCountdown->isEmpty()) {
                return ['success' => false, 'msg' => '倒计时不存在', 'error' => 404, 'data' => []];
            }
            
            // 只有创建者可以删除
            if ($TenantExamCountdown->user_id != $userId) {
                return ['success' => false, 'msg' => '无权限删除该倒计时', 'error' => 403, 'data' => []];
            }
            
            $success = TenantExamCountdown::transaction(function () use ($TenantExamCountdown, $id) {
                // 删除倒计时
                if ($TenantExamCountdown->delete()) {
                    // 同时删除订阅记录
                    UserSubscribe::where([
                        'type' => 'countdown',
                        'related_id' => $id
                    ])->delete();
                    return true;
                }
                return false;
            });
            
            if ($success) {
                return ['success' => true, 'msg' => '删除倒计时成功', 'data' => []];
            }
            
            return ['success' => false, 'msg' => '删除倒计时失败', 'error' => 500, 'data' => []];
        } catch (\Exception $e) {
            return ['success' => false, 'msg' => $e->getMessage(), 'error' => $e->getCode()];
        }
    }
    
    /**
     * 订阅/取消订阅倒计时
     * @param array $params
     * @return array
     */
    public static function follow(array $params): array
    {
        try {
            $id = (int)($params['id'] ?? 0);
            $userId = (int)($params['user_id'] ?? 0);
            $openid = $params['openid'] ?? '';
            
            // 检查用户是否已登录
            if ($userId <= 0) {
                return ['success' => false, 'msg' => '请先登录', 'error' => 401, 'data' => []];
            }
            
            if ($id <= 0) {
                return ['success' => false, 'msg' => '倒计时ID不能为空', 'error' => 400, 'data' => []];
            }
            
            // 检查倒计时是否存在
            $countdown = TenantExamCountdown::findOrEmpty($id);
            
            if ($countdown->isEmpty()) {
                return ['success' => false, 'msg' => '倒计时不存在', 'error' => 404, 'data' => []];
            }
            
            // 检查是否已订阅
            $isFollowed = UserSubscribe::where([
                'user_id' => $userId,
                'type' => 'countdown',
                'related_id' => $id
            ])->find() ? true : false;
            
            if ($isFollowed) {
                // 取消订阅
                $result = UserSubscribe::where([
                    'user_id' => $userId,
                    'type' => 'countdown',
                    'related_id' => $id
                ])->delete() > 0;
                if ($result) {
                    return ['success' => true, 'data' => [
                        'is_followed' => 0, 
                        'action' => '取消订阅',
                        'follow_count' => UserSubscribe::where([
                            'type' => 'countdown',
                            'related_id' => $id
                        ])->count()
                    ], 'msg' => '取消订阅成功'];
                }
            } else {
                // 订阅 - 使用事务确保原子操作
                $subscribe = new UserSubscribe();   
                $subscribe->user_id = $userId;
                $subscribe->openid = $openid;
                $subscribe->type = 'countdown';
                $subscribe->related_id = $id;
                $subscribe->template_id = '';
                $subscribe->subscribe_status = 1;
                $subscribe->subscribe_time = time(); // 秒时间戳
                $subscribe->is_pushed = 0;
                $subscribe->tenant_id = request()->tenantId; // 添加租户ID
                try {
                    $result = $subscribe->save();
                    if ($result) {
                        return ['success' => true, 'data' => [
                            'is_followed' => 1, 
                            'action' => '订阅',
                            'follow_count' => UserSubscribe::where([
                                'type' => 'countdown',
                                'related_id' => $id
                            ])->count()
                        ], 'msg' => '订阅成功'];
                    }
                } catch (\think\db\exception\DataNotFoundException $e) {
                    // 数据未找到异常，直接返回操作失败
                    return ['success' => false, 'msg' => '操作失败', 'error' => 500, 'data' => []];
                } catch (\think\db\exception\ModelNotFoundException $e) {
                    // 模型未找到异常，直接返回操作失败
                    return ['success' => false, 'msg' => '操作失败', 'error' => 500, 'data' => []];
                } catch (\Exception $e) {
                    // 捕获唯一约束冲突异常，返回已订阅状态
                    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                        return ['success' => true, 'data' => [
                            'is_followed' => 1, 
                            'action' => '订阅',
                            'follow_count' => UserSubscribe::where([
                                'type' => 'countdown',
                                'related_id' => $id
                            ])->count()
                        ], 'msg' => '订阅成功'];
                    }
                    // 其他异常，返回操作失败
                    return ['success' => false, 'msg' => '操作失败', 'error' => 500, 'data' => []];
                }
            }
            return ['success' => false, 'msg' => '操作失败', 'error' => 500, 'data' => []];
        } catch (\Exception $e) {
            return ['success' => false, 'msg' => $e->getMessage(), 'error' => $e->getCode(), 'data' => []];
        }
    }
    
    /**
     * 获取用户订阅的倒计时列表
     * @param array $params
     * @return array
     */
    public static function followedList(array $params): array
    {
        try {
            $page = (int)($params['page_no'] ?? 1);
            $limit = (int)($params['page_size'] ?? 10);
            $userId = (int)($params['user_id'] ?? 0);
            
            if ($userId <= 0) {
                return ['success' => false, 'data' => [], 'msg' => '获取订阅的倒计时列表失败'];
            }
            
            // 获取用户订阅的倒计时ID列表
            $followedIds = UserSubscribe::where([
                'user_id' => $userId,
                'type' => 'countdown'
            ])->column('related_id');
            
            $count = count($followedIds);
            $list = [];
            
            if (!empty($followedIds)) {
                // 根据订阅的倒计时ID获取倒计时详情
                $list = TenantExamCountdown::where('id', 'in', $followedIds)
                    ->order('create_time desc')
                    ->page($page, $limit)
                    ->select()
                    ->toArray();
            }

            $result = ['count' => $count,'list' => $list];
            
            // 计算实时倒计时
            $now = time();
            foreach ($result['list'] as &$item) {
                // 检查target_date是否存在且不为空
                if (!isset($item['target_date']) || empty($item['target_date'])) {
                    $item['days'] = 0;
                    $item['status_text'] = '已结束';
                } else {
                    $targetTimestamp = strtotime($item['target_date']);
                    $days = ceil(($targetTimestamp - $now) / 86400);
                    $item['days'] = max(0, $days);
                    $item['status_text'] = $days > 0 ? '进行中' : '已结束';
                }
                $item['is_followed'] = 1; // 已订阅
                // 获取订阅数量
                $item['follow_count'] = UserSubscribe::where([
                    'type' => 'countdown',
                    'related_id' => $item['id']
                ])->count();
            }
            
            return ['success' => true, 'data' => $result, 'msg' => '获取订阅的倒计时列表成功'];
        } catch (\Exception $e) {
            return ['success' => false, 'msg' => $e->getMessage(), 'error' => $e->getCode()];
        }
    }
    
    /**
     * 获取用户创建的倒计时列表
     * @param array $params
     * @return array
     */
    public static function createdList(array $params): array
    {
        try {
            $page = (int)($params['page_no'] ?? 1);
            $limit = (int)($params['page_size'] ?? 10);
            $userId = (int)($params['user_id'] ?? 0);
            
            if ($userId <= 0) {
                return ['success' => false, 'msg' => '请先登录', 'error' => 401];
            }
            
            $where = [
                ['user_id', '=', $userId]
            ];
            
            $list = TenantExamCountdown::where($where)
            ->order('create_time desc')
            ->page($page, $limit)
            ->select()
            ->toArray();
            
            $count = TenantExamCountdown::where($where)
            ->count();
            
             $result = ['count' => $count,'list' => $list];
            // 计算实时倒计时
            $now = time();
            foreach ($result['list'] as &$item) {
                // 检查target_date是否存在且不为空
                if (!isset($item['target_date']) || empty($item['target_date'])) {
                    $item['days'] = 0;
                    $item['status_text'] = '已结束';
                } else {
                    $targetTimestamp = strtotime($item['target_date']);
                    $days = ceil(($targetTimestamp - $now) / 86400);
                    $item['days'] = max(0, $days);
                    $item['status_text'] = $days > 0 ? '进行中' : '已结束';
                }
                // 获取订阅数量
                $item['follow_count'] = UserSubscribe::where([
                    'type' => 'countdown',
                    'related_id' => $item['id']
                ])->count();
            }
            
            return ['success' => true, 'data' => $result, 'msg' => '获取创建的倒计时列表成功'];
        } catch (\Exception $e) {
            return ['success' => false, 'msg' => $e->getMessage(), 'error' => $e->getCode()];
        }
    }
}