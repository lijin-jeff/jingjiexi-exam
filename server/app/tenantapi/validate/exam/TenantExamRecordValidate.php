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
 * 做题记录表验证器
 * Class TenantExamRecordValidate
 * @package app\tenantapi\validate\exam
 */
class TenantExamRecordValidate extends BaseValidate 
{

     /**
      * 设置校验规则
      * @var string[]
      */
    protected $rule = [
        'id' => 'require',
        'id' => 'require',
        'uid' => 'require',
        'questions_type' => 'require',
        'user_score' => 'require',
        'paper_score' => 'require',
        'tenant_id' => 'require',
        'exam_time' => 'require',
        'exam_submit_time' => 'require',
        'correct_count' => 'require',
        'error_count' => 'require',
        'question_uid' => 'require',
        'user_uid' => 'require',
    ];


    /**
     * 参数描述
     * @var string[]
     */
    protected $field = [
        'id' => 'id',
        'id' => '主键',
        'uid' => 'uid',
        'questions_type' => '做题类型：1章节练习、2试卷考试、3每日一练、4举一反三、5错题练习、6收藏题库',
        'user_score' => '答题得分',
        'paper_score' => '做题积分',
        'tenant_id' => '租户ID',
        'exam_time' => '考试时间',
        'exam_submit_time' => '答题时间',
        'correct_count' => '正确题数',
        'error_count' => '错误题数',
        'question_uid' => '题库试题 uid',
        'user_uid' => '用户 uid',
    ];


    /**
     * @notes 添加场景
     * @return TenantExamRecordValidate
     * @author 精解析答题
     * @date 2025/07/19 14:47
     */
    public function sceneAdd()
    {
        return $this->only(['uid','questions_type','user_score','paper_score','tenant_id','exam_time','exam_submit_time','correct_count','error_count','question_uid','user_uid']);
    }


    /**
     * @notes 编辑场景
     * @return TenantExamRecordValidate 
     * @author 精解析答题
     * @date 2025/07/19 14:47
     */
    public function sceneEdit()
    {
        return $this->only(['id','id','uid','questions_type','user_score','paper_score','tenant_id','exam_time','exam_submit_time','correct_count','error_count','question_uid','user_uid']);
    }


    /**
     * @notes 删除场景
     * @return TenantExamRecordValidate
     * @author 精解析答题
     * @date 2025/07/19 14:47
     */
    public function sceneDelete()
    {
        return $this->only(['id']);
    }


    /**
     * @notes 详情场景
     * @return TenantExamRecordValidate
     * @author 精解析答题
     * @date 2025/07/19 14:47
     */
    public function sceneDetail()
    {
        return $this->only(['id']);
    }

}