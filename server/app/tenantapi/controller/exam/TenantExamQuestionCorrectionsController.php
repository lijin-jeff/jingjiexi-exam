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
use app\tenantapi\lists\exam\TenantExamQuestionCorrectionsLists;
use app\tenantapi\logic\exam\TenantExamQuestionCorrectionsLogic;
use app\tenantapi\validate\exam\TenantExamQuestionCorrectionsValidate;


/**
 * corrections控制器
 * Class TenantExamQuestionCorrectionsController
 * @package app\tenantapi\controller\exam
 */
class TenantExamQuestionCorrectionsController extends BaseAdminController
{


    /**
     * @notes 获取corrections列表
     * @return \think\response\Json
     * @author 精解析题库
     * @date 2025/08/05 23:16
     */
    public function lists()
    {
        return $this->dataLists(new TenantExamQuestionCorrectionsLists());
    }


    /**
     * @notes 添加corrections
     * @return \think\response\Json
     * @author 精解析题库
     * @date 2025/08/05 23:16
     */
    public function add()
    {
        $params = (new TenantExamQuestionCorrectionsValidate())->post()->goCheck('add');
        $result = TenantExamQuestionCorrectionsLogic::add($params);
        if (true === $result) {
            return $this->success('添加成功', [], 1, 1);
        }
        return $this->fail(TenantExamQuestionCorrectionsLogic::getError());
    }


    /**
     * @notes 编辑corrections
     * @return \think\response\Json
     * @author 精解析题库
     * @date 2025/08/05 23:16
     */
    public function edit()
    {
        $params = (new TenantExamQuestionCorrectionsValidate())->post()->goCheck('edit');
        $result = TenantExamQuestionCorrectionsLogic::edit($params);
        if (true === $result) {
            return $this->success('编辑成功', [], 1, 1);
        }
        return $this->fail(TenantExamQuestionCorrectionsLogic::getError());
    }


    /**
     * @notes 删除corrections
     * @return \think\response\Json
     * @author 精解析题库
     * @date 2025/08/05 23:16
     */
    public function delete()
    {
        $params = (new TenantExamQuestionCorrectionsValidate())->post()->goCheck('delete');
        TenantExamQuestionCorrectionsLogic::delete($params);
        return $this->success('删除成功', [], 1, 1);
    }


    /**
     * @notes 获取corrections详情
     * @return \think\response\Json
     * @author 精解析题库
     * @date 2025/08/05 23:16
     */
    public function detail()
    {
        $params = (new TenantExamQuestionCorrectionsValidate())->goCheck('detail');
        $result = TenantExamQuestionCorrectionsLogic::detail($params);
        return $this->data($result);
    }


}