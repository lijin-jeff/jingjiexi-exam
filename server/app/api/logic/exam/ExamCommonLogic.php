<?php
declare(strict_types=1);
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 精解析答题系统开发者版权所有，拥有最终解释权。
namespace app\api\logic\exam;

use app\common\logic\BaseLogic;
use app\common\model\exam\TenantExamActivationCode;
use app\common\model\exam\TenantExamUserActivationRecord;
use app\common\model\exam\TenantExamActivationRecord;
use app\api\logic\IntegralLogic;
use app\common\model\user\User;
use think\facade\Db;
use app\common\enum\{IntegralEnum};

class ExamCommonLogic extends BaseLogic
{
    /**
     * 积分兑换会员
     * @param array $params
     * @return bool
     * @throws \Exception
     */
    public static function vipIntegralPay(array $params): bool
    {
        // 验证参数
        if (empty($params['vip_type']) || empty($params['integral_required']) || empty($params['user_uid'])) {
            self::setError('参数错误');
            return false;
        }
        
        Db::startTrans();
        try {
            // 获取用户信息
            $userId = $params['user_uid'];
            $user = User::query()
                ->where(['id' => $userId])
                ->findOrEmpty();
            
            if ($user->isEmpty()) {
                self::setError('用户不存在');
                Db::rollback();
                return false;
            }
            
            // 验证积分是否足够
            if ($user['integral'] < $params['integral_required']) {
                self::setError('积分不足');
                Db::rollback();
                return false;
            }
            
            $now = time();
            $endTime = $now + $params['duration'] * 24 * 3600;
            
            // 更新用户会员状态和有效期
            if (!self::updateUserVipStatus($userId, $endTime)) {
                self::setError('更新用户会员状态失败');
                Db::rollback();
                return false;
            }
            
            // 插入会员激活记录
            $params['remark'] = '积分兑换：' . $params['package_name'] . '会员';
            if (!self::insertUserVipActivationRecord($params, $endTime)) {
                self::setError('插入会员激活记录失败');
                Db::rollback();
                return false;
            }
                     
            // 如果是积分激活，插入用户积分日志并扣减用户积分
            if (!IntegralLogic::addIntegral($userId, 2, IntegralEnum::EXCHANGE_INTEGRAL, (float)$params['integral_required'], $params['remark'])) {
                self::setError('扣减用户积分失败');
                Db::rollback();
                return false;
            }

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::setError('积分兑换失败，请稍后重试');
            return false;
        }
    }
    
    /**
     * 激活码兑换会员
     * @param array $params
     * @return bool
     * @throws \Exception
     */
    public static function vipActivationCode(array $params): bool
    {
        // 验证参数
        if (empty($params['activation_code']) || empty($params['user_uid'])) {
            self::setError('参数错误');
            return false;
        }
        

        Db::startTrans();
        try {
            // 1. 获取激活码信息
            // 先不加状态条件查询，查看激活码是否存在
            $activationCode = TenantExamActivationCode::query()
                ->where(['code' => $params['activation_code']])
                ->with(['batch'])
                ->findOrEmpty();



            $now = time();
            // 3. 检查激活码是否存在，是否禁用，是否使用，批次是否禁用
            if ($activationCode->isEmpty()) {
                self::setError('激活码不存在或已被使用');
                Db::rollback();
                return false;
            }
            
            // 检查激活码状态
            if ($activationCode['status'] != 0) {
                self::setError('激活码不可用');
                Db::rollback();
                return false;
            }
            
            // 检查批次信息是否存在
            if (empty($activationCode['batch'])) {
                self::setError('激活码批次信息不存在');
                Db::rollback();
                return false;
            }
            
            // 检查批次状态
            if ($activationCode['batch']['status'] == 1) {
                self::setError('激活码批次已被禁用');
                Db::rollback();
                return false;
            }
            
            // 2. 获取用户信息
            $userId = $params['user_uid'];
            $user = User::query()
                ->where(['id' => $userId])
                ->findOrEmpty();
            
            if ($user->isEmpty()) {
                self::setError('用户不存在');
                Db::rollback();
                return false;
            }
            
            
            // 激活时长（天）
            $duration = $activationCode['batch']['duration_days'] ?? 0;
            
            // 验证激活时长
            if ($duration <= 0) {
                self::setError('激活码批次时长不正确');
                Db::rollback();
                return false;
            }

            //  计算会员有效期, 单位：秒
            $endTime = $now + ($duration * 24 * 3600);
            
            // 更新用户会员状态和有效期
            self::updateUserVipStatus($userId, $endTime);
            
            // 更新激活码状态为已使用
            self::updateUserActivationCode($activationCode['id'], $activationCode['batch_id'], $endTime, $userId, $user['nickname'], $now);
            
            // 插入会员激活记录
            $params['remark'] = '激活码兑换会员';
            self::insertUserVipActivationRecord($params, $endTime);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::setError('激活码兑换失败，请稍后重试');
            return false;
        }
    }

    // 更新用户的会员状态和有效期
    public static function updateUserVipStatus($userId, $endTime)
    {
        try {
            $vipInfo = User::query()
                ->where(['id' => $userId])
                ->find();

            if (!$vipInfo) {
                self::setError('更新用户会员状态失败，请稍后重试');
                return false;
            }
            
            // 记录原状态，用于决定是否发送消息通知
            $oldVipState = $vipInfo->vip_state;
            $statusText = '';
            
            switch ($vipInfo->vip_state) {
                case 1:
                    // 首次开通会员
                    $vipInfo->vip_endTime = $endTime; // vip_endTime是int类型，直接存储时间戳
                    $vipInfo->vip_state =3;
                    $statusText = '首次开通会员';
                    break;
                case 2:
                    // 会员未过期，延长有效期
                    // 使用传入的endTime作为新的有效期
                    $vipInfo->vip_endTime = $endTime; // vip_endTime是int类型，直接存储时间戳
                    $vipInfo->vip_state = 3;
                    $statusText = '会员续费成功';
                    break;
                case 3:
                    // 会员已过期，重新计算有效期
                    $vipInfo->vip_endTime = $endTime; // vip_endTime是int类型，直接存储时间戳
                    $vipInfo->vip_state = 3;
                    $statusText = '会员延期成功';
                    break;
                default:
                    // 未知会员状态，不做处理
                    break;
            }
            $vipInfo->save();
            
            // 如果状态发生变化，发送消息通知
            if ($oldVipState != 3 && $vipInfo->vip_state == 3 && !empty($statusText)) {
                \app\common\logic\user\MessageService::vipStatusChangeNotify(
                    $userId,
                    $statusText,
                    $vipInfo->vip_state
                );
            }
            
            return true;
        } catch (\Exception $e) {
            self::setError('更新用户会员状态失败，请稍后重试');
            return false;
        }
    }

    // 如果是激活码兑换，更新激活码表为已使用，并且插入激活码使用表
    public static function updateUserActivationCode($activationCodeId, $batchId, $endTime, $userId, $userName, $activationTime)
    {
        try {
            TenantExamActivationCode::query()
                ->where(['id' => $activationCodeId])
                ->update([
                    'status' => 2, // 2-已使用
                    'activation_time' => date('Y-m-d H:i:s', $activationTime), // 转换为datetime格式
                    'used_user_id' => strval($userId), // 转换为字符串类型
                ]);

                // 插入激活码使用表
                TenantExamActivationRecord::create([
                    'tenant_id' => (int)request()->tenantId,
                    'user_id' => (int)$userId,
                    'user_name' => (string)$userName,
                    'code_id' => (int)$activationCodeId,
                    'batch_id' => (int)$batchId,
                    'activation_ip' => request()->ip(), // 激活时的IP地址
                    'activation_device' => 1,//激活设备信息
                    'activation_time' => $activationTime,
                    'expiration_time' => $endTime
                ]);
            return true;
        } catch (\Exception $e) {
            self::setError('新激活码表为已使用状态失败，请稍后重试');
            return false;
        }
    }
    
    
    // 插入会员激活记录
    public static function insertUserVipActivationRecord($params, $endTime)
    {
        try {
            $now = time();
            TenantExamUserActivationRecord::create([
                'tenant_id' => (int)request()->tenantId,
                'user_uid' => (int)$params['user_uid'],
                'activation_type' => (string)$params['vip_type'],
                'consume_amount' => (string)($params['integral_required'] ?? '0'),
                'activation_time' => (int)$now,
                'status' => 1,
                'package_name' => (string)($params['package_name'] ?? ''),
                'duration' => (int)($params['duration'] ?? 0),
                'remark' => (string)($params['remark'] ?? ''),
                'create_time' => (int)$now,
                'update_time' => (int)$now,
            ]);
            return true;
        } catch (\Exception $e) {
            self::setError('插入会员激活记录失败，请稍后重试');
            return false;
        }
    }

}