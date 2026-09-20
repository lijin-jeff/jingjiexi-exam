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


use app\common\model\BaseModel;
use think\model\concern\SoftDelete;


/**
 * tenantRankingTotal模型
 * Class TenantExamRankingTotal
 * @package app\common\model\exam
 */
class TenantExamRankingTotal extends BaseModel
{
    use SoftDelete;
    protected $name = 'tenant_exam_ranking_total';
    protected $deleteTime = 'delete_time';

    /**
     * 关联查询，通过user_id查询用户最新的姓名和头像
     * @param $userId
     * @return \think\model\relation\BelongsTo
     * @author 精解析答题
     * @date 2025/12/20
     */
    public function user(): \think\model\relation\HasOne
    {
        return $this->hasOne(\app\common\model\user\User::class, 'id', 'user_id')
            ->field('id,nickname,avatar');
    }
}