<?php
// +----------------------------------------------------------------------
// | 精解析答题开发团队介绍
// +----------------------------------------------------------------------
// | 欢迎你使用本套系统，本套系统由精解析答题开发团队全力开发。
// | 如果本套系统是商业系统，请严格遵守系统使用相关协议，出现违背协议的法律行为，所有违法行为均与精解析答题无关。
// | 官网地址：
// | 官方邮箱：
// | 精解析答题团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | 作者: 精解析答题开发团队
// +----------------------------------------------------------------------
namespace app\api\controller\exam;

use app\api\controller\BaseApiController;
use app\api\lists\exam\HelpLists;
use app\api\logic\exam\HelpLogic;

class HelpController extends BaseApiController
{
    public array $notNeedLogin = ['helpList', 'helpContent'];

    /**
     * 帮助中心列表
     * @email 
     * @link 
     * @author 精解析答题
     * @return \think\response\Json
     */
    public function helpList(): \think\response\Json
    {
        return $this->success('帮助中心列表查询成功', (new HelpLists())->lists());
    }

    /**
     * 帮助中心详情
     * @return \think\response\Json
     * @link 
     * @email 
     * @author 精解析答题
     */
    public function helpContent(): \think\response\Json
    {
       $params = $this->request->param();
        return $this->success('帮助中心详情查询成功', HelpLogic::helpContent($params));
    }

}