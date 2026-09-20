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
use app\tenantapi\lists\exam\TenantUserIntegralLogLists;
use app\tenantapi\logic\exam\TenantUserIntegralLogLogic;
use app\tenantapi\validate\exam\TenantUserIntegralLogValidate;


/**
 * integral控制器
 * Class TenantUserIntegralLogController
 * @package app\tenantapi\controller\exam
 */
class TenantUserIntegralLogController extends BaseAdminController
{


    /**
     * @notes 获取integral列表
     * @return \think\response\Json
     * @author 精解析题库
     * @date 2025/08/26 09:14
     */
    public function lists()
    {
        return $this->dataLists(new TenantUserIntegralLogLists());
    }


    /**
     * @notes 添加integral
     * @return \think\response\Json
     * @author 精解析题库
     * @date 2025/08/26 09:14
     */
    public function add()
    {
        $params = (new TenantUserIntegralLogValidate())->post()->goCheck('add');
        $result = TenantUserIntegralLogLogic::add($params);
        if (true === $result) {
            return $this->success('添加成功', [], 1, 1);
        }
        return $this->fail(TenantUserIntegralLogLogic::getError());
    }


    /**
     * @notes 编辑integral
     * @return \think\response\Json
     * @author 精解析题库
     * @date 2025/08/26 09:14
     */
    public function edit()
    {
        $params = (new TenantUserIntegralLogValidate())->post()->goCheck('edit');
        $result = TenantUserIntegralLogLogic::edit($params);
        if (true === $result) {
            return $this->success('编辑成功', [], 1, 1);
        }
        return $this->fail(TenantUserIntegralLogLogic::getError());
    }


    /**
     * @notes 删除integral
     * @return \think\response\Json
     * @author 精解析题库
     * @date 2025/08/26 09:14
     */
    public function delete()
    {
        $params = (new TenantUserIntegralLogValidate())->post()->goCheck('delete');
        TenantUserIntegralLogLogic::delete($params);
        return $this->success('删除成功', [], 1, 1);
    }


    /**
     * @notes 获取integral详情
     * @return \think\response\Json
     * @author 精解析题库
     * @date 2025/08/26 09:14
     */
    public function detail()
    {
        $params = (new TenantUserIntegralLogValidate())->goCheck('detail');
        $result = TenantUserIntegralLogLogic::detail($params);
        return $this->data($result);
    }


}