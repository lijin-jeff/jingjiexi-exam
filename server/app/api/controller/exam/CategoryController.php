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
use app\api\logic\exam\CategoryLogic;

class CategoryController extends BaseApiController
{
    public array $notNeedLogin = ['category'];

    /**
     * 试题类型列表
     * @return \think\response\Json
     * @date 2025/5/2 23:52
     * @author 精解析答题
     */
    public function category(): \think\response\Json
    {
        return $this->success('分类获取成功', CategoryLogic::categoryTree());
    }
}