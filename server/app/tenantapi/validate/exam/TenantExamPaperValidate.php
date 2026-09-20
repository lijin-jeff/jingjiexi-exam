<?php
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 访问官网：
// | 官方邮箱：
// | 精解析答题系统开发者版权所有，拥有最终解释权。
declare (strict_types=1);

namespace app\tenantapi\validate\exam;


use app\common\validate\BaseValidate;


/**
 * 试卷管理验证器
 * Class TenantExamPaperValidate
 * @package app\platform\validate\exam
 */
class TenantExamPaperValidate extends BaseValidate
{

    /**
     * 设置校验规则
     * @var string[]
     */
    protected $rule = [
        'id'      => 'require',
        'title'   => 'require',
        'is_show' => 'require',
        'sort'    => 'require',
        'is_rand' => 'require',
        'image'   => 'require',
        'remark'  => 'require',
    ];


    /**
     * 参数描述
     * @var string[]
     */
    protected $field = [
        'id'      => 'id',
        'title'   => '试卷名称',
        'is_show' => '启用状态',
        'sort'    => '显示权重',
        'is_rand' => '随机状态',
        'image'   => '试卷封面',
        'remark'  => '试卷描述',
    ];


    /**
     * @notes 添加场景
     * @return TenantExamPaperValidate
     * @author 精解析答题
     * @date 2025/06/10 13:57
     */
    public function sceneAdd()
    {
        return $this->only(['title', 'is_show', 'sort', 'is_rand', 'image', 'remark']);
    }


    /**
     * @notes 编辑场景
     * @return TenantExamPaperValidate
     * @author 精解析答题
     * @date 2025/06/10 13:57
     */
    public function sceneEdit()
    {
        return $this->only(['id', 'title', 'is_show', 'sort', 'is_rand', 'image', 'remark']);
    }


    /**
     * @notes 删除场景
     * @return TenantExamPaperValidate
     * @author 精解析答题
     * @date 2025/06/10 13:57
     */
    public function sceneDelete()
    {
        return $this->only(['id']);
    }


    /**
     * @notes 详情场景
     * @return TenantExamPaperValidate
     * @author 精解析答题
     * @date 2025/06/10 13:57
     */
    public function sceneDetail()
    {
        return $this->only(['id']);
    }

}