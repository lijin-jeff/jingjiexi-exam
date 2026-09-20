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
namespace app\api\lists\exam;

use app\api\lists\BaseApiDataLists;
use app\common\model\exam\TenantVersionUpdate;

class VersionUpdateLists extends BaseApiDataLists
{



    /**
     * 获取版本更新列表
     * @param array $params
     * @return array
     */
    public function lists(): array
    {
        $lists = TenantVersionUpdate::query()
            ->where([
                ['status', '=', 1]
            ])
            ->field(['id', 'version', 'info', 'status', 'release_time', 'create_time'])
            ->order('id desc, release_time desc')
            ->select()
            ->toArray();
        return $lists;
    }

    /**
     * 获取数据总数
     * @return int
     */
    public function count(): int
    {
        return TenantVersionUpdate::query()
            ->where([
                ['status', '=', 1]
            ])
            ->count();
    }
}
