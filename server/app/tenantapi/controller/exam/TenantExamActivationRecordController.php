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
use app\tenantapi\lists\exam\TenantExamActivationRecordLists;
use app\tenantapi\logic\exam\TenantExamActivationRecordLogic;
use app\tenantapi\validate\exam\TenantExamActivationRecordValidate;


/**
 * activationRecord控制器
 * Class TenantExamActivationRecordController
 * @package app\tenantapi\controller\exam
 */
class TenantExamActivationRecordController extends BaseAdminController
{


    /**
     * @notes 获取activationRecord列表
     * @return \think\response\Json
     * @author 精解析题库
     * @date 2025/08/07 15:20
     */
    public function lists()
    {
        return $this->dataLists(new TenantExamActivationRecordLists());
    }


    /**
     * @notes 添加activationRecord
     * @return \think\response\Json
     * @author 精解析题库
     * @date 2025/08/07 15:20
     */
    public function add()
    {
        $params = (new TenantExamActivationRecordValidate())->post()->goCheck('add');
        $result = TenantExamActivationRecordLogic::add($params);
        if (true === $result) {
            return $this->success('添加成功', [], 1, 1);
        }
        return $this->fail(TenantExamActivationRecordLogic::getError());
    }


    /**
     * @notes 编辑activationRecord
     * @return \think\response\Json
     * @author 精解析题库
     * @date 2025/08/07 15:20
     */
    public function edit()
    {
        $params = (new TenantExamActivationRecordValidate())->post()->goCheck('edit');
        $result = TenantExamActivationRecordLogic::edit($params);
        if (true === $result) {
            return $this->success('编辑成功', [], 1, 1);
        }
        return $this->fail(TenantExamActivationRecordLogic::getError());
    }


    /**
     * @notes 删除activationRecord
     * @return \think\response\Json
     * @author 精解析题库
     * @date 2025/08/07 15:20
     */
    public function delete()
    {
        $params = (new TenantExamActivationRecordValidate())->post()->goCheck('delete');
        TenantExamActivationRecordLogic::delete($params);
        return $this->success('删除成功', [], 1, 1);
    }


    /**
     * @notes 获取activationRecord详情
     * @return \think\response\Json
     * @author 精解析题库
     * @date 2025/08/07 15:20
     */
    public function detail()
    {
        $params = (new TenantExamActivationRecordValidate())->goCheck('detail');
        $result = TenantExamActivationRecordLogic::detail($params);
        return $this->data($result);
    }


}