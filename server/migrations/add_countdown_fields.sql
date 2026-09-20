-- ============================================
-- 倒计时表字段迁移
-- 创建时间: 2026-02-28
-- 说明: 添加category_uid和tenant_id字段到倒计时表
-- ============================================

-- 添加category_uid字段
ALTER TABLE `la_tenant_exam_countdown` 
ADD COLUMN `category_uid` varchar(36) NOT NULL DEFAULT '' COMMENT '分类UID' AFTER `user_id`;

-- 添加tenant_id字段
ALTER TABLE `la_tenant_exam_countdown` 
ADD COLUMN `tenant_id` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '租户ID' AFTER `category_uid`;

-- 添加索引
ALTER TABLE `la_tenant_exam_countdown` 
ADD KEY `idx_category_uid` (`category_uid`),
ADD KEY `idx_tenant_id` (`tenant_id`);

-- 修改user_id字段允许为0
ALTER TABLE `la_tenant_exam_countdown` 
MODIFY COLUMN `user_id` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '创建用户ID';
