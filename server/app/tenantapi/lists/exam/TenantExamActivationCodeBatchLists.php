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
use app\common\model\exam\TenantExamActivationCodeBatch;
use app\common\lists\ListsSearchInterface;


/**
 * codeBatch列表
 * Class TenantExamActivationCodeBatchLists
 * @package app\tenantapi\listsexam
 */
class TenantExamActivationCodeBatchLists extends BaseAdminDataLists implements ListsSearchInterface
{


    /**
     * @notes 设置搜索条件
     * @return \string[][]
     * @author likeadmin
     * @date 2025/08/06 08:55
     */
    public function setSearch(): array
    {
        return [
            '=' => ['status'],
            '%like%' => ['remark'],
        ];
    }


    /**
     * @notes 获取codeBatch列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author likeadmin
     * @date 2025/08/06 08:55
     */
    public function lists(): array
    {
        return TenantExamActivationCodeBatch::where($this->searchWhere)
            ->field(['id', 'duration_days', 'total_count', 'used_count', 'remark', 'status'])
            ->limit($this->limitOffset, $this->limitLength)
            ->order(['id' => 'desc'])
            ->select()
            ->toArray();
    }


    /**
     * @notes 获取codeBatch数量
     * @return int
     * @author likeadmin
     * @date 2025/08/06 08:55
     */
    public function count(): int
    {
        return TenantExamActivationCodeBatch::where($this->searchWhere)->count();
    }

}