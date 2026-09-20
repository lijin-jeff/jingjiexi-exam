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
use app\common\model\exam\TenantExamActivationCode;
use app\common\lists\ListsSearchInterface;


/**
 * code列表
 * Class TenantExamActivationCodeLists
 * @package app\tenantapi\listsexam
 */
class TenantExamActivationCodeLists extends BaseAdminDataLists implements ListsSearchInterface
{


    /**
     * @notes 设置搜索条件
     * @return \string[][]
     * @author likeadmin
     * @date 2025/08/06 16:34
     */
    public function setSearch(): array
    {
        return [
            '=' => ['batch_id', 'code', 'status', 'activation_time'],
        ];
    }

    /**
     * @notes 获取code列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author likeadmin
     * @date 2025/08/06 16:34
     */
    public function lists(): array
    {
        $params = $this->request->param(); 
       
        return TenantExamActivationCode::where($this->searchWhere)
            ->field(['id', 'code', 'status', 'activation_time'])
            ->limit($this->limitOffset, $this->limitLength)
            ->order(['id' => 'desc'])
            ->select()
            ->toArray();
    }


    /**
     * @notes 获取code数量
     * @return int
     * @author likeadmin
     * @date 2025/08/06 16:34
     */
    public function count(): int
    {
        return TenantExamActivationCode::where($this->searchWhere)->count();
    }

}