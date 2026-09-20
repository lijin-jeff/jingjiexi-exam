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
namespace app\api\controller\resource;

use app\api\controller\BaseApiController;
use app\api\logic\resource\ResourceLogic;
use app\api\validate\CommonValidate;

class ResourceController extends BaseApiController
{
    public array $notNeedLogin = ['categoryList', 'resourceContent', 'resourceList'];

    /**
     * 资源分类树
     * @return \think\response\Json
     */
    public function categoryList(): \think\response\Json
    {
        $params = $this->request->param();
        return $this->success('分类查询成功', ResourceLogic::categoryList($params));
    }

    /**
     * 资源列表
     * @email 
     * @link 
     * @author 精解析答题
     * @return \think\response\Json
     */
    public function resourceList(): \think\response\Json
    {

       $params = (new CommonValidate())->get()->goCheck('page');
        return $this->success('资源查询成功', ResourceLogic::resourceList($params));
    }

    /**
     * 资源详情
     * @return \think\response\Json
     * @link 
     * @email 
     * @author 精解析答题
     */
    public function resourceContent(): \think\response\Json
    {
       $params = (new CommonValidate())->get()->goCheck('uid');
       $params['user_uid'] = $this->userId;
        return $this->success('资源查询成功', ResourceLogic::resourceContent($params));
    }

    /**
     * 资源下载
     * @return \think\response\Json
     * @link 
     * @email 
     * @author 精解析答题
     */
    public function resourceDownload(): \think\response\Json
    {
       $params = (new CommonValidate())->get()->goCheck('uid');
        return $this->success('资源查询成功', ResourceLogic::resourceDownload($params));
    }
    
    /**
     * 积分下载资源
     * @return \think\response\Json
     * @link 
     * @email 
     * @author 精解析答题
     */
    public function resourceDownloadPoints(): \think\response\Json
    {
       $params = (new CommonValidate())->get()->goCheck('uid');
       $params['user_uid'] = $this->userId;
        $result = ResourceLogic::resourceDownloadPoints($params);
        if($result) {
            return $this->success('资源下载成功', $result);
        }
        return $this->fail(ResourceLogic::getError());
    }

    /**
     * 资源收藏
     * @return \think\response\Json
     * @link 
     * @email 
     * @author 精解析答题
     */
    public function resourceCollection(): \think\response\Json
    {
       $params = (new CommonValidate())->post()->goCheck('uid');
       $params['user_uid'] = $this->userId;
        if (ResourceLogic::resourceCollection($params)) {
            return $this->success('收藏成功');
        }
        return $this->fail(ResourceLogic::getError());
    }
}