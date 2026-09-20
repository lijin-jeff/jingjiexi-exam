<?php
// +----------------------------------------------------------------------
// | 精解析答题开发团队介绍
// +----------------------------------------------------------------------
// | 欢迎你使用本套系统，本套系统由精解析答题开发团队全力开发。
// | 如果本套系统是商业系统，请严格遵守系统使用相关协议，出现违背协议的法律行为，所有违法行为均与精解析答题无关。
// | 官网地址：
// | 官方邮箱：
// | 精解析答题团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | 作者: 精解析答题开发团队
// +----------------------------------------------------------------------
namespace app\api\logic\exam;

use app\common\logic\BaseLogic;
use app\common\model\exam\TenantVersionUpdate;
use app\common\model\user\UserSubscribe;
use think\facade\Db;

class VersionUpdateLogic extends BaseLogic
{

    /**
     * 版本更新详情
     * @param array $params
     * @return array
     * @link 
     * @email 
     * @author 精解析答题
     */
    public static function versionUpdateContent(array $params): array
    {
        $versionUpdate = TenantVersionUpdate::query()->where([
            ['id', '=', $params['id']],
            ['status', '=', 1]
        ])->findOrEmpty();
        if ($versionUpdate->isEmpty()) return [];
        $versionUpdate = $versionUpdate->toArray();
        return $versionUpdate;
    }

    /**
     * 版本更新订阅状态
     * @param array $params
     * @return array
     * @link 
     * @email 
     * @author 精解析答题
     */

    public static function versionUpdateSubscribeStatus(array $params): int
    {
        $userId = $params['user_uid'] ?? 0;
        $isFollowed =  UserSubscribe::where([
            'user_id' => $userId,
            'type' => 'version_update',
            'subscribe_status' => 1,
            'is_pushed' => 0
        ])->findOrEmpty();
        return $isFollowed->isEmpty() ? 0 : 1;
    }
}
