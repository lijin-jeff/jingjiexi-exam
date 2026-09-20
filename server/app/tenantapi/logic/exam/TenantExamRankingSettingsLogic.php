<?php
// +----------------------------------------------------------------------
// | likeadmin快速开发前后端分离管理后台（PHP版）
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 开源版本可自由商用，可去除界面版权logo
// | gitee下载：https://gitee.com/likeshop_gitee/likeadmin
// | github下载：https://github.com/likeshop-github/likeadmin
// | 访问官网：https://www.likeadmin.cn
// | likeadmin团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeadminTeam
// +----------------------------------------------------------------------

namespace app\tenantapi\logic\exam;


use app\common\model\exam\TenantExamRankingSettings;
use app\common\logic\BaseLogic;
use think\facade\Db;


/**
 * rankSetting逻辑
 * Class TenantExamRankingSettingsLogic
 * @package app\tenantapi\logic\exam
 */
class TenantExamRankingSettingsLogic extends BaseLogic
{    
    /**
     * @notes 验证参数
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/12/20
     */
    private static function validateParams(array $params): bool
    {
        // 验证必填字段的完整性
        $requiredFields = [
            'tenant_id', 'ranking_type', 'display_top_count', 'ranking_dimension',
            'reset_time_day', 'reset_time_week', 'reset_time_month', 'reset_time_total',
            'is_show', 'reward_rule'
        ];
        
        foreach ($requiredFields as $field) {
            if (!isset($params[$field])) {
                self::setError("缺少必填字段：{$field}");
                return false;
            }
        }
        
        // 验证排行榜类型是数组且至少有一个元素
        if (!is_array($params['ranking_type']) || count($params['ranking_type']) < 1) {
            self::setError("排行榜类型必须是数组且至少选择一个");
            return false;
        }

        
        // 验证排行榜类型的有效性
        $validRankingTypes = ['day', 'week', 'month', 'total'];
        foreach ($params['ranking_type'] as $type) {
            if (!in_array($type, $validRankingTypes)) {
                self::setError("排行榜类型无效：{$type}");
                return false;
            }
        }
        
        // 验证周榜重置时间
        $weekDay = intval($params['reset_time_week']);
        if ($weekDay < 1 || $weekDay > 7) {
            self::setError("周榜重置时间必须是1-7之间的数字");
            return false;
        }
        
        // 验证月榜重置时间
        $day = intval($params['reset_time_month']);
        if ($day < 1 || $day > 31) {
            self::setError("月榜重置时间必须是1-31之间的数字");
            return false;
        }
        
        // 验证显示数量
        if ($params['display_top_count'] < 10 || $params['display_top_count'] > 100) {
            self::setError("展示数量必须在10-100之间");
            return false;
        }
        
        return true;
    }
    
    /**
     * @notes 添加rankSetting
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/08/24 13:16
     */
    public static function add(array $params): bool
    {
        // 参数验证
        if (!self::validateParams($params)) {
            return false;
        }
        
        Db::startTrans();
        try {
            // 将数组转换为JSON字符串存入数据库
            $rankingType = json_encode($params['ranking_type']);
            $rankingDimension = json_encode($params['ranking_dimension']);
            
            // 修复：使用save方法实现 upsert 功能
            $model = new TenantExamRankingSettings();
            // 不存在则新增
            $model->save([
                'tenant_id' => $params['tenant_id'],
                'ranking_type' => $rankingType,
                'display_top_count' => $params['display_top_count'],
                'ranking_dimension' => $rankingDimension,
                'reset_time_day' => $params['reset_time_day'],
                'reset_time_week' => $params['reset_time_week'],
                'reset_time_month' => $params['reset_time_month'],
                'reset_time_total' => $params['reset_time_total'],
                'is_show' => $params['is_show'],
                'reward_rule' => $params['reward_rule'],
                'integral_count_day' => $params['integral_count_day'] ?? '',
                'integral_count_week' => $params['integral_count_week'] ?? '',
                'integral_count_month' => $params['integral_count_month'] ?? '',
                'integral_count_total' => $params['integral_count_total'] ?? '',
                'desc' => $params['desc'] ?? '',
            ]);
            

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            // 记录错误日志
            trace("添加排行榜设置失败：" . $e->getMessage(), 'error');
            self::setError("添加排行榜设置失败：" . $e->getMessage());
            return false;
        }
    }


    /**
     * @notes 编辑rankSetting
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/08/24 13:16
     */
    public static function edit(array $params): bool
    {
        // 参数验证
        if (!self::validateParams($params)) {
            return false;
        }
        
        Db::startTrans();
        try {
            // 将数组转换为JSON字符串存入数据库
            $rankingType = json_encode($params['ranking_type']);
            $rankingDimension = json_encode($params['ranking_dimension']);

            // 修复：先检查是否存在记录，不存在则创建
            $existing = TenantExamRankingSettings::where('id', $params['id'])->find();
            
            if (!$existing) {
                // 记录不存在，使用insert创建
                TenantExamRankingSettings::insert([
                    'tenant_id' => $params['tenant_id'],
                    'ranking_type' => $rankingType,
                    'display_top_count' => $params['display_top_count'],
                    'ranking_dimension' => $rankingDimension,
                    'reset_time_day' => $params['reset_time_day'],
                    'reset_time_week' => $params['reset_time_week'],
                    'reset_time_month' => $params['reset_time_month'],
                    'reset_time_total' => $params['reset_time_total'],
                    'is_show' => $params['is_show'],
                    'reward_rule' => $params['reward_rule'],
                    'integral_count_day' => $params['integral_count_day'] ?? '',
                    'integral_count_week' => $params['integral_count_week'] ?? '',
                    'integral_count_month' => $params['integral_count_month'] ?? '',
                    'integral_count_total' => $params['integral_count_total'] ?? '',
                    'desc' => $params['desc'] ?? '',
                ]);
            } else {
                // 记录存在，执行更新
                TenantExamRankingSettings::where('id', $params['id'])->update([
                    'tenant_id' => $params['tenant_id'],
                    'ranking_type' => $rankingType,
                    'display_top_count' => $params['display_top_count'],
                    'ranking_dimension' => $rankingDimension,
                    'reset_time_day' => $params['reset_time_day'],
                    'reset_time_week' => $params['reset_time_week'],
                    'reset_time_month' => $params['reset_time_month'],
                    'reset_time_total' => $params['reset_time_total'],
                    'is_show' => $params['is_show'],
                    'reward_rule' => $params['reward_rule'],
                    'integral_count_day' => $params['integral_count_day'] ?? '',
                    'integral_count_week' => $params['integral_count_week'] ?? '',
                    'integral_count_month' => $params['integral_count_month'] ?? '',
                    'integral_count_total' => $params['integral_count_total'] ?? '',
                    'desc' => $params['desc'] ?? '',
                ]);
            }

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            // 记录错误日志
            trace("编辑排行榜设置失败：" . $e->getMessage(), 'error');
            self::setError("编辑排行榜设置失败：" . $e->getMessage());
            return false;
        }
    }


    /**
     * @notes 删除rankSetting
     * @param array $params
     * @return bool
     * @author 精解析答题
     * @date 2025/08/24 13:16
     */
    public static function delete(array $params): bool
    {
        return TenantExamRankingSettings::destroy($params['id']);
    }


    /**
     * @notes 获取rankSetting详情
     * @param $params
     * @return array
     * @author 精解析答题
     * @date 2025/08/24 13:16
     */
    public static function detail($params): array
    {
        try {
            $result = [];
            
            if (isset($params['id'])) {
                // 根据ID获取详情
                $result = TenantExamRankingSettings::where('id', $params['id'])->find();
            } elseif (isset($params['tenant_id'])) {
                // 根据tenant_id获取详情
                $result = TenantExamRankingSettings::where('tenant_id', $params['tenant_id'])->find();
            }
            
            if ($result) {
                $result = $result->toArray();
                $result = self::formatResult($result);
            }
            
            return $result;
        } catch (\Exception $e) {
            // 记录错误日志
            trace("获取排行榜设置详情失败：" . $e->getMessage(), 'error');
            self::setError("获取排行榜设置详情失败：" . $e->getMessage());
            return [];
        }
    }
    
    /**
     * @notes 格式化结果，将JSON字符串转换为数组
     * @param array $result
     * @return array
     * @author 精解析答题
     * @date 2025/12/20
     */
    private static function formatResult(array $result): array
    {
        // 将JSON字符串转换为数组
        if (isset($result['ranking_type']) && $result['ranking_type']) {
            $result['ranking_type'] = json_decode($result['ranking_type'], true) ?: [];
        } else {
            $result['ranking_type'] = [];
        }
        
        if (isset($result['ranking_dimension']) && $result['ranking_dimension']) {
            $result['ranking_dimension'] = json_decode($result['ranking_dimension'], true) ?: [];
        } else {
            $result['ranking_dimension'] = [];
        }
        
        return $result;
    }
}