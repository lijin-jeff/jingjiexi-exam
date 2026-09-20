<?php

namespace app\tenantapi\controller\exam;

use app\tenantapi\controller\BaseAdminController;
use app\tenantapi\lists\exam\TenantExamChapterLists;
use app\tenantapi\logic\exam\TenantExamChapterLogic;
use app\tenantapi\validate\exam\TenantExamChapterValidate;

/**
 * 题库章节控制器
 * Class TenantExamChapterController
 * @package app\platform\controller\exam
 */
class TenantExamChapterController extends BaseAdminController
{


    /**
     * @notes 获取题库章节列表
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/04/12 15:53
     */
    public function lists(): \think\response\Json
    {
        return $this->dataLists(new TenantExamChapterLists($this->tenantId));
    }


    /**
     * @notes 添加题库章节
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/04/12 15:53
     */
    public function add(): \think\response\Json
    {
        $params = (new TenantExamChapterValidate())->post()->goCheck('add');
        $result = TenantExamChapterLogic::add(array_merge($params, ['tenant_id' => $this->tenantId]));
        if (true === $result) {
            return $this->success('添加成功', [], 1, 1);
        }
        return $this->fail(TenantExamChapterLogic::getError());
    }


    /**
     * @notes 编辑题库章节
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/04/12 15:53
     */
    public function edit(): \think\response\Json
    {
        $params = (new TenantExamChapterValidate())->post()->goCheck('edit');
        $result = TenantExamChapterLogic::edit(array_merge($params, ['tenant_id' => $this->tenantId]));
        if (true === $result) {
            return $this->success('编辑成功', [], 1, 1);
        }
        return $this->fail(TenantExamChapterLogic::getError());
    }


    /**
     * @notes 删除题库章节
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/04/12 15:53
     */
    public function delete(): \think\response\Json
    {
        $params = (new TenantExamChapterValidate())->post()->goCheck('delete');
        TenantExamChapterLogic::delete(array_merge($params, ['tenant_id' => $this->tenantId]));
        return $this->success('删除成功', [], 1, 1);
    }


    /**
     * @notes 获取题库章节详情
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/04/12 15:53
     */
    public function detail(): \think\response\Json
    {

        $params = (new TenantExamChapterValidate())->goCheck('detail');
        //print_r($params);exit;
        $result = TenantExamChapterLogic::detail(array_merge($params));
        //print_r($result);
        return $this->data($result);
    }

    /**
     * 题库章节树
     * @return \think\response\Json
     * @date 2025/4/1 03:42
     */
    public function categoryTree()
    {
        return $this->dataLists(new TenantExamChapterLists($this->tenantId));
    }

    /**
     * 获取一级分类
     */
    public function parentList(): \think\response\Json
    {
        return $this->success('查询成功', TenantExamChapterLogic::parentList(array_merge($this->request->all(), ['tenant_id' => $this->tenantId])));
    }
    
    /**
     * @notes 章节导出
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2026/01/18
     */
    public function export(): \think\response\Json
    {
        return $this->dataLists(new TenantExamChapterLists($this->tenantId));
    }

    /**
     * @notes 从txt文件导入章节
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2026/03/08
     */
    public function importFromTxt(): \think\response\Json
    {
        $params = $this->request->post();
        $result = TenantExamChapterLogic::importFromTxt(array_merge($params, ['tenant_id' => $this->tenantId]));
        if ($result === true) {
            return $this->success('导入成功', [], 1, 1);
        }
        return $this->fail(TenantExamChapterLogic::getError());
    }
}