<?php
declare(strict_types=1);
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 访问官网：
// | 精解析答题系统开发者版权所有，拥有最终解释权。
namespace app\api\logic\exam;

use app\common\logic\BaseLogic;
use app\common\model\exam\TenantExamRankingSettings;
use Symfony\Polyfill\Intl\Idn\Info;

class RankLogic extends BaseLogic
{
    
    /**
     * @notes 获取排名设置
     * @param array $params
     * @return array|null
     * @author 精解析答题
     * @date 2025/08/24 13:16
     */
    public function getRankingSettings(array $params)
    {
        try {
            $settings = TenantExamRankingSettings::query()->where($params)->find();
            return $settings ? $settings->toArray() : null;
        } catch (\Exception $e) {
            $this->setError('获取排名设置失败: ' . $e->getMessage());
            return null;
        }
    }

    
    /**
     * @notes 每日定时更新所有排行榜（日榜/周榜/月榜/总榜）
     * @return bool
     * @author 精解析答题
     * @date 2025/12/20
     */
    public function dailyRefreshAllRankings(): bool
    {
        try {
            // 获取所有启用的租户排名设置
            $settingsResult = TenantExamRankingSettings::where('is_show', 1)->select();
            
            // 判空处理，防止 'Call to a member function toArray() on null' 错误
            if (!$settingsResult) {
                $this->setError('没有启用的排名设置');
                return false;
            }
            
            $allSettings = $settingsResult->toArray();

            if (empty($allSettings)) {
                $this->setError('没有启用的排名设置');
                return false;
            }

            // 按租户分组
            $settingsByTenant = [];
            foreach ($allSettings as $setting) {
                $tenantId = $setting['tenant_id'];
                if (!isset($settingsByTenant[$tenantId])) {
                    $settingsByTenant[$tenantId] = [];
                }
                
                // 处理 ranking_type，支持字符串和JSON数组两种格式
                $rankingTypes = [];
                if (is_string($setting['ranking_type'])) {
                    // 尝试解析JSON
                    $decoded = json_decode($setting['ranking_type'], true);
                    if (is_array($decoded)) {
                        // 是JSON数组，如 ["week","month","total","day"]
                        $rankingTypes = $decoded;
                    } else {
                        // 普通字符串，如 "day"
                        $rankingTypes = [$setting['ranking_type']];
                    }
                }
                
                // 将设置按类型分组
                foreach ($rankingTypes as $type) {
                    $settingsByTenant[$tenantId][$type] = $setting;
                }
            }

            // 依次执行各租户的各类型排行榜更新
            foreach ($settingsByTenant as $tenantId => $typeSettings) {
                // 按固定顺序执行
                $types = ['day', 'week', 'month', 'total'];
                foreach ($types as $type) {
                    if (isset($typeSettings[$type])) {
                        $this->refreshRankingByType($tenantId, $type, $typeSettings[$type]);
                    }
                }
            }

            return true;
        } catch (\Exception $e) {
            $this->setError('定时更新排名失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * @notes 更新指定租户的指定类型排行榜
     * @param int $tenantId 租户ID
     * @param string $rankingType 排行榜类型：day|week|month|total
     * @param array|null $settings 设置信息（可选，如果已有可直接传入）
     * @return bool
     * @author 精解析答题
     * @date 2025/12/20
     */
    public function refreshRankingByType(int $tenantId, string $rankingType, ?array $settings = null): bool
    {
        try {
            // 如果没有传入settings，则查询数据库
            if ($settings === null) {
                // 1. 获取该租户该类型的排名设置
                $settingsObj = TenantExamRankingSettings::where([
                    'tenant_id' => $tenantId,
                    'is_show' => 1
                ])
                ->where('ranking_type', 'IN', [$rankingType])
                ->find();

                // 如果没有配置，跳过该类型
                if (!$settingsObj) {
                    // 记录日志，但不报错
                    trace("租户{$tenantId}的{$rankingType}榜未启用或未配置，跳过", 'info');
                    return true;
                }

                $settings = $settingsObj->toArray();
            }

            // 2. 判断是否到达结算时间
            if (!$this->shouldSettle($rankingType, $settings)) {
                // 未到结算时间，仅更新数据
                $this->updateRanking($tenantId, $rankingType, $settings);
                return true;
            }

            // 3. 到达结算时间，执行结算（发放奖励）+ 更新排名
            $this->settleRanking($tenantId, $rankingType, $settings);
            $this->updateRanking($tenantId, $rankingType, $settings);

            return true;
        } catch (\Exception $e) {
            $this->setError("更新{$rankingType}榜失败: " . $e->getMessage());
            return false;
        }
    }

    /**
     * @notes 判断是否应该结算
     * @param string $rankingType 排行榜类型
     * @param array $settings 设置信息
     * @return bool
     * @author 精解析答题
     * @date 2025/12/20
     */
    private function shouldSettle(string $rankingType, array $settings): bool
    {
        $today = date('Y-m-d');
        $currentTime = date('H:i:s');
        
        switch ($rankingType) {
            case 'day':
                // 日榜：每日00:00:00结算
                return $currentTime >= ($settings['reset_time_day'] ?? '00:00:00');
                
            case 'week':
                // 周榜：根据设置的星期几结算（1=周一）
                $settleWeekday = (int)($settings['reset_time_week'] ?? 1);
                $currentWeekday = (int)date('N'); // 1-7 (周一到周日)
                return $currentWeekday == $settleWeekday && $currentTime >= '00:00:00';
                
            case 'month':
                // 月榜：根据设置的日期结算（1=每月1号）
                $settleDay = (int)($settings['reset_time_month'] ?? 1);
                $currentDay = (int)date('j');
                return $currentDay == $settleDay && $currentTime >= '00:00:00';
                
            case 'total':
                // 总榜：每年最后一天00:00:00结算
                return $today == date('Y') . '-12-31' && $currentTime >= '00:00:00';
                
            default:
                return false;
        }
    }

    /**
     * @notes 更新排名（不发放奖励）
     * @param int $tenantId 租户ID
     * @param string $rankingType 排行榜类型
     * @param array $settings 设置信息
     * @return void
     * @author 精解析答题
     * @date 2025/12/20
     */
    private function updateRanking(int $tenantId, string $rankingType, array $settings): void
    {
        // 1. 计算统计区间
        [$table, $start, $end] = $this->getCycleRange($rankingType, $settings);

        // 2. 从考试记录聚合数据（优化：使用索引，分批查询）
        $stat = $this->getExamStat($tenantId, $start, $end, $settings);

        // 3. 写入排名表
        $this->writeRanking($table, $stat, $rankingType, $tenantId, $start, $end);
    }

    /**
     * @notes 结算排名并发放奖励
     * @param int $tenantId 租户ID
     * @param string $rankingType 排行榜类型
     * @param array $settings 设置信息
     * @return void
     * @author 精解析答题
     * @date 2025/12/20
     */
    private function settleRanking(int $tenantId, string $rankingType, array $settings): void
    {
        // 1. 获取当前排名数据
        $table = $this->getTableName($rankingType);
        $rankingData = $this->getCurrentRankingData($table, $tenantId, $rankingType);

        if (empty($rankingData)) {
            return;
        }

        // 2. 解析奖励配置
        $rewardConfig = $this->parseRewardConfig($rankingType, $settings);

        if (empty($rewardConfig) || $settings['reward_rule'] !== 'integral') {
            return; // 无奖励配置或未启用积分奖励
        }

        // 3. 发放奖励积分
        $this->distributeRewards($rankingData, $rewardConfig, $tenantId);
    }

    /**
     * @notes 获取当前排名数据
     * @param string $table 表名
     * @param int $tenantId 租户ID
     * @param string $rankingType 排行榜类型
     * @return array
     * @author 精解析答题
     * @date 2025/12/20
     */
    private function getCurrentRankingData(string $table, int $tenantId, string $rankingType): array
    {
        $where = ['tenant_id' => $tenantId];
        
        // 添加时间条件
        switch ($rankingType) {
            case 'day':
                $where['create_date'] = date('Y-m-d');
                break;
            case 'week':
                $where['year'] = (int)date('Y');
                $where['week_number'] = (int)date('W');
                break;
            case 'month':
                $where['year'] = (int)date('Y');
                $where['month'] = (int)date('m');
                break;
        }

        return \think\facade\Db::table($table)
            ->where($where)
            ->order('ranking', 'asc')
            ->select()
            ->toArray();
    }

    /**
     * @notes 解析奖励配置
     * @param string $rankingType 排行榜类型
     * @param array $settings 设置信息
     * @return array 返回格式：[排名 => 积分]
     * @author 精解析答题
     * @date 2025/12/20
     */
    private function parseRewardConfig(string $rankingType, array $settings): array
    {
        $configField = 'integral_count_' . $rankingType;
        $configStr = $settings[$configField] ?? '';
        
        if (empty($configStr) || $configStr === '0') {
            return [];
        }

        // 格式：1|500,2|300,3|200
        $rewards = [];
        $items = explode(',', $configStr);
        
        foreach ($items as $item) {
            $parts = explode('|', trim($item));
            if (count($parts) === 2) {
                $ranking = (int)$parts[0];
                $integral = (int)$parts[1];
                $rewards[$ranking] = $integral;
            }
        }

        return $rewards;
    }

    /**
     * @notes 发放奖励积分
     * @param array $rankingData 排名数据
     * @param array $rewardConfig 奖励配置
     * @param int $tenantId 租户ID
     * @return void
     * @author 精解析答题
     * @date 2025/12/20
     */
    private function distributeRewards(array $rankingData, array $rewardConfig, int $tenantId): void
    {
        foreach ($rankingData as $record) {
            $ranking = $record['ranking'];
            
            if (!isset($rewardConfig[$ranking])) {
                continue; // 该排名无奖励
            }

            $integral = $rewardConfig[$ranking];
            $userId = $record['user_id'];

            // 发放积分（调用积分逻辑）
            // TODO: 这里需要调用您的积分发放接口
            // 示例：UserIntegralLogic::addIntegral($userId, $integral, '排行榜奖励');
            
            // 记录日志
            $this->logReward($tenantId, $userId, $ranking, $integral);
        }
    }

    /**
     * @notes 记录奖励日志
     * @param int $tenantId 租户ID
     * @param int $userId 用户ID
     * @param int $ranking 排名
     * @param int $integral 积分
     * @return void
     * @author 精解析答题
     * @date 2025/12/20
     */
    private function logReward(int $tenantId, int $userId, int $ranking, int $integral): void
    {
        // 记录到日志表或其他业务逻辑
        // TODO: 实现奖励日志记录
    }

    /**
     * @notes 获取表名
     * @param string $rankingType 排行榜类型
     * @return string
     * @author 精解析答题
     * @date 2025/12/20
     */
    private function getTableName(string $rankingType): string
    {
        $tables = [
            'day' => 'la_tenant_exam_ranking_daily',
            'week' => 'la_tenant_exam_ranking_weekly',
            'month' => 'la_tenant_exam_ranking_monthly',
            'total' => 'la_tenant_exam_ranking_total',
        ];

        return $tables[$rankingType] ?? 'la_tenant_exam_ranking_daily';
    }

    /**
     * @notes 根据周期计算统计区间与目标表名（优化版）
     * @param string $cycle 周期类型
     * @param array $settings 设置信息
     * @return array [表名, 开始时间戳, 结束时间戳]
     * @author 精解析答题
     * @date 2025/12/20
     */
    private function getCycleRange(string $cycle, array $settings): array
    {
        $table = $this->getTableName($cycle);
        $now = time();
        $today = strtotime(date('Y-m-d'));
        
        switch ($cycle) {
            case 'day':
                // 日榜：统计昨天的数据
                $start = $today - 86400;
                $end   = $today;
                break;
                
            case 'week':
                // 周榜：统计本周期数据
                $settleWeekday = (int)($settings['reset_time_week'] ?? 1); // 1=周一
                $currentWeekday = (int)date('N');
                
                // 计算本周期开始时间
                if ($currentWeekday >= $settleWeekday) {
                    $start = strtotime("this week Monday") + ($settleWeekday - 1) * 86400;
                } else {
                    $start = strtotime("last week Monday") + ($settleWeekday - 1) * 86400;
                }
                $end = $now;
                break;
                
            case 'month':
                // 月榜：统计本月期数据
                $settleDay = (int)($settings['reset_time_month'] ?? 1);
                $currentDay = (int)date('j');
                $currentMonth = (int)date('n');
                $currentYear = (int)date('Y');
                
                // 计算本周期开始时间
                if ($currentDay >= $settleDay) {
                    $start = mktime(0, 0, 0, $currentMonth, $settleDay, $currentYear);
                } else {
                    $start = mktime(0, 0, 0, $currentMonth - 1, $settleDay, $currentYear);
                }
                $end = $now;
                break;
                
            case 'total':
                // 总榜：统计本年数据
                $start = mktime(0, 0, 0, 1, 1, (int)date('Y'));
                $end   = $now;
                break;
                
            default:
                $start = $today - 86400;
                $end   = $today;
                break;
        }
        
        return [$table, $start, $end];
    }

    /**
     * @notes 聚合考试记录（性能优化版）
     * @param int $tenantId 租户ID
     * @param int $start 开始时间戳
     * @param int $end 结束时间戳
     * @param array $settings 设置信息
     * @return array
     * @author 精解析答题
     * @date 2025/12/20
     */
    private function getExamStat(int $tenantId, int $start, int $end, array $settings): array
    {
        // 获取排序维度
        $dimension = $settings['ranking_dimension'] ?? 'correct_count';
        
        // 数据库层面排序，避免PHP内存排序
        $orderField = $this->getDimensionOrderField($dimension);
        
        $stat = \think\facade\Db::table('la_tenant_exam_examination_history')
            ->where('tenant_id', $tenantId)
            ->where('create_time', '>=', $start)
            ->where('create_time', '<', $end)
            ->where('delete_time', null) // 排除软删除数据
            ->field('user_uid as user_id,
                     COUNT(id) as total_count,
                     SUM(correct_count) as correct_count,
                     ROUND(AVG(CASE WHEN options_count > 0 THEN correct_count / options_count * 100 ELSE 0 END), 2) as accuracy,
                     SUM(user_integral) as integral')
            ->group('user_uid')
            ->order($orderField, 'desc')
            ->limit(1000) // 限制返回数量，提升性能
            ->select()
            ->toArray();

        return $stat;
    }

    /**
     * @notes 获取排序字段
     * @param string $dimension 排序维度
     * @return string
     * @author 精解析答题
     * @date 2025/12/20
     */
    private function getDimensionOrderField(string $dimension): string
    {
        $fields = [
            'correct_count' => 'correct_count',
            'total_count' => 'total_count',
            'accuracy' => 'accuracy',
            'integral' => 'integral',
        ];

        return $fields[$dimension] ?? 'correct_count';
    }

    /**
     * @notes 写入排名表（性能优化版：使用REPLACE INTO）
     * @param string $table 表名
     * @param array $stat 统计数据
     * @param string $cycle 周期类型
     * @param int $tenantId 租户ID
     * @param int $start 开始时间戳
     * @param int $end 结束时间戳
     * @return void
     * @author 精解析答题
     * @date 2025/12/20
     */
    private function writeRanking(string $table, array $stat, string $cycle, int $tenantId, int $start, int $end): void
    {
        if (empty($stat)) {
            return;
        }

        // 使用事务保证数据一致性
        \think\facade\Db::startTrans();
        try {
            // 先清空本周期旧数据（性能优化：添加索引条件）
            $this->clearOldRankingData($table, $cycle, $tenantId, $start);

            // 批量插入（每次500条，避免内存溢出）
            $rows = [];
            $rank = 0;
            $batchSize = 500;
            
            foreach ($stat as $v) {
                $rank++;
                $row = [
                    'tenant_id'     => $tenantId,
                    'user_id'       => $v['user_id'],
                    'correct_count' => (int)$v['correct_count'],
                    'total_count'   => (int)$v['total_count'],
                    'accuracy'      => round((float)$v['accuracy'], 2),
                    'integral'      => (int)$v['integral'],
                    'ranking'       => $rank,
                    'create_time'   => date('Y-m-d H:i:s'),
                    'update_time'   => date('Y-m-d H:i:s'),
                ];

                // 根据周期添加必要字段
                $row = $this->addCycleFields($row, $cycle, $start, $end);
                $rows[] = $row;

                // 达到批次大小，执行插入
                if (count($rows) >= $batchSize) {
                    \think\facade\Db::table($table)->insertAll($rows);
                    $rows = [];
                }
            }

            // 插入剩余数据
            if (!empty($rows)) {
                \think\facade\Db::table($table)->insertAll($rows);
            }

            \think\facade\Db::commit();
        } catch (\Exception $e) {
            \think\facade\Db::rollback();
            throw $e;
        }
    }

    /**
     * @notes 清空旧排名数据
     * @param string $table 表名
     * @param string $cycle 周期类型
     * @param int $tenantId 租户ID
     * @param int $start 开始时间戳
     * @return void
     * @author 精解析答题
     * @date 2025/12/20
     */
    private function clearOldRankingData(string $table, string $cycle, int $tenantId, int $start): void
    {
        switch ($cycle) {
            case 'day':
                $createDate = date('Y-m-d', $start);
                \think\facade\Db::table($table)
                    ->where('tenant_id', $tenantId)
                    ->where('create_date', $createDate)
                    ->delete();
                break;
                
            case 'week':
                $year = (int)date('Y', $start);
                $weekNumber = (int)date('W', $start);
                \think\facade\Db::table($table)
                    ->where('tenant_id', $tenantId)
                    ->where('year', $year)
                    ->where('week_number', $weekNumber)
                    ->delete();
                break;
                
            case 'month':
                $year = (int)date('Y', $start);
                $month = (int)date('m', $start);
                \think\facade\Db::table($table)
                    ->where('tenant_id', $tenantId)
                    ->where('year', $year)
                    ->where('month', $month)
                    ->delete();
                break;
                
            case 'total':
                \think\facade\Db::table($table)
                    ->where('tenant_id', $tenantId)
                    ->delete();
                break;
        }
    }

    /**
     * @notes 添加周期特定字段
     * @param array $row 数据行
     * @param string $cycle 周期类型
     * @param int $start 开始时间戳
     * @param int $end 结束时间戳
     * @return array
     * @author 精解析答题
     * @date 2025/12/20
     */
    private function addCycleFields(array $row, string $cycle, int $start, int $end): array
    {
        switch ($cycle) {
            case 'day':
                $row['create_date'] = date('Y-m-d', $start);
                break;
                
            case 'week':
                $row['week_start_date'] = date('Y-m-d', $start);
                $row['week_end_date'] = date('Y-m-d', $end - 86400);
                $row['week_number'] = (int)date('W', $start);
                $row['year'] = (int)date('Y', $start);
                break;
                
            case 'month':
                $row['month'] = (int)date('m', $start);
                $row['year'] = (int)date('Y', $start);
                break;
        }

        return $row;
    }
}