<?php
declare(strict_types=1);
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 精解析答题系统开发者版权所有，拥有最终解释权。
namespace app\api\logic\exam;

use app\common\logic\BaseLogic;
use app\common\model\exam\TenantExamCategory;

class CategoryLogic extends BaseLogic
{
    /**
     * 考试分类树
     * @return array
     * @date 2025/5/2 23:53
     * @author 精解析答题 
     */
    public static function categoryTree(): array
    {
        $items = TenantExamCategory::where([
            ["parent_uid", "=", ""]
        ])->with(["children" => function ($query) {
            $query->field(["parent_uid", "uid", "title as label", "exam_time"]);
        }])->field(["parent_uid", "uid", "title as label", "exam_time"])->select()->toArray();
        foreach ($items as $key => $value) {
            $items[$key]["value"] = $value["uid"];
            foreach ($value["children"] as $k => $v) {
                $items[$key]["children"][$k]["value"] = $v["uid"];
                unset($items[$key]["children"][$k]["uid"]);
            }
            unset($items[$key]["uid"]);
        }
        return $items;
    }
}