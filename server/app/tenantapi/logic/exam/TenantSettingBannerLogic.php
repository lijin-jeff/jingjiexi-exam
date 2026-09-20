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


use app\common\model\exam\TenantSettingBanner;
use app\common\logic\BaseLogic;
use think\facade\Db;


/**
 * tenantSettingBanner逻辑
 * Class TenantSettingBannerLogic
 * @package app\tenantapi\logic\exam
 */
class TenantSettingBannerLogic extends BaseLogic
{
    /**
     * @notes 添加tenantSettingBanner
     * @param array $params
     * @return bool
     * @author likeadmin
     * @date 2025/08/19 08:53
     */
    public static function add(array $params): bool
    {
        Db::startTrans();
        try {
            $client = implode(',', $params['client']);
            $url = json_encode($params['url']);
            TenantSettingBanner::create([
                'uid' => uid(),
                'tenant_id' => $params['tenant_id'],
                'title' => $params['title'],
                'is_show' => $params['is_show'],
                'sort' => $params['sort'],
                'position' => $params['position'],
                'client' => $client,
                'image' => $params['image'],
                'image_type' => $params['image_type'],
                'url' =>  $url,
                'icon' => $params['icon'],
                'close_position' => intval($params['close_position']),
                'display_mode' => intval($params['display_mode']),
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
     * @notes 编辑tenantSettingBanner
     * @param array $params
     * @return bool
     * @author likeadmin
     * @date 2025/08/19 08:53
     */
    public static function edit(array $params): bool
    {
        Db::startTrans();
        try {
            $client = implode(',', $params['client']);
            $url = json_encode($params['url']);
            TenantSettingBanner::where('id', $params['id'])->update([
                'title' => $params['title'],
                'is_show' => $params['is_show'],
                'sort' => $params['sort'],
                'position' => $params['position'],
                'client' => $client,//数组
                'image' => $params['image'],
                'image_type' => $params['image_type'],
                'url' =>  $url,//数组
                'icon' => $params['icon'],
                'close_position' => intval($params['close_position']),
                'display_mode' => intval($params['display_mode']),
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
     * @notes 删除tenantSettingBanner
     * @param array $params
     * @return bool
     * @author likeadmin
     * @date 2025/08/19 08:53
     */
    public static function delete(array $params): bool
    {
        return TenantSettingBanner::destroy($params['id']);
    }


    /**
     * @notes 获取tenantSettingBanner详情
     * @param $params
     * @return array
     * @author likeadmin
     * @date 2025/08/19 08:53
     */
    public static function detail($params): array
    {
        $data = TenantSettingBanner::findOrEmpty('tenant_id', $params['tenant_id'])->toArray();
        
        // 正确的方式：修改特定字段而不是覆盖整个数组
        if (isset($data['client'])) {
            $data['client'] = !empty($data['client']) ? explode(',', $data['client']) : [];
        }
        
        if (isset($data['url'])) {
            $data['url'] = !empty($data['url']) ? explode(',', $data['url']) : [];
        }
        return $data;
    }
}