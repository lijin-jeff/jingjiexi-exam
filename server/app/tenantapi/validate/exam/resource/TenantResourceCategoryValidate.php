<?php
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 访问官网：精解析答题
// | 官方邮箱：精解析答题
// | 精解析答题系统开发者版权所有，拥有最终解释权。
declare (strict_types=1);

namespace app\tenantapi\validate\exam\resource;


use app\common\validate\BaseValidate;


/**
 * 资源分类验证器
 * Class TenantResourceCategoryValidate
 * @package app\platform\validate\resource
 */
class TenantResourceCategoryValidate extends BaseValidate
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
    ];


    /**
     * 参数描述
     * @var string[]
     */
    protected $field = [
        'id'      => 'id',
        'title'   => '分类名称',
        'is_show' => '显示状态',
        'sort'    => '显示权重',
    ];


    /**
     * @notes 添加场景
     * @return TenantResourceCategoryValidate
     * @author 精解析答题
     * @date 2025/06/16 23:36
     */
    public function sceneAdd(): TenantResourceCategoryValidate
    {
        return $this->only(['title', 'is_show', 'sort']);
    }


    /**
     * @notes 编辑场景
     * @return TenantResourceCategoryValidate
     * @author 精解析答题
     * @date 2025/06/16 23:36
     */
    public function sceneEdit(): TenantResourceCategoryValidate
    {
        return $this->only(['id', 'title', 'is_show', 'sort']);
    }


    /**
     * @notes 删除场景
     * @return TenantResourceCategoryValidate
     * @author 精解析答题
     * @date 2025/06/16 23:36
     */
    public function sceneDelete(): TenantResourceCategoryValidate
    {
        return $this->only(['id']);
    }


    /**
     * @notes 详情场景
     * @return TenantResourceCategoryValidate
     * @author 精解析答题
     * @date 2025/06/16 23:36
     */
    public function sceneDetail(): TenantResourceCategoryValidate
    {
        return $this->only(['id']);
    }

}