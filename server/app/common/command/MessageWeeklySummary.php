<?php
declare(strict_types=1);

namespace app\common\command;

use app\common\logic\user\MessageService;
use app\common\model\exam\TenantExamExaminationHistory;
use app\common\model\user\User;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Db;

/**
 * 每周学习总结定时任务
 * 每周一统计上周学习数据并发送总结通知
 * Class MessageWeeklySummary
 * @package app\common\command
 */
class MessageWeeklySummary extends Command
{
    protected function configure()
    {
        $this->setName('message:weekly-summary')
            ->setDescription('每周学习总结（每周一统计上周数据）');
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
        $lockFile = runtime_path() . 'message_weekly_summary.lock';
        
        if (file_exists($lockFile)) {
            $lockTime = filemtime($lockFile);
            // 如果锁文件存在且在2小时内，跳过执行
            if (time() - $lockTime < 7200) {
                $output->warning('⚠ 每周学习总结任务正在执行中，跳过本次执行');
                return 0;
            }
            @unlink($lockFile);
        }
        
        // 创建锁文件
        file_put_contents($lockFile, date('Y-m-d H:i:s'));
        
        $startTime = microtime(true);
        $output->writeln('========== 开始生成每周学习总结 ==========');
        $output->writeln('执行时间: ' . date('Y-m-d H:i:s'));

        try {
            // 计算上周的时间范围
            $lastWeekStart = strtotime('last monday 00:00:00', strtotime('this monday'));
            $lastWeekEnd = strtotime('last sunday 23:59:59', strtotime('this monday'));
            
            $output->writeln('统计周期: ' . date('Y-m-d', $lastWeekStart) . ' 至 ' . date('Y-m-d', $lastWeekEnd));
            
            // 获取所有活跃用户（上周有考试记录的用户）
            $activeUsers = TenantExamExaminationHistory::where('submit_time', '>=', $lastWeekStart)
                ->where('submit_time', '<=', $lastWeekEnd)
                ->group('user_uid')
                ->column('user_uid');
            
            $output->writeln("活跃用户数: " . count($activeUsers));
            
            $totalSent = 0;
            
            foreach ($activeUsers as $userId) {
                // 统计该用户上周的学习数据
                $summary = $this->getUserWeeklySummary($userId, $lastWeekStart, $lastWeekEnd);
                
                if ($summary) {
                    // 发送每周总结通知
                    MessageService::weeklySummaryNotify(
                        $userId,
                        $summary['exam_count'],
                        $summary['total_questions'],
                        $summary['correct_count'],
                        $summary['accuracy_rate']
                    );
                    $totalSent++;
                }
            }
            
            $endTime = microtime(true);
            $useTime = round($endTime - $startTime, 2);
            
            $output->writeln('========== 总结生成完成 ==========');
            $output->writeln("发送总结通知: {$totalSent} 条");
            $output->writeln("执行耗时: {$useTime}秒");
            $output->info('✓ 每周学习总结任务执行成功');
            
            // 删除锁文件
            @unlink($lockFile);
            return 0;
            
        } catch (\Exception $e) {
            $output->error('✗ 执行异常: ' . $e->getMessage());
            $output->writeln('错误位置: ' . $e->getFile() . ' Line:' . $e->getLine());
            
            @unlink($lockFile);
            return 1;
        }
    }
    
    /**
     * 获取用户每周学习总结数据
     * @param int $userId 用户ID
     * @param int $startTime 开始时间
     * @param int $endTime 结束时间
     * @return array|null
     */
    private function getUserWeeklySummary(int $userId, int $startTime, int $endTime): ?array
    {
        try {
            // 查询用户上周的考试记录
            $examHistory = TenantExamExaminationHistory::where('user_uid', $userId)
                ->where('submit_time', '>=', $startTime)
                ->where('submit_time', '<=', $endTime)
                ->field('correct_count,error_count,user_score,user_integral')
                ->select()
                ->toArray();
            
            if (empty($examHistory)) {
                return null;
            }
            
            // 统计数据
            $examCount = count($examHistory);
            $totalCorrect = 0;
            $totalError = 0;
            $totalScore = 0;
            $totalIntegral = 0;
            
            foreach ($examHistory as $exam) {
                $totalCorrect += $exam['correct_count'] ?? 0;
                $totalError += $exam['error_count'] ?? 0;
                $totalScore += $exam['user_score'] ?? 0;
                $totalIntegral += $exam['user_integral'] ?? 0;
            }
            
            $totalQuestions = $totalCorrect + $totalError;
            $accuracyRate = $totalQuestions > 0 
                ? round(($totalCorrect / $totalQuestions) * 100, 1) 
                : 0;
            
            return [
                'exam_count' => $examCount,
                'total_questions' => $totalQuestions,
                'correct_count' => $totalCorrect,
                'error_count' => $totalError,
                'accuracy_rate' => $accuracyRate,
                'total_score' => $totalScore,
                'total_integral' => $totalIntegral
            ];
            
        } catch (\Exception $e) {
            return null;
        }
    }
}
