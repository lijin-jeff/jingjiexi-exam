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

namespace app\tenantapi\logic\exam\countdown;

use app\common\logic\BaseLogic;
use app\common\model\exam\countdown\TenantExamCountdown;
use app\common\model\user\UserSubscribe;
use think\Exception;

/**
 * 倒计时管理逻辑
 * Class CountdownLogic
 * @package app\tenantapi\logic\exam\countdown
 */
class CountdownLogic extends BaseLogic
{
    
    /**
     * 获取倒计时详情
     * @param int $id
     * @return array
     */
    public static function detail(int $id): array
    {
        $detail = TenantExamCountdown::findOrEmpty($id)->toArray();
        if (empty($detail)) {
            throw new Exception('倒计时不存在');
        }
        
        // 计算剩余天数
        $detail['days'] = TenantExamCountdown::calculateDays($detail['target_date']);
        
        // 获取订阅数量
        $detail['follow_count'] = UserSubscribe::where(
            ['related_id' => $id, 'type' => 'countdown']
            )->count();
        
        return $detail;
    }
    
    /**
     * 添加倒计时
     * @param array $params
     * @return bool
     */
    public static function add(array $params): bool
    {
        $countdown = new TenantExamCountdown();
        $countdown->title = $params['title'];
        $countdown->target_date = $params['target_date'];
        $countdown->description = $params['description'] ?? '';
        $countdown->sort = (int)($params['sort'] ?? 0);
        $countdown->status = (int)($params['status'] ?? 1);
        $countdown->tenant_id = $params['tenant_id'] ?? '';
        
        return $countdown->save();
    }
    
    /**
     * 编辑倒计时
     * @param array $params
     * @return bool
     */
    public static function edit(array $params): bool
    {
        $id = (int)$params['id'];
        $countdown = TenantExamCountdown::findOrEmpty($id);
        
        if ($countdown->isEmpty()) {
            throw new Exception('倒计时不存在');
        }
        
        $countdown->title = $params['title'];
        $countdown->target_date = $params['target_date'];
        $countdown->description = $params['description'] ?? '';
        $countdown->sort = (int)($params['sort'] ?? 0);
        $countdown->status = (int)($params['status'] ?? 1);
        $countdown->tenant_id = $params['tenant_id'] ?? $countdown->tenant_id;
        
        return $countdown->save();
    }
    
    /**
     * 删除倒计时
     * @param int $id
     * @return bool
     */
    public static function delete(int $id): bool
    {
        $countdown = TenantExamCountdown::findOrEmpty($id);
        
        if ($countdown->isEmpty()) {
            throw new Exception('倒计时不存在');
        }
        
        // 开启事务
        TenantExamCountdown::startTrans();
        
        try {
            // 删除倒计时
            $countdown->delete();
            
            // 删除订阅记录
            UserSubscribe::where(
                ['related_id' => $id, 'type' => 'countdown']
                )->delete();
            
            // 提交事务
            TenantExamCountdown::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            TenantExamCountdown::rollback();
            throw $e;
        }
    }
    
    
    /**
     * 更新倒计时状态
     * @param array $params
     * @return bool
     */
    public static function updateStatus(array $params): bool
    {
        $id = (int)$params['id'];
        $status = (int)$params['status'];
        
        $countdown = TenantExamCountdown::findOrEmpty($id);
        
        if ($countdown->isEmpty()) {
            throw new Exception('倒计时不存在');
        }
        
        $countdown->status = $status;
        return $countdown->save();
    }
}