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
use app\tenantapi\lists\exam\TenantExamCommentLists;
use app\tenantapi\logic\exam\TenantExamCommentLogic;
use app\tenantapi\validate\exam\TenantExamCommentValidate;


/**
 * comment控制器
 * Class TenantExamCommentController
 * @package app\tenantapi\controller\exam
 */
class TenantExamCommentController extends BaseAdminController
{


    /**
     * @notes 获取comment列表
     * @return \think\response\Json
     * @author likeadmin
     * @date 2025/07/19 23:21
     */
    public function lists(): \think\response\Json   
    {
        $params = $this->request->param();
        return $this->dataLists(new TenantExamCommentLists($params));
    }


    /**
     * @notes 添加comment
     * @return \think\response\Json
     * @author likeadmin
     * @date 2025/07/19 23:21
     */
    public function add(): \think\response\Json
    {
        $params = (new TenantExamCommentValidate())->post()->goCheck('add');
        $result = TenantExamCommentLogic::add($params);
        if (true === $result) {
            return $this->success('添加成功', [], 1, 1);
        }
        return $this->fail(TenantExamCommentLogic::getError());
    }


    /**
     * @notes 编辑comment
     * @return \think\response\Json
     * @author likeadmin
     * @date 2025/07/19 23:21
     */
    public function edit(): \think\response\Json
    {
        $params = (new TenantExamCommentValidate())->post()->goCheck('edit');
        $result = TenantExamCommentLogic::edit($params);
        if (true === $result) {
            return $this->success('编辑成功', [], 1, 1);
        }
        return $this->fail(TenantExamCommentLogic::getError());
    }


    /**
     * @notes 删除comment
     * @return \think\response\Json
     * @author likeadmin
     * @date 2025/07/19 23:21
     */
    public function delete(): \think\response\Json
    {
        $params = (new TenantExamCommentValidate())->post()->goCheck('delete');
        TenantExamCommentLogic::delete($params);
        return $this->success('删除成功', [], 1, 1);
    }


    /**
     * @notes 获取comment详情
     * @return \think\response\Json
     * @author likeadmin
     * @date 2025/07/19 23:21
     */
    public function detail(): \think\response\Json
    {
        $params = (new TenantExamCommentValidate())->goCheck('detail');
        $result = TenantExamCommentLogic::detail($params);
        return $this->data($result);
    }


}