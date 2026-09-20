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


use app\common\model\exam\TenantOtherSettings;
use app\common\model\exam\TenantQuestionSettings;
use app\common\logic\BaseLogic;
use think\facade\Db;


/**
 * otherSetting逻辑
 * Class TenantOtherSettingsLogic
 * @package app\tenantapi\logic\exam
 */
class TenantOtherSettingsLogic extends BaseLogic
{


    /**
     * @notes 添加otherSetting
     * @param array $params
     * @return bool
     * @author 精解析题库
     * @date 2025/08/26 09:13
     */
    public static function add(array $params): bool
    {
        Db::startTrans();
        try {
            // 处理member_settings：如果已经是字符串，直接使用；如果是数组/对象，再编码
            $memberSettings = null;
            if (isset($params['member_settings'])) {
                if (is_string($params['member_settings'])) {
                    // 已经是字符串，直接使用
                    $memberSettings = $params['member_settings'];
                } else {
                    // 是数组/对象，需要编码
                    $memberSettings = json_encode($params['member_settings'], JSON_UNESCAPED_UNICODE);
                }
            }
            
            TenantOtherSettings::create([
                'tenant_id' => $params['tenant_id'],
                'customer_qrcode' => $params['customer_qrcode'],
                'customer_wechat' => $params['customer_wechat'],
                'copyright' => $params['copyright'],
                'customer_name' => $params['customer_name'],
                'customer_company' => $params['customer_company'],
                'customer_position' => $params['customer_position'],
                'customer_mobile' => $params['customer_mobile'],
                'template_id' => $params['template_id'],
                'adConfig' => $params['adConfig'],
                'homeRedirect' => $params['homeRedirect'],
                'member_settings' => $memberSettings,
            ]);

            // 存入缓存
            \think\facade\Cache::set('tenant_other_settings_' . request()->tenantId, $params);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }


    /**
     * @notes 编辑otherSetting
     * @param array $params
     * @return bool
     * @author 精解析题库
     * @date 2025/08/26 09:13
     */
    public static function edit(array $params): bool
    {
        Db::startTrans();
        try {
            $updateData = [
                'customer_qrcode' => $params['customer_qrcode'],
                'customer_wechat' => $params['customer_wechat'],
                'copyright' => $params['copyright'],
                'customer_name' => $params['customer_name'],
                'customer_company' => $params['customer_company'],
                'customer_position' => $params['customer_position'],    
                'customer_mobile' => $params['customer_mobile'],
                'template_id' => $params['template_id'],
                'adConfig' => $params['adConfig'],
                'homeRedirect' => $params['homeRedirect'],
            ];
            
            // 如果包含会员设置，则添加到更新数据中
            // 处理member_settings：如果已经是字符串，直接使用；如果是数组/对象，再编码
            if (isset($params['member_settings'])) {
                if (is_string($params['member_settings'])) {
                    // 已经是字符串，直接使用
                    $updateData['member_settings'] = $params['member_settings'];
                } else {
                    // 是数组/对象，需要编码
                    $updateData['member_settings'] = json_encode($params['member_settings'], JSON_UNESCAPED_UNICODE);
                }
            }
            
            TenantOtherSettings::where('id', $params['id'])->update($updateData);

            // 更新缓存
            \think\facade\Cache::set('tenant_other_settings_' . request()->tenantId, $params);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }




    /**
     * @notes 获取otherSetting详情
     * @param $params
     * @return array
     * @author 精解析题库
     * @date 2025/08/26 09:13
     */
    public static function detail(array $params): array
    {
        return TenantOtherSettings::where('tenant_id', $params['tenant_id'])->findOrEmpty()->toArray();
    }

    /**
     * @notes 添加问题设置
     * @param array $params
     * @return bool
     * @author 精解析题库
     * @date 2025/08/26 09:13
     */
    public static function addQuestionSetting(array $params): bool
    {
        Db::startTrans();
        try {
            TenantQuestionSettings::create([
                'tenant_id' => $params['tenant_id'],
                'customer_qrcode' => $params['customer_qrcode'],
                'customer_wechat' => $params['customer_wechat'],
                'copyright' => $params['copyright'],
                'customer_name' => $params['customer_name'],
                'customer_company' => $params['customer_company'],
                'customer_position' => $params['customer_position'],
                'customer_mobile' => $params['customer_mobile'],
                'template_id' => $params['template_id'],
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
     * @notes 编辑问题设置
     * @param array $params
     * @return bool
     * @author 精解析题库
     * @date 2025/08/26 09:13
     */
    public static function editQuestionSetting(array $params): bool
    {
        Db::startTrans();
        try {
            TenantQuestionSettings::where('id', $params['id'])
            ->where('tenant_id', $params['tenant_id'])
            ->update([
                'customer_qrcode' => $params['customer_qrcode'],
                'customer_wechat' => $params['customer_wechat'],
                'copyright' => $params['copyright'],
                'customer_name' => $params['customer_name'],
                'customer_company' => $params['customer_company'],
                'customer_position' => $params['customer_position'],    
                'customer_mobile' => $params['customer_mobile'],
                'template_id' => $params['template_id'],
                'adConfig' => $params['adConfig'],
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
     * @notes 获取问题设置详情
     * @param array $params
     * @return array
     * @author 精解析题库
     * @date 2025/08/26 09:13
     */
    public static function detailQuestionSetting(array $params): array
    {
        return TenantQuestionSettings::where('tenant_id', $params['tenant_id'])->findOrEmpty()->toArray();
    }
}