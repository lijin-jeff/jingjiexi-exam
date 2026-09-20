<?php
declare(strict_types=1);
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 访问官网：
// | 官方邮箱：
// | 精解析答题系统开发者版权所有，拥有最终解释权。
namespace app\common\enum;

class IntegralEnum
{
    const User_REGISTER = 1;//注册奖励
    const User_LOGIN = 2;//登录奖励
    const User_EXAMINATION = 3;//答题奖励
    const User_INVITE = 4;//邀请新用户
    const User_SHARE_MINI_PROGRAM = 5;//分享小程序
    const User_RECEIVE_SUBSCRIPTION_MESSAGE = 6;//接收订阅消息

    const DOWNLOAD_INTEGRAL = 7;//下载消耗
    const EXCHANGE_INTEGRAL = 8;//兑换会员消耗
    /**
     * 获取积分名称
     * @param int $integralType
     * @return string
     */
    public static function IntegralTitle(int $integralType): string
    {
        switch ($integralType) {
            case self::User_REGISTER:
                return '注册奖励';//注册奖励
            case self::User_LOGIN:
                return '登录奖励';//登录奖励
            case self::User_EXAMINATION:
                return '答题奖励';//答题奖励
            case self::User_INVITE:
                return '邀请新用户奖励';//邀请新用户
            case self::User_SHARE_MINI_PROGRAM:
                return '分享小程序奖励';//分享小程序
            case self::User_RECEIVE_SUBSCRIPTION_MESSAGE:
                return '接收订阅消息奖励';//接收订阅消息
            case self::DOWNLOAD_INTEGRAL:
                return '下载资源消耗积分';//下载消耗
            case self::EXCHANGE_INTEGRAL:
                return '兑换会员消耗积分';//兑换会员消耗
            default:
                return '未知积分';
        }
    }

    //从缓存中获取积分数量
    public static function IntegralAmount(int $integralType): float
    {
        try {
            //从缓存中获取积分设置
            $integralSettings = \think\facade\Cache::get('tenant_integral_settings_' . request()->tenantId);
            
            // 如果缓存为空，尝试从数据库加载
            if (empty($integralSettings)) {
                $model = \app\common\model\exam\TenantIntegralSettings::where('tenant_id', '=', request()->tenantId)->find();
                if ($model) {
                    $integralSettings = $model->toArray();
                    // 更新缓存
                    \think\facade\Cache::set('tenant_integral_settings_' . request()->tenantId, $integralSettings, 3600);
                } else {
                    // 使用默认积分设置
                    $integralSettings = [
                        'register_integral' => 10.00,
                        'login_integral' => 5.00,
                        'daily_integral_limit' => 50.00,
                        'invite_user_integral' => 20.00,
                        'share_integral' => 2.00,
                        'subscribe_integral' => 1.00,
                        'download_integral' => 10.00,
                        'exchange_integral' => 100.00
                    ];
                }
            }
            
            // 根据积分类型获取对应积分数量
            $integralAmount = 0.00;
            switch ($integralType) {
                case self::User_REGISTER:
                    $integralAmount = floatval($integralSettings['register_integral'] ?? 10.00);
                    break;
                case self::User_LOGIN:
                    $integralAmount = floatval($integralSettings['login_integral'] ?? 5.00);
                    break;
                case self::User_EXAMINATION:
                    $integralAmount = floatval($integralSettings['daily_integral_limit'] ?? 50.00);
                    break;
                case self::User_INVITE:
                    $integralAmount = floatval($integralSettings['invite_user_integral'] ?? 20.00);
                    break;
                case self::User_SHARE_MINI_PROGRAM:
                    $integralAmount = floatval($integralSettings['share_integral'] ?? 2.00);
                    break;
                case self::User_RECEIVE_SUBSCRIPTION_MESSAGE:
                    $integralAmount = floatval($integralSettings['subscribe_integral'] ?? 1.00);
                    break;
                case self::DOWNLOAD_INTEGRAL:
                    $integralAmount = floatval($integralSettings['download_integral'] ?? 10.00);
                    break;
                case self::EXCHANGE_INTEGRAL:
                    $integralAmount = floatval($integralSettings['exchange_integral'] ?? 100.00);
                    break;
                default:
                    $integralAmount = 0.00;
                    break;
            }
            
            return $integralAmount;
        } catch (\Exception $e) {
            \think\facade\Log::error('获取积分数量异常: ' . $e->getMessage());
            // 异常时返回默认积分
            if ($integralType == self::User_LOGIN) {
                return 5.00;
            }
            return 0.00;
        }
    }
}