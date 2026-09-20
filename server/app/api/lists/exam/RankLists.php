<?php
declare(strict_types=1);
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。

// | 精解析答题系统开发者版权所有，拥有最终解释权。
namespace app\api\lists\exam;

use app\api\lists\BaseApiDataLists;
use app\api\logic\exam\RankLogic;
use app\common\model\exam\TenantExamRankingDaily;
use app\common\model\exam\TenantExamRankingWeekly;
use app\common\model\exam\TenantExamRankingMonthly;
use app\common\model\exam\TenantExamRankingTotal;

class RankLists extends BaseApiDataLists
{
    /**
     * 排行榜设置信息
     * @var array|	hink\Collection
     */
    protected $rankingSettings;
    
    /**
     * @notes 初始化方法
     * @author 精解析答题
     * @date 2025/12/20
     */
    public function initialize()
    {
        // 获取排行榜设置
        $rankLogic = new RankLogic();

        $this->rankingSettings = $rankLogic->getRankingSettings(['tenant_id' => request()->tenantId]);
    }
    
    /**
     * @notes 实现 lists 方法
     * @return array
     * @author 自动实现
     */
    public function lists(): array
    {
        $params = $this->request->param();
        $result = [];
        
        // 添加user_id参数，确保能获取当前用户排名
        if (isset($params['user_id'])) {
            $this->userId = $params['user_id'];
        }
        
        // 检查排行榜是否开启
        if (!$this->rankingSettings || !$this->rankingSettings['is_show']) {
            return $result;
        }
        
        // 根据设置决定是否返回各排行榜数据
        $rankingType = $this->rankingSettings['ranking_type'] ?? [];
        // 确保ranking_type是数组
        if (!is_array($rankingType)) {
            // 正确解析JSON格式的字符串
            $rankingType = json_decode($rankingType, true) ?: [];
        }
        if (in_array('day', $rankingType)) {
            $result['day'] = $this->dailyLists($params);
        }
        if (in_array('week', $rankingType)) {
            $result['week'] = $this->weeklyLists($params);
        }
        if (in_array('month', $rankingType)) {
            $result['month'] = $this->monthlyLists($params);
        }
        if (in_array('total', $rankingType)) {
            $result['total'] = $this->totalLists($params);
        }
        
        return $result;
    }

    /**
     * @notes 实现 count 方法
     * @return int
     * @author 自动实现
     */
    public function count(): int
    {
        // 检查排行榜是否开启
        if (!$this->rankingSettings || !$this->rankingSettings['is_show']) {
            return 0;
        }
        
        $totalCount = 0;
        $tenantId = request()->tenantId;
        
        // 根据设置决定是否计算各排行榜数量
        if ($this->rankingSettings['is_daily_show']) {
            // 为日榜单独构建查询条件
            $dailyWhere = [
                ['tenant_id', '=', $tenantId],
                ['create_date', 'between', [date('Y-m-d'), date('Y-m-d')]],
            ];
            $totalCount += TenantExamRankingDaily::where($dailyWhere)->count();
        }
        if ($this->rankingSettings['is_weekly_show']) {
            // 为周榜单独构建查询条件
            $weeklyWhere = [
                ['tenant_id', '=', $tenantId],
            ];
            $totalCount += TenantExamRankingWeekly::where($weeklyWhere)->count();
        }
        if ($this->rankingSettings['is_monthly_show']) {
            // 为月榜单独构建查询条件
            $monthlyWhere = [
                ['tenant_id', '=', $tenantId],
            ];
            $totalCount += TenantExamRankingMonthly::where($monthlyWhere)->count();
        }
        if ($this->rankingSettings['is_total_show']) {
            // 为总榜单独构建查询条件
            $totalWhere = [
                ['tenant_id', '=', $tenantId],
            ];
            $totalCount += TenantExamRankingTotal::where($totalWhere)->count();
        }
        
        return $totalCount;
    }

    /**
     * @notes 获取每日排名列表
     * @return array
     * @author 精解析答题
     * @date 2025/08/24 13:16
     */
    public function dailyLists(array $params): array
    {
        // 使用设置中的显示数量
        $displayCount = $this->rankingSettings['display_top_count'] ?? 20;
        // 使用设置中的排名维度，默认改为correct_count
        $rankingDimension = $this->rankingSettings['ranking_dimension'] ?? 'correct_count';
        
        // 构建排序条件
        $order = [];
        if ($rankingDimension === 'correct_count') {
            $order = ['correct_count' => 'desc', 'total_count' => 'desc', 'ranking' => 'asc'];
        } elseif ($rankingDimension === 'accuracy') {
            $order = ['accuracy' => 'desc', 'total_count' => 'desc', 'ranking' => 'asc'];
        } else {
            // 即使是积分维度，也按答对题数排序
            $order = ['correct_count' => 'desc', 'total_count' => 'desc', 'ranking' => 'asc'];
        }
        $where = [
            ['tenant_id', '=', request()->tenantId],
            ['create_date', 'between', [date('Y-m-d'), date('Y-m-d')]],
        ];
        $result = TenantExamRankingDaily::where($where)
            ->field(['id', 'user_id', 'correct_count', 'total_count', 'accuracy', 'integral', 'ranking', 'create_date'])
            ->with('user')
            ->limit($displayCount)
            ->order($order)
            ->select()
            ->toArray();

        // 查询我的每日排名
        if (isset($this->userId)) {
            $myDailyRank = TenantExamRankingDaily::where($where)
                ->where('user_id', $this->userId)
                ->field(['id', 'user_id', 'correct_count', 'total_count', 'accuracy', 'integral', 'ranking', 'create_date'])
                ->with('user')
                ->find();
            if ($myDailyRank) {
                $myDailyRank = $myDailyRank->toArray();
                    $result['me'] = $myDailyRank;
            }else{
                // 如果每日排名不存在，将每日排名设为null
                $result['me'] = [];
            }
        }
        
        return $result;
    }

    /**
     * @notes 获取本周、上周排名列表
     * @return array
     * @author 精解析答题
     * @date 2025/08/24 13:16
     */
    public function weeklyLists(array $params): array
    {
        // 使用设置中的显示数量
        $displayCount = $this->rankingSettings['display_top_count'] ?? 20;
        // 使用设置中的排名维度，默认改为correct_count
        $rankingDimension = $this->rankingSettings['ranking_dimension'] ?? 'correct_count';
        
        // 构建排序条件
        $order = [];
        if ($rankingDimension === 'correct_count') {
            $order = ['correct_count' => 'desc', 'total_count' => 'desc', 'ranking' => 'asc'];
        } elseif ($rankingDimension === 'accuracy') {
            $order = ['accuracy' => 'desc', 'total_count' => 'desc', 'ranking' => 'asc'];
        } else {
            // 即使是积分维度，也按答对题数排序
            $order = ['correct_count' => 'desc', 'total_count' => 'desc', 'ranking' => 'asc'];
        }
        
        // 获取当前日期
        $now = new \DateTime();
        // 获取当前年份和周数
        $currentYear = (int)$now->format('Y');
        $currentWeek = (int)$now->format('W');
        
        // 计算上周的年份和周数
        $lastWeekDate = clone $now;
        $lastWeekDate->sub(new \DateInterval('P7D'));
        $lastYear = (int)$lastWeekDate->format('Y');
        $lastWeek = (int)$lastWeekDate->format('W');
        
        // 初始化结果数组
        $result = [];
        
        // 获取本周排名
        $thisWeekResult = TenantExamRankingWeekly::where($this->searchWhere)
            ->where('year', $currentYear)
            ->where('week_number', $currentWeek)
            ->field(['id', 'user_id', 'correct_count', 'total_count', 'accuracy', 'integral', 'ranking', 'create_time', 'year', 'week_number'])
            ->with('user')
            ->limit($displayCount)
            ->order($order)
            ->select()
            ->toArray();
        
        // 为结果添加周标识
        foreach ($thisWeekResult as &$item) {
            $item['week_type'] = 'current';
        }
        $result = array_merge($result, $thisWeekResult);
        
        // 获取上周排名
        $lastWeekResult = TenantExamRankingWeekly::where($this->searchWhere)
            ->where('year', $lastYear)
            ->where('week_number', $lastWeek)
            ->with('user')
            ->field(['id', 'user_id', 'correct_count', 'total_count', 'accuracy', 'integral', 'ranking', 'create_time', 'year', 'week_number'])
            ->limit($displayCount)
            ->order($order)
            ->select()
            ->toArray();
        
        // 为结果添加周标识
        foreach ($lastWeekResult as &$item) {
            $item['week_type'] = 'last';
        }
        $result = array_merge($result, $lastWeekResult);
        
        // 查询我的本周排名
        if (isset($this->userId)) {
            $myThisWeekRank = TenantExamRankingWeekly::where($this->searchWhere)
                ->where('year', $currentYear)
                ->where('week_number', $currentWeek)
                ->where('user_id', $this->userId)
                ->with('user')
                ->field(['id', 'user_id', 'correct_count', 'total_count', 'accuracy', 'integral', 'ranking', 'create_time', 'year', 'week_number'])
                ->find();
            if ($myThisWeekRank) {
                $myThisWeekRank = $myThisWeekRank->toArray();
                $myThisWeekRank['week_type'] = 'current';
                // 将我的本周排名添加到$result['week']中
                $result['me'] = $myThisWeekRank;
            }else{
                // 如果本周排名不存在，将本周排名设为null
                $result['me'] = [];
            }
            
            // 查询我的上周排名
            $myLastWeekRank = TenantExamRankingWeekly::where($this->searchWhere)
                ->where('year', $lastYear)
                ->where('week_number', $lastWeek)
                ->where('user_id', $this->userId)
                ->with('user')
                ->field(['id', 'user_id', 'correct_count', 'total_count', 'accuracy', 'integral', 'ranking', 'create_time', 'year', 'week_number'])
                ->find();
            
            if ($myLastWeekRank) {
                $myLastWeekRank = $myLastWeekRank->toArray();
                $myLastWeekRank['week_type'] = 'last';
                // 将我的上周排名添加到$result['week']中
                 $result['me'] = $myLastWeekRank;
            }else{
                // 如果上周排名不存在，将上周排名设为null
                $result['me'] = [];
            }
        }
        
        return $result;
    }

    /**
     * @notes 获取每月排名列表
     * @return array
     * @author 精解析答题
     * @date 2025/08/24 13:16
     */
    public function monthlyLists(array $params): array
    {
        // 使用设置中的显示数量
        $displayCount = $this->rankingSettings['display_top_count'] ?? 20;
        // 使用设置中的排名维度，默认改为correct_count
        $rankingDimension = $this->rankingSettings['ranking_dimension'] ?? 'correct_count';
        
        // 构建排序条件
        $order = [];
        if ($rankingDimension === 'correct_count') {
            $order = ['correct_count' => 'desc', 'total_count' => 'desc', 'ranking' => 'asc'];
        } elseif ($rankingDimension === 'accuracy') {
            $order = ['accuracy' => 'desc', 'total_count' => 'desc', 'ranking' => 'asc'];
        } else {
            // 即使是积分维度，也按答对题数排序
            $order = ['correct_count' => 'desc', 'total_count' => 'desc', 'ranking' => 'asc'];
        }
        
        // 获取当前日期
        $now = new \DateTime();
        // 获取当前年份和月份
        $currentYear = (int)$now->format('Y');
        $currentMonth = (int)$now->format('m');
        
        // 计算上月的年份和月份
        $lastMonthDate = clone $now;
        $lastMonthDate->sub(new \DateInterval('P1M'));
        $lastYear = (int)$lastMonthDate->format('Y');
        $lastMonth = (int)$lastMonthDate->format('m');
        
        // 初始化结果数组
        $result = [];
        
        // 获取本月排名
        $thisMonthResult = TenantExamRankingMonthly::where($this->searchWhere)
            ->where('year', $currentYear)
            ->where('month', $currentMonth)
            ->field(['id', 'user_id', 'correct_count', 'total_count', 'accuracy', 'integral', 'ranking', 'create_time', 'year', 'month'])
            ->with('user')
            ->limit($displayCount)
            ->order($order)
            ->select()
            ->toArray();
        
        // 为结果添加月标识
        foreach ($thisMonthResult as &$item) {
            $item['month_type'] = 'current';
        }
        $result = array_merge($result, $thisMonthResult);
        
        // 获取上月排名
        $lastMonthResult = TenantExamRankingMonthly::where($this->searchWhere)
            ->where('year', $lastYear)
            ->where('month', $lastMonth)
            ->field(['id', 'user_id', 'correct_count', 'total_count', 'accuracy', 'integral', 'ranking', 'create_time', 'year', 'month'])
            ->with('user')
            ->limit($displayCount)
            ->order($order)
            ->select()
            ->toArray();
        
        // 为结果添加月标识
        foreach ($lastMonthResult as &$item) {
            $item['month_type'] = 'last';
        }
        $result = array_merge($result, $lastMonthResult);
        
        // 查询我的本月排名
        if (isset($this->userId)) {
            $tenantId = request()->tenantId;
            // 为查询我的排名单独构建条件，不使用共享的searchWhere
            $myThisMonthRank = TenantExamRankingMonthly::where('tenant_id', $tenantId)
                ->where('year', $currentYear)
                ->where('month', $currentMonth)
                ->where('user_id', $this->userId)
                ->field(['id', 'user_id', 'correct_count', 'total_count', 'accuracy', 'integral', 'ranking', 'create_time', 'year', 'month'])
                ->with('user')
                ->find();
            
            if ($myThisMonthRank) {
                $myThisMonthRank = $myThisMonthRank->toArray();
                $myThisMonthRank['month_type'] = 'current';
                // 将我的本月排名添加到$result['month']中
                 $result['me'] = $myThisMonthRank;
            }else{
                // 查询我的上月排名
                $myLastMonthRank = TenantExamRankingMonthly::where('tenant_id', $tenantId)
                    ->where('year', $lastYear)
                    ->where('month', $lastMonth)
                    ->where('user_id', $this->userId)
                    ->field(['id', 'user_id', 'correct_count', 'total_count', 'accuracy', 'integral', 'ranking', 'create_time', 'year', 'month'])
                    ->with('user')
                    ->find();
                
                if ($myLastMonthRank) {
                    $myLastMonthRank = $myLastMonthRank->toArray();
                    $myLastMonthRank['month_type'] = 'last';
                    // 将我的上月排名添加到$result['month']中
                     $result['me'] = $myLastMonthRank;
                }else{
                    // 如果上月排名也不存在，将排名设为null
                    $result['me'] = [];
                }
            }
        }

        return $result;
    }

    /**
     * @notes 获取总排名列表
     * @return array
     * @author 精解析答题
     * @date 2025/08/24 13:16
     */
    public function totalLists(array $params): array
    {
        // 使用设置中的显示数量
        $displayCount = $this->rankingSettings['display_top_count'] ?? 20;
        // 使用设置中的排名维度，默认改为correct_count
        $rankingDimension = $this->rankingSettings['ranking_dimension'] ?? 'correct_count';
        
        // 构建排序条件
        $order = [];
        if ($rankingDimension === 'correct_count') {
            $order = ['correct_count' => 'desc', 'total_count' => 'desc', 'ranking' => 'asc'];
        } elseif ($rankingDimension === 'accuracy') {
            $order = ['accuracy' => 'desc', 'total_count' => 'desc', 'ranking' => 'asc'];
        } else {
            // 即使是积分维度，也按答对题数排序
            $order = ['correct_count' => 'desc', 'total_count' => 'desc', 'ranking' => 'asc'];
        }
        
        $result = TenantExamRankingTotal::where($this->searchWhere)
            ->field(['id', 'user_id', 'correct_count', 'total_count', 'accuracy', 'integral', 'ranking', 'create_time'])
            ->with('user')
            ->limit($displayCount)
            ->order($order)
            ->select()
            ->toArray();

        // 查询我的总排名
        if (isset($this->userId)) {
            $myTotalRank = TenantExamRankingTotal::where($this->searchWhere)
                ->where('user_id', $this->userId)
                ->with('user')
                ->field(['id', 'user_id', 'correct_count', 'total_count', 'accuracy', 'integral', 'ranking', 'create_time'])
                ->find();
            if ($myTotalRank) {
                $myTotalRank = $myTotalRank->toArray();
                //将我的总排名添加到$result['me']中
                $result['me'] = $myTotalRank;
            }else{
                // 如果总排名不存在，将总排名设为null
                $result['me'] = [];
            }
        }
        
        return $result;
    }
    
}
