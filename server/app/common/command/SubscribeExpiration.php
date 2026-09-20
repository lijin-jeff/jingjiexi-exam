<?php
declare(strict_types=1);

namespace app\common\command;

use app\api\logic\SubscribeLogic;
use app\common\model\user\UserSubscribe;
use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * 订阅授权到期提醒定时任务
 * 检查即将到期的订阅授权，提前发送提醒
 * Class SubscribeExpiration
 * @package app\common\command
 */
class SubscribeExpiration extends Command
{
    protected function configure()
    {
        $this->setName('subscribe:expiration')
            ->setDescription('订阅授权到期提醒（检查7天内到期的订阅）');
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
        $lockFile = runtime_path() . 'subscribe_expiration.lock';
        
        if (file_exists($lockFile)) {
            $lockTime = filemtime($lockFile);
            // 如果锁文件存在且在30分钟内，跳过执行
            if (time() - $lockTime < 1800) {
                $output->warning('⚠ 订阅到期提醒任务正在执行中，跳过本次执行');
                return 0;
            }
            // 超过30分钟的锁文件视为异常，删除后继续执行
            @unlink($lockFile);
        }
        
        // 创建锁文件
        file_put_contents($lockFile, date('Y-m-d H:i:s'));
        
        $startTime = microtime(true);
        $output->writeln('========== 开始检查订阅到期情况 ==========');
        $output->writeln('执行时间: ' . date('Y-m-d H:i:s'));

        try {
            $totalNotified = 0;
            
            // 计算7天前的时间戳
            $sevenDaysAgo = time() - 7 * 24 * 3600;
            
            // 获取7天内订阅的记录（即将到期的订阅）
            $expiringSubscribes = UserSubscribe::where('subscribe_time', '>', $sevenDaysAgo)
                ->where('is_pushed', 0) // 未标记为过期
                ->where('subscribe_status', 1) // 已订阅
                ->select();

            foreach ($expiringSubscribes as $subscribe) {
                // 检查是否接近7天期限（在到期前1天内）
                $daysSinceSubscribe = (time() - $subscribe['subscribe_time']) / (24 * 3600);
                
                // 如果订阅时间接近7天（比如还剩1天），发送提醒
                if ($daysSinceSubscribe >= 6 && $daysSinceSubscribe < 7) { // 已经订阅了6天但不到7天，即将到期
                    // 准备发送参数
                    $params = [
                        'user_id' => $subscribe['user_id'],
                        'template_id' => $subscribe['template_id'],
                        'type' => $subscribe['type'],
                        'related_id' => $subscribe['related_id'],
                        'message_data' => [
                            'thing1' => ['value' => '订阅授权提醒'],
                            'time2' => ['value' => date('Y-m-d H:i:s', $subscribe['subscribe_time'] + 7 * 24 * 3600)], // 显示到期时间
                            'thing3' => ['value' => '您的订阅授权即将到期，请留意相关提醒功能']
                        ]
                    ];
                    
                    // 发送订阅消息
                    $result = SubscribeLogic::sendOneTimeSubscribeMsg($params);
                    $resultData = json_decode($result->getContent(), true);
                    
                    if ($resultData['code'] === 0) {
                        $totalNotified++;
                        $output->writeln("✓ 订阅到期提醒发送成功: 类型({$subscribe['type']}) (用户ID: {$subscribe['user_id']})");
                        
                        // 更新推送状态
                        $subscribe->is_pushed = 1;
                        $subscribe->save();
                    } else {
                        $output->writeln("✗ 订阅到期提醒发送失败: 类型({$subscribe['type']}) (用户ID: {$subscribe['user_id']}) - {$resultData['msg']}");
                    }
                }
            }
            
            $endTime = microtime(true);
            $useTime = round($endTime - $startTime, 2);
            
            $output->writeln('========== 检查完成 ==========');
            $output->writeln("总共发送提醒: {$totalNotified} 条");
            $output->writeln("执行耗时: {$useTime}秒");
            $output->info('✓ 订阅到期提醒任务执行成功');
            
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