<?php
// +----------------------------------------------------------------------
// | likeadmin快速开发前后端分离管理后台（PHP版）
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// +----------------------------------------------------------------------
// | 开源版本可自由商用，可去除界面版权logo
// +----------------------------------------------------------------------
// | gitee下载：https://gitee.com/likeshop_gitee/likeadmin
// | github下载：https://github.com/likeshop-github/likeadmin
// | 访问官网：https://www.likeadmin.cn
// | likeadmin团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeadminTeam
// +----------------------------------------------------------------------

namespace app\tenantapi\controller\exam\countdown;

use app\tenantapi\controller\BaseAdminController;
use app\tenantapi\lists\exam\countdown\CountdownLists;
use app\tenantapi\logic\exam\countdown\CountdownLogic;
use app\tenantapi\validate\exam\countdown\CountdownValidate;

/**
 * 倒计时控制器
 * Class CountdownController
 * @package app\tenantapi\controller\exam\countdown
 */
class CountdownController extends BaseAdminController
{

    /**
     * @notes  查看倒计时列表
     * @return \think\response\Json
     * @author likeadmin
     * @date 2024/01/08
     */
    public function lists(): \think\response\Json     
    {
        return $this->dataLists(new CountdownLists($this->tenantId));
    }

    /**
     * @notes  添加倒计时
     * @return \think\response\Json
     * @author likeadmin
     * @date 2024/01/08
     */
    public function add(): \think\response\Json     
    {
        $params = (new CountdownValidate())->post()->goCheck('add');
        $params['tenant_id'] = $this->tenantId;
        try {
            CountdownLogic::add($params);        
            return $this->success('添加成功', [], 1, 1);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * @notes  编辑倒计时
     * @return \think\response\Json
     * @author likeadmin
     * @date 2024/01/08
     */
    public function edit(): \think\response\Json     
    {
        $params = (new CountdownValidate())->post()->goCheck('edit');
        try {
            CountdownLogic::edit($params);
            return $this->success('编辑成功', [], 1, 1);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * @notes  删除倒计时
     * @return \think\response\Json
     * @author likeadmin
     * @date 2024/01/08
     */
    public function delete(): \think\response\Json     
    {
        $params = (new CountdownValidate())->post()->goCheck('delete');
        try {
            CountdownLogic::delete($params['id']);
            return $this->success('删除成功', [], 1, 1);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * @notes  倒计时详情
     * @return \think\response\Json
     * @author likeadmin
     * @date 2024/01/08
     */
    public function detail(): \think\response\Json     
    {
        $params = (new CountdownValidate())->goCheck('detail');
        try {
            $result = CountdownLogic::detail($params['id']);
            return $this->data($result);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * @notes  更改倒计时状态
     * @return \think\response\Json
     * @author likeadmin
     * @date 2024/01/08
     */
    public function updateStatus(): \think\response\Json     
    {
        $params = (new CountdownValidate())->post()->goCheck('status');
        try {
            $result = CountdownLogic::updateStatus($params);
            return $this->success('修改成功', [], 1, 1);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }
}
