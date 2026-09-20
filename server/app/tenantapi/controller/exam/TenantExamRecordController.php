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
use app\tenantapi\lists\exam\TenantExamRecordLists;
use app\tenantapi\logic\exam\TenantExamRecordLogic;
use app\tenantapi\validate\exam\TenantExamRecordValidate;


/**
 * 做题记录表控制器
 * Class TenantExamRecordController
 * @package app\tenantapi\controller\exam
 */
class TenantExamRecordController extends BaseAdminController
{


    /**
     * @notes 获取做题记录表列表
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/07/19 14:47
     */
    public function lists(): \think\response\Json
    {
        return $this->dataLists(new TenantExamRecordLists());
    }


    /**
     * @notes 添加做题记录表
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/07/19 14:47
     */
    public function add(): \think\response\Json
    {
        $params = (new TenantExamRecordValidate())->post()->goCheck('add');
        $result = TenantExamRecordLogic::add($params);
        if (true === $result) {
            return $this->success('添加成功', [], 1, 1);
        }
            return $this->fail(TenantExamRecordLogic::getError());
    }


    /**
     * @notes 编辑做题记录表
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/07/19 14:47
     */
    public function edit(): \think\response\Json
    {
        $params = (new TenantExamRecordValidate())->post()->goCheck('edit');
        $result = TenantExamRecordLogic::edit($params);
        if (true === $result) {
            return $this->success('编辑成功', [], 1, 1);
        }
        return $this->fail(TenantExamRecordLogic::getError());
    }


    /**
     * @notes 删除做题记录表
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/07/19 14:47
     */
    public function delete(): \think\response\Json
    {
        $params = (new TenantExamRecordValidate())->post()->goCheck('delete');
        $result = TenantExamRecordLogic::delete($params);
        if (!$result) {
            return $this->fail(TenantExamRecordLogic::getError() ?: '删除失败');
        }
        return $this->success('删除成功');
    }


    /**
     * @notes 获取做题记录表详情
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/07/19 14:47
     */
    public function detail()
    {
        $params = (new TenantExamRecordValidate())->goCheck('detail');
        $result = TenantExamRecordLogic::detail($params);
        return $this->data($result);
    }


}