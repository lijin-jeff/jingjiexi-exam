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
 * 题库章节验证器
 * Class TenantExamChapterValidate
 * @package app\platform\validate\exam
 */
class TenantExamChapterValidate extends BaseValidate
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
        'library_uid' => "require",
    ];


    /**
     * 参数描述
     * @var string[]
     */
    protected $field = [
        'id'                      => '数据编号',
        'title'                   => '章节名称',
        'is_show'                 => '显示状态',
        'sort'                    => '显示权重',
        'library_uid' => '题库编号'
    ];


    /**
     * @notes 添加场景
     * @return TenantExamChapterValidate
     * @author 精解析答题
     * @date 2025/04/12 15:53
     */
    public function sceneAdd(): TenantExamChapterValidate
    {
        return $this->only(['title', 'is_show', 'sort', 'library_uid']);
    }


    /**
     * @notes 编辑场景
     * @return TenantExamChapterValidate
     * @author 精解析答题
     * @date 2025/04/12 15:53
     */
    public function sceneEdit(): TenantExamChapterValidate
    {
        return $this->only(['id', 'title', 'is_show', 'sort', 'library_uid']);
    }


    /**
     * @notes 删除场景
     * @return TenantExamChapterValidate
     * @author 精解析答题
     * @date 2025/04/12 15:53
     */
    public function sceneDelete(): TenantExamChapterValidate
    {
        return $this->only(['id']);
    }


    /**
     * @notes 详情场景
     * @return TenantExamChapterValidate
     * @author 精解析答题
     * @date 2025/04/12 15:53
     */
    public function sceneDetail(): TenantExamChapterValidate
    {
        return $this->only(['id']);
    }

}