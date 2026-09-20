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



/**
 * 评论点赞模型
 * Class TenantExamCommentLike
 * @package app\common\model\exam
 */
class TenantExamCommentLike extends BaseModel
{
    
    protected $name = 'tenant_exam_comment_like';
    
    //通过comment_id关联点赞的评论
    public function comment() : \think\model\relation\HasOne
    {
        return $this->hasOne(\app\common\model\exam\TenantExamComment::class, 'id', 'comment_id');
    }
    
    /**
     * @notes 通过评论qid关联题目
     * @return \think\model\relation\HasOne
     * @author likeadmin
     * @date 2025/07/19 23:21
     */
    public function question() : \think\model\relation\HasOne
    {
        return $this->hasOne(\app\common\model\exam\TenantExamQuestion::class, 'id', 'qid');
    }

    //通过评论qid关联文章
    public function article() : \think\model\relation\HasOne
    {
        return $this->hasOne(\app\common\model\article\Article::class, 'id', 'qid');
    }

    /**
     * @notes 通过评论user_id关联用户
     * @return \think\model\relation\HasOne
     * @author likeadmin
     * @date 2025/07/19 23:21
     */
    public function user(): \think\model\relation\HasOne
    {
        return $this->hasOne(\app\common\model\user\User::class, 'id', 'user_id');
    }

}