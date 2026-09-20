<?php
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 精解析答题系统开发者版权所有，拥有最终解释权。

namespace app\tenantapi\validate\exam;


use app\common\validate\BaseValidate;


/**
 * 题库管理验证器
 * Class TenantExamLibraryValidate
 * @package app\platform\validate\exam
 */
class TenantExamLibraryValidate extends BaseValidate
{

    /**
     * 设置校验规则
     * @var string[]
     */
    protected $rule = [
        'id'              => 'require',
        'title'           => 'require',
        'is_show'         => 'require',
        'sort'            => 'require|integer',
        'category_uid'    => 'require',
        'author'          => 'require',
        'free_state'      => 'require',
        'year'            => 'require',
        'recommend_state' => 'require',
        'hot_state'       => 'require'
    ];


    /**
     * 参数描述
     * @var string[]
     */
    protected $field = [
        'id'              => 'id',
        'title'           => '题库名称',
        'is_show'         => '启用状态',
        'sort'            => '显示权重',
        'category_uid'    => '题库分类',
        'author'          => '题库作者',
        'free_state'      => '付费状态',
        'year'            => '题库年份',
        'recommend_state' => '推荐状态',
        'hot_state'       => '热门状态',
    ];


    /**
     * @notes 添加场景
     * @return TenantExamLibraryValidate
     * @author 精解析答题
     * @date 2025/04/01 04:41
     */
    public function sceneAdd(): TenantExamLibraryValidate
    {
        return $this->only(['title', 'is_show', 'sort', 'category_uid', 'author', 'free_state', 'year', 'recommend_state', 'hot_state']);
    }


    /**
     * @notes 编辑场景
     * @return TenantExamLibraryValidate
     * @author 精解析答题
     * @date 2025/04/01 04:41
     */
    public function sceneEdit(): TenantExamLibraryValidate
    {
        return $this->only(['id', 'title', 'is_show', 'sort', 'category_uid', 'author', 'free_state', 'year', 'recommend_state', 'hot_state']);
    }


    /**
     * @notes 删除场景
     * @return TenantExamLibraryValidate
     * @author 精解析答题
     * @date 2025/04/01 04:41
     */
    public function sceneDelete(): TenantExamLibraryValidate
    {
        return $this->only(['id']);
    }


    /**
     * @notes 详情场景
     * @return TenantExamLibraryValidate
     * @author 精解析答题
     * @date 2025/04/01 04:41
     */
    public function sceneDetail(): TenantExamLibraryValidate
    {
        return $this->only(['id']);
    }

}