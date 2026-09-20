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
use app\common\model\exam\TenantExamRankingMonthly;
use app\common\lists\ListsSearchInterface;


/**
 * tenantRankingMonthly列表
 * Class TenantExamRankingMonthlyLists
 * @package app\tenantapi\lists\exam
 */
class TenantExamRankingMonthlyLists extends BaseAdminDataLists implements ListsSearchInterface
{


    /**
     * @notes 设置搜索条件
     * @return \string[][]
     * @author 精解析答题
     * @date 2025/08/24 10:49
     */
    public function setSearch(): array
    {
        // 搜索条件,用户ID,统计日期
        return [
            '=' => ['user_id', 'year','month'],
        ];
    }


    /**
     * @notes 获取tenantRankingMonthly列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 精解析答题
     * @date 2025/08/24 10:49
     */
    public function lists(): array
    {
        return TenantExamRankingMonthly::where($this->searchWhere)
            ->field(['id', 'user_id', 'year','month','total_count','correct_count','accuracy','integral','ranking', 'create_time'])
            ->limit($this->limitOffset, $this->limitLength)
            ->order(['id' => 'desc'])
            ->select()
            ->toArray();
    }


    /**
     * @notes 获取tenantRankingMonthly数量
     * @return int
     * @author 精解析答题
     * @date 2025/08/24 10:49
     */
    public function count(): int
    {
        return TenantExamRankingMonthly::where($this->searchWhere)->count();
    }

}