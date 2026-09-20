<?php
// +----------------------------------------------------------------------
// | likeadmin快速开发前后端分离管理后台（PHP版）
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 开源版本可自由商用，可去除界面版权logo
// | gitee下载：https://gitee.com/likeshop_gitee/likeadmin
// | github下载：https://github.com/likeshop-github/likeadmin
// | 访问官网：https://www.likeadmin.cn
// | likeadmin团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeadminTeam
// +----------------------------------------------------------------------

namespace app\tenantapi\logic\exam;


use app\common\model\exam\TenantUserIntegralLog;
use app\common\logic\BaseLogic;
use think\facade\Db;


/**
 * integral逻辑
 * Class TenantUserIntegralLogLogic
 * @package app\tenantapi\logic\exam
 */
class TenantUserIntegralLogLogic extends BaseLogic
{


    /**
     * @notes 添加integral
     * @param array $params
     * @return bool
     * @author 精解析题库
     * @date 2025/08/26 09:14
     */
    public static function add(array $params): bool
    {
        Db::startTrans();
        try {
            TenantUserIntegralLog::create([
                'tenant_id' => $params['tenant_id'],
                'user_id' => $params['user_id'],
                'action' => $params['action'],
                'change_amount' => $params['change_amount'],
                'remark' => $params['remark'],
                'extra' => $params['extra'],
                'title' => $params['title'],
                'change_type' => $params['change_type'],
                'action_type' => $params['action_type'],
            ]);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }


    /**
     * @notes 编辑integral
     * @param array $params
     * @return bool
     * @author 精解析题库
     * @date 2025/08/26 09:14
     */
    public static function edit(array $params): bool
    {
        Db::startTrans();
        try {
            TenantUserIntegralLog::where('id', $params['id'])->update([
                'tenant_id' => $params['tenant_id'],
                'user_id' => $params['user_id'],
                'action' => $params['action'],
                'change_amount' => $params['change_amount'],
                'remark' => $params['remark'],
                'extra' => $params['extra'],
                'title' => $params['title'],
                'change_type' => $params['change_type'],
                'action_type' => $params['action_type'],
            ]);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }


    /**
     * @notes 删除score
     * @param array $params
     * @return bool
     * @author 精解析题库
     * @date 2025/08/26 09:14
     */
    public static function delete(array $params): bool
    {
        return TenantUserIntegralLog::destroy($params['id']);
    }


    /**
     * @notes 获取integral详情
     * @param $params
     * @return array
     * @author 精解析题库
     * @date 2025/08/26 09:14
     */
    public static function detail($params): array
    {
        return TenantUserIntegralLog::findOrEmpty($params['id'])->toArray();
    }
}