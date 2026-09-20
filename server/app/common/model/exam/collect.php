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

namespace app\common\model\exam;

use app\common\enum\YesNoEnum;
use app\common\model\BaseModel;
use think\model\concern\SoftDelete;

/**
 * 收藏
 * Class Collect
 * @package app\common\model\exam
 */
class Collect extends BaseModel
{
    use SoftDelete;
    protected $name = 'article_collect';
    protected $deleteTime = 'delete_time';


    /**
     * @notes 是否已收藏
     * @param $userId
     * @param $qid
     * @return bool (true=已收藏, false=未收藏)
     * @author 段誉
     * @date 2022/10/20 15:13
     */
    public static function isCollect($userId, $qid, $type)
    {
        $collect = Collect::where([
            'user_id' => $userId,
            'qid' => $qid,
            'type' => $type,
            'status' => YesNoEnum::YES
        ])->findOrEmpty();

        return !$collect->isEmpty();
    }

}