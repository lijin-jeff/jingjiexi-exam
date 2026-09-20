<?php
declare(strict_types=1);

namespace app\common\command;

use app\api\logic\SubscribeLogic;
use app\common\model\user\UserSubscribe;
use app\common\model\exam\TenantVersionUpdate;
use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * 版本更新提醒定时任务
 * 检查新版本发布并向订阅用户发送提醒
 * Class VersionUpdateNotification
 * @package app\common\command
 */
class VersionUpdateNotification extends Command
{
    protected function configure()
    {
        $this->setName('version:update-notification')
            ->setDescription('版本更新提醒通知（检查新版本并发送提醒）');
    }

    /**
     * @notes 执行命令
     * @param Input $input
     * @param Output $output
     * @return int
     */
    protected function execute(Input $input, Output $output)
    {
        // 检查执行锁，防止重复执行
        $lockFile = runtime_path() . 'version_update_notification.lock';
        
        if (file_exists($lockFile)) {
            $lockTime = filemtime($lockFile);
            // 如果锁文件存在且在30分钟内，跳过执行
            if (time() - $lockTime < 1800) {
                $output->warning('⚠ 版本更新提醒任务正在执行中，跳过本次执行');
                return 0;
            }
            // 超过30分钟的锁文件视为异常，删除后继续执行
            @unlink($lockFile);
        }
        
        // 创建锁文件
        file_put_contents($lockFile, date('Y-m-d H:i:s'));
        
        $startTime = microtime(true);
        $output->writeln('========== 开始检查版本更新提醒情况 ==========');
        $output->writeln('执行时间: ' . date('Y-m-d H:i:s'));

        try {
            $totalNotified = 0;
            
            // 获取最近发布的版本更新（过去7天内发布的）
            $recentUpdates = TenantVersionUpdate::where('status', 1)
                ->where('release_time', '>', time() - 7 * 24 * 3600) // 过去7天内
                ->where('release_time', '<=', time())
                ->field('id, version, info, release_time')
                ->select();

            foreach ($recentUpdates as $update) {
                // 获取订阅了版本更新的用户
                $subscribers = UserSubscribe::where([
                    'type' => 'version_update',
                    'related_id' => $update['id'],
                    'is_pushed' => 0, // 未推送
                    'subscribe_status' => 1 // 已订阅
                ])->select();
                
                foreach ($subscribers as $subscribe) {
                    // 使用数据库锁确保并发安全
                    $lockedSubscribe = UserSubscribe::where('id', $subscribe['id'])->lock(true)->find();
                    
                    // 检查是否已经被其他进程处理
                    if (!$lockedSubscribe || $lockedSubscribe->is_pushed != 0) {
                        continue; // 跳过已被处理的订阅
                    }
                    
                    // 准备发送参数
                    $params = [
                        'user_id' => $lockedSubscribe['user_id'],
                        'template_id' => $lockedSubscribe['template_id'],
                        'type' => 'version_update',
                        'related_id' => $update['id'],
                        'message_data' => [
                            'thing1' => ['value' => $update['version'] . "\n" . $update['info']],
                            'time2' => ['value' => date('Y-m-d H:i:s', $update['release_time'])],
                            'thing3' => ['value' => '感谢您使用我们的应用，欢迎体验新功能并提出宝贵意见！']
                        ]
                    ];
                    
                    // 发送订阅消息
                    $result = SubscribeLogic::sendOneTimeSubscribeMsg($params);
                    $resultData = json_decode($result->getContent(), true);
                    
                    if ($resultData['code'] === 0) {
                        $totalNotified++;
                        $output->writeln("✓ 版本更新提醒发送成功: {$update['version']} (用户ID: {$lockedSubscribe['user_id']})");
                        
                        // 更新推送状态
                        $lockedSubscribe->is_pushed = 1;
                        $lockedSubscribe->save();
                    } else {
                        $output->writeln("✗ 版本更新提醒发送失败: {$update['version']} (用户ID: {$lockedSubscribe['user_id']}) - {$resultData['msg']}");
                    }
                }
            }
            
            // 获取推送统计信息
            $stats = \app\api\logic\SubscribeLogic::getPushStats('version_update', 1);
            
            $endTime = microtime(true);
            $useTime = round($endTime - $startTime, 2);
            
            $output->writeln('========== 检查完成 ==========');
            $output->writeln("总共发送提醒: {$totalNotified} 条");
            $output->writeln("推送统计 - 总数: {$stats['total']}, 成功: {$stats['success']}, 失败: {$stats['failure']}, 成功率: {$stats['success_rate']}%");
            $output->writeln("执行耗时: {$useTime}秒");
            $output->info('✓ 版本更新提醒任务执行成功');
            
            // 删除锁文件
            @unlink($lockFile);
            return 0; // 成功
            
        } catch (\Exception $e) {
            $output->error('✗ 执行异常: ' . $e->getMessage());
            $output->writeln('错误位置: ' . $e->getFile() . ' Line:' . $e->getLine());
            
            // 删除锁文件
            @unlink($lockFile);
            return 1; // 异常
        }
    }
}