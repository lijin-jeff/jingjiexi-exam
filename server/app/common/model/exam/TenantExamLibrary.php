<?php
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 精解析答题系统开发者版权所有，拥有最终解释权。

namespace app\common\model\exam;


use app\common\model\BaseModel;
use think\model\concern\SoftDelete;
use think\db\Query;


/**
 * 题库管理模型
 * Class TenantExamLibrary
 * @package app\common\model\exam
 */
class TenantExamLibrary extends BaseModel
{
    use SoftDelete;

    protected $name = 'tenant_exam_library';

    protected $deleteTime = 'delete_time';

    public function category(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(TenantExamCategory::class, 'category_uid', 'uid');
    }

    /**
     * 题库分类名称
     * @param $value
     * @param $data
     * @return string
     * @date 2025/5/3 00:21
     * @author 精解析答题
     */
    public function getCateNameAttr($value, $data): string
    {
        return TenantExamCategory::query()->where('uid', '=', $data['category_uid'])->value('title');
    }

    /**
     * 查询题库作答次数
     * @param $value
     * @param $data
     * @return int
     * @date 2025/5/3 00:38
     * @author 精解析答题
     */
    public function getSubmitCountAttr($value, $data): int
    {
        return mt_rand(10, 100);
    }

    /**
     * 查询题库题目数量
     * @param $value
     * @param $data
     * @return int
     * @throws \think\db\exception\DbException
     * @date 2025/5/3 01:41
     */
    public function getQuestionCountAttr($value, $data): int
    {
        return TenantExamQuestion::query()->where([
            ['library_uid', '=', $data['uid']],
            ['is_show', '=', 1]
        ])->count();
    }

    /**
     * 题库试题总数
     * @param $value
     * @param $data
     * @return int
     * @throws \think\db\exception\DbException
     */
    public function getExamCountAttr($value, $data): int
    {
        return TenantExamQuestion::query()->where('library_uid', '=', $data['uid'])->count('id');
    }

    /**
     * 获取基础查询对象
     * @param array $searchWhere 搜索条件
     * @param mixed $request 请求对象
     * @return Query
     * @author 精解析答题
     * @date 2025/04/01 04:41
     */
    public static function getBaseQuery(array $searchWhere, $request): Query
    {
        $params = $request->param();
        $categoryUid = $params['category_uid'] ?? '';

        // 1. 如果 category_uid 不存在，查询当前租户下的所有题库
        if (empty($categoryUid)) {
            return self::where($searchWhere)
                ->where([
                    ['tenant_id', '=', $request->tenantId],
                    ['is_show', '=', 1],
                ])
                ->whereNull('delete_time');
        }

        // 2. 如果 category_uid 存在，根据其层级关系查询
        $categoryModel = new \app\common\model\exam\TenantExamCategory();
        $category = $categoryModel->where('uid', $categoryUid)->find();

        // 如果传入的 category_uid 无效（数据库中不存在），则返回空查询
        if (empty($category)) {
            return self::where('1', '=', '2');
        }

        // 准备一个数组，用于存放所有需要查询的 category_uid
        $categoryUidsToQuery = [$categoryUid];

        // 判断是查询父级还是子级
        if (!empty($category['parent_uid'])) {
            // 情况A: 有父级，同时包含当前分类和父级分类
            $categoryUidsToQuery[] = $category['parent_uid'];
        } else {
            // 情况B: 没有父级（是顶级分类），查询其所有子分类的UID
            $childCategoryUids = $categoryModel->where('parent_uid', $categoryUid)->column('uid');
            $categoryUidsToQuery = array_merge($categoryUidsToQuery, $childCategoryUids);
        }
        
        // 去重并过滤掉可能存在的空值，保证查询的健壮性
        $categoryUidsToQuery = array_unique(array_filter($categoryUidsToQuery));

        // 从搜索条件中移除 'category_uid'，因为我们将使用 IN 条件来精确控制
        $newSearchWhere = array_filter($searchWhere, function ($item) {
            return $item[0] !== 'category_uid';
        });

        // 构建最终的查询
        return self::where($newSearchWhere)
            ->where([
                ['category_uid', 'IN', $categoryUidsToQuery],
                ['is_show', '=', 1],
            ])
            ->whereNull('delete_time');
    }
}