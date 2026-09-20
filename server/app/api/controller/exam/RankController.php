<?php
declare(strict_types=1);
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 精解析答题系统开发者版权所有，拥有最终解释权。
namespace app\api\controller\exam;

use app\api\controller\BaseApiController;
use app\api\validate\CommonValidate;
use app\api\logic\exam\RankLogic;
use app\api\lists\exam\RankLists;

class RankController extends BaseApiController
{
    public array $notNeedLogin = [
        'getRankSettings',
        'detail',
        'rankingList',
    ];

    // 获取排名设置
    public function getRankSettings(): \think\response\Json
    {
        $result = (new RankLogic())->getRankingSettings($this->request->param());
        if ($result !== null) {
            return $this->success('获取成功', $result);
        }
        return $this->fail(RankLogic::getError());
    }

     /**
     * 排行列表
     * @email 
     * @link 
     * @author 精解析答题
     * @return \think\response\Json
     */
    public function rankingList(): \think\response\Json
    {
        $params = $this->request->param();
        // 添加当前登录用户ID
        if ($this->userId) {
            $params['user_id'] = $this->userId;
        }
        
        $rankLists = new RankLists();
        $rankLists->initialize($params); // 传递参数初始化
        $result = $rankLists->lists();
        return $this->success('排行列表查询成功', $result);
    }

    /**
     * @notes 获取每日排名列表
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/08/24 13:16
     */
    public function dailyLists(): \think\response\Json
    {
        (new CommonValidate())->get()->goCheck('page');
        $params = $this->request->param();
        $params['user_id'] = $this->userId;
        $result = (new RankLists())->dailyLists($params);
        if (true === $result) { 
            return $this->success('获取成功', $result);
        }
        return $this->fail('获取排名列表失败');
    }

    /**
     * @notes 获取每周排名列表
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/08/24 13:16
     */
    public function weeklyLists(): \think\response\Json
    {
        (new CommonValidate())->get()->goCheck('page');
        $params = $this->request->param();
        $params['user_id'] = $this->userId;
        $result = (new RankLists())->weeklyLists($params);
        if (true === $result) { 
            return $this->success('获取成功', $result);
        }
        return $this->fail('获取排名列表失败');
    }

 

    /**
     * @notes 获取每月排名列表
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/08/24 13:16
     */
    public function monthlyLists(): \think\response\Json
    {
        (new CommonValidate())->get()->goCheck('page');
        $params = $this->request->param();
        $params['user_id'] = $this->userId;
        $result = (new RankLists())->monthlyLists($params);

        if (true === $result) { 
            return $this->success('获取成功', $result);
        }
        return $this->fail('获取排名列表失败');
    }

    /**
     * @notes 获取总排名列表
     * @return \think\response\Json
     * @author 精解析答题
     * @date 2025/08/24 13:16
     */
    public function totalLists(): \think\response\Json
    {
        (new CommonValidate())->get()->goCheck('page');
        $params = $this->request->param();
        $params['user_id'] = $this->userId;
        $result = (new RankLists())->totalLists($params);
        if (true === $result) { 
            return $this->success('获取成功', $result);
        }
        return $this->fail('获取排名列表失败');
    }
        
}