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
 * corrections验证器
 * Class TenantExamQuestionCorrectionsValidate
 * @package app\tenantapi\validate\exam
 */
class TenantExamQuestionCorrectionsValidate extends BaseValidate
{

     /**
      * 设置校验规则
      * @var string[]
      */
    protected $rule = [
        'id' => 'require',
        'question_id' => 'require',
        'question_name' => 'require',
        'correction_reason' => 'require',
        'user_id' => 'require',
        'user_nickname' => 'require',
        'platform_feedback' => 'require',
    ];


    /**
     * 参数描述
     * @var string[]
     */
    protected $field = [
        'id' => 'id',
        'question_id' => '题目ID',
        'question_name' => '题目名称',
        'correction_reason' => '纠错原因',
        'user_id' => '用户ID',
        'user_nickname' => '用户昵称',
        'platform_feedback' => '平台反馈',
    ];


    /**
     * @notes 添加场景
     * @return TenantExamQuestionCorrectionsValidate
     * @author 精解析题库
     * @date 2025/08/05 23:16
     */
    public function sceneAdd()
    {
        return $this->only(['question_id','question_name','correction_reason','user_id','user_nickname']);
    }


    /**
     * @notes 编辑场景
     * @return TenantExamQuestionCorrectionsValidate
     * @author 精解析题库
     * @date 2025/08/05 23:16
     */
    public function sceneEdit()
    {
        return $this->only(['id','platform_feedback']);
    }


    /**
     * @notes 删除场景
     * @return TenantExamQuestionCorrectionsValidate
     * @author 精解析题库
     * @date 2025/08/05 23:16
     */
    public function sceneDelete()
    {
        return $this->only(['id']);
    }


    /**
     * @notes 详情场景
     * @return TenantExamQuestionCorrectionsValidate
     * @author 精解析题库
     * @date 2025/08/05 23:16
     */
    public function sceneDetail()
    {
        return $this->only(['id']);
    }

}