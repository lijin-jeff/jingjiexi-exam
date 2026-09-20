<?php
declare(strict_types=1);
// +----------------------------------------------------------------------
// | 精解析答题考试系统
// +----------------------------------------------------------------------
// | 感谢使用精解析答题系统
// | 本系统经过商业授权，不能转售、开源等其他不符合精解析答题系统版权协议外的商业行为，违者必追究其侵犯版权行为。
// | 精解析答题系统开发者版权所有，拥有最终解释权。
namespace app\api\logic;

use app\common\enum\IntegralEnum;
use app\common\logic\BaseLogic;
use app\common\model\user\User;
use app\common\model\exam\TenantUserIntegralLog;
use think\facade\Cache;
use think\facade\Db;
use think\facade\Log;

class IntegralLogic extends BaseLogic
{
    // 缓存前缀
    protected const CACHE_PREFIX = 'integral_';
    // 缓存时间（秒）
    protected const CACHE_TIME = 3600;
    
    /**
     * 增加用户积分
     * @param int $userId 用户 id
     * @param int $action 动作类型 1-增加 2-减少
     * @param int $integralType 积分类型
     * @param float $integral 积分数量
     * @param string $remark 积分备注
     * @param array $extra 积分扩展
     * @return bool
     */
    public static function addIntegral(int $userId, int $action, int $integralType, float $integral, string $remark = '', array $extra = []): bool
    {
        // 数据验证
        if (!in_array($action, [1, 2])) {
            Log::error('积分动作类型错误: ' . $action);
            return false;
        }
        
        if ($integral <= 0) {
            Log::error('积分数量必须大于0: ' . $integral);
            return false;
        }
        
        Db::startTrans();
        try {
            // 创建积分记录
            $userIntegralModel = TenantUserIntegralLog::create([
                'tenant_id'     => request()->tenantId,
                'user_id'       => $userId,
                'action'        => $action, // 1-增加积分，2-减少积分
                'change_amount' => $integral,
                'change_type'   => $integralType, // 变动类型 
                'title'         => IntegralEnum::IntegralTitle($integralType),
                'remark'        => $remark,
                'extra'         => json_encode($extra, JSON_UNESCAPED_UNICODE),
            ]);
            
            // 更新用户积分
            $user = User::where('id', $userId)->lock(true)->findOrFail();
            $user->integral = $user->integral + ($action == 1 ? $integral : -$integral);
            if (!empty($userIntegralModel->getKey()) && $user->save()) {
                // 发送积分变化系统消息通知
                \app\common\logic\user\MessageService::integralChangeNotify(
                    $userId, 
                    $action == 1 ? '增加' : '减少', 
                    $integral, 
                    \app\common\enum\IntegralEnum::IntegralTitle($integralType)
                );
                
                // 清除缓存
                self::clearUserIntegralCache($userId);
                Db::commit();
                return true;
            }
            Db::rollback();
            return false;
        } catch (\Exception $exception) {
            Db::rollback();
            Log::error('操作积分失败: ' . $exception->getMessage());
            return false;
        }
    }

    /**
     * 积分明细
     * @param array $params
     * @return array
     */
    public static function integralList(array $params): array
    {
        try {
            // 参数校验
            if (empty($params['user_id'])) {
                throw new \Exception('用户ID不能为空');
            }
            
            $userId = (int)$params['user_id'];
            $pageNo = max(1, (int)($params['page_no'] ?? 1));
            $pageSize = max(1, min(100, (int)($params['page_size'] ?? 20))); // 限制每页最大条数
            $actionType = (int)($params['action'] ?? 0);
            
            // 构建缓存键
            $cacheKey = self::CACHE_PREFIX . 'list_' . $userId . '_' . $actionType . '_' . $pageNo . '_' . $pageSize;
            
            // 尝试从缓存获取
            $cachedData = Cache::get($cacheKey);
            if (!empty($cachedData)) {
                return $cachedData;
            }
            
            // 构建查询
            $query = (new TenantUserIntegralLog())->where('user_id', $userId);
            
            // 条件过滤
            if ($actionType > 0) {
                $query->where('action', $actionType);
            }
            
            // 字段选择和分页查询
            $userIntegral = $query->field([
                'title', 'action_type', 'action', 'change_amount', 
                'change_type', 'remark', 'create_time'
            ])->order('create_time', 'desc')
              ->paginate([
                  'page' => $pageNo, 
                  'list_rows' => $pageSize
              ]);
            
            // 构建返回数据
            $result = [
                'lists'     => $userIntegral->items(),
                'page_no'   => $userIntegral->currentPage(),
                'page_size' => $pageSize,
                'count'     => $userIntegral->total(),
                'extend'    => [
                    'user_integral' => self::userIntegral($userId)
                ]
            ];
            
            // 缓存结果
            Cache::set($cacheKey, $result, self::CACHE_TIME);
            
            return $result;
        } catch (\Exception $e) {
            // 记录错误日志
            Log::error('查询积分明细失败: ' . $e->getMessage());
            return [
                'lists'     => [],
                'page_no'   => max(1, (int)($params['page_no'] ?? 1)),
                'page_size' => max(1, min(100, (int)($params['page_size'] ?? 20))),
                'count'     => 0,
                'extend'    => [
                    'user_integral' => self::userIntegral($userId)
                ]
            ];
        }
    }

    /**
     * 查询用户总结积分
     * @param int $userId
     * @return string
     */
    public static function userIntegral(int $userId): string
    {
        // 构建缓存键
        $cacheKey = self::CACHE_PREFIX . 'user_integral_' . $userId;
        
        // 尝试从缓存获取
        $cachedIntegral = Cache::get($cacheKey);
        if (!empty($cachedIntegral)) {
            return $cachedIntegral;
        }
        
        try {
            $userIntegral = (new User())->where('id', $userId)->value('integral');
            $formattedIntegral = number_format((float)$userIntegral, 2);
            
            // 缓存结果
            Cache::set($cacheKey, $formattedIntegral, self::CACHE_TIME);
            
            return $formattedIntegral;
        } catch (\Exception $e) {
            Log::error('查询用户积分失败: ' . $e->getMessage());
            return '0.00';
        }
    }
    
    /**
     * 清除用户积分相关缓存
     * @param int $userId 用户ID
     * @return void
     */
    public static function clearUserIntegralCache(int $userId): void
    {
        // 清除用户积分缓存
        Cache::delete(self::CACHE_PREFIX . 'user_score_' . $userId);
        
        // 清除用户积分明细缓存（使用通配符）
        Cache::delete(self::CACHE_PREFIX . 'list_' . $userId . '*');
    }

    /**
     * 从用户表查询用户积分列表（用于积分排行榜）和我的积分
     * @return array
     */
    public static function userIntegralRanking(int $userId): array
    {
        try {
            $userIntegral = (new User())->field([
                'id', 'nickname', 'avatar', 'integral'
            ])->order('integral', 'desc')
            ->limit(100)
            ->select();
            $userIntegral['me'] = (new User())->field([
                'id', 'nickname', 'avatar', 'integral'
            ])->where('id', $userId)->find();
            
            return $userIntegral->toArray();
        } catch (\Exception $e) {
            Log::error('查询用户积分排行榜失败: ' . $e->getMessage());
            return [];
        }
    }
}