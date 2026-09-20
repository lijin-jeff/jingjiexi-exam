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

namespace app\common\model\resource;


use app\common\model\BaseModel;
use think\model\concern\SoftDelete;


/**
 * 资源管理模型
 * Class TenantResource
 * @package app\common\model\resource
 */
class TenantResource extends BaseModel
{
    use SoftDelete;

    protected $name = 'tenant_resource';

    protected $deleteTime = 'delete_time';

    public function category(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(TenantResourceCategory::class, 'category_uid', 'uid');
    }

    /**
     * 所属分类父级
     * @return \think\model\relation\BelongsTo
     */
    public function categoryParent()
    {
        return $this->belongsTo(TenantResourceCategory::class, 'category_parent_uid', 'uid');
    }
    
        /**
     * 所属题库分类
     * @return \think\model\relation\BelongsTo
     */
    public function examCategory()
    {
        return $this->belongsTo(\app\common\model\exam\TenantExamCategory::class, 'exam_category_uid', 'uid');
    }

}