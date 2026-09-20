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

namespace app\api\controller;

use app\api\logic\SubscribeLogic;
use app\common\model\user\UserSubscribe;
use think\response\Json;

/**
 * 统一订阅控制器
 * Class SubscribeController
 * @package app\api\controller
 */
class UserSubscribeController extends BaseApiController
{
    /**
     * @notes 统一订阅/取消订阅
     * @return Json
     * @author 精解析题库
     * @date 2026/01/13
     */
    public function subscribe(): Json
    {
        $params = $this->request->post();
        $params['user_id'] = $this->userId;
        $result = SubscribeLogic::subscribe($params);
        return $result['success'] ? $this->success($result['msg'], $result['data']) : $this->fail($result['msg'], $result['data']);
    }
    
    /** 
     * 推送消息后更新状态（一次性授权消耗后标记） 
     * @return Json 
     */ 
    public function updatePushStatus(): Json 
    { 
        try { 
            // 1. 获取参数 
            $templateId = $this->request->post('template_id', ''); 
            
            // 2. 参数校验 
            if (empty($this->userId) || empty($templateId)) { 
                return $this->fail('参数缺失'); 
            } 
            
            // 3. 更新推送状态 
            $affected = UserSubscribe::where([ 
                'user_id'        => $this->userId, 
                'template_id'   => $templateId 
            ])->update(['is_pushed' => 1]); 
            
            if ($affected === 0) { 
                return $this->fail('无匹配的订阅记录'); 
            } 
            
            return $this->success('推送状态更新成功'); 
        } catch (\Exception $e) { 
            return $this->fail('更新失败：' . $e->getMessage()); 
        } 
    } 
    
    /** 
     * 校验该模板是否可推送（核心判断逻辑） 
     * @return Json 
     */ 
    public function checkCanPush(): Json 
    { 
        try { 
            // 1. 获取参数 
            $templateId = $this->request->get('template_id', ''); 
            
            // 2. 参数校验 
            if (empty($this->userId) || empty($templateId)) { 
                return $this->fail('参数缺失', ['can_push' => false]); 
            } 
            
            // 3. 查询订阅记录 
            $subscribe = UserSubscribe::where([ 
                'user_id'        => $this->userId, 
                'template_id'   => $templateId 
            ])->find(); 
            
            // 无记录 → 不可推送 
            if (!$subscribe) { 
                return $this->success('无订阅记录', ['can_push' => false]); 
            } 
            
            // 4. 核心判断：未推送 + 7天内（7天=604800秒=604800000毫秒） 
            $sevenDays = 7 * 24 * 60 * 60 * 1000; 
            $isTimeout = time() - $subscribe->subscribe_time > $sevenDays / 1000; // 秒时间戳比较 
            $canPush = !$subscribe->is_pushed && !$isTimeout; 
            
            return $this->success($canPush ? '可推送' : ($isTimeout ? '授权已超时' : '已推送过'), [ 
                'can_push'  => $canPush 
            ]); 
        } catch (\Exception $e) { 
            return $this->fail('校验失败：' . $e->getMessage(), ['can_push' => false]); 
        } 
    }

    /**
     * @notes 获取订阅状态
     * @return Json
     * @author 精解析题库
     * @date 2026/01/13
     */
    public function status(): Json
    {
        $params = $this->request->get();
        $params['user_id'] = $this->userId;
        $result = SubscribeLogic::getStatus($params);
        return $result['success'] ? $this->success($result['msg'], $result['data']) : $this->fail($result['msg'], $result['data']);
    }
    
    /** 
     * 记录用户一次性订阅授权状态（前端订阅成功后调用） 
     * @return Json 
     */ 
    public function recordSubscribe(): Json 
    {
        try {
            // 1. 获取参数 
            $params = $this->request->post();
            $params['user_id'] = $this->userId;
            
            // 2. 参数校验 
            if (empty($this->userId) || empty($params['template_id'])) { 
                return $this->fail('参数缺失'); 
            } 
            // 3. 记录订阅状态 
            $result = SubscribeLogic::recordSubscribe($params); 
            if (!$result['success']) { 
                return $this->fail($result['msg']); 
            } 
            
            return $this->success('订阅状态记录成功'); 
        } catch (\Exception $e) { 
            return $this->fail('记录失败：' . $e->getMessage()); 
        } 
    }
    
    /**
     * @notes 发送一次性订阅消息
     * @return Json
     * @author 精解析题库
     * @date 2026/01/15
     */
    public function sendOneTimeSubscribeMsg(): Json
    {
        $params = $this->request->post();
        $params['user_id'] = $this->userId;
        return SubscribeLogic::sendOneTimeSubscribeMsg($params);
    }
    
    /**
     * @notes 查询订阅状态接口（按类型和关联ID）
     * @return Json
     * @author 精解析题库
     * @date 2026/01/15
     */
    public function checkSubscribeStatus(): Json
    {
        $params = $this->request->get();
        $params['user_id'] = $this->userId;
        $result = SubscribeLogic::getStatus($params);
        return $result['success'] ? $this->success($result['msg'], $result['data']) : $this->fail($result['msg'], $result['data']);
    }
}