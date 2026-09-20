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
use app\common\model\exam\TenantSettingBanner;
use app\common\lists\ListsSearchInterface;


/**
 * tenantBanner列表
 * Class TenantSettingBannerLists
 * @package app\tenantapi\listsexam
 */
class TenantSettingBannerLists extends BaseAdminDataLists implements ListsSearchInterface   
{


    /**
     * @notes 设置搜索条件
     * @return \string[][]
     * @author likeadmin
     * @date 2025/08/19 08:53
     */
    public function setSearch(): array
    {
        return [
            '=' => ['title', 'is_show', 'sort', 'position', 'image_type'],
            // client字段不在这里处理，因为前端传数组，在lists()和count()中自定义处理
        ];
    }


    /**
     * @notes 获取tenantBanner列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author likeadmin
     * @date 2025/08/19 08:53
     */
    public function lists(): array
    {
        // 构建查询条件
        $query = TenantSettingBanner::where($this->searchWhere);
        
        // 处理client字段的数组查询
        if (isset($this->params['client']) && !empty($this->params['client'])) {
            $clientValue = $this->params['client'];
            // 如果是数组，转换为字符串后使用LIKE查询
            if (is_array($clientValue)) {
                $clientValue = implode(',', $clientValue);
            }
            $query->where('client', 'like', '%' . $clientValue . '%');
        }
        
        return $query
            ->field(['id', 'uid', 'title', 'is_show', 'sort', 'position', 'client', 'image', 'image_type', 'url', 'icon', 'close_position', 'display_mode'])
            ->limit($this->limitOffset, $this->limitLength)
            ->order(['id' => 'desc'])
            ->select()
            ->toArray();
    }


    /**
     * @notes 获取tenantSettingBanner数量
     * @return int
     * @author likeadmin
     * @date 2025/08/19 08:53
     */
    public function count(): int
    {
        // 构建查询条件
        $query = TenantSettingBanner::where($this->searchWhere);
        
        // 处理client字段的数组查询
        if (isset($this->params['client']) && !empty($this->params['client'])) {
            $clientValue = $this->params['client'];
            // 如果是数组，转换为字符串后使用LIKE查询
            if (is_array($clientValue)) {
                $clientValue = implode(',', $clientValue);
            }
            $query->where('client', 'like', '%' . $clientValue . '%');
        }
        
        return $query->count();
    }

}