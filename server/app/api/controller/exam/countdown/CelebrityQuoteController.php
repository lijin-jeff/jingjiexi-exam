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
use app\api\logic\exam\countdown\CelebrityQuoteLogic;
use think\response\Json;

/**
 * 倒计时-名人名言控制器
 * Class CelebrityQuoteController
 * @package app\api\controller\exam\countdown 
 */
class CelebrityQuoteController extends BaseApiController
{
     public array $notNeedLogin = ['random', 'lists'];
    /**
     * 获取随机倒计时-名人名言
     * @return Json
     */
    public function random(): Json
    {
        $quote = CelebrityQuoteLogic::random();
        return $this->success('获取随机倒计时-名人名言成功', $quote['data']);
    }
    
    /**
     * 获取倒计时-名人名言列表  
     * @return Json
     */
    public function lists(): Json
    {
        $params = $this->request->get();
        $result = CelebrityQuoteLogic::lists($params);
        return $this->success('获取名人名言列表成功', $result['data']);
    }
}