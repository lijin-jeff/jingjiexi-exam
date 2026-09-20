<?php
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 访问官网：
// | 官方邮箱：
// | 精解析答题系统开发者版权所有，拥有最终解释权。

namespace app\tenantapi\validate\exam;


use app\common\validate\BaseValidate;


/**
 * 考试管理验证器
 * Class TenantExamExaminationValidate
 * @package app\platform\validate\exam
 */
class TenantExamExaminationValidate extends BaseValidate
{

    /**
     * 设置校验规则
     * @var string[]
     */
    protected $rule = [
        'id'               => 'require',
        'title'            => 'require',
        'score'            => 'require',
        'content'          => 'require',
        'sort'             => 'require',
        'is_show'          => 'require',
        'start_time'       => 'require',
        'end_time'         => 'require',
        'privilege'        => 'require',
        'exam_time'        => 'require',
        'exam_submit_type' => 'require',
        'login_style'      => 'require',
        'image'            => 'require',
    ];


    /**
     * 参数描述
     * @var string[]
     */
    protected $field = [
        'id'               => 'id',
        'title'            => '考试名称',
        'score'            => '及格分数',
        'content'          => '考试说明',
        'sort'             => '显示权重',
        'is_show'          => '发布状态',
        'start_time'       => '开始时间',
        'end_time'         => '结束时间',
        'privilege'        => '考试权限',
        'exam_time'        => '考试时间',
        'exam_submit_type' => '答题类型',
        'login_style'      => '登录方式',
        'image'            => '考试封面',
    ];


    /**
     * @notes 添加场景
     * @return TenantExamExaminationValidate
     * @author 精解析答题
     * @date 2025/04/13 21:43
     */
    public function sceneAdd(): TenantExamExaminationValidate
    {
        return $this->only(['title', 'score', 'content', 'sort', 'is_show', 'start_time', 'end_time', 'privilege', 'exam_time', 'exam_submit_type', 'login_style', 'image']);
    }


    /**
     * @notes 编辑场景
     * @return TenantExamExaminationValidate
     * @author 精解析答题
     * @date 2025/04/13 21:43
     */
    public function sceneEdit(): TenantExamExaminationValidate
    {
        return $this->only(['id', 'title', 'score', 'content', 'sort', 'is_show', 'start_time', 'end_time', 'privilege', 'exam_time', 'exam_submit_type', 'login_style', 'image']);
    }


    /**
     * @notes 删除场景
     * @return TenantExamExaminationValidate
     * @author 精解析答题
     * @date 2025/04/13 21:43
     */
    public function sceneDelete(): TenantExamExaminationValidate
    {
        return $this->only(['id']);
    }


    /**
     * @notes 详情场景
     * @return TenantExamExaminationValidate
     * @author 精解析答题
     * @date 2025/04/13 21:43
     */
    public function sceneDetail(): TenantExamExaminationValidate
    {
        return $this->only(['id']);
    }

}