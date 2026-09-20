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

namespace app\tenantapi\validate\exam;


use app\common\validate\BaseValidate;


/**
 * comment验证器
 * Class TenantExamCommentValidate
 * @package app\tenantapi\validate\exam
 */
class TenantExamCommentValidate extends BaseValidate
{

     /**
      * 设置校验规则
      * @var string[]
      */
    protected $rule = [
        'id' => 'require',
        'user_id' => 'require',
        'qid' => 'require',
        'content' => 'require',
        'comments' => 'require',
        'likes' => 'require',
        'ip' => 'require',
        'subscribe' => 'require',
        'create_time' => 'require',
        'update_time' => 'require',
        'status' => 'require',
    ];


    /**
     * 参数描述
     * @var string[]
     */
    protected $field = [
        'id' => 'id',
        'user_id' => '用户ID',
        'qid' => '试题ID',
        'content' => '内容',
        'comments' => '评论数',
        'likes' => '点赞数',
        'ip' => 'IP地址',
        'subscribe' => '订阅',
        'create_time' => '创建时间',
        'update_time' => '更新时间',
        'status' => '状态',
    ];


    /**
     * @notes 添加场景
     * @return TenantExamCommentValidate
     * @author likeadmin
     * @date 2025/07/19 23:21
     */
    public function sceneAdd()
    {
        return $this->only(['user_id','qid','content','comments','likes','ip','subscribe','create_time','update_time','status']);
    }


    /**
     * @notes 编辑场景
     * @return TenantExamCommentValidate
     * @author likeadmin
     * @date 2025/07/19 23:21
     */
    public function sceneEdit()
    {
        return $this->only(['id','user_id','qid','content','comments','likes','ip','subscribe','create_time','update_time','status']);
    }


    /**
     * @notes 删除场景
     * @return TenantExamCommentValidate
     * @author likeadmin
     * @date 2025/07/19 23:21
     */
    public function sceneDelete()
    {
        return $this->only(['id']);
    }


    /**
     * @notes 详情场景
     * @return TenantExamCommentValidate
     * @author likeadmin
     * @date 2025/07/19 23:21
     */
    public function sceneDetail()
    {
        return $this->only(['id']);
    }

}