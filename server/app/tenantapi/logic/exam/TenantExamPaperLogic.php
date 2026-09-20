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

namespace app\tenantapi\logic\exam;


use app\common\model\exam\TenantExamPaper;
use app\common\logic\BaseLogic;
use think\facade\Db;


/**
 * 试卷管理逻辑
 * Class TenantExamPaperLogic
 * @package app\platform\logic\exam
 */
class TenantExamPaperLogic extends BaseLogic
{


    /**
     * @notes 添加试卷管理
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/06/10 13:57
     */
    public static function add(array $params): bool
    {
        Db::startTrans();
        try {
            TenantExamPaper::create([
                'uid'     => uid(),
                'title'   => $params['title'],
                'is_show' => $params['is_show'],
                'sort'    => $params['sort'],
                'is_rand' => $params['is_rand'],
                'image'   => $params['image'],
                'remark'  => $params['remark']
            ]);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }


    /**
     * @notes 编辑试卷管理
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/06/10 13:57
     */
    public static function edit(array $params): bool
    {
        Db::startTrans();
        try {
            TenantExamPaper::where('id', $params['id'])->update([
                'title'   => $params['title'],
                'is_show' => $params['is_show'],
                'sort'    => $params['sort'],
                'is_rand' => $params['is_rand'],
                'image'   => $params['image'],
                'remark'  => $params['remark']
            ]);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }


    /**
     * @notes 删除试卷管理
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/06/10 13:57
     */
    public static function delete(array $params): bool
    {
        return TenantExamPaper::destroy($params['id']);
    }

    /**
     * 试卷试题详情
     * @param array $prams
     * @return array
     */
    public static function paperDetail(array $params): array
    {
        $paperDetail = TenantExamPaper::query()->where([
            ['id', '=', $params['id']],
            ['tenant_id', '=', $params['tenant_id']]
        ])->field(['option_count', 'option_score', 'option_config', 'option_content'])->findOrEmpty();
        
        // 默认题型结构（支持6种题型）
        $defaultData = [
            [
                'total_score' => 0,
                'total_number' => 0,
                'score' => 2.0,
                'value' => 1,
                'exam_type' => '单选题'
            ],
            [
                'total_score' => 0,
                'total_number' => 0,
                'score' => 2.0,
                'value' => 2,
                'exam_type' => '多选题'
            ],
            [
                'total_score' => 0,
                'total_number' => 0,
                'score' => 2.0,
                'value' => 3,
                'exam_type' => '判断题'
            ],
            [
                'total_score' => 0,
                'total_number' => 0,
                'score' => 2.0,
                'value' => 4,
                'exam_type' => '填空题'
            ],
            [
                'total_score' => 0,
                'total_number' => 0,
                'score' => 2.0,
                'value' => 5,
                'exam_type' => '问答题'
            ],
            [
                'total_score' => 0,
                'total_number' => 0,
                'score' => 2.0,
                'value' => 6,
                'exam_type' => '案例题'
            ],
        ];
        
        if ($paperDetail->isEmpty()) {
            return [
                'total' => [
                    'exam_count' => 0,
                    'exam_score' => 0,
                ],
                'data' => $defaultData,
                'exam_list' => []
            ];
        }
        
        // 如果有配置数据，返回完整信息
        if (!empty($paperDetail['option_config'])) {
            return [
                'total' => [
                    'exam_count' => $paperDetail['option_count'],
                    'exam_score' => $paperDetail['option_score'],
                ],
                'data' => json_decode($paperDetail['option_config'], true),
                'exam_list' => json_decode($paperDetail['option_content'], true) ?? []
            ];
        }
        
        // 没有配置数据，返回默认结构
        return [
            'total' => [
                'exam_count' => 0,
                'exam_score' => 0,
            ],
            'data' => $defaultData,
            'exam_list' => []
        ];
    }

    /**
     * @notes 获取试卷管理详情
     * @param $params
     * @return array
     * @author 精解析答题
     * @date 2025/06/10 13:57
     */
    public static function detail($params): array
    {
        return TenantExamPaper::findOrEmpty($params['id'])->toArray();
    }

    /**
     * 保存试卷组卷数据
     * @return bool
     */
    public static function savePaper(array $params): bool
    {
        try {
            // 验证必要参数
            if (!isset($params['id']) || !isset($params['tenant_id'])) {
                self::setError('参数错误');
                return false;
            }
            
            // 验证试卷是否存在
            $paper = TenantExamPaper::query()->where([
                ['id', '=', $params['id']],
                ['tenant_id', '=', $params['tenant_id']]
            ])->findOrEmpty();
            
            if ($paper->isEmpty()) {
                self::setError('试卷不存在');
                return false;
            }
            
            // 准备更新数据
            // 转换 exam_type 为数字类型
            $exam_list = $params['exam_list'] ?? [];
            foreach ($exam_list as &$item) {
                $item['exam_type'] = (int) $item['exam_type'];
            }
            unset($item);
            
            // 再次强制转换所有exam_type为数字类型，确保万无一失
            array_walk_recursive($exam_list, function (&$value, $key) {
                if ($key === 'exam_type') {
                    $value = (int) $value;
                }
            });
            
            $updateData = [
                'option_count' => $params['total']['exam_count'] ?? 0,
                'option_score' => $params['total']['exam_score'] ?? 0,
                'option_config' => json_encode($params['data'] ?? [], JSON_UNESCAPED_UNICODE),
                'option_content' => json_encode($exam_list, JSON_UNESCAPED_UNICODE),
            ];
            
            // 执行更新
            $result = TenantExamPaper::query()->where([
                ['id', '=', $params['id']],
                ['tenant_id', '=', $params['tenant_id']]
            ])->update($updateData);
            
            return $result !== false;
        } catch (\Exception $exception) {
            self::setError($exception->getMessage());
            return false;
        }
    }
}