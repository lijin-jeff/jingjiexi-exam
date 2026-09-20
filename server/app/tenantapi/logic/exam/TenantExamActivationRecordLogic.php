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


use app\common\model\exam\TenantExamActivationRecord;
use app\common\logic\BaseLogic;
use think\facade\Db;


/**
 * activationRecord逻辑
 * Class TenantExamActivationRecordLogic
 * @package app\tenantapi\logic\exam
 */
class TenantExamActivationRecordLogic extends BaseLogic
{


    /**
     * @notes 添加activationRecord
     * @param array $params
     * @return bool
     * @author 精解析题库
     * @date 2025/08/07 15:20
     */
    public static function add(array $params): bool
    {
        Db::startTrans();
        try {
            TenantExamActivationRecord::create([

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
     * @notes 编辑activationRecord
     * @param array $params
     * @return bool
     * @author 精解析题库
     * @date 2025/08/07 15:20
     */
    public static function edit(array $params): bool
    {
        Db::startTrans();
        try {
            TenantExamActivationRecord::where('id', $params['id'])->update([
                'tenant_id' => $params['tenant_id'],
                'code_id' => $params['code_id'],
                'batch_id' => $params['batch_id'],
                'user_id' => $params['user_id'],
                'user_name' => $params['user_name'],
                'activation_ip' => $params['activation_ip'],
                'activation_device' => $params['activation_device'],
                'activation_time' => $params['activation_time'],
                'expiration_time' => $params['expiration_time'],
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
     * @notes 删除activationRecord
     * @param array $params
     * @return bool
     * @author 精解析题库
     * @date 2025/08/07 15:20
     */
    public static function delete(array $params): bool
    {
        return TenantExamActivationRecord::destroy($params['id']);
    }


    /**
     * @notes 获取activationRecord详情
     * @param $params
     * @return array
     * @author 精解析题库
     * @date 2025/08/07 15:20
     */
    public static function detail($params): array
    {
        return TenantExamActivationRecord::findOrEmpty($params['id'])->toArray();
    }
}