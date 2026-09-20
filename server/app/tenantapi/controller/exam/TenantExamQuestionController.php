<?php
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 精解析答题系统开发者版权所有，拥有最终解释权。


namespace app\tenantapi\controller\exam;


use app\tenantapi\controller\BaseAdminController;
use app\tenantapi\lists\exam\TenantExamQuestionLists;
use app\tenantapi\logic\exam\TenantExamQuestionLogic;
use app\tenantapi\validate\exam\TenantExamQuestionValidate;


/**
 * 试题管理控制器
 * Class TenantExamQuestionController
 * @package app\tenantapi\controller\exam
 */
class TenantExamQuestionController extends BaseAdminController
{
    /**
     * @notes 获取试题管理列表
     * @return \think\response\Json
     * @date 2025/04/10 19:22
     */
    public function lists(): \think\response\Json
    {
        (new TenantExamQuestionValidate())->get()->goCheck("list");
        return $this->dataLists(new TenantExamQuestionLists($this->tenantId));
    }


    /**
     * @notes 添加试题管理
     * @return \think\response\Json
     * @date 2025/04/10 19:22
     */
    public function add(): \think\response\Json
    {
        //print_r($this->request->all());
        $params = (new TenantExamQuestionValidate())->post()->goCheck('add');
        
        $result = TenantExamQuestionLogic::add(array_merge($params, $this->tenantInfo));
        if (true === $result) {
            return $this->success('添加成功', [], 1, 1);
        }
        return $this->fail(TenantExamQuestionLogic::getError());
    }


    /**
     * @notes 编辑试题管理
     * @return \think\response\Json
     * @date 2025/04/10 19:22
     */
    public function edit(): \think\response\Json
    {
        $params = (new TenantExamQuestionValidate())->post()->goCheck('edit');
        $result = TenantExamQuestionLogic::edit(array_merge($params, $this->tenantInfo));
        if (true === $result) {
            return $this->success('编辑成功', [], 1, 1);
        }
        return $this->fail(TenantExamQuestionLogic::getError());
    }


    /**
     * @notes 删除试题管理
     * @return \think\response\Json
     * @date 2025/04/10 19:22
     */
    public function delete(): \think\response\Json
    {
        $params = (new TenantExamQuestionValidate())->post()->goCheck('delete');
        TenantExamQuestionLogic::delete(array_merge($params, $this->tenantInfo));
        return $this->success('删除成功', [], 1, 1);
    }


    /**
     * @notes 获取试题管理详情
     * @return \think\response\Json
     * @date 2025/04/10 19:22
     */
    public function detail(): \think\response\Json
    {
        $params = (new TenantExamQuestionValidate())->goCheck('detail');
        //print_r($params);exit;
        $result = TenantExamQuestionLogic::detail(array_merge($params, $this->tenantInfo));
        return $this->data($result);
    }

    /**
     * 文本批量导入
     * @return \think\response\Json
     */
    public function textAdd(): \think\response\Json
    {
        $result = TenantExamQuestionLogic::textAdd(array_merge($this->request->all(), $this->tenantInfo));
        if (true === $result) {
            return $this->success('添加成功', [], 1, 1);
        }
        return $this->fail(TenantExamQuestionLogic::getError());
    }

    /**
     * 通过题库 id 查询试题
     * @return \think\response\Json
     */
    public function questionListByLibrary(): \think\response\Json
    {
        return $this->success('查询成功', TenantExamQuestionLogic::questionListByLibrary(array_merge($this->request->all(), $this->tenantInfo)));
    }
}