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

namespace app\tenantapi\lists\exam;


use app\tenantapi\lists\BaseAdminDataLists;
use app\common\model\exam\TenantExamRankingWeekly;
use app\common\lists\ListsSearchInterface;


/**
 * tenantRankingWeekly列表
 * Class TenantExamRankingWeeklyLists
 * @package app\tenantapi\lists\exam    
 */
class TenantExamRankingWeeklyLists extends BaseAdminDataLists implements ListsSearchInterface
{


    /**
     * @notes 设置搜索条件
     * @return \string[][]
     * @author likeadmin
     * @date 2025/08/24 10:48
     */
    public function setSearch(): array
    {
        // 搜索条件,用户ID,周数,年份
        return [
            '=' => ['user_id', 'week_number', 'year'],
        ];
    }


    /**
     * @notes 获取tenantRankingWeekly列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author likeadmin
     * @date 2025/08/24 10:48
     */
    public function lists(): array
    {
        return TenantExamRankingWeekly::where($this->searchWhere)
            ->field(['id', 'user_id', 'correct_count', 'total_count', 'accuracy', 'integral', 'ranking', 'week_start_date', 'week_end_date', 'week_number', 'year'])
            ->limit($this->limitOffset, $this->limitLength)
            ->order(['id' => 'desc'])
            ->select()
            ->toArray();
    }


    /**
     * @notes 获取tenantRankingWeekly数量
     * @return int
     * @author likeadmin
     * @date 2025/08/24 10:48
     */
    public function count(): int
    {
        return TenantExamRankingWeekly::where($this->searchWhere)->count();
    }

}