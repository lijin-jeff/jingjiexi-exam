<?php
// +----------------------------------------------------------------------
// | 精解析答题开发团队介绍
// +----------------------------------------------------------------------
// | 欢迎你使用本套系统，本套系统由精解析答题开发团队全力开发。
// | 如果本套系统是商业系统，请严格遵守系统使用相关协议，出现违背协议的法律行为，所有违法行为均与精解析答题无关。
// | 精解析答题团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | 作者: 精解析答题开发团队
// +----------------------------------------------------------------------
namespace app\common\model\exam;

use app\common\model\BaseModel;
use think\model\concern\SoftDelete;

/**
 * 题库试题收藏
 */
class TenantExamQuestionCollection extends BaseModel
{
    use SoftDelete;

    protected $name = 'tenant_exam_question_collection';

    protected $deleteTime = 'delete_time';
    
    /**
     * @notes 通过user_uid关联用户
     * @return \think\model\relation\HasOne
     */
    public function user(): \think\model\relation\HasOne
    {
        return $this->hasOne(\app\common\model\user\User::class, 'uid', 'user_uid')
            ->field('id,uid,nickname,avatar');
    }
}