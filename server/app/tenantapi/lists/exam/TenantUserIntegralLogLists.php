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
use app\common\model\exam\TenantUserIntegralLog;
use app\common\lists\ListsSearchInterface;


/**
 * integral列表
 * Class TenantUserIntegralLogLists
 * @package app\tenantapi\listsexam
 */
class TenantUserIntegralLogLists extends BaseAdminDataLists implements ListsSearchInterface 
{


    /**
     * @notes 设置搜索条件
     * @return \string[][]
     * @author 精解析题库
     * @date 2025/08/26 09:14
     */
    public function setSearch(): array
    {
        return [
            '=' => ['tenant_id', 'user_id', 'action', 'change_amount', 'remark', 'extra', 'title', 'change_type', 'action_type'],
        ];
    }


    /**
     * @notes 获取integral列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 精解析题库
     * @date 2025/08/26 09:14
     */
    public function lists(): array
    {
        return TenantUserIntegralLog::where($this->searchWhere)
            ->field(['id', 'user_id', 'action', 'change_amount', 'remark', 'create_time', 'title', 'change_type', 'action_type'])
            ->limit($this->limitOffset, $this->limitLength)
            ->order(['id' => 'desc'])
            ->select()
            ->toArray();
    }


    /**
     * @notes 获取integral数量
     * @return int
     * @author 精解析题库
     * @date 2025/08/26 09:14
     */
    public function count(): int
    {
        return TenantUserIntegralLog::where($this->searchWhere)->count();
    }

}