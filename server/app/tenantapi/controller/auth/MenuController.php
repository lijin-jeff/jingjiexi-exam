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
declare (strict_types=1);

namespace app\tenantapi\controller\auth;


use app\tenantapi\controller\BaseAdminController;
use app\tenantapi\lists\auth\MenuLists;
use app\tenantapi\logic\auth\MenuLogic;
use app\tenantapi\validate\auth\MenuValidate;


/**
 * 系统菜单权限
 * Class MenuController
 * @package app\tenantapi\controller\auth
 */
class MenuController extends BaseAdminController
{

    /**
     * @notes 获取菜单路由
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/6/29 17:41
     */
    public function route()
    {
        $result = MenuLogic::getMenuByAdminId($this->adminId);
        return $this->data($result);
    }


    /**
     * @notes 获取菜单列表
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/6/29 17:23
     */
    public function lists()
    {
        try {
            $result = $this->dataLists(new MenuLists());
            //print_r($result);
            //abort(500,'页面不存在');
            return $result;
        } catch (\Exception $e) {
            // 记录错误日志
            throw new \RuntimeException('获取资源分类数量失败: ' . $e->getMessage(), 500);
            // 返回友好错误信息
            return $this->fail('获取菜单列表失败，请稍后重试');
        }
    }


    /**
     * @notes 菜单详情
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/6/30 10:07
     */
    public function detail()
    {
        $params = (new MenuValidate())->goCheck('detail');
        return $this->data(MenuLogic::detail($params));
    }


    /**
     * @notes 添加菜单
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/6/30 10:07
     */
    public function add()
    {
        $params = (new MenuValidate())->post()->goCheck('add');
        MenuLogic::add($params);
        return $this->success('操作成功', [], 1, 1);
    }


    /**
     * @notes 编辑菜单
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/6/30 10:07
     */
    public function edit()
    {
        $params = (new MenuValidate())->post()->goCheck('edit');
        MenuLogic::edit($params);
        return $this->success('操作成功', [], 1, 1);
    }


    /**
     * @notes 删除菜单
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/6/30 10:07
     */
    public function delete()
    {
        $params = (new MenuValidate())->post()->goCheck('delete');
        MenuLogic::delete($params);
        return $this->success('操作成功', [], 1, 1);
    }


    /**
     * @notes 更新状态
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/7/6 17:04
     */
    public function updateStatus()
    {
        $params = (new MenuValidate())->post()->goCheck('status');
        MenuLogic::updateStatus($params);
        return $this->success('操作成功', [], 1, 1);
    }


    /**
     * @notes 获取菜单数据
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/10/13 11:03
     */
    public function all()
    {
        $result = MenuLogic::getAllData();
        return $this->data($result);
    }


}