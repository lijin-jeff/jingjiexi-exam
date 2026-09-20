<?php
declare(strict_types=1);
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 访问官网：https://www.jingjiexi.com
// | 官方邮箱：jingjiexi@outlook.com
// | 精解析答题系统开发者版权所有，拥有最终解释权。
namespace app\api\controller;

use app\api\logic\ConfigLogic;
use think\response\Json;

class ConfigController extends BaseApiController
{
    /**
     * 字典类型列表
     * @return Json
     * @date 2025/5/10 01:57
     * @author 精解析答题
     */
    public function dictType(): Json
    {
        return $this->success('字典类型查询成功', ConfigLogic::dictType());
    }

    /**
     * 字典数据列表
     * @return Json
     * @date 2025/5/10 01:57
     * @author 精解析答题
     */
    public function dictData(): Json
    {
        return $this->success('字段数据查询成功', ConfigLogic::dictData($this->request->all()));
    }
}