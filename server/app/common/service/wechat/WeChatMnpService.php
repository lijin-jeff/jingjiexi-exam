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
namespace app\common\service\wechat;


use app\common\service\wechat\WeChatConfigService;
use EasyWeChat\Kernel\Exceptions\Exception;
use EasyWeChat\MiniApp\Application;


/**
 * 微信功能类
 * Class WeChatMnpService
 * @package app\common\service
 */
class WeChatMnpService
{

    protected $app;

    protected $config;

    public function __construct()
    {
        $this->config = $this->getConfig();
        $this->app = new Application($this->config);
    }


    /**
     * @notes 配置
     * @return array
     * @throws \Exception
     * @author 段誉
     * @date 2023/2/27 12:03
     */
    protected function getConfig()
    {
        $config = WeChatConfigService::getMnpConfig();
        if (empty($config['app_id']) || empty($config['secret'])) {
            throw new \Exception('请先设置小程序配置');
        }
        return $config;
    }


    /**
     * @notes 小程序-根据code获取微信信息
     * @param string $code
     * @return array
     * @throws Exception
     * @throws \EasyWeChat\Kernel\Exceptions\HttpException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @author 段誉
     * @date 2023/2/27 11:03
     */
    public function getMnpResByCode(string $code)
    {
        $utils = $this->app->getUtils();
        $response = $utils->codeToSession($code);

        if (!isset($response['openid']) || empty($response['openid'])) {
            throw new Exception('获取openID失败');
        }

        return $response;
    }


    /**
     * @notes 获取手机号
     * @param string $code
     * @return \EasyWeChat\Kernel\HttpClient\Response|\Symfony\Contracts\HttpClient\ResponseInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @author 段誉
     * @date 2023/2/27 11:46
     */
    public function getUserPhoneNumber(string $code)
    {
        return $this->app->getClient()->postJson('wxa/business/getuserphonenumber', [
            'code' => $code,
        ]);
    }

    /**
     * @notes 发送订阅消息
     * @param string $touser 接收者openid
     * @param string $templateId 模板ID
     * @param array $data 模板数据
     * @return array
     * @throws \Exception
     * @author 精解析题库
     * @date 2026/01/13
     */
    public function sendSubscribeMessage(string $touser, string $templateId, array $data, string $page = ''): array
    {
        try {
            $params = [
                'touser' => $touser,
                'template_id' => $templateId,
                'data' => $data,
                'miniprogram_state' => 'formal'
            ];

            // 如果有跳转页面，添加到参数中
            if (!empty($page)) {
                $params['page'] = $page;
            }

            $response = $this->app->getClient()->postJson('cgi-bin/message/subscribe/send', $params);

            return [
                'success' => $response['errcode'] == 0,
                'errmsg' => $response['errmsg'] ?? '',
                'msgid' => $response['msgid'] ?? '',
                'data' => $response
            ];
        } catch (\Exception $e) {
            \think\facade\Log::error('sendSubscribeMessage 发送失败: ' . json_encode([
                'touser' => $touser,
                'template_id' => $templateId,
                'data' => $data,
                'page' => $page,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE));
            
            return [
                'success' => false,
                'errmsg' => $e->getMessage(),
                'msgid' => '',
                'data' => null
            ];
        }
    }

    /**
     * @notes 根据订阅类型发送统一订阅消息
     * @param string $type 订阅类型：countdown(倒计时)、version_update(版本更新)
     * @param string $touser 接收者openid
     * @param string $templateId 模板ID
     * @param array $data 模板数据
     * @return array
     * @throws \Exception
     * @author 精解析题库
     * @date 2026/01/13
     */
    public function sendSubscribeMessageByType(string $type, string $touser, string $templateId, array $data): array
    {
        // 根据不同类型设置不同的跳转路径
        $page = '';
        switch ($type) {
            case 'version_update':
                $page = '/subpages/common/update';
                break;
            case 'countdown':
                // 倒计时需要根据实际ID拼接页面路径
                if (isset($data['countdown_id'])) {
                    $page = '/subpages/countdown/detail?id=' . $data['countdown_id'];
                }
                break;
        }
        
        // 调用统一发送方法
        return $this->sendSubscribeMessage($touser, $templateId, $data, $page);
    }

    /**
     * @notes 发送版本更新订阅消息
     * @param array $userIds 用户ID数组
     * @param string $version 版本号
     * @param string $info 更新内容
     * @param int $releaseTime 发布时间
     * @param string $templateId 模板ID
     * @return array
     * @throws \Exception
     * @author 精解析题库
     * @date 2026/01/13
     */
    public function sendVersionUpdateMessage(array $userIds, string $version, string $info, int $releaseTime, string $templateId): array
    {
        try {
            $successCount = 0;
            $failCount = 0;
            $results = [];

            foreach ($userIds as $userId) {
                $user = \app\common\model\user\User::where('id', $userId)->field('openid')->find();
                
                if (!$user || empty($user['openid'])) {
                    \think\facade\Log::warning('sendVersionUpdateMessage 用户openid不存在: ' . json_encode([
                        'user_id' => $userId
                    ], JSON_UNESCAPED_UNICODE));
                    $failCount++;
                    continue;
                }

                $result = $this->sendSubscribeMessage(
                    $user['openid'],
                    $templateId,
                    [
                        'thing1' => [
                            'value' => $version . "\n" . $info
                        ],
                        'time2' => [
                            'value' => date('Y-m-d H:i:s', $releaseTime)
                        ],
                        'thing3' => [
                            'value' => '感谢您使用我们的应用，欢迎体验新功能并提出宝贵意见！'
                        ]
                    ]
                );

                if ($result['success']) {
                    $successCount++;
                } else {
                    $failCount++;
                    \think\facade\Log::error('sendVersionUpdateMessage 发送失败: ' . json_encode([
                        'user_id' => $userId,
                        'openid' => $user['openid'],
                        'result' => $result
                    ], JSON_UNESCAPED_UNICODE));
                }

                $results[] = [
                    'user_id' => $userId,
                    'openid' => $user['openid'] ?? '',
                    'success' => $result['success'],
                    'errmsg' => $result['errmsg']
                ];
            }

            \think\facade\Log::info('sendVersionUpdateMessage 发送完成: ' . json_encode([
                'total_users' => count($userIds),
                'success_count' => $successCount,
                'fail_count' => $failCount
            ], JSON_UNESCAPED_UNICODE));

            return [
                'success' => $successCount > 0,
                'total' => count($userIds),
                'success_count' => $successCount,
                'fail_count' => $failCount,
                'results' => $results
            ];
        } catch (\Exception $e) {
            \think\facade\Log::error('sendVersionUpdateMessage 执行失败: ' . json_encode([
                'user_ids' => $userIds,
                'version' => $version,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE));
            
            return [
                'success' => false,
                'total' => count($userIds),
                'success_count' => 0,
                'fail_count' => count($userIds),
                'results' => [],
                'error' => $e->getMessage()
            ];
        }
    }
}