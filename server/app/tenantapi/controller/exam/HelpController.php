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
use app\tenantapi\lists\exam\HelpLists;
use app\tenantapi\logic\exam\HelpLogic;
use app\tenantapi\validate\exam\HelpValidate;

/**
 * 帮助中心控制器
 * Class HelpController
 * @package app\tenantapi\controller\exam
 */
class HelpController extends BaseAdminController
{

    /**
     * @notes  查看帮助中心列表
     * @return \think\response\Json
     * @author heshihu
     * @date 2022/2/22 9:47
     */
    public function lists()
    {
        return $this->dataLists(new HelpLists());
    }

    /**
     * @notes  添加帮助中心
     * @return \think\response\Json
     * @author heshihu
     * @date 2022/2/22 9:57
     */
    public function add()
    {

        $params = (new HelpValidate())->post()->goCheck('add');
        $params['tenant_id'] = $this->tenantId;
        $result = HelpLogic::add($params);        
        if (false === $result) {
            return $this->fail(HelpLogic::getError());
        }
        return $this->success('添加成功', [], 1, 1);
    }

    /**
     * @notes  编辑帮助中心
     * @return \think\response\Json
     * @author heshihu
     * @date 2022/2/22 10:12
     */
    public function edit()
    {
        $params = (new HelpValidate())->post()->goCheck('edit');
        $result = HelpLogic::edit($params);
        if (true === $result) {
            return $this->success('编辑成功', [], 1, 1);
        }
        return $this->fail(HelpLogic::getError());
    }

    /**
     * @notes  删除帮助中心
     * @return \think\response\Json
     * @author heshihu
     * @date 2022/2/22 10:17
     */
    public function delete()
    {
        $params = (new HelpValidate())->post()->goCheck('delete');
        HelpLogic::delete($params);
        return $this->success('删除成功', [], 1, 1);
    }

    /**
     * @notes  帮助中心详情
     * @return \think\response\Json
     * @author heshihu
     * @date 2022/2/22 10:15
     */
    public function detail()
    {
        $params = (new HelpValidate())->goCheck('detail');
        $result = HelpLogic::detail($params);
        return $this->data($result);
    }


    /**
     * @notes  更改帮助中心状态
     * @return \think\response\Json
     * @author heshihu
     * @date 2022/2/22 10:18
     */
    public function updateStatus()
    {
        $params = (new HelpValidate())->post()->goCheck('status');
        $result = HelpLogic::updateStatus($params);
        if (false === $result) {
            return $this->fail(HelpLogic::getError());
        }
        return $this->success('修改成功', [], 1, 1);
    }


}