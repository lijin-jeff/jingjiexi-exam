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
use app\common\model\exam\Help;

class HelpLists extends BaseApiDataLists
{


    public function lists(): array
    {
        $lists = Help::query()
            ->where([
                ['is_show', '=', 1]
            ])
            ->field(['id', 'image', 'title', 'create_time'])
            ->order('sort desc,id desc')
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
        return Help::query()
            ->where([
                ['is_show', '=', 1]
            ])
            ->count();
    }
}
