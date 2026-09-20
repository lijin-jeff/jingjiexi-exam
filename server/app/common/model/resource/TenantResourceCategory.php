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
use think\model\relation\HasMany;


/**
 * 资源分类模型
 * Class TenantResourceCategory
 * @package app\common\model\resource
 */
class TenantResourceCategory extends BaseModel
{
    use SoftDelete;

    protected $name = 'tenant_resource_category';

    protected $deleteTime = 'delete_time';

    public function children(): HasMany
    {
        return $this->hasMany(self::class, "parent_uid", "uid");
    }
    
    /**
     * 所属题库分类
     * @return \think\model\relation\BelongsTo
     */
    public function examCategory()
    {
        return $this->belongsTo(\app\common\model\exam\TenantExamCategory::class, 'exam_category_uid', 'uid');
    }
    
     /**
     * 获取所属题库分类名称
     * @param $value
     * @param $data
     * @return mixed|string
     */
    public function getExamCategoryTitleAttr($value, $data)
    {
        if (isset($this->examCategory) && $this->examCategory) {
            return $this->examCategory->title;
        }
        return '';
    }
}