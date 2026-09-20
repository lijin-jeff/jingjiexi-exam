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

namespace app\tenantapi\logic\exam\resource;


use app\common\model\resource\TenantResourceCategory;
use app\common\logic\BaseLogic;
use think\facade\Db;


/**
 * 资源分类逻辑
 * Class TenantResourceCategoryLogic
 * @package app\platform\logic\resource
 */
class TenantResourceCategoryLogic extends BaseLogic
{
    /**
     * 资源分类
     * @param array $params
     * @return array
     */
    public static function parentList(array $params): array
    {
        $where = [];
        if (!empty($params['exam_category_uid'])) {
            $where[] = ['exam_category_uid', '=', $params['exam_category_uid']];
        }
        return TenantResourceCategory::where($where)
        ->where([
            ['tenant_id', '=', $params['tenant_id']],
            ["parent_uid", "=", '']
        ])->column(["uid", "title"]);
    }

    /**
     * @notes 添加资源分类
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/06/16 23:36
     */
    public static function add(array $params): bool
    {
        try {
            TenantResourceCategory::create([
                'uid'        => uid(),
                'exam_category_uid' => $params['exam_category_uid'],
                'title'      => $params['title'],
                'is_show'    => $params['is_show'],
                'sort'       => $params['sort'],
                'parent_uid' => empty($params['parent_uid']) ? '' : $params['parent_uid'],
                'image'      => $params['image'],
                'tenant_id'  => $params['tenant_id'],
            ]);
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }


    /**
     * @notes 编辑资源分类
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/06/16 23:36
     */
    public static function edit(array $params): bool
    {
        try {
            TenantResourceCategory::where([
                ['id', '=', $params['id']],
                ['tenant_id', '=', $params['tenant_id']]
            ])->update([
                'exam_category_uid' => $params['exam_category_uid'],
                'title'      => $params['title'],
                'is_show'    => $params['is_show'],
                'sort'       => $params['sort'],
                'parent_uid' => empty($params['parent_uid']) ? '' : $params['parent_uid'],
                'image'      => $params['image']
            ]);
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }


    /**
     * @notes 删除资源分类
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/06/16 23:36
     */
    public static function delete(array $params): bool
    {
        return TenantResourceCategory::destroy($params['id']);
    }


    /**
     * @notes 获取资源分类详情
     * @param $params
     * @return array
     * @author 精解析答题
     * @date 2025/06/16 23:36
     */
    public static function detail($params): array
    {
        return TenantResourceCategory::query()->where([
            ['id', '=', $params['id']],
            ['tenant_id', '=', $params['tenant_id']]
        ])->findOrEmpty();
    }

    /**
     * 分类树
     * @param array $params
     * @return array
     * @link 精解析答题
     * @date 2024/4/21 00:45
     * @author 精解析答题
     */
    public static function categoryTree(array $params): array
    {
        $items = TenantResourceCategory::where([
            ['tenant_id', '=', $params['tenant_id']],
            ["parent_uid", "=", ""]
        ])->with(["children" => function ($query) {
            $query->field(["uid", "parent_uid", "title", "exam_category_uid"]); // 添加exam_category_uid字段
        }])->field(["uid", "parent_uid", "title", "exam_category_uid"])->select()->toArray();
        
        // 只移除子分类的parent_uid字段，保留uid字段
        foreach ($items as $key => $value) {
            foreach ($value["children"] as $k => $v) {
                unset($items[$key]["children"][$k]["parent_uid"]); // 只移除不必要的parent_uid字段
            }
        }
        
        return $items;
    }
}