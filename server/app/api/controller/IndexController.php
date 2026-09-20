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


use app\api\logic\IndexLogic;
use think\response\Json;
use app\api\validate\ConfigValidate;
use app\api\logic\ConfigLogic;
use app\api\logic\setting\web\WebSettingLogic;
use app\api\validate\CommonValidate;

/**
 * 首页
 * Class IndexController
 * @package app\api\controller
 */
class IndexController extends BaseApiController
{


    public array $notNeedLogin = ['index', 'config', 'policy', 'decorate', 'imageConfig','dataQuery', 'otherSettings', 'rankSettings', 'integralSettings', 'subscribeTemplates'];


    /**
     * @notes 首页数据
     * @return Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/9/21 19:15
     */
    public function index()
    {
        $result = IndexLogic::getIndexData();
        return $this->data($result);
    }


    /**
     * @notes 全局配置
     * @return Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/9/21 19:41
     */
    public function config()
    {
        $result = IndexLogic::getConfigData();
        return $this->data($result);
    }


    /**
     * @notes 政策协议
     * @return Json
     * @author 段誉
     * @date 2022/9/20 20:00
     */
    public function policy()
    {
        $type = $this->request->get('type/s', '');
        $result = IndexLogic::getPolicyByType($type);
        return $this->data($result);
    }


    /**
     * @notes 装修信息
     * @return Json
     * @author 段誉
     * @date 2022/9/21 18:37
     */
    public function decorate()
    {
        $type = $this->request->get('type/d');
        $result = IndexLogic::getDecorate($type);
        return $this->data($result);
    }

    /**
     * 获取图片先关类型的配置
     * @return Json
     * @date 2025/5/2 16:22
     * @author 精解析答题
     */
    public function imageConfig(): Json
    {
       // print_r($this->request->param());
        $params = (new ConfigValidate())->get()->goCheck('platform');
        //print_r($params);
        try {
            $imageList = ConfigLogic::imageList($params);
            return $this->success('获取成功', $imageList);
        } catch (\Exception $e) {
            return $this->fail('获取失败: ' . $e->getMessage());
        }
    }

    /**
     * 系统全局查询
     * @return Json
     * @author 精解析答题
     */
    public function dataQuery(): Json
    {
        (new CommonValidate())->get()->goCheck('page');
        return $this->success('查询成功', IndexLogic::dataQuery($this->request->param()));
    }

    /**
     * @description 获取排行榜设置
     * @return { Promise }
     */
        public function rankSettings(): Json
        {
            return $this->success('查询成功', IndexLogic::rankSettings());
        }
    

    /**
     * @description 获取其他设置
     * @return { Promise }
     */
        public function otherSettings(): Json
        {
            return $this->success('查询成功', IndexLogic::otherSettings());
        }

    /**
     * @description 获取积分设置
     * @return { Promise }
     */
        public function integralSettings(): Json
        {
            return $this->success('查询成功', IndexLogic::integralSettings());
        }

    /**
     * @description 模板订阅
     * @return { Promise }
     */
        public function templateSubscribe(): Json
        {
            return $this->success('查询成功', IndexLogic::templateSubscribe($this->request->param()));
        }
        
    /**
     * @description 获取订阅模板ID列表
     * @return { Promise }
     */
        public function subscribeTemplates(): Json
        {
            return $this->success('查询成功', IndexLogic::getSubscribeTemplates());
        }

    /**
     * @notes 加入收藏
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/9/20 17:01
     */
    public function addCollect()
    {
        $qid = $this->request->post('id/d');
        $type = $this->request->post('type/d', 1);
        IndexLogic::addCollect($qid, $this->userId, $type);
        return $this->success('操作成功');
    }


    /**
     * @notes 取消收藏
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/9/20 17:01
     */
    public function cancelCollect()
    {
        $qid = $this->request->post('id/d');
        IndexLogic::cancelCollect($qid, $this->userId);
        return $this->success('操作成功');
    }
    
    /**
     * @notes 获取网站信息
     * @return \think\response\Json
     * @author 段誉
     * @date 2021/12/28 15:44
     */
    public function getWebsite()
    {
        $result = WebSettingLogic::getWebsiteInfo();
        return $this->data($result);
    }
}