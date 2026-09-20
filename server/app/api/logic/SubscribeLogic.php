<?php
// +----------------------------------------------------------------------
// | likeadmin快速开发前后端分离管理后台（PHP版）
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 开源版本可自由商用，可去除界面版权logo
// | gitee下载：https://gitee.com/likeshop_gitee/likeadmin
// | github下载：https://github.com/likeshop-github/likeadmin
// | 访问官网：https://www.likeadmin.cn
// | likeadmin团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeadminTeam
// +----------------------------------------------------------------------

namespace app\api\logic;

use app\api\logic\IndexLogic;
use app\common\model\user\UserSubscribe;
use app\api\logic\exam\countdown\CountdownLogic;
use app\common\logic\BaseLogic;
use think\facade\Cache;
use think\response\Json;
use app\common\model\user\UserAuth;
use app\common\model\SubscribeSendLog;
/**
 * 统一订阅逻辑
 * Class SubscribeLogic
 * @package app\api\logic
 */
class SubscribeLogic extends BaseLogic
{
    /**
     * 订阅类型映射
     */
    private const SUBSCRIBE_TYPES = [
        'countdown' => '倒计时',
        'version_update' => '版本更新',
        'exam' => '考试'
    ];

    /**
     * @notes 统一订阅/取消订阅逻辑
     * @param array $params 请求参数
     * @return array
     * @author 精解析题库
     * @date 2026/01/13
     */
    public static function subscribe(array $params): array
    {
        // 参数验证
        $validateResult = self::validateParams($params);
        if (!$validateResult['success']) {
            return $validateResult;
        }

        $type = $params['type'];
        $subscribeStatus = $params['subscribe_status'] ?? 1;

        // 根据不同类型处理订阅
        switch ($type) {
            case 'countdown':
                // 倒计时订阅逻辑
                return self::handleCountdownSubscribe($params);
            case 'version_update':
                // 版本更新订阅逻辑
                return self::handleVersionUpdateSubscribe($params);
            case 'exam':
                // 考试订阅逻辑
                return self::handleExamSubscribe($params);
            default:
                return [
                    'success' => false,
                    'msg' => '未知的订阅类型',
                    'data' => []
                ];
        }
    }

    /**
     * @notes 获取订阅状态
     * @param array $params 请求参数
     * @return array
     * @author 精解析题库
     * @date 2026/01/13
     */
    public static function getStatus(array $params): array
    {
        // 参数验证
        if (empty($params['type'])) {
            return [
                'success' => false,
                'msg' => '订阅类型不能为空',
                'data' => []
            ];
        }

        $type = $params['type'];
        $userId = $params['user_id'] ?? 0;

        if ($userId <= 0) {
            return [
                'success' => false,
                'msg' => '用户未登录',
                'data' => ['is_followed' => 0]
            ];
        }

        // 根据不同类型获取订阅状态
        switch ($type) {
            case 'countdown':
                // 获取倒计时订阅状态
                return self::handleCountdownStatus($params);
            case 'version_update':
                // 获取版本更新订阅状态
                return self::handleVersionUpdateStatus($params);
            case 'exam':
                // 获取考试订阅状态
                return self::handleExamStatus($params);
            default:
                return [
                    'success' => false,
                    'msg' => '未知的订阅类型',
                    'data' => []
                ];
        }
    }

    /**
     * @notes 验证请求参数
     * @param array $params 请求参数
     * @return array
     * @author 精解析题库
     * @date 2026/01/13
     */
    private static function validateParams(array $params): array
    {
        if (empty($params['type'])) {
            return [
                'success' => false,
                'msg' => '订阅类型不能为空',
                'data' => []
            ];
        }

        if (!array_key_exists($params['type'], self::SUBSCRIBE_TYPES)) {
            return [
                'success' => false,
                'msg' => '不支持的订阅类型',
                'data' => []
            ];
        }

        if (empty($params['user_id'])) {
            return [
                'success' => false,
                'msg' => '用户未登录',
                'data' => []
            ];
        }

        return [
            'success' => true,
            'data' => [],
            'msg' => '参数验证成功'
        ];
    }


    /**写入/更新订阅记录
     * 
    */
    public static function recordSubscribe(array $params): array
    {
          // 1. 获取参数 
            $templateId = $params['template_id'] ?? ''; 
            $subscribeTime = $params['subscribe_time'] ?? 0; 
            // 转换为秒时间戳（如果是毫秒则除以1000）
            if ($subscribeTime > 2147483647) { // 超过INT_MAX，说明是毫秒
                $subscribeTime = floor($subscribeTime / 1000);
            }
            $type = $params['type'] ?? '';      
            $relatedId = $params['related_id'] ?? 0; 
            $userAuth = UserAuth::where('user_id', '=', $params['user_id'])->findOrEmpty();
            $openid = $userAuth->openid ?? ''; 
            
            // 2. 参数校验 
            if (empty($params['user_id']) || empty($templateId) || empty($subscribeTime) || empty($type) || empty($openid)) { 
                return [
                    'success' => false,
                    'msg' => '参数缺失',
                    'data' => []
                ]; 
            } 
            
            // 3. 写入/更新订阅记录 - 使用正确的唯一索引条件
            $subscribe = UserSubscribe::where([
                'user_id' => $params['user_id'],
                'type' => $type,
                'related_id' => $relatedId
            ])->find();
            
            if ($subscribe) {
                // 更新现有记录
                $subscribe->template_id = $templateId;
                $subscribe->subscribe_time = $subscribeTime;
                $subscribe->is_pushed = 0;
                $subscribe->openid = $openid;
                $subscribe->subscribe_status = 1; // 确保订阅状态为1（已订阅）
                $subscribe->save();
                $msg = '订阅记录更新成功';
            } else {
                // 创建新记录
                UserSubscribe::create([
                    'user_id' => $params['user_id'],
                    'template_id' => $templateId,
                    'subscribe_time' => $subscribeTime,
                    'is_pushed' => 0,
                    'type' => $type,
                    'related_id' => $relatedId,
                    'openid' => $openid,
                    'subscribe_status' => 1 // 确保订阅状态为1（已订阅）
                ]);
                $msg = '订阅记录创建成功';
            }
            
            // 4. 返回结果
            return [
                'success' => true,
                'msg' => $msg,
                'data' => []
            ];
    }


    /**
     * @notes 处理倒计时订阅
     * @param array $params 请求参数
     * @return array
     * @author 精解析题库
     * @date 2026/01/13
     */
    private static function handleCountdownSubscribe(array $params): array
    {
        // 获取用户openid
        $userAuth = UserAuth::where('user_id', '=', $params['user_id'])->findOrEmpty();
        $openid = $userAuth->openid ?? '';
        
        // 适配CountdownLogic::follow方法的参数
        $countdownParams = [
            'id' => $params['related_id'],
            'user_id' => $params['user_id'],
            'subscribe_status' => $params['subscribe_status'] ?? 1,
            'openid' => $openid
        ];

        // 如果有订阅结果，添加到参数中
        if (!empty($params['subscribe_results'])) {
            $countdownParams['subscribe_results'] = $params['subscribe_results'];
        }

        // 调用现有倒计时订阅逻辑
        return CountdownLogic::follow($countdownParams);
    }

    /**
     * @notes 处理版本更新订阅
     * @param array $params 请求参数
     * @return array
     * @author 精解析题库
     * @date 2026/01/13
     */
    private static function handleVersionUpdateSubscribe(array $params): array
    {
        // 适配IndexLogic::templateSubscribe方法的参数
        $versionParams = [
            'code' => 'app_version_update',
            'type' => 'wechat_mini',
            'user_id' => $params['user_id']
        ];

        // 如果有模板数据，添加到参数中
        if (!empty($params['template_data'])) {
            $versionParams['data'] = $params['template_data'];
        }

        // 如果有订阅结果，添加到参数中
        if (!empty($params['subscribe_results'])) {
            $versionParams['subscribe_results'] = $params['subscribe_results'];
        }

        // 调用现有版本更新订阅逻辑
        $result = IndexLogic::templateSubscribe($versionParams);

        return [
            'success' => $result['code'] === 0,
            'msg' => $result['code'] === 0 ? '订阅成功' : $result['msg'],
            'data' => $result['data']
        ];
    }

    /**
     * @notes 处理倒计时订阅状态
     * @param array $params 请求参数
     * @return array
     * @author 精解析题库
     * @date 2026/01/13
     */
    private static function handleCountdownStatus(array $params): array
    {
        // 调用CountdownLogic获取详情，包含订阅状态
        $detailParams = [
            'id' => $params['related_id'],
            'user_id' => $params['user_id']
        ];

        $result = CountdownLogic::detail($detailParams);

        if (!$result['success']) {
            return [
                'success' => false,
                'msg' => $result['msg'],
                'data' => ['is_followed' => 0]
            ];
        }

        return [
            'success' => true,
            'msg' => '获取订阅状态成功',
            'data' => ['is_followed' => $result['data']['is_followed'] ?? 0]
        ];
    }

    /**
     * @notes 处理版本更新订阅状态
     * @param array $params 请求参数
     * @return array
     * @author 精解析题库
     * @date 2026/01/13
     */
    private static function handleVersionUpdateStatus(array $params): array
    {
        // 版本更新订阅状态逻辑，此处可根据实际需求实现
        // 暂时返回默认值
        return [
            'success' => true,
            'msg' => '获取订阅状态成功',
            'data' => ['is_followed' => 1] // 假设默认已订阅
        ];
    }
    
    /**
     * @notes 处理考试订阅
     * @param array $params 请求参数
     * @return array
     * @author 精解析题库
     * @date 2026/01/13
     */
    private static function handleExamSubscribe(array $params): array
    {
        // 从参数中提取openid
        $userAuth = UserAuth::where('user_id', '=', $params['user_id'])->findOrEmpty();
        $openid = $userAuth->openid ?? '';
        
        // 构造UserSubscribe模型需要的参数
        $subscribeParams = [
            'user_id' => $params['user_id'],
            'type' => 'exam',
            'related_id' => $params['related_id'],
            'subscribe_status' => $params['subscribe_status'] ?? 1,
            'template_id' => '',
            'subscribe_time' => time(), // 秒时间戳
            'is_pushed' => 0,
            'openid' => $openid
        ];
        
        // 如果有模板数据，添加到参数中
        if (!empty($params['template_data'])) {
            $subscribeParams['template_data'] = $params['template_data'];
        }
        
        // 使用UserSubscribe模型直接处理订阅
        // 检查是否已订阅
        $subscribe = UserSubscribe::where([
            'user_id' => $params['user_id'],
            'type' => 'exam',
            'related_id' => $params['related_id']
        ])->find();
        
        if ($subscribe) {
            // 已订阅，根据subscribe_status决定更新或删除
            if ($subscribeParams['subscribe_status'] == 0) {
                // 取消订阅，删除记录
                $result = $subscribe->delete() > 0;
                $action = '取消订阅';
                $isFollowed = 0;
            } else {
                // 更新订阅状态
                $result = $subscribe->save($subscribeParams) !== false;
                $action = '更新订阅';
                $isFollowed = 1;
            }
        } else {
            // 未订阅，创建新记录
            $result = UserSubscribe::create($subscribeParams);
            $action = '订阅';
            $isFollowed = 1;
        }
        
        if ($result) {
            return [
                'success' => true,
                'data' => [
                    'is_followed' => $isFollowed,
                    'action' => $action,
                    'follow_count' => UserSubscribe::where([
                        'type' => 'exam',
                        'related_id' => $params['related_id']
                    ])->count()
                ],
                'msg' => $action . '成功'
            ];
        }
        
        return [
            'success' => false,
            'msg' => $action . '失败',
            'data' => []
        ];
    }
    
    /**
     * @notes 处理考试订阅状态
     * @param array $params 请求参数
     * @return array
     * @author 精解析题库
     * @date 2026/01/13
     */
    private static function handleExamStatus(array $params): array
    {
        // 查询考试订阅状态
        $subscribe = UserSubscribe::where([
            'user_id' => $params['user_id'],
            'type' => 'exam',
            'related_id' => $params['related_id']
        ])->find();
        
        return [
            'success' => true,
            'msg' => '获取订阅状态成功',
            'data' => ['is_followed' => $subscribe ? 1 : 0]
        ];
    }

    /**
     * @notes 获取微信access_token
     * @return string
     * @throws \Exception
     * @author 精解析题库
     * @date 2026/01/14
     */
    public static function getWxAccessToken()
    {
        // 从配置中获取小程序的AppID和Secret
        $appid = config('weapp.appid', '');
        $secret = config('weapp.secret', '');
        
        if (empty($appid) || empty($secret)) {
            // 如果配置中没有，尝试从环境变量获取
            $appid = $_ENV['WECHAT_APPID'] ?? '';
            $secret = $_ENV['WECHAT_SECRET'] ?? '';
        }
        
        if (empty($appid) || empty($secret)) {
            throw new \Exception('小程序AppID或Secret未配置');
        }
        
        $cacheKey = 'wx_access_token_' . md5($appid);
        
        // 从缓存获取（避免频繁调用）
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        // 调用微信接口获取
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$secret}";
        $resData = self::curlRequest($url);
        
        if (empty($resData['access_token'])) {
            throw new \Exception('获取access_token失败：' . $resData['errmsg']);
        }
        
        // 缓存2小时（微信返回的expires_in是7200秒），提前5分钟刷新
        $expiresIn = ($resData['expires_in'] ?? 7200) - 300;
        Cache::set($cacheKey, $resData['access_token'], $expiresIn);
        return $resData['access_token'];
    }

    /**
     * @notes 计算倒计时发送时间
     * @param int $endTime 结束时间戳
     * @return array 发送时间点数组
     * @author 精解析题库
     * @date 2026/01/15
     */
    public static function calculateCountdownSendTimes($endTime)
    {
        $now = time();
        $sendTimes = [];
        
        // 1天前（24小时）
        $oneDayBefore = $endTime - 24 * 3600;
        if ($oneDayBefore > $now) {
            $sendTimes[] = $oneDayBefore;
        }
        
        // 3小时前
        $threeHoursBefore = $endTime - 3 * 3600;
        if ($threeHoursBefore > $now) {
            $sendTimes[] = $threeHoursBefore;
        }
        
        // 30分钟前
        $thirtyMinutesBefore = $endTime - 30 * 60;
        if ($thirtyMinutesBefore > $now) {
            $sendTimes[] = $thirtyMinutesBefore;
        }
        
        // 5分钟前
        $fiveMinutesBefore = $endTime - 5 * 60;
        if ($fiveMinutesBefore > $now) {
            $sendTimes[] = $fiveMinutesBefore;
        }
        
        return $sendTimes;
    }
    
    /**
     * @notes 计算考试发送时间
     * @param int $examStartTime 考试开始时间戳
     * @param int $examEndTime 考试结束时间戳
     * @return array 发送时间点数组
     * @author 精解析题库
     * @date 2026/01/15
     */
    public static function calculateExamSendTimes($examStartTime, $examEndTime)
    {
        $now = time();
        $sendTimes = [];
        
        // 考试开始前1天
        $oneDayBefore = $examStartTime - 24 * 3600;
        if ($oneDayBefore > $now) {
            $sendTimes[] = ['time' => $oneDayBefore, 'type' => 'exam_start_remind'];
        }
        
        // 考试开始前3小时
        $threeHoursBefore = $examStartTime - 3 * 3600;
        if ($threeHoursBefore > $now) {
            $sendTimes[] = ['time' => $threeHoursBefore, 'type' => 'exam_start_remind'];
        }
        
        // 考试开始前30分钟
        $thirtyMinutesBefore = $examStartTime - 30 * 60;
        if ($thirtyMinutesBefore > $now) {
            $sendTimes[] = ['time' => $thirtyMinutesBefore, 'type' => 'exam_start_remind'];
        }
        
        // 考试开始前5分钟
        $fiveMinutesBefore = $examStartTime - 5 * 60;
        if ($fiveMinutesBefore > $now) {
            $sendTimes[] = ['time' => $fiveMinutesBefore, 'type' => 'exam_start_remind'];
        }
        
        // 考试结束前15分钟
        $fifteenMinutesBeforeEnd = $examEndTime - 15 * 60;
        if ($fifteenMinutesBeforeEnd > $now) {
            $sendTimes[] = ['time' => $fifteenMinutesBeforeEnd, 'type' => 'exam_end_remind'];
        }
        
        return $sendTimes;
    }
    
    /**
     * @notes 检查订阅是否过期
     * @param object $subscribe 订阅记录模型对象
     * @return bool 是否过期
     * @author 精解析题库
     * @date 2026/01/15
     */
    private static function checkSubscriptionExpired($subscribe)
    {
        // 确保subscribe_time是秒级时间戳（如果是毫秒则转换）
        $subscribeTime = $subscribe->subscribe_time;
        if ($subscribeTime > 2147483647) { // 超过INT_MAX，说明是毫秒
            $subscribeTime = floor($subscribeTime / 1000);
        }
        
        // 计算7天前的时间戳（秒）
        $sevenDaysAgo = time() - 7 * 24 * 3600;
        // 检查订阅时间是否超过7天
        if ($subscribeTime < $sevenDaysAgo) {
            // 授权已过期，更新状态
            $subscribe->save(['is_pushed' => -1, 'status' => 'expired']);
            return true;
        }
        return false;
    }
    
    /**
     * @notes 发送一次性订阅消息
     * @param array $params 请求参数
     * @param int $retryTimes 重试次数
     * @return Json
     * @author 精解析题库
     * @date 2026/01/14
     */
    public static function sendOneTimeSubscribeMsg(array $params, int $retryTimes = 0): Json
    {
        try {
            // 1. 获取参数
            $userAuth = UserAuth::where('user_id', '=', $params['user_id'])->findOrEmpty();
            $openid = $userAuth->openid ?? '';
            $templateId = $params['template_id'] ?? '你的模板ID';
            $page = $params['page'] ?? 'pages/index/index';
            $messageData = $params['message_data'] ?? [];
            $type = $params['type'] ?? '';
            $relatedId = $params['related_id'] ?? '';
            $async = $params['async'] ?? false;
            
            // 2. 验证必要参数
            if (empty($openid)) {
                // 创建发送记录 - 参数错误
                self::createSendLog([
                    'user_id' => $params['user_id'] ?? 0,
                    'openid' => $openid,
                    'type' => $type,
                    'related_id' => $relatedId,
                    'template_id' => $templateId,
                    'message_data' => json_encode($messageData, JSON_UNESCAPED_UNICODE),
                    'send_status' => 2, // 发送失败
                    'error_code' => 1,
                    'error_msg' => '缺少openid参数',
                    'retry_times' => $retryTimes
                ]);
                
                return json(['code' => 1, 'msg' => '缺少openid参数']);
            }
            
            // 3. 检查订阅状态（使用悲观锁防止并发问题）
            $subscribe = UserSubscribe::where([
                'openid' => $openid,
                'type' => $type,
                'related_id' => $relatedId
            ])->lock(true)->find();  // lock(true) 表示使用FOR UPDATE锁
            
            if (!$subscribe) {
                // 创建发送记录 - 订阅记录不存在
                self::createSendLog([
                    'user_id' => $params['user_id'] ?? 0,
                    'openid' => $openid,
                    'type' => $type,
                    'related_id' => $relatedId,
                    'template_id' => $templateId,
                    'data' => json_encode($messageData, JSON_UNESCAPED_UNICODE),
                    'send_status' => 2, // 发送失败
                    'error_code' => 3,
                    'error_msg' => '订阅记录不存在',
                    'retry_times' => $retryTimes,
                    'tenant_id' => 0 // 订阅记录不存在时，使用默认租户ID 0
                ]);
                
                return json(['code' => 3, 'msg' => '订阅记录不存在']);
            }
            
            // 检查订阅是否已过期
            if (self::checkSubscriptionExpired($subscribe)) {
                // 创建发送记录 - 订阅已过期
                self::createSendLog([
                    'user_id' => $params['user_id'] ?? 0,
                    'openid' => $openid,
                    'type' => $type,
                    'related_id' => $relatedId,
                    'template_id' => $templateId,
                    'data' => json_encode($messageData, JSON_UNESCAPED_UNICODE),
                    'send_status' => 2, // 发送失败
                    'error_code' => 4,
                    'error_msg' => '订阅已过期，请重新订阅',
                    'retry_times' => $retryTimes,
                    'tenant_id' => $subscribe->tenant_id ?? 0 // 添加租户ID
                ]);
                
                return json(['code' => 4, 'msg' => '订阅已过期，请重新订阅']);
            }
            
            // 再次检查是否已推送（双重检查，防止并发问题）
            if ($subscribe->is_pushed == 1) {
                // 创建发送记录 - 已发送过消息
                self::createSendLog([
                    'user_id' => $params['user_id'] ?? 0,
                    'openid' => $openid,
                    'type' => $type,
                    'related_id' => $relatedId,
                    'template_id' => $templateId,
                    'data' => json_encode($messageData, JSON_UNESCAPED_UNICODE),
                    'send_status' => 2, // 发送失败
                    'error_code' => 5,
                    'error_msg' => '该订阅已发送过消息',
                    'retry_times' => $retryTimes,
                    'tenant_id' => $subscribe->tenant_id ?? 0 // 添加租户ID
                ]);
                
                return json(['code' => 5, 'msg' => '该订阅已发送过消息']);
            }
            
            // 4. 准备消息内容
            if (empty($messageData)) {
                // 根据不同类型生成默认消息内容
                switch ($type) {
                    case 'countdown':
                        $messageData = [
                            'thing1' => ['value' => $params['countdown_title'] ?? '倒计时提醒'],
                            'thing2' => ['value' => $params['countdown_remarks'] ?? '倒计时提醒'],
                            'time3' => ['value' => $params['countdown_time'] ?? date('Y-m-d H:i:s')]
                        ];
                        break;
                    case 'exam':
                        $messageData = [
                            'thing1' => ['value' => $params['exam_title'] ?? '考试提醒'],
                            'thing2' => ['value' => $params['exam_remarks'] ?? '考试提醒'],
                            'time3' => ['value' => $params['exam_start_time'] ?? date('Y-m-d H:i:s')],
                            'time4' => ['value' => $params['exam_end_time'] ?? date('Y-m-d H:i:s')]
                        ];
                        break;
                    case 'version_update':
                        $messageData = [
                            'thing1' => ['value' => $params['update_content'] ?? '版本更新通知'],
                            'time2' => ['value' => $params['update_time'] ?? date('Y-m-d H:i:s')],
                            'thing3' => ['value' => $params['tips'] ?? '请及时更新体验']
                        ];
                        break;
                    default:
                        $messageData = [
                            'thing1' => ['value' => $params['default_title'] ?? '系统通知'],
                            'thing2' => ['value' => $params['default_content'] ?? date('Y-m-d H:i:s')],
                            'thing3' => ['value' => $params['default_remarks'] ?? '您有新的消息通知']
                        ];
                }
            }
            
            // 确保消息数据格式正确，过滤掉空值
            $filteredMessageData = [];
            foreach ($messageData as $key => $value) {
                if (isset($value['value']) && trim($value['value']) !== '') {
                    $filteredMessageData[$key] = $value;
                }
            }
            $messageData = $filteredMessageData;
            
            // 5. 异步发送处理
            if ($async) {
                // 创建发送记录 - 异步处理
                self::createSendLog([
                    'user_id' => $params['user_id'] ?? 0,
                    'openid' => $openid,
                    'type' => $type,
                    'related_id' => $relatedId,
                    'template_id' => $templateId,
                    'data' => json_encode($messageData, JSON_UNESCAPED_UNICODE),
                    'send_status' => 0, // 待发送
                    'error_code' => 0,
                    'error_msg' => '异步处理中',
                    'retry_times' => $retryTimes,
                    'tenant_id' => $subscribe->tenant_id ?? 0 // 添加租户ID
                ]);
                
                // 这里可以实现异步发送，例如将任务放入消息队列
                // 暂时返回成功，实际发送在异步任务中处理
                return json(['code' => 0, 'msg' => '消息已加入发送队列']);
            }
            
            // 6. 获取access_token
            $accessToken = self::getWxAccessToken();
            
            // 7. 调用微信发送接口
            $sendUrl = "https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token={$accessToken}";
            $sendParams = [
                'touser'      => $openid,          // 用户openid
                'template_id' => $templateId,      // 订阅模板ID
                'page'        => $page,            // 点击消息跳转的小程序页面（可选）
                'data'        => $messageData      // 模板内容
            ];
            
            $sendResData = self::curlRequest($sendUrl, 'POST', $sendParams);
            
            // 8. 处理发送结果
            if ($sendResData['errcode'] != 0) {
                // 发送失败，重试机制
                if ($retryTimes < 3) {
                    // 使用指数退避算法：2^retryTimes 秒，最大不超过30秒
                    $retryDelay = min(pow(2, $retryTimes) * 2, 30); // 2秒, 4秒, 8秒
                    sleep($retryDelay);
                    return self::sendOneTimeSubscribeMsg($params, $retryTimes + 1);
                }
                
                // 重试失败，记录失败状态
                $subscribe->save(['is_pushed' => -2, 'push_time' => time()]);
                
                // 创建发送记录 - 最终发送失败
                self::createSendLog([
                    'user_id' => $params['user_id'] ?? 0,
                    'openid' => $openid,
                    'type' => $type,
                    'related_id' => $relatedId,
                    'template_id' => $templateId,
                    'data' => json_encode($messageData, JSON_UNESCAPED_UNICODE),
                    'send_status' => 2, // 发送失败
                    'error_code' => $sendResData['errcode'],
                    'error_msg' => $sendResData['errmsg'],
                    'result' => json_encode($sendResData, JSON_UNESCAPED_UNICODE),
                    'retry_times' => $retryTimes,
                    'send_time' => time(),
                    'tenant_id' => $subscribe->tenant_id ?? 0 // 添加租户ID
                ]);
                
                // 记录失败日志
                self::logSendFailure($openid, $type, $relatedId, $sendResData['errcode'], $sendResData['errmsg']);
                
                return json(['code' => 2, 'msg' => '发送失败：' . $sendResData['errmsg']]);
            }
            
            // 9. 发送成功后，更新"已推送"状态（一次性授权已消耗）
            $subscribe->save(['is_pushed' => 1, 'push_time' => time()]);
            
            // 创建发送记录 - 发送成功
            self::createSendLog([
                'user_id' => $params['user_id'] ?? 0,
                'openid' => $openid,
                'type' => $type,
                'related_id' => $relatedId,
                'template_id' => $templateId,
                'data' => json_encode($messageData, JSON_UNESCAPED_UNICODE),
                'send_status' => 1, // 发送成功
                'error_code' => 0,
                'error_msg' => '发送成功',
                'result' => json_encode($sendResData, JSON_UNESCAPED_UNICODE),
                'retry_times' => $retryTimes,
                'send_time' => time(),
                'tenant_id' => $subscribe->tenant_id ?? 0 // 添加租户ID
            ]);
            
            // 记录成功日志
            self::logSendSuccess($openid, $type, $relatedId);
            
            return json(['code' => 0, 'msg' => '一次性订阅消息发送成功']);
        } catch (\Exception $e) {
            // 创建发送记录 - 异常
            self::createSendLog([
                'user_id' => $params['user_id'] ?? 0,
                'openid' => $params['openid'] ?? '',
                'type' => $params['type'] ?? '',
                'related_id' => $params['related_id'] ?? 0,
                'template_id' => $params['template_id'] ?? '',
                'data' => json_encode($params['message_data'] ?? [], JSON_UNESCAPED_UNICODE),
                'send_status' => 2, // 发送失败
                'error_code' => 500,
                'error_msg' => '发送异常：' . $e->getMessage(),
                'retry_times' => $retryTimes,
                'tenant_id' => $params['tenant_id'] ?? 0 // 添加租户ID
            ]);
            
            // 记录异常日志
            self::logSendException($params, $e->getMessage());
            
            return json(['code' => 500, 'msg' => '发送异常：' . $e->getMessage()]);
        }
    }
    
    /**
     * @notes 批量发送订阅消息
     * @param array $paramsList 消息参数列表
     * @param bool $async 是否异步发送
     * @return array 发送结果
     * @author 精解析题库
     * @date 2026/01/15
     */
    public static function batchSendOneTimeSubscribeMsg(array $paramsList, bool $async = false): array
    {
        $successCount = 0;
        $failCount = 0;
        $failList = [];
        
        // 分批发送，每批100个（微信API限制）
        $batchSize = 100;
        $batches = array_chunk($paramsList, $batchSize);
        
        foreach ($batches as $batch) {
            foreach ($batch as $params) {
                // 添加异步参数
                $params['async'] = $async;
                
                // 发送消息
                $result = self::sendOneTimeSubscribeMsg($params);
                $resultData = json_decode($result->getContent(), true);
                
                if ($resultData['code'] === 0) {
                    $successCount++;
                } else {
                    $failCount++;
                    $failList[] = [
                        'openid' => $params['openid'],
                        'msg' => $resultData['msg']
                    ];
                }
            }
            
            // 每批发送后休息1秒，避免触发限流
            sleep(1);
        }
        
        return [
            'code' => 0,
            'msg' => '批量发送完成',
            'data' => [
                'total' => count($paramsList),
                'success' => $successCount,
                'fail' => $failCount,
                'fail_list' => $failList
            ]
        ];
    }
    
    /**
     * @notes 发送版本更新消息
     * @param int $versionId 版本ID
     * @param bool $async 是否异步发送
     * @return array 发送结果
     * @author 精解析题库
     * @date 2026/01/15
     */
    public static function sendVersionUpdateMsg($versionId, bool $async = false) {
        // 1. 查询版本信息
        $versionInfo = \app\common\model\exam\TenantVersionUpdate::find($versionId);
        if (!$versionInfo) {
            return ['code' => 1, 'msg' => '版本信息不存在'];
        }
        
        // 2. 查询订阅用户
        $subscribers = UserSubscribe::where([
            'type' => 'version_update',
            'is_pushed' => 0  // 只查询未推送的订阅
        ])->select();
        
        $paramsList = [];
        
        // 3. 准备发送参数列表
        foreach ($subscribers as $subscribe) {
            // 检查订阅是否过期
            if (self::checkSubscriptionExpired($subscribe)) {
                continue;
            }
            
            $paramsList[] = [
                'openid' => $subscribe->openid,
                'template_id' => $subscribe->template_id,
                'type' => 'version_update',
                'related_id' => $versionId,
                'message_data' => [
                    'thing1' => ['value' => $versionInfo['version'] . "\n" . $versionInfo['info']],
                    'time2' => ['value' => date('Y-m-d H:i:s', $versionInfo['release_time'])],
                    'thing3' => ['value' => '感谢您使用我们的应用，欢迎体验新功能并提出宝贵意见！']
                ]
            ];
        }
        
        // 4. 批量发送消息
        return self::batchSendOneTimeSubscribeMsg($paramsList, $async);
    }
    
    /**
     * @notes 记录发送成功日志
     * @param string $openid 用户openid
     * @param string $type 订阅类型
     * @param string $relatedId 关联ID
     * @author 精解析题库
     * @date 2026/01/15
     */
    private static function logSendSuccess($openid, $type, $relatedId) {
        // 这里可以根据实际需求实现日志记录，例如写入文件或数据库
        $logData = [
            'openid' => $openid,
            'type' => $type,
            'related_id' => $relatedId,
            'status' => 'success',
            'message' => '发送成功',
            'create_time' => time(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // 示例：写入日志文件
        file_put_contents(
            app()->getRootPath() . 'runtime/logs/subscribe_send.log',
            json_encode($logData, JSON_UNESCAPED_UNICODE) . PHP_EOL,
            FILE_APPEND
        );
    }
    
    /**
     * @notes 记录发送失败日志
     * @param string $openid 用户openid
     * @param string $type 订阅类型
     * @param string $relatedId 关联ID
     * @param int $errcode 错误码
     * @param string $errmsg 错误信息
     * @author 精解析题库
     * @date 2026/01/15
     */
    private static function logSendFailure($openid, $type, $relatedId, $errcode, $errmsg) {
        $logData = [
            'openid' => $openid,
            'type' => $type,
            'related_id' => $relatedId,
            'status' => 'failure',
            'errcode' => $errcode,
            'errmsg' => $errmsg,
            'create_time' => time(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        file_put_contents(
            app()->getRootPath() . 'runtime/logs/subscribe_send.log',
            json_encode($logData, JSON_UNESCAPED_UNICODE) . PHP_EOL,
            FILE_APPEND
        );
    }
    
    /**
     * @notes 记录发送异常日志
     * @param array $params 请求参数
     * @param string $exception 异常信息
     * @author 精解析题库
     * @date 2026/01/15
     */
    private static function logSendException($params, $exception) {
        $logData = [
            'params' => $params,
            'status' => 'exception',
            'exception' => $exception,
            'create_time' => time(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        file_put_contents(
            app()->getRootPath() . 'runtime/logs/subscribe_send.log',
            json_encode($logData, JSON_UNESCAPED_UNICODE) . PHP_EOL,
            FILE_APPEND
        );
    }
    
    /**
     * @notes 创建发送记录
     * @param array $data 发送记录数据
     * @return mixed
     * @author 精解析题库
     * @date 2026/01/15
     */
    private static function createSendLog(array $data)
    {
        try {
            // 确保使用正确的字段名（与模型映射一致）
            $logData = [
                'user_id' => $data['user_id'] ?? 0,
                'openid' => $data['openid'] ?? '',
                'type' => $data['type'] ?? '',
                'related_id' => $data['related_id'] ?? 0,
                'template_id' => $data['template_id'] ?? '',
                'data' => $data['message_data'] ?? $data['data'] ?? '', // 支持两种字段名
                'send_status' => $data['send_status'] ?? 0,
                'send_time' => $data['send_time'] ?? 0,
                'retry_times' => $data['retry_times'] ?? 0,
                'error_code' => $data['error_code'] ?? 0,
                'error_msg' => $data['error_msg'] ?? '',
                'result' => $data['response_data'] ?? $data['result'] ?? '', // 支持两种字段名
                'tenant_id' => $data['tenant_id'] ?? 0, // 添加租户ID
                'create_time' => time(),
                'update_time' => time()
            ];
            
            return SubscribeSendLog::create($logData);
        } catch (\Exception $e) {
            // 如果数据库记录创建失败，至少记录到日志文件
            $logData = [
                'type' => 'send_log_error',
                'message' => '发送记录创建失败: ' . $e->getMessage(),
                'data' => $data,
                'create_time' => time(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            file_put_contents(
                app()->getRootPath() . 'runtime/logs/subscribe_send.log',
                json_encode($logData, JSON_UNESCAPED_UNICODE) . PHP_EOL,
                FILE_APPEND
            );
            
            return null;
        }
    }
    
    /**
     * @notes 获取推送统计信息（从数据库记录）
     * @param string $type 订阅类型
     * @param int $days 统计天数
     * @return array 统计结果
     * @author 精解析题库
     * @date 2026/01/15
     */
    public static function getPushStats($type = '', $days = 7) {
        // 优先从数据库获取统计数据
        $startDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        $query = SubscribeSendLog::where('create_time', '>=', strtotime($startDate));
        
        if (!empty($type)) {
            $query = $query->where('type', $type);
        }
        
        $stats = $query->field([
            'COUNT(*) as total',
            'SUM(CASE WHEN send_status = 1 THEN 1 ELSE 0 END) as success',
            'SUM(CASE WHEN send_status = 2 THEN 1 ELSE 0 END) as failure'
        ])->find();
        
        $result = [
            'total' => (int)$stats['total'],
            'success' => (int)$stats['success'],
            'failure' => (int)$stats['failure'],
            'success_rate' => 0
        ];
        
        if ($result['total'] > 0) {
            $result['success_rate'] = round(($result['success'] / $result['total']) * 100, 2);
        }
        
        return $result;
    }

    /**
     * @notes CURL请求方法
     * @param string $url 请求URL
     * @param string $method 请求方法 GET/POST
     * @param array $data 请求数据
     * @return array
     * @throws \Exception
     * @author 精解析题库
     * @date 2026/01/14
     */
    private static function curlRequest(string $url, string $method = 'GET', array $data = []): array
    {
        $curl = curl_init();
        
        // 设置请求选项
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        
        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen(json_encode($data, JSON_UNESCAPED_UNICODE))
            ]);
        }
        
        // 执行请求
        $response = curl_exec($curl);
        
        // 检查请求是否成功
        if ($response === false) {
            $error = curl_error($curl);
            throw new \Exception('CURL请求失败：' . $error);
        }
        
        // 解析JSON响应
        $result = json_decode($response, true);
        
        // 检查JSON解析是否成功
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('JSON解析失败：' . json_last_error_msg());
        }
        
        return $result;
    }
}