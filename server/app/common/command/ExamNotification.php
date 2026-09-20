<?php
declare(strict_types=1);

namespace app\common\command;

use app\api\logic\SubscribeLogic;
use app\common\model\exam\TenantExamExamination;
use app\common\model\user\UserSubscribe;
use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * 考试提醒定时任务
 * 检查即将开始的考试，向订阅的用户发送提醒
 * Class ExamNotification
 * @package app\common\command
 */
class ExamNotification extends Command
{
    protected function configure()
    {
        $this->setName('exam:notification')
            ->setDescription('考试提醒通知（提前1天、3小时、30分钟、5分钟通知）');
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
        $lockFile = runtime_path() . 'exam_notification.lock';
        
        if (file_exists($lockFile)) {
            $lockTime = filemtime($lockFile);
            // 如果锁文件存在且在30分钟内，跳过执行
            if (time() - $lockTime < 1800) {
                $output->warning('⚠ 考试提醒任务正在执行中，跳过本次执行');
                return 0;
            }
            // 超过30分钟的锁文件视为异常，删除后继续执行
            @unlink($lockFile);
        }
        
        // 创建锁文件
        file_put_contents($lockFile, date('Y-m-d H:i:s'));
        
        $startTime = microtime(true);
        $output->writeln('========== 开始检查考试提醒情况 =========='); 
        $output->writeln('执行时间: ' . date('Y-m-d H:i:s'));

        try {
            $totalNotified = 0;
            
            // 获取所有已发布的考试
            $examinations = TenantExamExamination::where('status', 1)
                ->where('start_time', '>', time()) // 只处理未来的考试
                ->field('id, title, description, start_time, end_time')
                ->select();

            foreach ($examinations as $exam) {
                // 计算考试提醒时间点
                $sendTimes = SubscribeLogic::calculateExamSendTimes($exam['start_time'], $exam['end_time']);
                
                foreach ($sendTimes as $sendTimeInfo) {
                    $sendTime = $sendTimeInfo['time'];
                    $sendType = $sendTimeInfo['type'];
                    
                    // 检查是否接近发送时间（在当前时间的1分钟内）
                    if (abs(time() - $sendTime) <= 60) {
                        // 获取订阅了此考试的用户
                        $subscribers = UserSubscribe::where([
                            'type' => 'exam',
                            'related_id' => $exam['id'],
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
                                'type' => 'exam',
                                'related_id' => $exam['id'],
                                'message_data' => [
                                    'thing1' => ['value' => $exam['title']],
                                    'thing2' => ['value' => $exam['description'] ?? '考试提醒'],
                                    'time3' => ['value' => date('Y-m-d H:i:s', $exam['start_time'])],
                                    'time4' => ['value' => date('Y-m-d H:i:s', $exam['end_time'])]
                                ]
                            ];
                            
                            // 发送订阅消息
                            $result = SubscribeLogic::sendOneTimeSubscribeMsg($params);
                            $resultData = json_decode($result->getContent(), true);
                            
                            if ($resultData['code'] === 0) {
                                $totalNotified++;
                                $output->writeln("✓ 考试提醒发送成功: {$exam['title']} (用户ID: {$lockedSubscribe['user_id']})");
                                
                                // 更新推送状态
                                $lockedSubscribe->is_pushed = 1;
                                $lockedSubscribe->save();
                            } else {
                                $output->writeln("✗ 考试提醒发送失败: {$exam['title']} (用户ID: {$lockedSubscribe['user_id']}) - {$resultData['msg']}");
                            }
                        }
                    }
                }
            }
            
            // 获取推送统计信息
            $stats = \app\api\logic\SubscribeLogic::getPushStats('exam', 1);
            
            $endTime = microtime(true);
            $useTime = round($endTime - $startTime, 2);
            
            $output->writeln('========== 检查完成 ==========');
            $output->writeln("总共发送提醒: {$totalNotified} 条");
            $output->writeln("推送统计 - 总数: {$stats['total']}, 成功: {$stats['success']}, 失败: {$stats['failure']}, 成功率: {$stats['success_rate']}%");
            $output->writeln("执行耗时: {$useTime}秒");
            $output->info('✓ 考试提醒任务执行成功');
            
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