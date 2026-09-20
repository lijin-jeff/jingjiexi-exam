<?php
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 精解析答题系统开发者版权所有，拥有最终解释权。

namespace app\tenantapi\logic\exam;


use app\common\logic\BaseLogic;
use app\common\model\exam\TenantExamCategory;
use app\common\model\exam\countdown\TenantExamCountdown;


/**
 * 题库分类逻辑
 * Class ExamCategoryLogic
 * @package app\platform\logic\exam
 */
class ExamCategoryLogic extends BaseLogic
{
    /**
     * 查询一级分类
     * @param array $params
     * @return array
     * @date 2025/4/1 02:58
     * @author 精解析答题 
     */
    public static function parentList(array $params): array
    {
        return TenantExamCategory::where([
            ['tenant_id', '=', $params['tenant_id']],
            ["parent_uid", "=", '']
        ])->column(["uid", "title"]);
    }

    /**
     * @notes 处理倒计时插入和更新
     * @param array $params 包含tenant_id, title, sort等参数
     * @param int $exam_time 考试时间戳
     * @param string $category_uid 分类UID
     * @return void
     * @author 精解析答题
     * @date 2026/01/09
     */
    private static function handleCountdown(array $params, int $exam_time, string $category_uid): void
    {
        try {
            if (empty($category_uid)) {
                return;
            }
            
            if ($exam_time <= 0) {
                return;
            }
            
            $targetDate = date('Y-m-d', $exam_time);
            $targetTimestamp = strtotime($targetDate . ' 23:59:59');

            
            // BaseModel有全局作用域tenantId，会自动添加tenant_id条件
            // 所以这里只需要添加category_uid条件
            $existingCountdown = TenantExamCountdown::where('category_uid', $category_uid)->find();
            

            if ($existingCountdown) {
                if ($targetTimestamp > time()) {
                    $existingCountdown->target_date = $targetDate;
                    $existingCountdown->title = $params['title'] . '考试';
                    $existingCountdown->sort = (int)($params['sort'] ?? 0);
                    $existingCountdown->save();
                } else {
                    $existingCountdown->delete();
                    TenantExamCountdown::create([
                        'target_date' => $targetDate,
                        'title' => $params['title'] . '考试',
                        'sort' => (int)($params['sort'] ?? 0),
                        'category_uid' => $category_uid,
                        'status' => 1,
                    ]);
                }
            } else {
                TenantExamCountdown::create([
                    'target_date' => $targetDate,
                    'title' => $params['title'] . '考试',
                    'sort' => (int)($params['sort'] ?? 0),
                    'category_uid' => $category_uid,
                    'status' => 1,
                ]);
            }
        } catch (\Exception $e) {
            \think\facade\Log::error('处理倒计时异常：' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * @notes 添加题库分类
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/04/01 01:51
     */
    public static function add(array $params): bool
    {
        try {
            // 处理考试时间
            $exam_time = !empty($params['exam_time']) ? strtotime($params['exam_time']) : 0;
            
            $model = TenantExamCategory::create([
                'title'        => $params['title'],
                'is_show'      => $params['is_show'],
                'sort'         => $params['sort'],
                'cover'        => $params['cover'],
                'is_recommend' => $params['is_recommend'],
                'icon'         => $params['icon'],
                'uid'          => uid(),
                "parent_uid"   => $params["parent_uid"] ?? "",
                'tenant_id'    => $params['tenant_id'],
                'exam_time'    => $exam_time,//科目考试时间，转化为时间戳
            ]);
            
            if (!$model->getKey()) {
                return false;
            }
            
            //exam_time插入倒计时表
            if ($exam_time) {
                self::handleCountdown($params, $exam_time, $model->uid);
            }
            
            return true;
        } catch (\think\exception\ValidateException $e) {
            // 记录验证错误日志
            \think\facade\Log::error('题库分类添加验证失败：' . $e->getError());
            self::setError($e->getError());
            return false;
        } catch (\Exception $e) {
            // 记录异常错误日志
            \think\facade\Log::error('题库分类添加异常失败：' . $e->getMessage());
            self::setError('添加失败：' . $e->getMessage());
            return false;
        }       
    }


    /**
     * @notes 编辑题库分类
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/04/01 01:51
     */
    public static function edit(array $params): bool
    {
        try {
            // 处理考试时间
            $exam_time = !empty($params['exam_time']) ? strtotime($params['exam_time']) : 0;
            
            // 更新分类信息
            $row = TenantExamCategory::where([
                ['id', '=', $params['id']],
                ['tenant_id', '=', $params['tenant_id']]
            ])->update([
                'title'        => $params['title'],
                'is_show'      => $params['is_show'],
                'sort'         => $params['sort'],
                'cover'        => $params['cover'],
                'is_recommend' => $params['is_recommend'],
                'icon'         => $params['icon'],
                "parent_uid"   => $params["parent_uid"] ?? '',
                'exam_time'    => $exam_time,//科目考试时间，转化为时间戳
            ]);
            
            // 无论是否更新了数据，只要exam_time有效就处理倒计时
            if ($exam_time) {
                self::handleCountdown($params, $exam_time, $params['uid']);
            }
            
            // $row >= 0 表示操作成功，0 表示没有数据被修改
            return $row >= 0;
        } catch (\Exception $e) {
            self::setError('编辑失败：' . $e->getMessage());
            return false;
        }
    }


    /**
     * @notes 删除题库分类
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/04/01 01:51
     */
    public static function delete(array $params): bool
    {
        if (is_array($params["id"])) {
            return TenantExamCategory::whereIn("id", $params["id"])->where([
                    ['tenant_id', '=', $params['tenant_id']]
                ])->update(['delete_time' => time()]) > 0;
        }
        return TenantExamCategory::where([
                ['id', '=', $params['id']],
                ['tenant_id', '=', $params['tenant_id']]
            ])->update(['delete_time' => time()]) > 0;
    }


    /**
     * @notes 获取题库分类详情
     * @param $params
     * @return array
     * @author 精解析答题
     * @date 2025/04/01 01:51
     */
    public static function detail($params): array
    {
        $where = [];
        if (isset($params['id'])) {
            $where[] = ['id', '=', $params['id']];
        }
        if (isset($params['uid'])) {
            $where[] = ['uid', '=', $params['uid']];
        }
        $where[] = ['tenant_id', '=', $params['tenant_id']];
        $data = TenantExamCategory::where($where)->find();
        if (!$data) {
            return [];
        }
        $result = $data->toArray();
        // 将exam_time时间戳转换为日期字符串格式
        if (!empty($result['exam_time'])) {
            $result['exam_time'] = date('Y-m-d', $result['exam_time']);
        } else {
            $result['exam_time'] = '';
        }
        return $result;
    }

    /**
     * 分类树
     * @param array $params
     * @return array
     * @link 
     * @date 2024/4/21 00:45
     * @author 精解析答题
     */
    public static function categoryTree(array $params): array
    {
        $items = TenantExamCategory::where([
            ['tenant_id', '=', $params['tenant_id']],
            ["parent_uid", "=", ""]
        ])->with(["children" => function ($query) {
            $query->field(["uid", "parent_uid", "title"]);
        }])->field(["uid", "parent_uid", "title"])->select()->toArray();
        
        foreach ($items as $key => $value) {
            foreach ($value["children"] as $k => $v) {
                unset($items[$key]["children"][$k]["parent_uid"]);
            }
        }
        
        return $items;
    }

      /**
     * @notes  更改题库分类状态
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/04/01 01:51
     */
    public static function updateStatus(array $params): bool
    {
        TenantExamCategory::update([
            'is_show' => $params['is_show']
        ], ['id' => $params['id'], 'uid' => $params['uid']]);
        //同时将子分类的状态更改为与父分类相同
        TenantExamCategory::where([
            ['parent_uid', '=', $params['uid']],
        ])->update([
            'is_show' => $params['is_show']
        ]);
        return true;                                                               
    }

}