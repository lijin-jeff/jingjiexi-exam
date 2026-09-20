<?php
// +----------------------------------------------------------------------
// | likeadmin快速开发前后端分离管理后台（PHP版）
// +----------------------------------------------------------------------

namespace app\common\model\exam;

use app\common\model\BaseModel;

/**
 * 评论赞赏模型
 * Class TenantExamCommentReward
 * @package app\common\model\exam
 */
class TenantExamCommentReward extends BaseModel
{
    protected $name = 'tenant_exam_comment_reward';

    // 赞赏类型常量
    const TYPE_QUESTION = 1;  // 试题评论赞赏
    const TYPE_ARTICLE = 2;   // 文章评论赞赏

    /**
     * @notes 关联评论信息
     */
    public function comment()
    {
        return $this->hasOne(\app\common\model\exam\TenantExamComment::class, 'id', 'comment_id')
            ->field('id,user_id,content,qid,type');
    }

    /**
     * @notes 关联赞赏者用户信息
     */
    public function user()
    {
        return $this->hasOne(\app\common\model\user\User::class, 'id', 'user_id')
            ->field('id,nickname,avatar');
    }

    /**
     * @notes 关联被赞赏者用户信息
     */
    public function commentUser()
    {
        return $this->hasOne(\app\common\model\user\User::class, 'id', 'comment_user_id')
            ->field('id,nickname,avatar');
    }

    /**
     * @notes 关联试题信息
     */
    public function question()
    {
        return $this->hasOne(\app\common\model\exam\TenantExamQuestion::class, 'id', 'qid')
            ->field('id,title');
    }

    /**
     * @notes 关联文章信息
     */
    public function article()
    {
        return $this->hasOne(\app\common\model\article\Article::class, 'id', 'qid')
            ->field('id,title');
    }
}
