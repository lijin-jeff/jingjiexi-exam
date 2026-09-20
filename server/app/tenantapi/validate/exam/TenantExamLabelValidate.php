<?php

namespace app\tenantapi\validate\exam;


use app\common\validate\BaseValidate;


/**
 * 题库标签验证器
 * Class TenantExamLabelValidate
 * @package app\platform\validate\exam
 */
class TenantExamLabelValidate extends BaseValidate
{

    /**
     * 设置校验规则
     * @var string[]
     */
    protected $rule = [
        'id'                      => 'require',
        'title'                   => 'require',
        'is_show'                 => 'require',
        'sort'                    => 'require',
    ];


    /**
     * 参数描述
     * @var string[]
     */
    protected $field = [
        'id'                      => '数据编号',
        'title'                   => '标签名称',
        'is_show'                 => '显示状态',
        'sort'                    => '显示权重',

    ];


    /**
     * @notes 添加场景
     * @return TenantExamLabelValidate
     * @author 精解析答题
     * @date 2025/04/12 15:53
     */
    public function sceneAdd(): TenantExamLabelValidate
    {
        return $this->only(['title', 'is_show', 'sort']);
    }


    /**
     * @notes 编辑场景
     * @return TenantExamLabelValidate
     * @author 精解析答题
     * @date 2025/04/12 15:53
     */
    public function sceneEdit(): TenantExamLabelValidate
    {
        return $this->only(['id', 'title', 'is_show', 'sort']);
    }


    /**
     * @notes 删除场景
     * @return TenantExamLabelValidate
     * @author 精解析答题
     * @date 2025/04/12 15:53
     */
    public function sceneDelete(): TenantExamLabelValidate
    {
        return $this->only(['id']);
    }


    /**
     * @notes 详情场景
     * @return TenantExamLabelValidate
     * @author 精解析答题
     * @date 2025/04/12 15:53
     */
    public function sceneDetail(): TenantExamLabelValidate
    {
        return $this->only(['id']);
    }

}