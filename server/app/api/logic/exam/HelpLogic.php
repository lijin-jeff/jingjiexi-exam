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
use app\common\model\exam\Help;
use think\facade\Db;

class HelpLogic extends BaseLogic
{

    /**
     * 帮助中心详情
     * @param array $params
     * @return array
     * @link 
     * @email 
     * @author 精解析答题
     */
    public static function helpContent(array $params): array
    {
        $help = Help::query()->where([
            ['id', '=', $params['id']],
            ['is_show', '=', 1]
        ])->findOrEmpty();
        if ($help->isEmpty()) return [];
        Help::query()->where([
            ['id', '=', $params['id']]
        ])->update(['click_actual' => Db::raw('click_actual + 1')]);
        $help = $help->toArray();
        return $help;
    }

}
