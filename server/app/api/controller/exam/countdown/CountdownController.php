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

namespace app\api\controller\exam\countdown;

use app\api\controller\BaseApiController;
use app\api\logic\exam\countdown\CountdownLogic;
use think\response\Json;

/**
 * 倒计时控制器
 * Class CountdownController
 * @package app\api\controller\exam\countdown
 */
class CountdownController extends BaseApiController
{
    public array $notNeedLogin = ['lists', 'detail', 'followedList'];
    /**
     * 获取倒计时列表
     * @return Json
     */
    public function lists(): Json
    {
        $params = $this->request->get();
        $params['user_id'] = $this->userId;
        $result = CountdownLogic::lists($params);
        return $this->success('获取倒计时列表成功', $result['data']);
    }
    
    /**
     * 获取倒计时详情
     * @return Json
     */
    public function detail(): Json
    {
        $params = $this->request->get();
        $params['user_id'] = $this->userId;
        $result = CountdownLogic::detail($params);
        return $result['success'] ? $this->success('获取倒计时详情成功', $result['data']) : $this->fail($result['msg']);
    }
    
    /**
     * 创建倒计时
     * @return Json
     */
    public function create(): Json
    {
        $params = $this->request->post();
        $params['tenant_id'] = request()->tenantId; 
        $params['user_id'] = $this->userId;
        $result = CountdownLogic::add($params);
        return $result['success'] ? $this->success('创建倒计时成功', $result['data']) : $this->fail($result['msg']);
    }
    
    /**
     * 更新倒计时
     * @return Json
     */
    public function update(): Json
    {
        $params = $this->request->post();
        $params['user_id'] = $this->userId;
        $result = CountdownLogic::update($params);
        return $result['success'] ? $this->success('更新倒计时成功', $result['data']) : $this->fail($result['msg']);
    }
    
    /**
     * 删除倒计时
     * @return Json
     */
    public function delete(): Json
    {
        $params = $this->request->post();
        $params['user_id'] = $this->userId;
        $result = CountdownLogic::delete($params);
        return $result['success'] ? $this->success('删除倒计时成功', $result['data']) : $this->fail($result['msg']);
    }
    
    /**
     * 订阅/取消订阅倒计时
     * @return Json
     */
    public function follow(): Json
    {
        $params = $this->request->post();
        $params['user_id'] = $this->userId;
        $result = CountdownLogic::follow($params);
        return $result['success'] ? $this->success($result['msg'], $result['data']) : $this->fail($result['msg'], []);
    }
    
    /**
     * 获取用户订阅的倒计时列表
     * @return Json
     */
    public function followedList(): Json
    {
        $params = $this->request->get();
        $params['user_id'] = $this->userId;
        $result = CountdownLogic::followedList($params);
        return $result['success'] ? $this->success('获取用户订阅的倒计时列表成功', $result['data']) : $this->fail($result['msg']);
    }
    
    /**
     * 获取用户创建的倒计时列表
     * @return Json
     */
    public function createdList(): Json
    {
        if ($this->userId <= 0) {
            return $this->fail('请先登录');
        }
        
        $params = $this->request->get();
        $params['user_id'] = $this->userId;
        $result = CountdownLogic::createdList($params);
        return $result['success'] ? $this->success('获取用户创建的倒计时列表成功', $result['data']) : $this->fail($result['msg']);
    }
}