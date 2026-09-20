<?php
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 访问官网：
// | 官方邮箱：
// | 精解析答题系统开发者版权所有，拥有最终解释权。

namespace app\common\model\exam;


use app\common\model\BaseModel;
use think\model\concern\SoftDelete;


/**
 * 会员激活记录模型
 * Class TenantExamUserActivationRecord
 * @package app\common\model\exam
 */
class TenantExamUserActivationRecord extends BaseModel
{
    use SoftDelete;

    protected $name = 'tenant_exam_user_activation_record';

    protected $deleteTime = 'delete_time';

}