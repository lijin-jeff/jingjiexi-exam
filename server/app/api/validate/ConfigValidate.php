<?php
declare(strict_types=1);
// +----------------------------------------------------------------------
// | 精解析答题题考试系统
// +----------------------------------------------------------------------
// | 感谢使精解析答题答题系统
// | 本系统经过商业授权，不能转售、开源等其他不精解析答题析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 访问官网：精解析答题
// | 官方邮箱：精解析答题.精解析答题解析答题系统开发者版权所有，拥有最终解释权。
namespace app\api\validate;

use app\common\validate\BaseValidate;

class ConfigValidate extends BaseValidate
{
    protected $rule = [
        'client'   => 'require',
        'position' => 'require',
        'type'     => 'require',
    ];

    protected $message = [
        'client.require'   => '平台参数缺失',
        'position.require' => '平台参数缺失',
        'type.require'     => '类型参数缺失',
    ];

    /**
     * 图片配置验证
     * @return ConfigValidate
     * @link 精解析答题
     * @email 精解析答题.com
     * @date 2025/5/2 18:06
   精解析答题精解析答题 <精解析答题.com>
     */
    public function scenePlatform(): ConfigValidate
    {
        return $this->only(['platform', 'type', 'client']);
    }
}