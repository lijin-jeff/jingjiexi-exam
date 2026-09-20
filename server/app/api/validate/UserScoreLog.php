<?php
declare(strict_types=1);
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 精解析答题系统开发者版权所有，拥有最终解释权。
namespace app\api\validate;

use app\common\model\BaseModel;
use think\model\concern\SoftDelete;

class UserIntegralLog extends BaseModel
{
    use SoftDelete;

    protected $name = 'tenant_user_integral_log';

    protected $deleteTime = 'delete_time';
}