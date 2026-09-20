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
use app\tenantapi\lists\exam\TenantExamRankingMonthlyLists;
use app\tenantapi\logic\exam\TenantExamRankingMonthlyLogic;
use app\tenantapi\validate\exam\TenantExamRankingMonthlyValidate;


/**
 * tenantRankingMonthly控制器
 * Class TenantExamRankingMonthlyController
 * @package app\tenantapi\controller\exam
 */
class TenantExamRankingMonthlyController extends BaseAdminController
{


    /**
     * @notes 获取tenantRankingMonthly列表
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/08/24 10:49
     */
    public function lists()
    {
        return $this->dataLists(new TenantExamRankingMonthlyLists());
    }


    /**
     * @notes 添加tenantRankingMonthly
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/08/24 10:49
     */
    public function add()
    {
        $params = (new TenantExamRankingMonthlyValidate())->post()->goCheck('add');
        $result = TenantExamRankingMonthlyLogic::add($params);
        if (true === $result) {
            return $this->success('添加成功', [], 1, 1);
        }
        return $this->fail(TenantExamRankingMonthlyLogic::getError());
    }


    /**
     * @notes 编辑tenantRankingMonthly
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/08/24 10:49
     */
    public function edit()
    {
        $params = (new TenantExamRankingMonthlyValidate())->post()->goCheck('edit');
        $result = TenantExamRankingMonthlyLogic::edit($params);
        if (true === $result) {
            return $this->success('编辑成功', [], 1, 1);
        }
        return $this->fail(TenantExamRankingMonthlyLogic::getError());
    }


    /**
     * @notes 删除tenantRankingMonthly
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/08/24 10:49
     */
    public function delete()
    {
        $params = (new TenantExamRankingMonthlyValidate())->post()->goCheck('delete');
        TenantExamRankingMonthlyLogic::delete($params);
        return $this->success('删除成功', [], 1, 1);
    }


    /**
     * @notes 获取tenantRankingMonthly详情
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/08/24 10:49
     */
    public function detail()
    {
        $params = (new TenantExamRankingMonthlyValidate())->goCheck('detail');
        $result = TenantExamRankingMonthlyLogic::detail($params);
        return $this->data($result);
    }


}