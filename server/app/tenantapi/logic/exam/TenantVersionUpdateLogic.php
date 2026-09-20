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

use app\common\logic\BaseLogic;
use app\common\model\exam\TenantVersionUpdate;
use app\common\logic\user\MessageService;
use app\common\service\wechat\WeChatMnpService;
use Exception;
use think\facade\Db;

/**
 * 版本更新管理逻辑
 * Class TenantVersionUpdateLogic
 * @package app\tenantapi\logic\exam
 */
class TenantVersionUpdateLogic extends BaseLogic
{

    /**
     * @notes  添加版本更新
     * @param array $params
     * @author heshihu
     * @date 2022/2/22 9:57
     */
    public static function add(array $params)
    {
        try {
            // 处理发布时间
            $release_time = null;
            if ($params['status'] == 1) {
                if (!empty($params['release_time'])) {
                    // 如果提供了发布时间字符串，转换为时间戳
                    $release_time = strtotime($params['release_time']);
                    // 如果strtotime转换失败，使用当前时间
                    if ($release_time === false) {
                        $release_time = time();
                    }
                } else {
                    // 否则使用当前时间
                    $release_time = time();
                }
            }
            TenantVersionUpdate::create([
                'version'       => $params['version'],
                'tenant_id'     => $params['tenant_id'],
                'info'          => $params['info'],
                'status'        => $params['status'],
                'release_time'  => $release_time,
            ]);

            // 如果是发布状态，发送版本更新通知
            if ($params['status'] == 1 && $release_time) {
                $subscribedUsers = \think\facade\Db::table('la_user_subscribe')
                    ->where('type', 'version_update')
                    ->where('template_id', 'app_version_update')
                    ->where('subscribe_status', 1)
                    ->column('user_id');

                if (!empty($subscribedUsers)) {
                    $templateId = \think\facade\Db::table('la_tenant_exam_other_settings')
                        ->where('tenant_id', $params['tenant_id'])
                        ->value('template_id');

                    if ($templateId) {
                        $templateIds = json_decode($templateId, true);
                        if (is_array($templateIds) && isset($templateIds['app_version_update'])) {
                            $wechatTemplateId = $templateIds['app_version_update'];
                            $wechatMnpService = new \app\common\service\wechat\WeChatMnpService();
                            $wechatMnpService->sendVersionUpdateMessage(
                                $subscribedUsers,
                                $params['version'],
                                $params['info'],
                                $release_time,
                                $wechatTemplateId
                            );
                        }
                    }
                }
            }

            return true;
        } catch (Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }


    /**
     * @notes  编辑帮助
     * @param array $params
     * @return bool
     * @author heshihu
     * @date 2022/2/22 10:12
     */
    public static function edit(array $params): bool
    {
        try {
            // 获取原始版本信息，检查状态变化
            $originalVersion = TenantVersionUpdate::find($params['id']);
            if (!$originalVersion) {
                self::setError('版本更新不存在');
                return false;
            }

            // 处理发布时间
            $release_time = null;
            if ($params['status'] == 1) {
                if (!empty($params['release_time'])) {
                    // 如果提供了发布时间字符串，转换为时间戳
                    $release_time = strtotime($params['release_time']);
                    // 如果strtotime转换失败，使用当前时间
                    if ($release_time === false) {
                        $release_time = time();
                    }
                } else {
                    // 否则使用当前时间
                    $release_time = time();
                }
            }

            TenantVersionUpdate::update([
                'version'       => $params['version'],
                'info'          => $params['info'],
                'status'        => $params['status'],
                'release_time'  => $release_time,
            ], ['id' => $params['id']]);

            // 如果状态从非发布改为发布，发送版本更新通知
            if ($params['status'] == 1 && $originalVersion['status'] != 1 && $release_time) {
                $subscribedUsers = \think\facade\Db::table('la_user_subscribe')
                    ->where('type', 'version_update')
                    ->where('template_id', 'app_version_update')
                    ->where('subscribe_status', 1)
                    ->column('user_id');

                if (!empty($subscribedUsers)) {
                    $templateId = \think\facade\Db::table('la_tenant_exam_other_settings')
                        ->where('tenant_id', $originalVersion['tenant_id'])
                        ->value('template_id');

                    if ($templateId) {
                        $templateIds = json_decode($templateId, true);
                        if (is_array($templateIds) && isset($templateIds['app_version_update'])) {
                            $wechatTemplateId = $templateIds['app_version_update'];
                            $wechatMnpService = new \app\common\service\wechat\WeChatMnpService();
                            $wechatMnpService->sendVersionUpdateMessage(
                                $subscribedUsers,
                                $params['version'],
                                $params['info'],
                                $release_time,
                                $wechatTemplateId
                            );
                        }
                    }
                }
            }

            return true;
        } catch (Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }


    /**
     * @notes  删除版本更新 
     * @param array $params
     * @author heshihu
     * @date 2022/2/22 10:17
     */
    public static function delete(array $params)
    {
        TenantVersionUpdate::destroy($params['id']);
    }

    /**
     * @notes  查看版本更新详情
     * @param $params
     * @return array
     * @author heshihu
     * @date 2022/2/22 10:15
     */
    public static function detail($params): array
    {
        return TenantVersionUpdate::findOrEmpty($params['id'])->toArray();
    }

    /**
     * @notes  更改版本更新状态
     * @param array $params
     * @return false|void
     * @author heshihu
     * @date 2022/2/22 10:18
     */
    public static function updateStatus(array $params)
    {
        try {
            // 获取原始版本信息，检查状态变化
            $originalVersion = TenantVersionUpdate::find($params['id']);
            if (!$originalVersion) {
                self::setError('版本更新不存在');
                return false;
            }

            $release_time = null;
            if ($params['status'] == 1) {
                if (!empty($params['release_time'])) {
                    // 如果提供了发布时间字符串，转换为时间戳
                    $release_time = strtotime($params['release_time']);
                    // 如果strtotime转换失败，使用当前时间
                    if ($release_time === false) {
                        $release_time = time();
                    }
                } else {
                    // 否则使用当前时间
                    $release_time = time();
                }
            }

            TenantVersionUpdate::update([
                'status'        => $params['status'],
                'release_time'  => $release_time,
            ], ['id' => $params['id']]);

            // 如果状态从非发布改为发布，发送版本更新通知
            if ($params['status'] == 1 && $originalVersion['status'] != 1 && $release_time) {
                $subscribedUsers = \think\facade\Db::table('la_user_subscribe')
                    ->where('type', 'version_update')
                    ->where('template_id', 'app_version_update')
                    ->where('subscribe_status', 1)
                    ->column('user_id');

                if (!empty($subscribedUsers)) {
                    $templateId = \think\facade\Db::table('la_tenant_exam_other_settings')
                        ->where('tenant_id', $originalVersion['tenant_id'])
                        ->value('template_id');

                    if ($templateId) {
                        $templateIds = json_decode($templateId, true);
                        if (is_array($templateIds) && isset($templateIds['app_version_update'])) {
                            $wechatTemplateId = $templateIds['app_version_update'];
                            $wechatMnpService = new \app\common\service\wechat\WeChatMnpService();
                            $wechatMnpService->sendVersionUpdateMessage(
                                $subscribedUsers,
                                $originalVersion['version'],
                                $originalVersion['info'],
                                $release_time,
                                $wechatTemplateId
                            );
                        }
                    }
                }
            }
        } catch (Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 初始化租户版本更新列表
     * @param mixed $tenant_id
     * @return void
     * @throws Exception
     * @author yfdong
     * @date 2024/09/10 20:59
     */
    public static function initialization(mixed $tenant_id): void
    {
        Db::startTrans();
        try {
            $versionUpdateField = 'tenant_id,title,image,author,content,click_virtual,click_actual,is_show,sort';

            $templateVersionUpdate = TenantVersionUpdate::where('tenant_id', 0)->field($versionUpdateField)->select()->toArray();

            foreach ($templateVersionUpdate as $item) {  
                $item['tenant_id'] = $tenant_id;
                TenantVersionUpdate::create($item);
            }
            Db::commit();
        } catch (Exception) {
            Db::rollback();
            throw new Exception('版本更新初始化失败');
        }
    }
}