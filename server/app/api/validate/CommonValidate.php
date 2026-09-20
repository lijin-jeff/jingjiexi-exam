<?php
declare(strict_types=1);
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 访问官网：精解析答题
// | 官方邮箱：精解析答题
// | 精解析答题系统开发者版权所有，拥有最终解释权。
namespace app\api\validate;

use app\common\validate\BaseValidate;

class CommonValidate extends BaseValidate
{
    protected $rule = [
        'page_no'   => 'require|number|min:1',
        'page_size' => 'require|number|min:1|max:20',
        'uid'       => 'require',
    ];

    protected $message = [
        'page_no.require'   => '页数不能为空',
        'page_no.number'    => '页数格式错误',
        'page_no.min'       => '格式格式错误',
        'page_size.require' => '分页不能为空',
        'page_size.number'  => '分页格式错误',
        'page_size.min'     => '分页格式错误',
        'page_size.max'     => '分页格式错误',
        'uid.require'       => '编号不能为空',
    ];

    public function scenePage(): CommonValidate
    {
        return $this->only(['page_no', 'page_size']);
    }

    public function sceneUid(): CommonValidate
    {
        return $this->only(['uid']);
    }
}