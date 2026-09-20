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


namespace app\tenantapi\controller\exam;


use app\tenantapi\controller\BaseAdminController;
use app\tenantapi\lists\exam\TenantExamRankingWeeklyLists;
use app\tenantapi\logic\exam\TenantExamRankingWeeklyLogic;
use app\tenantapi\validate\exam\TenantExamRankingWeeklyValidate;


/**
 * tenantRankingWeekly控制器
 * Class TenantExamRankingWeeklyController
 * @package app\tenantapi\controller\exam
 */
class TenantExamRankingWeeklyController extends BaseAdminController
{


    /**
     * @notes 获取tenantRankingWeekly列表
     * @return \think\response\Json
     * @author likeadmin
     * @date 2025/08/24 10:48
     */
    public function lists()
    {
        return $this->dataLists(new TenantExamRankingWeeklyLists());
    }


    /**
     * @notes 添加tenantRankingWeekly
     * @return \think\response\Json
     * @author likeadmin
     * @date 2025/08/24 10:48
     */
    public function add()
    {
        $params = (new TenantExamRankingWeeklyValidate())->post()->goCheck('add');
        $result = TenantExamRankingWeeklyLogic::add($params);
        if (true === $result) {
            return $this->success('添加成功', [], 1, 1);
        }
        return $this->fail(TenantExamRankingWeeklyLogic::getError());
    }


    /**
     * @notes 编辑tenantRankingWeekly
     * @return \think\response\Json
     * @author likeadmin
     * @date 2025/08/24 10:48
     */
    public function edit()
    {
        $params = (new TenantExamRankingWeeklyValidate())->post()->goCheck('edit');
        $result = TenantExamRankingWeeklyLogic::edit($params);
        if (true === $result) {
            return $this->success('编辑成功', [], 1, 1);
        }
        return $this->fail(TenantExamRankingWeeklyLogic::getError());
    }


    /**
     * @notes 删除tenantRankingWeekly
     * @return \think\response\Json
     * @author likeadmin
     * @date 2025/08/24 10:48
     */
    public function delete()
    {
        $params = (new TenantExamRankingWeeklyValidate())->post()->goCheck('delete');
        TenantExamRankingWeeklyLogic::delete($params);
        return $this->success('删除成功', [], 1, 1);
    }


    /**
     * @notes 获取tenantRankingWeekly详情
     * @return \think\response\Json
     * @author likeadmin
     * @date 2025/08/24 10:48
     */
    public function detail()
    {
        $params = (new TenantExamRankingWeeklyValidate())->goCheck('detail');
        $result = TenantExamRankingWeeklyLogic::detail($params);
        return $this->data($result);
    }


}