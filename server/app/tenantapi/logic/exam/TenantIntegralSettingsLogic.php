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


use app\common\model\exam\TenantIntegralSettings;
use app\common\logic\BaseLogic;
use think\facade\Db;


/**
 * integralSetting逻辑
 * Class TenantIntegralSettingsLogic
 * @package app\tenantapi\logic\exam
 */
class TenantIntegralSettingsLogic extends BaseLogic
{


    /**
     * @notes 添加integralSetting
     * @param array $params
     * @return bool
     * @author 精解析题库
     * @date 2025/08/26 09:13
     */
    public static function add(array $params): bool
    {
        Db::startTrans();
        try {
            TenantIntegralSettings::create([
                'tenant_id' => $params['tenant_id'],
                'integral_text_custom' => $params['integral_text_custom'],
                'user_integral_rule' => $params['user_integral_rule'],
                'daily_integral_limit' => $params['daily_integral_limit'],
                'register_integral' => $params['register_integral'],
                'login_integral' => $params['login_integral'],
                'subscribe_integral' => $params['subscribe_integral'],
                'share_integral' => $params['share_integral'],
                'invite_user_integral' => $params['invite_user_integral'],
                'download_integral' => $params['download_integral'],
                'integral_count_rule' => $params['integral_count_rule'],
            ]);

            Db::commit();
            // 插入时将积分设置存入缓存redis，供前端调用
            $integralSettings = TenantIntegralSettings::where('tenant_id', $params['tenant_id'])->findOrEmpty()->toArray();
            cache('tenant_integral_settings_' . $params['tenant_id'], $integralSettings);

            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }


    /**
     * @notes 编辑integralSetting
     * @param array $params
     * @return bool
     * @author 精解析题库
     * @date 2025/08/26 09:13
     */
    public static function edit(array $params): bool
    {
        Db::startTrans();
        try {
            TenantIntegralSettings::where('id', $params['id'])->update([
                'integral_text_custom' => $params['integral_text_custom'],
                'user_integral_rule' => $params['user_integral_rule'],
                'daily_integral_limit' => $params['daily_integral_limit'],
                'register_integral' => $params['register_integral'],
                'login_integral' => $params['login_integral'],
                'subscribe_integral' => $params['subscribe_integral'],
                'share_integral' => $params['share_integral'],
                'invite_user_integral' => $params['invite_user_integral'],
                'download_integral' => $params['download_integral'],
                'integral_count_rule' => $params['integral_count_rule'],
            ]); 

            Db::commit();
            // 更新时将积分设置存入缓存redis，供前端调用
            $integralSettings = TenantIntegralSettings::where('tenant_id', $params['tenant_id'])->findOrEmpty()->toArray();
            cache('tenant_integral_settings_' . $params['tenant_id'], $integralSettings);

            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }




    /**
     * @notes 获取integralSetting详情
     * @param $params
     * @return array
     * @author 精解析题库
     * @date 2025/08/26 09:13
     */
    public static function detail(array $params): array
    {
        return TenantIntegralSettings::where('tenant_id', $params['tenant_id'])->findOrEmpty()->toArray();
    }
}