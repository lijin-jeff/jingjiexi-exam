-- ========================================
-- 排行榜性能优化SQL - 添加必要索引
-- 执行时间：2025-12-20
-- 用途：提升排行榜统计查询性能
-- ========================================

-- 1. 为考试历史表添加复合索引，提升聚合查询性能
-- 索引覆盖：tenant_id + create_time + delete_time
ALTER TABLE `la_tenant_exam_examination_history` 
ADD INDEX `idx_tenant_time_stat` (`tenant_id`, `create_time`, `delete_time`);

-- 为用户维度统计添加索引
ALTER TABLE `la_tenant_exam_examination_history` 
ADD INDEX `idx_tenant_user_time` (`tenant_id`, `user_uid`, `create_time`);

-- 2. 日榜表索引优化
ALTER TABLE `la_tenant_exam_ranking_daily`
ADD INDEX `idx_date_ranking` (`create_date`, `ranking`);

-- 3. 周榜表索引优化  
ALTER TABLE `la_tenant_exam_ranking_weekly`
ADD INDEX `idx_year_week_ranking` (`year`, `week_number`, `ranking`);

-- 4. 月榜表索引优化
ALTER TABLE `la_tenant_exam_ranking_monthly`
ADD INDEX `idx_year_month_ranking` (`year`, `month`, `ranking`);

-- 5. 总榜表索引优化（已有索引，检查即可）
-- 索引：idx_tenant_ranking, idx_tenant_correct

-- ========================================
-- 说明：
-- 1. 这些索引会显著提升排行榜统计查询的性能
-- 2. 建议在业务低峰期执行（如凌晨）
-- 3. 如果表数据量很大（>100万），索引创建可能需要较长时间
-- 4. 执行前建议先备份数据库
-- ========================================
