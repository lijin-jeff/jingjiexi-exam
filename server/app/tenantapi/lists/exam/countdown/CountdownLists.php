<?php
// +----------------------------------------------------------------------
// | likeadmin快速开发前后端分离管理后台（PHP版）
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// +----------------------------------------------------------------------
// | 开源版本可自由商用，可去除界面版权logo
// +----------------------------------------------------------------------
// | gitee下载：https://gitee.com/likeshop_gitee/likeadmin
// | github下载：https://github.com/likeshop-github/likeadmin
// | 访问官网：https://www.likeadmin.cn
// | likeadmin团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeadminTeam
// +----------------------------------------------------------------------

namespace app\tenantapi\lists\exam\countdown;

use app\tenantapi\lists\BaseAdminDataLists;
use app\common\lists\ListsSearchInterface;
use app\common\model\exam\countdown\TenantExamCountdown;
use app\common\model\user\UserSubscribe;
/**
 * 倒计时列表
 * Class CountdownLists
 * @package app\tenantapi\lists\exam\countdown
 */
class CountdownLists extends BaseAdminDataLists implements ListsSearchInterface
{
    /**
     * @notes 设置搜索条件
     * @return array
     * @author likeadmin
     * @date 2024/01/08
     */
    public function setSearch(): array
    {
        return [
            '=' => ['status', 'tenant_id'],
            '%like%' => ['title'],
        ];
    }
    
    /**
     * @notes 设置支持排序字段
     * @return array
     * @author likeadmin
     * @date 2024/01/08
     */
    public function setSortFields(): array
    {
        return ['sort' => 'sort', 'id' => 'id', 'create_time' => 'create_time', 'update_time' => 'update_time'];
    }
    
    /**
     * @notes 设置默认排序
     * @return array
     * @author likeadmin
     * @date 2024/01/08
     */
    public function setDefaultOrder(): array
    {
        return ['sort' => 'desc', 'id' => 'desc'];
    }
    
    /**
     * @notes 获取列表数据
     * @return array
     * @author likeadmin
     * @date 2024/01/08
     */
    public function lists(): array
    {
        $list = TenantExamCountdown::where($this->searchWhere)
            ->limit($this->limitOffset, $this->limitLength)
            ->order($this->sortOrder)
            ->select()
            ->toArray();
        
        return $this->format($list);
    }
    
    /**
     * @notes 获取总数
     * @return int
     * @author likeadmin
     * @date 2024/01/08
     */
    public function count(): int
    {
        return TenantExamCountdown::where($this->searchWhere)->count();
    }
    
    /**
     * @notes 处理列表数据
     * @param array $list
     * @return array
     * @author likeadmin
     * @date 2024/01/08
     */
    public function format(array $list): array
    {
        $result = [];
        if (!empty($list)) {
            foreach ($list as $item) {
                // 计算剩余天数
                $days = TenantExamCountdown::calculateDays($item['target_date']);
                
                // 获取订阅数量
                $followCount = UserSubscribe::where(
                    ['related_id' => $item['id'], 'type' => 'countdown']
                    )->count();
                
                $result[] = [
                    'id' => $item['id'],
                    'title' => $item['title'],
                    'target_date' => $item['target_date'],
                    'days' => $days,
                    'description' => $item['description'] ?? '',
                    'follow_count' => $followCount,
                    'sort' => $item['sort'],
                    'status' => $item['status'],
                    'create_time' => $item['create_time'],
                    'update_time' => $item['update_time'],
                ];
            }
        }
        return $result;
    }
    
    /**
     * @notes 扩展数据
     * @return array
     * @author likeadmin
     * @date 2024/01/08
     */
    public function extend()
    {
        return [];
    }
}