<?php

namespace app\tenantapi\controller\exam;


use app\common\controller\BaseLikeAdminController;
use app\tenantapi\controller\BaseAdminController;
use app\tenantapi\lists\exam\TenantExamKnowledgeLists;
use app\tenantapi\logic\exam\TenantExamKnowledgeLogic;
use app\tenantapi\validate\exam\TenantExamKnowledgeValidate;


/**
 * 题库章节知识点控制器
 * Class TenantExamCKnowledgeController
 * @package app\platform\controller\exam
 */
class TenantExamKnowledgeController extends BaseAdminController
{


    /**
     * @notes 获取题库章节知识点列表
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/04/12 15:53
     */
    public function lists(): \think\response\Json
    {
        return $this->dataLists(new TenantExamKnowledgeLists());
    }


    /**
     * @notes 添加题库章节知识点
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/04/12 15:53
     */
    public function add(): \think\response\Json
    {
        $params = (new TenantExamKnowledgeValidate())->post()->goCheck('add');
        $result = TenantExamKnowledgeLogic::add(array_merge($params, ['tenant_id' => $this->tenantId]));
        if (true === $result) {
            return $this->success('添加成功', [], 1, 1);
        }
        return $this->fail(TenantExamKnowledgeLogic::getError());
    }


    /**
     * @notes 编辑题库章节知识点
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/04/12 15:53
     */
    public function edit(): \think\response\Json
    {
        $params = (new TenantExamKnowledgeValidate())->post()->goCheck('edit');
        $result = TenantExamKnowledgeLogic::edit(array_merge($params, ['tenant_id' => $this->tenantId]));
        if (true === $result) {
            return $this->success('编辑成功', [], 1, 1);
        }
        return $this->fail(TenantExamKnowledgeLogic::getError());
    }


    /**
     * @notes 删除题库章节知识点
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/04/12 15:53
     */
    public function delete(): \think\response\Json
    {
        $params = (new TenantExamKnowledgeValidate())->post()->goCheck('delete');
        TenantExamKnowledgeLogic::delete(array_merge($params, ['tenant_id' => $this->tenantId]));
        return $this->success('删除成功', [], 1, 1);
    }


    /**
     * @notes 获取题库章节知识点详情
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/04/12 15:53
     */
    public function detail(): \think\response\Json
    {
        $params = (new TenantExamKnowledgeValidate())->goCheck('detail');
        $params['chapter_uid'] = $this->request->param('chapter_uid', '');
        $result = TenantExamKnowledgeLogic::detail(array_merge($params, ['tenant_id' => $this->tenantId]));
        return $this->data($result);
    }

    /**
     * 题库章节知识点树
     * @return \think\response\Json
     */
    public function categoryTree(): \think\response\Json
    {
        //print_r($this->request->param('chapter_uid'));
        return $this->success('章节知识点获取成功', TenantExamKnowledgeLogic::categoryTree(['tenant_id' => $this->tenantId, 'chapter_uid' => $this->request->param('chapter_uid')]));
    }

    /**
     * 获取一级分类
     * @return \think\response\Json
     */
    public function parentList(): \think\response\Json
    {
        return $this->success('查询成功', TenantExamKnowledgeLogic::parentList(array_merge($this->request->all(), ['tenant_id' => $this->tenantId])));
    }
}