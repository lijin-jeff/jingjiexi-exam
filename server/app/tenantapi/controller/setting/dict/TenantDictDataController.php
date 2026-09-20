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

namespace app\tenantapi\controller\setting\dict;

use app\tenantapi\controller\BaseAdminController;
use app\tenantapi\lists\setting\dict\TenantDictDataLists;
use app\tenantapi\logic\setting\dict\TenantDictDataLogic;
use app\tenantapi\validate\setting\dict\TenantDictDataValidate;


/**
 * 字典数据
 * Class DictDataController
 * @package app\tenantapi\controller\setting\dict
 */
class TenantDictDataController extends BaseAdminController
{

    /**
     * @notes 获取字典数据列表
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/6/20 16:35
     */
    public function lists()
    {
        return $this->dataLists(new TenantDictDataLists());
    }


    /**
     * @notes 添加字典数据
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/6/20 17:13
     */
    public function add()
    {
        $params = (new TenantDictDataValidate())->post()->goCheck('add');
        $params['tenant_id'] = $this->tenantId;
        TenantDictDataLogic::save($params);
        return $this->success('添加成功', [], 1, 1);
    }


    /**
     * @notes 编辑字典数据
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/6/20 17:13
     */
    public function edit()
    {
        $params = (new TenantDictDataValidate())->post()->goCheck('edit');
        $params['tenant_id'] = $this->tenantId;
        TenantDictDataLogic::save($params);
        return $this->success('编辑成功', [], 1, 1);
    }


    /**
     * @notes 删除字典数据
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/6/20 17:13
     */
    public function delete()
    {
        $params = (new TenantDictDataValidate())->post()->goCheck('id');
        TenantDictDataLogic::delete($params);
        return $this->success('删除成功', [], 1, 1);
    }


    /**
     * @notes 获取字典详情
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/6/20 17:14
     */
    public function detail()
    {
        $params = (new TenantDictDataValidate())->goCheck('id');
        $result = TenantDictDataLogic::detail($params); 
        return $this->data($result);
    }


}