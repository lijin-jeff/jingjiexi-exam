-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2025-12-26 18:44:21
-- 服务器版本： 5.7.44-log
-- PHP 版本： 7.3.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `dt_allpp_cn`
--

-- --------------------------------------------------------

--
-- 表的结构 `la_admin`
--

CREATE TABLE `la_admin` (
  `id` int(11) UNSIGNED NOT NULL,
  `root` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否超级管理员 0-否 1-是',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '名称',
  `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '用户头像',
  `account` varchar(32) NOT NULL DEFAULT '' COMMENT '账号',
  `password` varchar(32) NOT NULL COMMENT '密码',
  `login_time` int(10) DEFAULT NULL COMMENT '最后登录时间',
  `login_ip` varchar(39) DEFAULT '' COMMENT '最后登录ip',
  `multipoint_login` tinyint(1) UNSIGNED DEFAULT '1' COMMENT '是否支持多处登录：1-是；0-否；',
  `disable` tinyint(1) UNSIGNED DEFAULT '0' COMMENT '是否禁用：0-否；1-是；',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(10) DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_admin_dept`
--

CREATE TABLE `la_admin_dept` (
  `admin_id` int(10) NOT NULL DEFAULT '0' COMMENT '管理员id',
  `dept_id` int(10) NOT NULL DEFAULT '0' COMMENT '部门id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='部门关联表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_admin_jobs`
--

CREATE TABLE `la_admin_jobs` (
  `admin_id` int(10) NOT NULL COMMENT '管理员id',
  `jobs_id` int(10) NOT NULL COMMENT '岗位id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='岗位关联表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_admin_role`
--

CREATE TABLE `la_admin_role` (
  `admin_id` int(10) NOT NULL COMMENT '管理员id',
  `role_id` int(10) NOT NULL COMMENT '角色id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色关联表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_admin_session`
--

CREATE TABLE `la_admin_session` (
  `id` int(11) UNSIGNED NOT NULL,
  `admin_id` int(11) UNSIGNED NOT NULL COMMENT '用户id',
  `terminal` tinyint(1) NOT NULL DEFAULT '1' COMMENT '客户端类型：1-pc管理后台 2-mobile手机管理后台',
  `token` varchar(32) NOT NULL COMMENT '令牌',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
  `expire_time` int(10) NOT NULL COMMENT '到期时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员会话表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_article`
--

CREATE TABLE `la_article` (
  `id` int(11) NOT NULL COMMENT '文章id',
  `tenant_id` int(11) NOT NULL COMMENT '租户ID',
  `cid` int(11) NOT NULL COMMENT '文章分类',
  `title` varchar(255) NOT NULL COMMENT '文章标题',
  `desc` varchar(255) DEFAULT '' COMMENT '简介',
  `abstract` text COMMENT '文章摘要',
  `image` varchar(128) DEFAULT NULL COMMENT '文章图片',
  `author` varchar(255) DEFAULT '' COMMENT '作者',
  `content` text COMMENT '文章内容',
  `click_virtual` int(10) DEFAULT '0' COMMENT '虚拟浏览量',
  `click_actual` int(11) DEFAULT '0' COMMENT '实际浏览量',
  `is_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示:1-是.0-否',
  `sort` int(5) DEFAULT '0' COMMENT '排序',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文章表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_article_cate`
--

CREATE TABLE `la_article_cate` (
  `id` int(11) NOT NULL COMMENT '文章分类id',
  `tenant_id` int(11) NOT NULL COMMENT '租户ID',
  `name` varchar(90) DEFAULT NULL COMMENT '分类名称',
  `sort` int(11) DEFAULT '0' COMMENT '排序',
  `is_show` tinyint(1) DEFAULT '1' COMMENT '是否显示:1-是;0-否',
  `create_time` int(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(10) DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文章分类表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_article_collect`
--

CREATE TABLE `la_article_collect` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '主键',
  `tenant_id` int(11) NOT NULL COMMENT '租户ID',
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID',
  `article_id` int(10) NOT NULL DEFAULT '0' COMMENT '来源ID',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '收藏状态 0-未收藏 1-已收藏',
  `create_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `delete_time` int(10) DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文章收藏表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_config`
--

CREATE TABLE `la_config` (
  `id` int(11) NOT NULL,
  `type` varchar(30) DEFAULT NULL COMMENT '类型',
  `name` varchar(60) NOT NULL DEFAULT '' COMMENT '名称',
  `value` text COMMENT '值',
  `create_time` int(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='配置表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_content_push_record`
--

CREATE TABLE `la_content_push_record` (
  `id` int(11) UNSIGNED NOT NULL COMMENT '记录ID',
  `content_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '内容类型: resource-资料 article-文章',
  `content_id` int(11) UNSIGNED NOT NULL COMMENT '内容ID',
  `tenant_id` int(11) UNSIGNED NOT NULL DEFAULT '1' COMMENT '租户ID',
  `push_time` int(11) UNSIGNED NOT NULL COMMENT '推送时间',
  `push_count` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '推送用户数量',
  `create_time` int(11) UNSIGNED NOT NULL COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='内容推送记录表';

-- --------------------------------------------------------

--
-- 替换视图以便查看 `la_data_search`
-- （参见下面的实际视图）
--
CREATE TABLE `la_data_search` (
`uid` varchar(36),
`title` varchar(255),
`image` mediumtext,
`data_type` varchar(12) DEFAULT '' COMMENT '数据类型',
`publish_time` int(11) DEFAULT '0' COMMENT '发布时间'
);

-- --------------------------------------------------------

--
-- 表的结构 `la_decorate_page`
--

CREATE TABLE `la_decorate_page` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '主键',
  `tenant_id` int(10) NOT NULL COMMENT '租户ID',
  `type` tinyint(2) UNSIGNED NOT NULL DEFAULT '10' COMMENT '页面类型 1=商城首页, 2=个人中心, 3=客服设置 4-PC首页',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '页面名称',
  `data` text COMMENT '页面数据',
  `meta` text COMMENT '页面设置',
  `create_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) UNSIGNED NOT NULL COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='装修页面配置表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_decorate_tabbar`
--

CREATE TABLE `la_decorate_tabbar` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '主键',
  `tenant_id` int(10) NOT NULL COMMENT '租户ID',
  `name` varchar(20) NOT NULL DEFAULT '' COMMENT '导航名称',
  `selected` varchar(200) NOT NULL DEFAULT '' COMMENT '未选图标',
  `unselected` varchar(200) NOT NULL DEFAULT '' COMMENT '已选图标',
  `link` varchar(200) DEFAULT NULL COMMENT '链接地址',
  `is_show` tinyint(255) UNSIGNED NOT NULL DEFAULT '1' COMMENT '显示状态',
  `create_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='装修底部导航表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_dept`
--

CREATE TABLE `la_dept` (
  `id` int(11) NOT NULL COMMENT 'id',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '部门名称',
  `pid` bigint(20) NOT NULL DEFAULT '0' COMMENT '上级部门id',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `leader` varchar(64) DEFAULT NULL COMMENT '负责人',
  `mobile` varchar(16) DEFAULT NULL COMMENT '联系电话',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '部门状态（0停用 1正常）',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(10) DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='部门表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_dev_crontab`
--

CREATE TABLE `la_dev_crontab` (
  `id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL COMMENT '定时任务名称',
  `type` tinyint(1) NOT NULL COMMENT '类型 1-定时任务',
  `system` tinyint(4) DEFAULT '0' COMMENT '是否系统任务 0-否 1-是',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `command` varchar(64) NOT NULL COMMENT '命令内容',
  `params` varchar(64) DEFAULT '' COMMENT '参数',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1-运行 2-停止 3-错误',
  `expression` varchar(64) NOT NULL COMMENT '运行规则',
  `error` varchar(256) DEFAULT NULL COMMENT '运行失败原因',
  `last_time` int(11) DEFAULT NULL COMMENT '最后执行时间',
  `time` varchar(64) DEFAULT '0' COMMENT '实时执行时长',
  `max_time` varchar(64) DEFAULT '0' COMMENT '最大执行时长',
  `create_time` int(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(10) DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='计划任务表';

-- --------------------------------------------------------

--
-- 表的结构 `la_dict_data`
--

CREATE TABLE `la_dict_data` (
  `id` int(11) NOT NULL COMMENT 'id',
  `name` varchar(255) NOT NULL COMMENT '数据名称',
  `value` varchar(255) NOT NULL COMMENT '数据值',
  `type_id` int(11) NOT NULL COMMENT '字典类型id',
  `type_value` varchar(255) NOT NULL COMMENT '字典类型',
  `sort` int(10) DEFAULT '0' COMMENT '排序值',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 0-停用 1-正常',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(10) DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='字典数据表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_dict_type`
--

CREATE TABLE `la_dict_type` (
  `id` int(11) NOT NULL COMMENT 'id',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '字典名称',
  `type` varchar(255) NOT NULL DEFAULT '' COMMENT '字典类型名称',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 0-停用 1-正常',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(10) DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='字典类型表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_file`
--

CREATE TABLE `la_file` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '主键ID',
  `cid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '类目ID',
  `source_id` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '上传者id',
  `source` tinyint(1) NOT NULL DEFAULT '0' COMMENT '来源类型[0-后台,1-用户]',
  `type` tinyint(2) UNSIGNED NOT NULL DEFAULT '10' COMMENT '类型[10=图片, 20=视频]',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '文件名称',
  `uri` varchar(200) NOT NULL COMMENT '文件路径',
  `create_time` int(10) UNSIGNED DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(10) DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文件表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_file_cate`
--

CREATE TABLE `la_file_cate` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '主键ID',
  `pid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '父级ID',
  `type` tinyint(2) UNSIGNED NOT NULL DEFAULT '10' COMMENT '类型[10=图片，20=视频，30=文件]',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '分类名称',
  `create_time` int(10) UNSIGNED DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) UNSIGNED DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(10) UNSIGNED DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文件分类表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_generate_column`
--

CREATE TABLE `la_generate_column` (
  `id` int(11) NOT NULL COMMENT 'id',
  `table_id` int(11) NOT NULL DEFAULT '0' COMMENT '表id',
  `column_name` varchar(100) NOT NULL DEFAULT '' COMMENT '字段名称',
  `column_comment` varchar(300) NOT NULL DEFAULT '' COMMENT '字段描述',
  `column_type` varchar(100) NOT NULL DEFAULT '' COMMENT '字段类型',
  `is_required` tinyint(1) DEFAULT '0' COMMENT '是否必填 0-非必填 1-必填',
  `is_pk` tinyint(1) DEFAULT '0' COMMENT '是否为主键 0-不是 1-是',
  `is_insert` tinyint(1) DEFAULT '0' COMMENT '是否为插入字段 0-不是 1-是',
  `is_update` tinyint(1) DEFAULT '0' COMMENT '是否为更新字段 0-不是 1-是',
  `is_lists` tinyint(1) DEFAULT '0' COMMENT '是否为列表字段 0-不是 1-是',
  `is_query` tinyint(1) DEFAULT '0' COMMENT '是否为查询字段 0-不是 1-是',
  `query_type` varchar(100) DEFAULT '=' COMMENT '查询类型',
  `view_type` varchar(100) DEFAULT 'input' COMMENT '显示类型',
  `dict_type` varchar(255) DEFAULT '' COMMENT '字典类型',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '修改时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='代码生成表字段信息表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_generate_table`
--

CREATE TABLE `la_generate_table` (
  `id` int(11) NOT NULL COMMENT 'id',
  `table_name` varchar(200) NOT NULL DEFAULT '' COMMENT '表名称',
  `table_comment` varchar(300) NOT NULL DEFAULT '' COMMENT '表描述',
  `template_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '模板类型 0-单表(curd) 1-树表(curd)',
  `author` varchar(100) DEFAULT '' COMMENT '作者',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `generate_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '生成方式  0-压缩包下载 1-生成到模块',
  `module_name` varchar(100) DEFAULT '' COMMENT '模块名',
  `class_dir` varchar(100) DEFAULT '' COMMENT '类目录名',
  `class_comment` varchar(100) DEFAULT '' COMMENT '类描述',
  `admin_id` int(11) DEFAULT '0' COMMENT '管理员id',
  `menu` text COMMENT '菜单配置',
  `delete` text COMMENT '删除配置',
  `tree` text COMMENT '树表配置',
  `relations` text COMMENT '关联配置',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '修改时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='代码生成表信息表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_hot_search`
--

CREATE TABLE `la_hot_search` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '主键',
  `tenant_id` int(11) NOT NULL COMMENT '租户ID',
  `name` varchar(200) NOT NULL DEFAULT '' COMMENT '关键词',
  `sort` smallint(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT '排序号',
  `create_time` int(10) DEFAULT NULL COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='热门搜索表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_jobs`
--

CREATE TABLE `la_jobs` (
  `id` int(11) NOT NULL COMMENT 'id',
  `name` varchar(50) NOT NULL COMMENT '岗位名称',
  `code` varchar(64) NOT NULL COMMENT '岗位编码',
  `sort` int(11) DEFAULT '0' COMMENT '显示顺序',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态（0停用 1正常）',
  `remark` varchar(500) DEFAULT NULL COMMENT '备注',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(10) DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='岗位表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_message_config`
--

CREATE TABLE `la_message_config` (
  `id` int(11) UNSIGNED NOT NULL COMMENT '配置ID',
  `key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '配置键名',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '配置名称',
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '消息类型',
  `enable` tinyint(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '是否启用: 0-禁用 1-启用',
  `template` text COLLATE utf8mb4_unicode_ci COMMENT '消息模板',
  `remark` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备注说明',
  `create_time` int(11) UNSIGNED NOT NULL COMMENT '创建时间',
  `update_time` int(11) UNSIGNED DEFAULT NULL COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='消息推送配置表';

-- --------------------------------------------------------

--
-- 表的结构 `la_notice_record`
--

CREATE TABLE `la_notice_record` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID',
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '用户id',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
  `content` text NOT NULL COMMENT '内容',
  `scene_id` int(10) UNSIGNED DEFAULT '0' COMMENT '场景',
  `read` tinyint(1) DEFAULT '0' COMMENT '已读状态;0-未读,1-已读',
  `recipient` tinyint(1) DEFAULT '0' COMMENT '通知接收对象类型;1-会员;2-商家;3-平台;4-游客(未注册用户)',
  `send_type` tinyint(1) DEFAULT '0' COMMENT '通知发送类型 1-系统通知 2-短信通知 3-微信模板 4-微信小程序',
  `notice_type` tinyint(1) DEFAULT NULL COMMENT '通知类型 1-业务通知 2-验证码',
  `extra` varchar(255) DEFAULT '' COMMENT '其他',
  `create_time` int(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(10) DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='通知记录表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_notice_setting`
--

CREATE TABLE `la_notice_setting` (
  `id` int(11) NOT NULL,
  `scene_id` int(10) NOT NULL COMMENT '场景id',
  `scene_name` varchar(255) NOT NULL DEFAULT '' COMMENT '场景名称',
  `scene_desc` varchar(255) NOT NULL DEFAULT '' COMMENT '场景描述',
  `recipient` tinyint(1) NOT NULL DEFAULT '1' COMMENT '接收者 1-用户 2-平台',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '通知类型: 1-业务通知 2-验证码',
  `system_notice` text COMMENT '系统通知设置',
  `sms_notice` text COMMENT '短信通知设置',
  `oa_notice` text COMMENT '公众号通知设置',
  `mnp_notice` text COMMENT '小程序通知设置',
  `support` char(10) NOT NULL DEFAULT '' COMMENT '支持的发送类型 1-系统通知 2-短信通知 3-微信模板消息 4-小程序提醒',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='通知设置表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_official_account_reply`
--

CREATE TABLE `la_official_account_reply` (
  `id` int(11) UNSIGNED NOT NULL,
  `tenant_id` int(11) NOT NULL COMMENT '租户ID',
  `name` varchar(64) NOT NULL DEFAULT '' COMMENT '规则名称',
  `keyword` varchar(64) NOT NULL DEFAULT '' COMMENT '关键词',
  `reply_type` tinyint(1) NOT NULL COMMENT '回复类型 1-关注回复 2-关键字回复 3-默认回复',
  `matching_type` tinyint(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '匹配方式：1-全匹配；2-模糊匹配',
  `content_type` tinyint(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '内容类型：1-文本',
  `content` text NOT NULL COMMENT '回复内容',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '启动状态：1-启动；0-关闭',
  `sort` int(11) UNSIGNED NOT NULL DEFAULT '50' COMMENT '排序',
  `create_time` int(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(10) DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='公众号消息回调表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_operation_log`
--

CREATE TABLE `la_operation_log` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL COMMENT '管理员ID',
  `admin_name` varchar(16) NOT NULL DEFAULT '' COMMENT '管理员名称',
  `account` varchar(16) NOT NULL DEFAULT '' COMMENT '管理员账号',
  `action` varchar(64) DEFAULT '' COMMENT '操作名称',
  `type` varchar(8) NOT NULL COMMENT '请求方式',
  `url` varchar(600) NOT NULL COMMENT '访问链接',
  `params` text COMMENT '请求数据',
  `result` text COMMENT '请求结果',
  `ip` varchar(39) NOT NULL DEFAULT '' COMMENT 'ip地址',
  `create_time` int(10) DEFAULT NULL COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统日志表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_pay_config`
--

CREATE TABLE `la_pay_config` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '模版名称',
  `pay_way` tinyint(1) NOT NULL COMMENT '支付方式:1-余额支付;2-微信支付;3-支付宝支付;',
  `config` text COMMENT '对应支付配置(json字符串)',
  `icon` varchar(255) DEFAULT NULL COMMENT '图标',
  `sort` int(5) DEFAULT NULL COMMENT '排序',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='支付配置表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_pay_way`
--

CREATE TABLE `la_pay_way` (
  `id` int(11) UNSIGNED NOT NULL,
  `pay_config_id` int(11) NOT NULL COMMENT '支付配置ID',
  `scene` tinyint(1) NOT NULL COMMENT '场景:1-微信小程序;2-微信公众号;3-H5;4-PC;5-APP;',
  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否默认支付:0-否;1-是;',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态:0-关闭;1-开启;'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='支付方式表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_recharge_order`
--

CREATE TABLE `la_recharge_order` (
  `id` int(11) NOT NULL COMMENT 'id',
  `tenant_id` int(11) NOT NULL COMMENT '租户ID',
  `sn` varchar(64) NOT NULL COMMENT '订单编号',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `pay_sn` varchar(255) DEFAULT '' COMMENT '支付编号-冗余字段，针对微信同一主体不同客户端支付需用不同订单号预留。',
  `pay_way` tinyint(2) NOT NULL DEFAULT '2' COMMENT '支付方式 2-微信支付 3-支付宝支付',
  `pay_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '支付状态：0-待支付；1-已支付',
  `pay_time` int(10) DEFAULT NULL COMMENT '支付时间',
  `order_amount` decimal(10,2) NOT NULL COMMENT '充值金额',
  `order_terminal` tinyint(1) DEFAULT '1' COMMENT '终端',
  `transaction_id` varchar(128) DEFAULT NULL COMMENT '第三方平台交易流水号',
  `refund_status` tinyint(1) DEFAULT '0' COMMENT '退款状态 0-未退款 1-已退款',
  `refund_transaction_id` varchar(255) DEFAULT NULL COMMENT '退款交易流水号',
  `create_time` int(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(10) DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='充值订单表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_refund_log`
--

CREATE TABLE `la_refund_log` (
  `id` int(11) NOT NULL COMMENT 'id',
  `tenant_id` int(11) NOT NULL COMMENT '租户ID',
  `sn` varchar(32) DEFAULT NULL COMMENT '编号',
  `record_id` int(11) NOT NULL COMMENT '退款记录id',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联用户',
  `handle_id` int(11) NOT NULL DEFAULT '0' COMMENT '处理人id（管理员id）',
  `order_amount` decimal(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '订单总的应付款金额，冗余字段',
  `refund_amount` decimal(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '本次退款金额',
  `refund_status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '退款状态，0退款中，1退款成功，2退款失败',
  `refund_msg` text COMMENT '退款信息',
  `create_time` int(10) UNSIGNED DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='退款日志' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_refund_record`
--

CREATE TABLE `la_refund_record` (
  `id` int(11) NOT NULL COMMENT 'id',
  `tenant_id` int(11) NOT NULL COMMENT '租户ID',
  `sn` varchar(32) NOT NULL DEFAULT '' COMMENT '退款编号',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联用户',
  `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '来源订单id',
  `order_sn` varchar(32) NOT NULL COMMENT '来源单号',
  `order_type` varchar(255) DEFAULT 'order' COMMENT '订单来源 order-商品订单 recharge-充值订单',
  `order_amount` decimal(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '订单总的应付款金额，冗余字段',
  `refund_amount` decimal(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '本次退款金额',
  `transaction_id` varchar(255) DEFAULT NULL COMMENT '第三方平台交易流水号',
  `refund_way` tinyint(1) NOT NULL DEFAULT '1' COMMENT '退款方式 1-线上退款 2-线下退款',
  `refund_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '退款类型 1-后台退款',
  `refund_status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '退款状态，0退款中，1退款成功，2退款失败',
  `create_time` int(10) UNSIGNED DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='退款记录' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_sms_log`
--

CREATE TABLE `la_sms_log` (
  `id` int(11) NOT NULL COMMENT 'id',
  `tenant_id` int(11) NOT NULL COMMENT '租户ID',
  `scene_id` int(11) NOT NULL COMMENT '场景id',
  `mobile` varchar(11) NOT NULL COMMENT '手机号码',
  `content` varchar(255) NOT NULL COMMENT '发送内容',
  `code` varchar(32) DEFAULT NULL COMMENT '发送关键字（注册、找回密码）',
  `is_verify` tinyint(1) DEFAULT '0' COMMENT '是否已验证；0-否；1-是',
  `check_num` int(5) DEFAULT '0' COMMENT '验证次数',
  `send_status` tinyint(1) NOT NULL COMMENT '发送状态：0-发送中；1-发送成功；2-发送失败',
  `send_time` int(10) NOT NULL COMMENT '发送时间',
  `results` text COMMENT '短信结果',
  `create_time` int(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(10) DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='短信记录表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_system_menu`
--

CREATE TABLE `la_system_menu` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '主键',
  `pid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '上级菜单',
  `type` char(2) NOT NULL DEFAULT '' COMMENT '权限类型: M=目录，C=菜单，A=按钮',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '菜单名称',
  `icon` varchar(100) NOT NULL DEFAULT '' COMMENT '菜单图标',
  `sort` smallint(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT '菜单排序',
  `perms` varchar(100) NOT NULL DEFAULT '' COMMENT '权限标识',
  `paths` varchar(100) NOT NULL DEFAULT '' COMMENT '路由地址',
  `component` varchar(200) NOT NULL DEFAULT '' COMMENT '前端组件',
  `selected` varchar(200) NOT NULL DEFAULT '' COMMENT '选中路径',
  `params` varchar(200) NOT NULL DEFAULT '' COMMENT '路由参数',
  `is_cache` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否缓存: 0=否, 1=是',
  `is_show` tinyint(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '是否显示: 0=否, 1=是',
  `is_disable` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否禁用: 0=否, 1=是',
  `create_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统菜单表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_system_role`
--

CREATE TABLE `la_system_role` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(16) NOT NULL DEFAULT '' COMMENT '名称',
  `desc` varchar(128) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '描述',
  `sort` int(11) DEFAULT '0' COMMENT '排序',
  `create_time` int(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(10) DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_system_role_menu`
--

CREATE TABLE `la_system_role_menu` (
  `role_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '角色ID',
  `menu_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '菜单ID'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色菜单关系表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant`
--

CREATE TABLE `la_tenant` (
  `id` int(11) UNSIGNED NOT NULL COMMENT '主键',
  `sn` varchar(50) NOT NULL COMMENT '编号',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '名称',
  `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '租户头像',
  `tel` varchar(30) DEFAULT NULL COMMENT '联系方式',
  `disable` tinyint(1) UNSIGNED DEFAULT '0' COMMENT '是否禁用：0-否；1-是；',
  `tactics` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '分表策略: [0=否, 1=是]',
  `notes` varchar(255) DEFAULT NULL COMMENT '租户备注',
  `domain_alias` varchar(255) DEFAULT NULL COMMENT '域名别名',
  `domain_alias_enable` tinyint(10) NOT NULL DEFAULT '1' COMMENT '启用域名别名：0-启用；1-禁用',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(10) DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='租户表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_admin`
--

CREATE TABLE `la_tenant_admin` (
  `id` int(11) UNSIGNED NOT NULL,
  `tenant_id` int(10) NOT NULL COMMENT '租户ID',
  `root` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否超级管理员 0-否 1-是',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '名称',
  `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '用户头像',
  `account` varchar(32) NOT NULL DEFAULT '' COMMENT '账号',
  `password` varchar(32) NOT NULL COMMENT '密码',
  `login_time` int(10) DEFAULT NULL COMMENT '最后登录时间',
  `login_ip` varchar(39) DEFAULT '' COMMENT '最后登录ip',
  `multipoint_login` tinyint(1) UNSIGNED DEFAULT '1' COMMENT '是否支持多处登录：1-是；0-否；',
  `disable` tinyint(1) UNSIGNED DEFAULT '0' COMMENT '是否禁用：0-否；1-是；',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(10) DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='租户管理员表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_admin_dept`
--

CREATE TABLE `la_tenant_admin_dept` (
  `admin_id` int(10) NOT NULL DEFAULT '0' COMMENT '管理员id',
  `dept_id` int(10) NOT NULL DEFAULT '0' COMMENT '部门id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='部门关联表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_admin_jobs`
--

CREATE TABLE `la_tenant_admin_jobs` (
  `admin_id` int(10) NOT NULL COMMENT '管理员id',
  `jobs_id` int(10) NOT NULL COMMENT '岗位id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='岗位关联表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_admin_role`
--

CREATE TABLE `la_tenant_admin_role` (
  `admin_id` int(10) NOT NULL COMMENT '管理员id',
  `role_id` int(10) NOT NULL COMMENT '角色id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色关联表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_admin_session`
--

CREATE TABLE `la_tenant_admin_session` (
  `id` int(11) UNSIGNED NOT NULL,
  `admin_id` int(11) UNSIGNED NOT NULL COMMENT '租户id',
  `terminal` tinyint(1) NOT NULL DEFAULT '1' COMMENT '客户端类型：1-pc管理后台 2-mobile手机管理后台',
  `token` varchar(32) NOT NULL COMMENT '令牌',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
  `expire_time` int(10) NOT NULL COMMENT '到期时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员会话表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_banner`
--

CREATE TABLE `la_tenant_banner` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uid` varchar(36) NOT NULL,
  `title` varchar(32) NOT NULL,
  `is_show` tinyint(3) UNSIGNED NOT NULL DEFAULT '2',
  `sort` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `create_time` int(10) UNSIGNED NOT NULL,
  `update_time` int(10) UNSIGNED NOT NULL,
  `delete_time` int(10) UNSIGNED DEFAULT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `position` varchar(255) NOT NULL,
  `client` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `image_type` varchar(255) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='图片配置管理' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_config`
--

CREATE TABLE `la_tenant_config` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL COMMENT '租户ID',
  `type` varchar(30) DEFAULT NULL COMMENT '类型',
  `name` varchar(60) NOT NULL DEFAULT '' COMMENT '名称',
  `value` text COMMENT '值',
  `create_time` int(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='配置表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_dept`
--

CREATE TABLE `la_tenant_dept` (
  `id` int(11) NOT NULL COMMENT 'id',
  `tenant_id` int(11) NOT NULL COMMENT '租户ID',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '部门名称',
  `pid` bigint(20) NOT NULL DEFAULT '0' COMMENT '上级部门id',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `leader` varchar(64) DEFAULT NULL COMMENT '负责人',
  `mobile` varchar(16) DEFAULT NULL COMMENT '联系电话',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '部门状态（0停用 1正常）',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(10) DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='租户部门表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_dict_data`
--

CREATE TABLE `la_tenant_dict_data` (
  `id` int(11) NOT NULL COMMENT 'id',
  `tenant_id` int(11) NOT NULL COMMENT '租户ID',
  `name` varchar(255) NOT NULL COMMENT '数据名称',
  `value` varchar(255) NOT NULL COMMENT '数据值',
  `type_id` int(11) NOT NULL COMMENT '字典类型id',
  `type_value` varchar(255) NOT NULL COMMENT '字典类型',
  `sort` int(10) DEFAULT '0' COMMENT '排序值',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 0-停用 1-正常',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(10) DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='租户字典数据表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_dict_type`
--

CREATE TABLE `la_tenant_dict_type` (
  `id` int(11) NOT NULL COMMENT 'id',
  `tenant_id` int(11) NOT NULL COMMENT '租户ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '字典名称',
  `type` varchar(255) NOT NULL DEFAULT '' COMMENT '字典类型名称',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 0-停用 1-正常',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(10) DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='字典类型表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_exam_activation_code`
--

CREATE TABLE `la_tenant_exam_activation_code` (
  `id` int(11) NOT NULL COMMENT '激活码ID',
  `tenant_id` int(10) NOT NULL DEFAULT '0',
  `batch_id` int(11) NOT NULL COMMENT '所属批次ID',
  `code` varchar(50) NOT NULL COMMENT '激活码',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态(1:禁用,2:已使用,0:正常)',
  `activation_time` datetime DEFAULT NULL COMMENT '激活时间',
  `used_user_id` varchar(50) DEFAULT NULL COMMENT '已使用用户ID',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='激活码表';

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_exam_activation_code_batch`
--

CREATE TABLE `la_tenant_exam_activation_code_batch` (
  `id` int(11) NOT NULL COMMENT '批次ID',
  `tenant_id` int(10) NOT NULL DEFAULT '0',
  `duration_days` int(11) NOT NULL COMMENT '可开通时长(天)',
  `total_count` int(11) NOT NULL COMMENT '总数量',
  `used_count` int(11) NOT NULL DEFAULT '0' COMMENT '已使用数量',
  `remark` text COMMENT '备注',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态(1:启用,0:禁用)',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='激活码批次表';

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_exam_activation_record`
--

CREATE TABLE `la_tenant_exam_activation_record` (
  `id` int(11) NOT NULL COMMENT '记录ID',
  `tenant_id` int(11) NOT NULL COMMENT '租户ID',
  `code_id` int(11) NOT NULL COMMENT '关联的激活码ID',
  `batch_id` int(11) NOT NULL COMMENT '关联的批次ID',
  `user_id` varchar(50) NOT NULL COMMENT '激活用户ID',
  `user_name` varchar(100) DEFAULT NULL COMMENT '激活用户姓名',
  `activation_ip` varchar(50) DEFAULT NULL COMMENT '激活时的IP地址',
  `activation_device` varchar(255) DEFAULT NULL COMMENT '激活设备信息',
  `activation_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '激活时间',
  `expiration_time` datetime NOT NULL COMMENT '激活码过期时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='激活码激活记录表';

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_exam_category`
--

CREATE TABLE `la_tenant_exam_category` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uid` varchar(36) NOT NULL,
  `title` varchar(32) NOT NULL,
  `is_show` tinyint(3) UNSIGNED NOT NULL DEFAULT '2',
  `sort` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `parent_uid` varchar(255) NOT NULL DEFAULT '0',
  `exam_time` int(10) DEFAULT NULL COMMENT '考试时间（parent_uid不存在时显示）',
  `create_time` int(10) UNSIGNED NOT NULL,
  `update_time` int(10) UNSIGNED NOT NULL,
  `delete_time` int(10) UNSIGNED DEFAULT NULL,
  `cover` text,
  `is_recommend` tinyint(3) UNSIGNED NOT NULL DEFAULT '2',
  `icon` varchar(36) DEFAULT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='题库分类' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_exam_chapter`
--

CREATE TABLE `la_tenant_exam_chapter` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uid` varchar(36) NOT NULL,
  `title` varchar(32) NOT NULL,
  `is_show` tinyint(3) UNSIGNED NOT NULL DEFAULT '2',
  `sort` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `parent_uid` varchar(255) NOT NULL DEFAULT '0',
  `create_time` int(10) UNSIGNED NOT NULL,
  `update_time` int(10) UNSIGNED NOT NULL,
  `delete_time` int(10) UNSIGNED DEFAULT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `library_uid` varchar(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='题库章节' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_exam_comment`
--

CREATE TABLE `la_tenant_exam_comment` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID',
  `type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '评论类型',
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID',
  `qid` varchar(36) NOT NULL DEFAULT '0' COMMENT '试题/文章ID',
  `pid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '父ID（修改为UNSIGNED，与其他ID类型保持一致）',
  `tenant_id` int(10) UNSIGNED NOT NULL COMMENT '租户ID',
  `content` longtext COMMENT '内容',
  `comments` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '评论数',
  `likes` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '点赞数',
  `ip` varchar(45) NOT NULL DEFAULT '' COMMENT 'IP地址（IPv6最长45字符，缩减长度）',
  `subscribe` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '订阅',
  `create_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间（使用int存储时间戳，节省空间）',
  `update_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `delete_time` int(10) UNSIGNED DEFAULT NULL COMMENT '删除时间',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '状态（用tinyint替代enum，更高效）'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='试题评论表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_exam_comment_like`
--

CREATE TABLE `la_tenant_exam_comment_like` (
  `id` bigint(20) UNSIGNED NOT NULL COMMENT '主键ID',
  `tenant_id` int(10) UNSIGNED NOT NULL COMMENT '租户ID',
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `comment_id` bigint(20) UNSIGNED NOT NULL COMMENT '评论ID',
  `qid` varchar(36) NOT NULL DEFAULT '0' COMMENT '来源ID（试题ID/文章ID等）',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '评论类型：1=试题评论，其他=文章评论',
  `create_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '点赞时间戳',
  `update_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间戳',
  `ip` varchar(45) NOT NULL DEFAULT '' COMMENT 'IP地址'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='评论点赞记录表';

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_exam_comment_reward`
--

CREATE TABLE `la_tenant_exam_comment_reward` (
  `id` int(11) UNSIGNED NOT NULL COMMENT '赞赏ID',
  `comment_id` int(11) UNSIGNED NOT NULL COMMENT '评论ID',
  `comment_user_id` int(11) UNSIGNED NOT NULL COMMENT '被赞赏者用户ID(评论作者)',
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '赞赏者用户ID',
  `tenant_id` int(11) UNSIGNED NOT NULL DEFAULT '1' COMMENT '租户ID',
  `qid` int(11) UNSIGNED NOT NULL COMMENT '关联ID(试题ID或文章ID)',
  `type` tinyint(2) UNSIGNED NOT NULL DEFAULT '1' COMMENT '赞赏类型: 1-试题评论 2-文章评论',
  `integral` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '赞赏积分数量',
  `remark` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '赞赏留言',
  `create_time` int(11) UNSIGNED NOT NULL COMMENT '赞赏时间',
  `update_time` int(11) UNSIGNED DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) UNSIGNED DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='评论赞赏表';

-- --------------------------------------------------------


-- --------------------------------------------------------


-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_exam_examination`
--

CREATE TABLE `la_tenant_exam_examination` (
  `id` bigint(20) UNSIGNED NOT NULL COMMENT '自增主键',
  `uid` varchar(36) NOT NULL COMMENT '唯一标识',
  `library_category_uid` varchar(36)  COMMENT '题库分类唯一标识',
  `library_uid` varchar(36)  COMMENT '题库唯一标识',
  `title` varchar(255) NOT NULL COMMENT '考试标题',
  `score` decimal(6,2) UNSIGNED NOT NULL DEFAULT '60.00' COMMENT '及格分数',
  `content` text COMMENT '考试内容',
  `sort` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '排序权重',
  `is_show` tinyint(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '是否显示(1:显示,0:隐藏)',
  `create_time` int(10) UNSIGNED NOT NULL COMMENT '创建时间戳',
  `update_time` int(10) UNSIGNED NOT NULL COMMENT '更新时间戳',
  `delete_time` int(10) UNSIGNED DEFAULT NULL COMMENT '删除时间戳(软删除)',
  `admin_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建管理员ID',
  `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '租户ID',
  `start_time` datetime NOT NULL COMMENT '开始时间',
  `end_time` datetime NOT NULL COMMENT '结束时间',
  `privilege` tinyint(4) NOT NULL COMMENT '权限(1:公开,2:私有)',
  `exam_time` time NOT NULL COMMENT '考试时长',
  `exam_submit_type` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '答题次数类型',
  `submit_count_value` tinyint(4) NOT NULL COMMENT '答题次数值',
  `login_style` tinyint(3) UNSIGNED NOT NULL COMMENT '登录方式',
  `paper_uid` varchar(36) DEFAULT NULL COMMENT '试卷UID',
  `image` varchar(500) NOT NULL COMMENT '图片地址'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='考试管理' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_exam_examination_history`
--

CREATE TABLE `la_tenant_exam_examination_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uid` varchar(36) NOT NULL,
  `questions_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '做题类型',
  `user_score` decimal(6,2) UNSIGNED NOT NULL DEFAULT '1.00' COMMENT '答题得分',
  `user_integral` decimal(6,2) NOT NULL DEFAULT '1.00' COMMENT '答题积分',
  `sort` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `is_show` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `practice_duration` int(10) UNSIGNED DEFAULT NULL COMMENT '练习时长（秒）',
  `create_time` bigint(13) UNSIGNED DEFAULT NULL COMMENT '创建时间（13位时间戳）',
  `update_time` int(10) UNSIGNED NOT NULL,
  `delete_time` int(10) UNSIGNED DEFAULT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `paper_uid` varchar(36) NOT NULL COMMENT '试卷/章节uid',
  `examination_uid` varchar(36) NOT NULL COMMENT '考试/模考/题库uid',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `user_uid` varchar(36) NOT NULL COMMENT '用户 uid',
  `paper_score` decimal(10,2) NOT NULL COMMENT '试卷总分',
  `submit_time` bigint(13) UNSIGNED DEFAULT NULL COMMENT '提交时间（13位时间戳）',
  `paper_time` time NOT NULL COMMENT '试卷时间',
  `options_count` int(11) NOT NULL COMMENT '答题总数',
  `options` json DEFAULT NULL COMMENT '答题详情（JSON格式，包含题目uid、用户答案、是否正确等）',
  `error_count` int(11) NOT NULL COMMENT '错误题数',
  `correct_count` int(11) NOT NULL COMMENT '正确题数',
  `error_option_uid` json NOT NULL COMMENT '错误试题选项',
  `correct_option_uid` json NOT NULL COMMENT '正确试题选项',
  `basic_score` decimal(10,2) NOT NULL COMMENT '考试及格分数',
  `start_time` datetime DEFAULT NULL COMMENT '考试开始时间',
  `end_time` datetime DEFAULT NULL COMMENT '考试结束时间',
  `is_rand` tinyint(4) NOT NULL DEFAULT '2' COMMENT '随机状态 1 是 2 否',
  `total_count` int(10) UNSIGNED DEFAULT '0' COMMENT '总题数',
  `answered_count` int(10) UNSIGNED DEFAULT '0' COMMENT '已答题数',
  `accuracy_rate` decimal(5,2) DEFAULT '0.00' COMMENT '正确率（%）'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='做题记录' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_exam_help`
--

CREATE TABLE `la_tenant_exam_help` (
  `id` int(11) NOT NULL COMMENT '帮助中心id',
  `tenant_id` int(11) NOT NULL COMMENT '租户ID',
  `title` varchar(255) NOT NULL COMMENT '帮助中心标题',
  `image` varchar(128) DEFAULT NULL COMMENT '帮助中心图片',
  `author` varchar(255) DEFAULT '' COMMENT '作者',
  `content` text COMMENT '帮助中心内容',
  `click_virtual` int(10) DEFAULT '0' COMMENT '虚拟浏览量',
  `click_actual` int(11) DEFAULT '0' COMMENT '实际浏览量',
  `is_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示:1-是.0-否',
  `sort` int(5) DEFAULT '0' COMMENT '排序',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='帮助中心表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_exam_knowledge`
--

CREATE TABLE `la_tenant_exam_knowledge` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uid` varchar(36) NOT NULL,
  `title` varchar(32) NOT NULL,
  `content` text COMMENT '知识点内容',
  `is_show` tinyint(3) UNSIGNED NOT NULL DEFAULT '2',
  `sort` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `parent_uid` varchar(255) NOT NULL DEFAULT '0',
  `create_time` int(10) UNSIGNED NOT NULL,
  `update_time` int(10) UNSIGNED NOT NULL,
  `delete_time` int(10) UNSIGNED DEFAULT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `chapter_uid` varchar(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='题库章节知识点' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_exam_label`
--

CREATE TABLE `la_tenant_exam_label` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uid` varchar(36) NOT NULL,
  `title` varchar(32) NOT NULL,
  `is_show` tinyint(3) UNSIGNED NOT NULL DEFAULT '2',
  `sort` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `create_time` int(10) UNSIGNED NOT NULL,
  `update_time` int(10) UNSIGNED NOT NULL,
  `delete_time` int(10) UNSIGNED DEFAULT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `library_uid` varchar(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='题库标签' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_exam_library`
--

CREATE TABLE `la_tenant_exam_library` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uid` varchar(36) NOT NULL,
  `title` varchar(255) NOT NULL,
  `is_show` tinyint(3) UNSIGNED NOT NULL DEFAULT '2',
  `sort` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `create_time` int(10) UNSIGNED NOT NULL,
  `update_time` int(10) UNSIGNED NOT NULL,
  `delete_time` int(10) UNSIGNED DEFAULT NULL,
  `image` text,
  `remark` text NOT NULL,
  `category_uid` varchar(36) NOT NULL DEFAULT '',
  `author` varchar(20) NOT NULL DEFAULT '精解析答题',
  `free_state` varchar(36) NOT NULL DEFAULT '2',
  `money` decimal(10,2) UNSIGNED DEFAULT '0.00',
  `discount` decimal(10,2) DEFAULT '0.00',
  `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `year` int(10) UNSIGNED DEFAULT '0',
  `recommend_state` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `hot_state` tinyint(3) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='题库' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_exam_mock_examination`
--

CREATE TABLE `la_tenant_exam_mock_examination` (
  `id` bigint(20) UNSIGNED NOT NULL COMMENT '主键ID',
  `uid` varchar(36) NOT NULL COMMENT '考试记录唯一标识',
  `questions_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '考试类型',
  `user_score` decimal(6,2) UNSIGNED NOT NULL DEFAULT '1.00' COMMENT '答题得分',
  `paper_score` decimal(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '试卷积分',
  `score` decimal(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '及格分数',
  `create_time` int(10) UNSIGNED NOT NULL COMMENT '创建时间戳',
  `update_time` int(10) UNSIGNED NOT NULL COMMENT '更新时间戳',
  `delete_time` int(10) UNSIGNED DEFAULT NULL COMMENT '删除时间戳（软删除）',
  `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '租户ID',
  `exam_time` time NOT NULL COMMENT '考试时间',
  `exam_submit_time` time NOT NULL DEFAULT '00:00:00' COMMENT '答题时间',
  `option_type` tinyint(3) UNSIGNED NOT NULL COMMENT '选项打乱模式 1-随机 2-顺序',
  `checkbox_type` tinyint(3) UNSIGNED NOT NULL DEFAULT '2' COMMENT '多选试题模式 1-全部答对得分 2-答对即可得分',
  `option_type_config` json NOT NULL COMMENT '题型配置',
  `options` json DEFAULT NULL COMMENT '提交试题选项',
  `error_option` json DEFAULT NULL COMMENT '错题选项',
  `correct_option` json DEFAULT NULL COMMENT '正确选项',
  `correct_count` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '正确题数',
  `error_count` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '错误题数',
  `library_uid` varchar(36) NOT NULL COMMENT '题库唯一标识',
  `user_uid` varchar(36) NOT NULL COMMENT '用户唯一标识',
  `priority_wrong` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '错题优先(0-关闭,1-开启)',
  `priority_unattempted` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '未做题优先(0-关闭,1-开启)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='模拟考试' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_exam_other_settings`
--

CREATE TABLE `la_tenant_exam_other_settings` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '主键',
  `tenant_id` int(10) NOT NULL DEFAULT '0',
  `customer_qrcode` text COMMENT '客服二维码容',
  `customer_wechat` varchar(120) DEFAULT NULL COMMENT '客服微信',
  `customer_mobile` varchar(16) DEFAULT NULL COMMENT '客服电话',
  `copyright` varchar(250) DEFAULT NULL COMMENT '版权信息',
  `customer_name` varchar(36) DEFAULT NULL COMMENT '客服名称',
  `customer_company` varchar(56) DEFAULT NULL COMMENT '客服所在公司',
  `customer_position` varchar(56) DEFAULT NULL COMMENT '客服岗位',
  `template_id` varchar(150) DEFAULT NULL,
  `adConfig` tinyint(3) NOT NULL DEFAULT '1' COMMENT '免广告设置。1-会员免广告，2-全部用户免广告',
  `member_settings` json DEFAULT NULL COMMENT '会员设置',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='基础设置';

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_exam_paper`
--

CREATE TABLE `la_tenant_exam_paper` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uid` varchar(36) NOT NULL,
  `title` varchar(32) NOT NULL,
  `is_show` tinyint(3) UNSIGNED NOT NULL DEFAULT '2',
  `sort` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `create_time` int(10) UNSIGNED NOT NULL,
  `update_time` int(10) UNSIGNED NOT NULL,
  `delete_time` int(10) UNSIGNED DEFAULT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `is_rand` tinyint(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '是否随机打乱 1 是 2 否',
  `image` varchar(255) NOT NULL COMMENT '试卷封面',
  `option_count` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '试题总数',
  `option_score` decimal(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '试题总分',
  `option_config` json DEFAULT NULL COMMENT '选项配置，各种题型数量和分数',
  `option_content` json DEFAULT NULL COMMENT '各种题型选项和答案',
  `remark` text COMMENT '试卷描述'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='试卷管理' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_exam_question`
--

CREATE TABLE `la_tenant_exam_question` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uid` varchar(36) NOT NULL,
  `library_uid` varchar(36) DEFAULT NULL COMMENT '题库uid',
  `title` text NOT NULL,
  `option` json DEFAULT NULL COMMENT '选项列表（JSON数组）',
  `integral` decimal(6,2) UNSIGNED NOT NULL DEFAULT '1.00' COMMENT '积分',
  `score` decimal(6,2) UNSIGNED NOT NULL DEFAULT '2.00' COMMENT '分值',
  `answer` json DEFAULT NULL COMMENT '正确答案（JSON数组，如["A","B"]）',
  `analysis` text,
  `commentaries` text,
  `exam_type` tinyint(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '试题题型',
  `sort` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `is_show` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `create_time` int(10) UNSIGNED NOT NULL,
  `update_time` int(10) UNSIGNED NOT NULL,
  `delete_time` int(10) UNSIGNED DEFAULT NULL,
  `level` tinyint(3) UNSIGNED NOT NULL,
  `admin_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `chapter_uid` varchar(128) DEFAULT NULL COMMENT '章节id',
  `knowledge_uid` varchar(128) DEFAULT NULL COMMENT '知识点uid',
  `total_attempts` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '总做题人次',
  `total_correct` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '总正确次数',
  `like_count` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '点赞总数',
  `collect_count` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '收藏总数',
  `easy_mistakes` text COMMENT '易错项',
  `label_uid` varchar(128) DEFAULT NULL COMMENT '标签uid',
  `answer_str` varchar(50) DEFAULT NULL COMMENT '答案字符串（如"A,B"，冗余字段便于展示）',
  `correct_attempts` int(10) UNSIGNED DEFAULT '0' COMMENT '答对次数',
  `accuracy_rate` decimal(5,2) DEFAULT '0.00' COMMENT '正确率（%）',
  `avg_time_spent` int(10) UNSIGNED DEFAULT '0' COMMENT '平均答题时间（秒）'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='题库试题' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_exam_question_collection`
--

CREATE TABLE `la_tenant_exam_question_collection` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `create_time` int(10) UNSIGNED NOT NULL,
  `update_time` int(10) UNSIGNED NOT NULL,
  `delete_time` int(10) UNSIGNED DEFAULT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `library_uid` varchar(36) NOT NULL COMMENT '题库 uid',
  `question_uid` varchar(36) NOT NULL COMMENT '题库试题 uid',
  `user_uid` varchar(36) NOT NULL COMMENT '用户 uid',
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID',
  `exam_type` int(11) DEFAULT NULL COMMENT '题型ID',
  `exam_type_name` varchar(32) NOT NULL DEFAULT '1' COMMENT '题型名称',
  `chapter_uid` varchar(36) DEFAULT NULL COMMENT '章节ID',
  `title` varchar(255) DEFAULT NULL COMMENT '题目标题',
  `level` tinyint(4) DEFAULT NULL COMMENT '题目难度（1-简单，2-中等，3-困难）',
  `score` decimal(10,2) DEFAULT NULL COMMENT '题目分值',
  `correct_answer` text COMMENT '正确答案'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='试题收藏' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_exam_question_corrections`
--

CREATE TABLE `la_tenant_exam_question_corrections` (
  `id` int(11) NOT NULL COMMENT '序号',
  `tenant_id` int(10) NOT NULL DEFAULT '0',
  `library_uid` varchar(36) NOT NULL COMMENT '题库 uid',
  `question_uid` varchar(50) NOT NULL COMMENT '题目ID',
  `question_name` varchar(255) NOT NULL COMMENT '题目名称',
  `exam_type` tinyint(3) NOT NULL DEFAULT '1',
  `correction_type` json NOT NULL COMMENT '纠错类型',
  `correction_reason` text NOT NULL COMMENT '纠错原因',
  `correction_image` varchar(256) DEFAULT NULL COMMENT '纠错图片',
  `user_id` varchar(50) NOT NULL COMMENT '用户ID',
  `user_nickname` varchar(100) NOT NULL COMMENT '用户昵称',
  `platform_feedback` text DEFAULT NULL COMMENT '平台反馈',
  `feedback_time` datetime DEFAULT NULL COMMENT '反馈时间',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '提交时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='题目纠错信息表';

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_exam_question_error`
--

CREATE TABLE `la_tenant_exam_question_error` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `create_time` int(10) UNSIGNED NOT NULL,
  `update_time` int(10) UNSIGNED NOT NULL,
  `delete_time` int(10) UNSIGNED DEFAULT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `library_uid` varchar(36) NOT NULL COMMENT '题库 uid',
  `question_uid` varchar(36) NOT NULL COMMENT '题库试题 uid',
  `user_uid` varchar(36) NOT NULL COMMENT '用户 uid',
  `eliminated_status` tinyint(4) DEFAULT '0' COMMENT '消灭状态：0-未消灭，1-已消灭',
  `eliminated_at` datetime DEFAULT NULL COMMENT '消灭时间',
  `eliminated_by` varchar(20) DEFAULT 'manual' COMMENT '消灭方式：manual-手动，auto-自动',
  `questions_type` int(11) DEFAULT NULL COMMENT '做题类型',
  `exam_type` int(11) DEFAULT NULL COMMENT '题型ID',
  `chapter_uid` varchar(36) DEFAULT NULL COMMENT '章节ID',
  `title` varchar(255) DEFAULT NULL COMMENT '题目标题',
  `level` tinyint(4) DEFAULT NULL COMMENT '题目难度（1-简单，2-中等，3-困难）',
  `score` decimal(10,2) DEFAULT NULL COMMENT '题目分值',
  `user_answer` text COMMENT '用户答案',
  `correct_answer` text COMMENT '正确答案',
  `practice_mode` int(11) DEFAULT NULL COMMENT '练习模式',
  `examination_uid` varchar(36) DEFAULT NULL COMMENT '关联考试ID',
  `error_count` int(10) UNSIGNED DEFAULT '1' COMMENT '错误次数',
  `is_high_frequency` tinyint(4) DEFAULT '0' COMMENT '是否高频错题：0-否，1-是',
  `correct_count` int(10) UNSIGNED DEFAULT '0' COMMENT '正确次数'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='错题表' ROW_FORMAT=DYNAMIC;

--
-- 索引 `la_tenant_exam_question_error`
--
ALTER TABLE `la_tenant_exam_question_error`
  ADD INDEX `idx_user_question` (`user_uid`, `question_uid`),
  ADD INDEX `idx_user_high_frequency` (`user_uid`, `is_high_frequency`);

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_exam_question_error_eliminate_log`
--

CREATE TABLE `la_tenant_exam_question_error_eliminate_log` (
  `id` int(11) NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `question_error_uid` bigint(20) UNSIGNED NOT NULL COMMENT '错题唯一标识（关联la_tenant_exam_library_remove的id）',
  `user_uid` varchar(32) NOT NULL COMMENT '用户唯一标识',
  `old_status` tinyint(4) NOT NULL COMMENT '旧状态',
  `new_status` tinyint(4) NOT NULL COMMENT '新状态',
  `create_time` datetime NOT NULL COMMENT '操作时间',
  `eliminated_by` varchar(20) NOT NULL COMMENT '操作方式',
  `ip` varchar(45) DEFAULT NULL COMMENT '操作IP'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='错题消灭状态变更日志';

-- --------------------------------------------------------

--
-- 替换视图以便查看 `la_tenant_exam_question_error_stats`
-- （参见下面的实际视图）
--
CREATE TABLE `la_tenant_exam_question_error_stats` (
);

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_exam_question_like`
--

CREATE TABLE `la_tenant_exam_question_like` (
  `id` int(10) UNSIGNED NOT NULL,
  `create_time` int(10) UNSIGNED NOT NULL,
  `update_time` int(10) UNSIGNED NOT NULL,
  `delete_time` int(10) UNSIGNED DEFAULT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `library_uid` varchar(36) NOT NULL COMMENT '题库 uid',
  `question_uid` varchar(36) NOT NULL COMMENT '题库试题 uid',
  `user_uid` varchar(36) NOT NULL COMMENT '用户 uid',
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='题库试题点赞' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_exam_ranking_daily`
--

CREATE TABLE `la_tenant_exam_ranking_daily` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '自增ID',
  `tenant_id` int(10) UNSIGNED NOT NULL COMMENT '租户ID',
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `correct_count` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '当日答对题数',
  `total_count` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '当日答题总数',
  `accuracy` decimal(5,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '当日正确率(%)',
  `integral` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '当日获得积分',
  `ranking` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '当日排名',
  `create_date` date NOT NULL COMMENT '统计日期(YYYY-MM-DD)',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '记录创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  `delete_time` datetime DEFAULT NULL COMMENT '软删除时间(NULL表示未删除)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='每日答题排行榜';

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_exam_ranking_monthly`
--

CREATE TABLE `la_tenant_exam_ranking_monthly` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '自增ID',
  `tenant_id` int(10) UNSIGNED NOT NULL COMMENT '租户ID',
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `correct_count` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '本月答对题数',
  `total_count` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '本月答题总数',
  `accuracy` decimal(5,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '本月正确率(%)',
  `integral` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '本月获得积分',
  `ranking` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '本月排名',
  `month` tinyint(3) UNSIGNED NOT NULL COMMENT '月份(1-12)',
  `year` smallint(5) UNSIGNED NOT NULL COMMENT '年份',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '记录创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  `delete_time` datetime DEFAULT NULL COMMENT '软删除时间(NULL表示未删除)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='每月答题排行榜表';

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_exam_ranking_settings`
--

CREATE TABLE `la_tenant_exam_ranking_settings` (
  `id` int(11) NOT NULL COMMENT '自增ID',
  `tenant_id` int(11) NOT NULL COMMENT '租户ID',
  `ranking_type` varchar(50) NOT NULL DEFAULT 'total' COMMENT '排行榜类型：day(日榜)、week(周榜)、month(月榜)、total(总榜)',
  `display_top_count` int(11) NOT NULL DEFAULT '30' COMMENT '排行榜展示的前N名数量',
  `ranking_dimension` varchar(50) NOT NULL DEFAULT 'correct_count' COMMENT '显示维度：correct_count(答对题数)、total_count(答题总数)、accuracy(正确率)、integral(积分)',
  `reset_time_day` varchar(50) DEFAULT NULL COMMENT '日榜重置时间，格式：固定值''00:00:00''；',
  `reset_time_week` varchar(50) DEFAULT NULL COMMENT '周榜重置时间，格式：周榜-''1''表示周一；',
  `reset_time_month` varchar(50) DEFAULT NULL COMMENT '月榜重置时间，格式：月榜-''1''表示每月1日；',
  `reset_time_total` varchar(50) DEFAULT NULL COMMENT '总榜重置时间，格式：总榜-固定值''0''',
  `reward_rule` varchar(100) DEFAULT '' COMMENT '奖励规则类型：integral(积分奖励)',
  `integral_count_day` varchar(60) DEFAULT '0' COMMENT '日榜奖励积分配置，格式：1|5,2|3,3|2',
  `integral_count_week` varchar(60) DEFAULT '0' COMMENT '周榜奖励积分配置，格式：1|15,2|13,3|12',
  `integral_count_month` varchar(60) DEFAULT '0' COMMENT '月榜奖励积分配置，格式：1|30,2|25,3|20',
  `integral_count_total` varchar(60) DEFAULT '0' COMMENT '总榜奖励积分配置，格式：1|500,2|300,3|200',
  `is_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '排行榜是否启用：1(启用)、0(禁用)',
  `desc` text COMMENT '排行榜说明',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '记录创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='排行榜设置表';

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_exam_ranking_total`
--

CREATE TABLE `la_tenant_exam_ranking_total` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '自增ID',
  `tenant_id` int(10) UNSIGNED NOT NULL COMMENT '租户ID',
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `correct_count` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '总答对题数',
  `total_count` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '总答题总数',
  `accuracy` decimal(5,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '总正确率(%)',
  `integral` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '总获得积分',
  `ranking` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '总排名',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '记录创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  `delete_time` datetime DEFAULT NULL COMMENT '软删除时间(NULL表示未删除)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='总答题排行榜表';

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_exam_ranking_weekly`
--

CREATE TABLE `la_tenant_exam_ranking_weekly` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '自增ID',
  `tenant_id` int(10) UNSIGNED NOT NULL COMMENT '租户ID',
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `correct_count` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '本周答对题数',
  `total_count` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '本周答题总数',
  `accuracy` decimal(5,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '本周正确率(%)',
  `integral` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '本周获得积分',
  `ranking` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '本周排名',
  `week_start_date` date NOT NULL COMMENT '本周开始日期',
  `week_end_date` date NOT NULL COMMENT '本周结束日期',
  `week_number` tinyint(3) UNSIGNED NOT NULL COMMENT '周数(1-53)',
  `year` smallint(5) UNSIGNED NOT NULL COMMENT '年份',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '记录创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '记录更新时间',
  `delete_time` datetime DEFAULT NULL COMMENT '软删除时间(NULL表示未删除)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='每周答题排行榜表';

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_exam_record`
--

CREATE TABLE `la_tenant_exam_record` (
  `id` bigint(20) UNSIGNED NOT NULL COMMENT '主键',
  `uid` varchar(36) NOT NULL,
  `questions_type` int(10) NOT NULL COMMENT '做题类型：1章节练习、2试卷考试、3每日一练、4举一反三、5错题练习、6收藏题库',
  `user_score` decimal(6,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '答题得分',
  `paper_score` decimal(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '做题积分',
  `create_time` int(10) UNSIGNED NOT NULL,
  `update_time` int(10) UNSIGNED NOT NULL,
  `delete_time` int(10) UNSIGNED DEFAULT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `exam_time` time NOT NULL COMMENT '考试时间',
  `exam_submit_time` time NOT NULL DEFAULT '00:00:00' COMMENT '答题时间',
  `correct_count` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '正确题数',
  `error_count` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '错误题数',
  `library_uid` varchar(36) NOT NULL COMMENT '题库 uid',
  `question_uid` varchar(36) NOT NULL COMMENT '题库试题 uid',
  `user_uid` varchar(36) NOT NULL COMMENT '用户 uid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='做题记录表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_exam_user_activation_record`
--

CREATE TABLE `la_tenant_exam_user_activation_record` (
  `id` bigint(20) UNSIGNED NOT NULL COMMENT '主键ID',
  `tenant_id` int(11) NOT NULL DEFAULT '1' COMMENT '租户ID',
  `user_uid` int(11) NOT NULL DEFAULT '0' COMMENT '会员ID',
  `activation_type` varchar(50) NOT NULL COMMENT '激活类型(1-积分，2-激活码，3-付费)',
  `consume_amount` varchar(50) NOT NULL DEFAULT '0' COMMENT '消耗数量',
  `activation_time` int(10) NOT NULL DEFAULT '0' COMMENT '激活时间',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '激活状态：1-成功，2-失败',
  `package_name` varchar(100) NOT NULL DEFAULT '' COMMENT '会员套餐名称',
  `duration` int(11) NOT NULL DEFAULT '0' COMMENT '有效期（天）',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='会员激活记录表';

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_file`
--

CREATE TABLE `la_tenant_file` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '主键ID',
  `tenant_id` int(11) NOT NULL COMMENT '租户ID',
  `cid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '类目ID',
  `source_id` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '上传者id',
  `source` tinyint(1) NOT NULL DEFAULT '0' COMMENT '来源类型[0-后台,1-用户]',
  `type` tinyint(2) UNSIGNED NOT NULL DEFAULT '10' COMMENT '类型[10=图片, 20=视频]',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '文件名称',
  `uri` varchar(200) NOT NULL COMMENT '文件路径',
  `create_time` int(10) UNSIGNED DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(10) DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文件表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_file_cate`
--

CREATE TABLE `la_tenant_file_cate` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '主键ID',
  `tenant_id` int(11) NOT NULL COMMENT '租户ID',
  `pid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '父级ID',
  `type` tinyint(2) UNSIGNED NOT NULL DEFAULT '10' COMMENT '类型[10=图片，20=视频，30=文件]',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '分类名称',
  `create_time` int(10) UNSIGNED DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) UNSIGNED DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(10) UNSIGNED DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文件分类表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_integral_settings`
--

CREATE TABLE `la_tenant_integral_settings` (
  `id` int(11) NOT NULL COMMENT '主键ID',
  `tenant_id` int(11) NOT NULL COMMENT '租户ID',
  `integral_text_custom` varchar(9) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '积分文字自定义，不超过3个汉字（utf8mb4编码，每个汉字占3个字符长度）',
  `user_integral_rule` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '用户积分使用规则，可存储较长的文本说明',
  `daily_integral_limit` int(10) NOT NULL DEFAULT '0' COMMENT '每日答题积分上限，0代表无上限',
  `integral_count_rule` tinyint(3) NOT NULL DEFAULT '1' COMMENT '填空题、案例题积分计算规则',
  `login_integral` int(10) NOT NULL DEFAULT '0' COMMENT '登录赠送积分，0表示不赠送',
  `invite_user_integral` int(10) NOT NULL DEFAULT '0' COMMENT '邀请新用户注册赠送积分，0表示不赠送',
  `subscribe_integral` int(10) NOT NULL DEFAULT '0' COMMENT '会员订阅赠送积分，0表示不赠送',
  `share_integral` int(10) NOT NULL DEFAULT '0' COMMENT '分享给好友获得积分，0表示不赠送',
  `download_integral` int(10) NOT NULL DEFAULT '0' COMMENT '下载资源消耗积分，0表示不消耗',
  `register_integral` int(10) NOT NULL DEFAULT '0' COMMENT '注册赠送积分，0表示不赠送',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='积分设置数据表';

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_jobs`
--

CREATE TABLE `la_tenant_jobs` (
  `id` int(11) NOT NULL COMMENT 'id',
  `tenant_id` int(11) NOT NULL COMMENT '租户ID',
  `name` varchar(50) NOT NULL COMMENT '岗位名称',
  `code` varchar(64) NOT NULL COMMENT '岗位编码',
  `sort` int(11) DEFAULT '0' COMMENT '显示顺序',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态（0停用 1正常）',
  `remark` varchar(500) DEFAULT NULL COMMENT '备注',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '修改时间',
  `delete_time` int(10) DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='岗位表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_notice_record`
--

CREATE TABLE `la_tenant_notice_record` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'ID',
  `tenant_id` int(11) NOT NULL COMMENT '租户ID',
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '用户id',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
  `content` text NOT NULL COMMENT '内容',
  `scene_id` int(10) UNSIGNED DEFAULT '0' COMMENT '场景',
  `read` tinyint(1) DEFAULT '0' COMMENT '已读状态;0-未读,1-已读',
  `recipient` tinyint(1) DEFAULT '0' COMMENT '通知接收对象类型;1-会员;2-商家;3-平台;4-游客(未注册用户)',
  `send_type` tinyint(1) DEFAULT '0' COMMENT '通知发送类型 1-系统通知 2-短信通知 3-微信模板 4-微信小程序',
  `notice_type` tinyint(1) DEFAULT NULL COMMENT '通知类型 1-业务通知 2-验证码',
  `extra` varchar(255) DEFAULT '' COMMENT '其他',
  `create_time` int(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(10) DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='通知记录表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_notice_setting`
--

CREATE TABLE `la_tenant_notice_setting` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL COMMENT '租户ID',
  `scene_id` int(10) NOT NULL COMMENT '场景id',
  `scene_name` varchar(255) NOT NULL DEFAULT '' COMMENT '场景名称',
  `scene_desc` varchar(255) NOT NULL DEFAULT '' COMMENT '场景描述',
  `recipient` tinyint(1) NOT NULL DEFAULT '1' COMMENT '接收者 1-用户 2-平台',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '通知类型: 1-业务通知 2-验证码',
  `system_notice` text COMMENT '系统通知设置',
  `sms_notice` text COMMENT '短信通知设置',
  `oa_notice` text COMMENT '公众号通知设置',
  `mnp_notice` text COMMENT '小程序通知设置',
  `support` char(10) NOT NULL DEFAULT '' COMMENT '支持的发送类型 1-系统通知 2-短信通知 3-微信模板消息 4-小程序提醒',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='通知设置表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_pay_config`
--

CREATE TABLE `la_tenant_pay_config` (
  `id` int(11) UNSIGNED NOT NULL,
  `tenant_id` int(11) NOT NULL COMMENT '租户ID',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '模版名称',
  `pay_way` tinyint(1) NOT NULL COMMENT '支付方式:1-余额支付;2-微信支付;3-支付宝支付;',
  `config` text COMMENT '对应支付配置(json字符串)',
  `icon` varchar(255) DEFAULT NULL COMMENT '图标',
  `sort` int(5) DEFAULT NULL COMMENT '排序',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='支付配置表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_pay_way`
--

CREATE TABLE `la_tenant_pay_way` (
  `id` int(11) UNSIGNED NOT NULL,
  `tenant_id` int(11) NOT NULL COMMENT '租户ID',
  `pay_config_id` int(11) NOT NULL COMMENT '支付配置ID',
  `scene` tinyint(1) NOT NULL COMMENT '场景:1-微信小程序;2-微信公众号;3-H5;4-PC;5-APP;',
  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否默认支付:0-否;1-是;',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态:0-关闭;1-开启;'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='支付方式表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_resource`
--

CREATE TABLE `la_tenant_resource` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uid` varchar(36) NOT NULL,
  `title` varchar(255) NOT NULL,
  `is_show` tinyint(3) UNSIGNED NOT NULL DEFAULT '2',
  `sort` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `create_time` int(10) UNSIGNED NOT NULL,
  `update_time` int(10) UNSIGNED NOT NULL,
  `delete_time` int(10) UNSIGNED DEFAULT NULL,
  `image` text,
  `remark` text NOT NULL,
  `category_uid` varchar(36) NOT NULL,
  `exam_category_uid` varchar(36) NOT NULL COMMENT '题库分类uid',
  `author` varchar(20) NOT NULL DEFAULT '平台',
  `free_state` varchar(36) NOT NULL DEFAULT '2',
  `money` decimal(10,2) UNSIGNED DEFAULT '0.00',
  `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `year` int(10) UNSIGNED DEFAULT '0',
  `file_url` varchar(1000) NOT NULL COMMENT '资源链接',
  `view_count` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '查询量',
  `download_count` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '下载量'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='题库' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_resource_category`
--

CREATE TABLE `la_tenant_resource_category` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uid` varchar(36) NOT NULL,
  `title` varchar(32) NOT NULL,
  `is_show` tinyint(3) UNSIGNED NOT NULL DEFAULT '2',
  `sort` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `parent_uid` varchar(255) NOT NULL DEFAULT '0',
  `create_time` int(10) UNSIGNED NOT NULL,
  `update_time` int(10) UNSIGNED NOT NULL,
  `delete_time` int(10) UNSIGNED DEFAULT NULL,
  `image` text,
  `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='资源分类' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_resource_collect`
--

CREATE TABLE `la_tenant_resource_collect` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `create_time` int(10) UNSIGNED NOT NULL,
  `update_time` int(10) UNSIGNED NOT NULL,
  `delete_time` int(10) UNSIGNED DEFAULT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `user_uid` varchar(36) NOT NULL COMMENT '用户 uid',
  `resource_uid` varchar(36) NOT NULL COMMENT '资源 uid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='资源收藏' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_sms_log`
--

CREATE TABLE `la_tenant_sms_log` (
  `id` int(11) NOT NULL COMMENT 'id',
  `tenant_id` int(11) NOT NULL COMMENT '租户ID',
  `scene_id` int(11) NOT NULL COMMENT '场景id',
  `mobile` varchar(11) NOT NULL COMMENT '手机号码',
  `content` varchar(255) NOT NULL COMMENT '发送内容',
  `code` varchar(32) DEFAULT NULL COMMENT '发送关键字（注册、找回密码）',
  `is_verify` tinyint(1) DEFAULT '0' COMMENT '是否已验证；0-否；1-是',
  `check_num` int(5) DEFAULT '0' COMMENT '验证次数',
  `send_status` tinyint(1) NOT NULL COMMENT '发送状态：0-发送中；1-发送成功；2-发送失败',
  `send_time` int(10) NOT NULL COMMENT '发送时间',
  `results` text COMMENT '短信结果',
  `create_time` int(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(10) DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='租户短信记录表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_system_menu`
--

CREATE TABLE `la_tenant_system_menu` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '主键',
  `tenant_id` int(11) NOT NULL COMMENT '租户ID',
  `pid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '上级菜单',
  `type` char(2) NOT NULL DEFAULT '' COMMENT '权限类型: M=目录，C=菜单，A=按钮',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '菜单名称',
  `icon` varchar(100) DEFAULT '' COMMENT '菜单图标',
  `sort` smallint(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT '菜单排序',
  `perms` varchar(100) NOT NULL DEFAULT '' COMMENT '权限标识',
  `paths` varchar(100) NOT NULL DEFAULT '' COMMENT '路由地址',
  `component` varchar(200) NOT NULL DEFAULT '' COMMENT '前端组件',
  `selected` varchar(200) NOT NULL DEFAULT '' COMMENT '选中路径',
  `params` varchar(200) NOT NULL DEFAULT '' COMMENT '路由参数',
  `is_cache` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否缓存: 0=否, 1=是',
  `is_show` tinyint(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '是否显示: 0=否, 1=是',
  `is_disable` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否禁用: 0=否, 1=是',
  `create_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统菜单表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_system_role`
--

CREATE TABLE `la_tenant_system_role` (
  `id` int(11) UNSIGNED NOT NULL,
  `tenant_id` int(11) NOT NULL COMMENT '租户ID',
  `name` varchar(16) NOT NULL DEFAULT '' COMMENT '名称',
  `desc` varchar(128) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '描述',
  `sort` int(11) DEFAULT '0' COMMENT '排序',
  `create_time` int(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(10) DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_system_role_menu`
--

CREATE TABLE `la_tenant_system_role_menu` (
  `role_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '角色ID',
  `menu_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '菜单ID'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色菜单关系表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_user_chapter_statistics`
--

CREATE TABLE `la_tenant_user_chapter_statistics` (
  `id` bigint(20) UNSIGNED NOT NULL COMMENT '主键ID',
  `uid` varchar(36) NOT NULL COMMENT '统计记录唯一标识',
  `tenant_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '租户ID',
  `user_uid` int(10) NOT NULL COMMENT '用户UID',
  `library_uid` varchar(36) NOT NULL COMMENT '题库UID',
  `chapter_uid` varchar(36) NOT NULL COMMENT '章节UID',
  `total_questions` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '章节题目总数',
  `attempted_count` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '已答题目数',
  `correct_count` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '正确题目数',
  `wrong_count` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '错误题目数',
  `accuracy_rate` decimal(5,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '正确率（百分比）',
  `total_time_spent` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '总答题用时（秒）',
  `last_practice_time` int(10) UNSIGNED DEFAULT NULL COMMENT '最后练习时间',
  `first_practice_time` int(10) UNSIGNED DEFAULT NULL COMMENT '首次练习时间',
  `create_time` int(10) UNSIGNED NOT NULL COMMENT '创建时间',
  `update_time` int(10) UNSIGNED NOT NULL COMMENT '更新时间',
  `delete_time` int(10) UNSIGNED DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户章节做题统计表' ROW_FORMAT=DYNAMIC;

--
-- 触发器 `la_tenant_user_chapter_statistics`
--
DELIMITER $$
CREATE TRIGGER `update_accuracy_rate_before_insert` BEFORE INSERT ON `la_tenant_user_chapter_statistics` FOR EACH ROW BEGIN
  IF NEW.attempted_count > 0 THEN
    SET NEW.accuracy_rate = ROUND((NEW.correct_count / NEW.attempted_count) * 100, 2);
  ELSE
    SET NEW.accuracy_rate = 0.00;
  END IF;
  
  SET NEW.wrong_count = GREATEST(NEW.attempted_count - NEW.correct_count, 0);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_accuracy_rate_before_update` BEFORE UPDATE ON `la_tenant_user_chapter_statistics` FOR EACH ROW BEGIN
  -- 核心：计算正确率（防除零错误，保留2位小数）
  IF NEW.attempted_count > 0 THEN
    SET NEW.accuracy_rate = ROUND((NEW.correct_count / NEW.attempted_count) * 100, 2);
  ELSE
    SET NEW.accuracy_rate = 0.00;
  END IF;
  
  -- 关键优化：错误题数强制非负（避免数据异常导致负数）
  SET NEW.wrong_count = GREATEST(NEW.attempted_count - NEW.correct_count, 0);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_user_exam_progress`
--

CREATE TABLE `la_tenant_user_exam_progress` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL COMMENT '用户ID',
  `library_uid` varchar(50) NOT NULL COMMENT '题库UID',
  `questions_type` tinyint(4) NOT NULL COMMENT '练习类型',
  `chapter_uid` varchar(50) DEFAULT NULL COMMENT '章节UID',
  `progress_data` json NOT NULL COMMENT '进度数据（题目列表、当前索引、模式等）',
  `current_index` int(11) DEFAULT '0' COMMENT '当前题目索引',
  `answered_count` int(11) DEFAULT '0' COMMENT '已答题数',
  `elapsed_time` int(11) DEFAULT '0' COMMENT '已用时间（秒）',
  `updated_at` bigint(13) UNSIGNED DEFAULT NULL COMMENT '更新时间',
  `created_at` bigint(13) UNSIGNED DEFAULT NULL COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户答题进度表';

-- --------------------------------------------------------

--
-- 表的结构 `la_tenant_user_integral_log`
--

CREATE TABLE `la_tenant_user_integral_log` (
  `id` int(11) UNSIGNED NOT NULL COMMENT '主键ID',
  `tenant_id` int(11) NOT NULL COMMENT '租户ID',
  `user_id` int(11) NOT NULL COMMENT '用户ID',
  `action` tinyint(1) NOT NULL DEFAULT '0' COMMENT '动作 1-增加 2-减少',
  `change_amount` decimal(10,2) NOT NULL COMMENT '变动数量',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `extra` json DEFAULT NULL COMMENT '预留扩展字段',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(10) DEFAULT NULL COMMENT '删除时间',
  `title` varchar(255) NOT NULL COMMENT '积分名称',
  `change_type` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '变动类型 1-新用户注册 2-用户答题',
  `action_type` tinyint(3) UNSIGNED NOT NULL DEFAULT '2' COMMENT '操作类型 1-平台操作 2-用户操作'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户账户变动记录表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_user`
--

CREATE TABLE `la_user` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '主键',
  `tenant_id` int(11) NOT NULL COMMENT '租户ID',
  `sn` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '编号',
  `avatar` varchar(200) NOT NULL DEFAULT '' COMMENT '头像',
  `real_name` varchar(32) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `nickname` varchar(32) NOT NULL DEFAULT '' COMMENT '用户昵称',
  `account` varchar(32) NOT NULL DEFAULT '' COMMENT '用户账号',
  `password` varchar(32) NOT NULL DEFAULT '' COMMENT '用户密码',
  `mobile` varchar(32) NOT NULL DEFAULT '' COMMENT '用户电话',
  `sex` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户性别: [0=保密, 1=男, 2=女]',
  `channel` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '注册渠道: [1-微信小程序 2-微信公众号 3-手机H5 4-电脑PC 5-苹果APP 6-安卓APP]',
  `is_disable` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否禁用: [0=否, 1=是]',
  `login_ip` varchar(200) NOT NULL DEFAULT '' COMMENT '最后登录IP',
  `login_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `is_new_user` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否是新注册用户: [1-是, 0-否]',
  `user_money` decimal(10,2) UNSIGNED DEFAULT '0.00' COMMENT '用户余额',
  `total_recharge_amount` decimal(10,2) UNSIGNED DEFAULT '0.00' COMMENT '累计充值',
  `create_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `delete_time` int(10) UNSIGNED DEFAULT NULL COMMENT '删除时间',
  `remark` varchar(255) NOT NULL DEFAULT '这家伙很懒，什么都没留下' COMMENT '用户备注',
  `integral` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户积分',
  `vip_state` tinyint(1) NOT NULL DEFAULT '1' COMMENT '会员状态: [1-未激活, 2-已过期, 3-已激活]',
  `vip_endTime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '会员到期时间',
  `auto_eliminate_threshold` int(10) UNSIGNED DEFAULT '2' COMMENT '自动消灭错题阈值：1-答对1次自动消灭，2-答对2次自动消灭'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_user_account_log`
--

CREATE TABLE `la_user_account_log` (
  `id` int(11) UNSIGNED NOT NULL,
  `tenant_id` int(11) NOT NULL COMMENT '租户ID',
  `sn` varchar(32) NOT NULL DEFAULT '' COMMENT '流水号',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `change_object` tinyint(1) NOT NULL DEFAULT '0' COMMENT '变动对象',
  `change_type` smallint(5) NOT NULL COMMENT '变动类型',
  `action` tinyint(1) NOT NULL DEFAULT '0' COMMENT '动作 1-增加 2-减少',
  `change_amount` decimal(10,2) NOT NULL COMMENT '变动数量',
  `left_amount` decimal(10,2) NOT NULL DEFAULT '100.00' COMMENT '变动后数量',
  `source_sn` varchar(255) DEFAULT NULL COMMENT '关联单号',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `extra` text COMMENT '预留扩展字段',
  `create_time` int(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(10) DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户账户变动记录表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_user_auth`
--

CREATE TABLE `la_user_auth` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL COMMENT '租户ID',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `openid` varchar(128) NOT NULL COMMENT '微信openid',
  `unionid` varchar(128) DEFAULT '' COMMENT '微信unionid',
  `terminal` tinyint(1) NOT NULL DEFAULT '1' COMMENT '客户端类型：1-微信小程序；2-微信公众号；3-手机H5；4-电脑PC；5-苹果APP；6-安卓APP',
  `create_time` int(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户授权表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `la_user_message`
--

CREATE TABLE `la_user_message` (
  `id` int(11) UNSIGNED NOT NULL COMMENT '消息ID',
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '接收用户ID',
  `type` tinyint(2) UNSIGNED NOT NULL DEFAULT '1' COMMENT '消息类型: 1-评论回复 2-评论点赞 3-评论赞赏 4-系统通知',
  `sub_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '子类型: article_comment-文章评论 question_comment-试题评论 integral_change-积分变化 vip_remind-会员提醒 vip_change-会员变更 weekly_summary-每周总结 resource_add-资料添加 article_add-文章添加 question_add-试题添加',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '消息标题',
  `content` text COLLATE utf8mb4_unicode_ci COMMENT '消息内容',
  `extra` text COLLATE utf8mb4_unicode_ci COMMENT '扩展数据(JSON格式): 包含发送者信息、相关内容ID等',
  `is_read` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否已读: 0-未读 1-已读',
  `read_time` int(11) DEFAULT NULL COMMENT '阅读时间',
  `create_time` int(11) UNSIGNED NOT NULL COMMENT '创建时间',
  `update_time` int(11) UNSIGNED DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(11) UNSIGNED DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户消息通知表';

-- --------------------------------------------------------

--
-- 表的结构 `la_user_session`
--

CREATE TABLE `la_user_session` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL COMMENT '租户ID',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `terminal` tinyint(1) NOT NULL DEFAULT '1' COMMENT '客户端类型：1-微信小程序；2-微信公众号；3-手机H5；4-电脑PC；5-苹果APP；6-安卓APP',
  `token` varchar(32) NOT NULL COMMENT '令牌',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
  `expire_time` int(10) NOT NULL COMMENT '到期时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户会话表' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 视图结构 `la_data_search`
--
DROP TABLE IF EXISTS `la_data_search`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dt_allpp_cn`@`localhost` SQL SECURITY DEFINER VIEW `la_data_search`  AS SELECT `la_data_search`.`uid` AS `uid`, `la_data_search`.`title` AS `title`, `la_data_search`.`image` AS `image`, `la_data_search`.`data_type` AS `data_type`, `la_data_search`.`publish_time` AS `publish_time` FROM (select `la_article`.`id` AS `uid`,`la_article`.`title` AS `title`,`la_article`.`image` AS `image`,'article' AS `data_type`,`la_article`.`create_time` AS `publish_time` from `la_article` where (isnull(`la_article`.`delete_time`) and (`la_article`.`is_show` = 1)) union all select `la_tenant_exam_library`.`uid` AS `uid`,`la_tenant_exam_library`.`title` AS `title`,`la_tenant_exam_library`.`image` AS `image`,'exam_library' AS `data_type`,`la_tenant_exam_library`.`create_time` AS `publish_time` from `la_tenant_exam_library` where (isnull(`la_tenant_exam_library`.`delete_time`) and (`la_tenant_exam_library`.`is_show` = 1)) union all select `la_tenant_resource`.`uid` AS `uid`,`la_tenant_resource`.`title` AS `title`,`la_tenant_resource`.`image` AS `image`,'resource' AS `data_type`,`la_tenant_resource`.`create_time` AS `publish_time` from `la_tenant_resource` where (isnull(`la_tenant_resource`.`delete_time`) and (`la_tenant_resource`.`is_show` = 1))) AS `la_data_search` ;

-- --------------------------------------------------------

--
-- 视图结构 `la_tenant_exam_question_error_stats`
--
DROP TABLE IF EXISTS `la_tenant_exam_question_error_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`dt_allpp_cn`@`localhost` SQL SECURITY DEFINER VIEW `la_tenant_exam_question_error_stats`  AS SELECT 
    q.library_uid AS library_uid, 
    q.user_uid AS user_uid, 
    count(q.id) AS total_errors, 
    sum((case when (q.eliminated_status = 1) then 1 else 0 end)) AS eliminated_errors, 
    round(((sum((case when (q.eliminated_status = 1) then 1 else 0 end)) / count(q.id)) * 100),2) AS elimination_rate, 
    COALESCE((SELECT SUM(attempted_count) FROM la_tenant_user_chapter_statistics s 
              WHERE s.user_uid = q.user_uid AND s.library_uid = q.library_uid 
              GROUP BY s.user_uid, s.library_uid), 0) AS total_answers 
FROM `la_tenant_exam_question_error` q 
GROUP BY q.library_uid, q.user_uid ;

--
-- 转储表的索引
--

--
-- 表的索引 `la_admin`
--
ALTER TABLE `la_admin`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_admin_dept`
--
ALTER TABLE `la_admin_dept`
  ADD PRIMARY KEY (`admin_id`,`dept_id`) USING BTREE;

--
-- 表的索引 `la_admin_jobs`
--
ALTER TABLE `la_admin_jobs`
  ADD PRIMARY KEY (`admin_id`,`jobs_id`) USING BTREE;

--
-- 表的索引 `la_admin_role`
--
ALTER TABLE `la_admin_role`
  ADD PRIMARY KEY (`admin_id`,`role_id`) USING BTREE;

--
-- 表的索引 `la_admin_session`
--
ALTER TABLE `la_admin_session`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `admin_id_client` (`admin_id`,`terminal`) USING BTREE COMMENT '一个用户在一个终端只有一个token',
  ADD UNIQUE KEY `token` (`token`) USING BTREE COMMENT 'token是唯一的';

--
-- 表的索引 `la_article`
--
ALTER TABLE `la_article`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_article_cate`
--
ALTER TABLE `la_article_cate`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_article_collect`
--
ALTER TABLE `la_article_collect`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_config`
--
ALTER TABLE `la_config`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_content_push_record`
--
ALTER TABLE `la_content_push_record`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_content` (`content_type`,`content_id`,`tenant_id`),
  ADD KEY `idx_tenant_id` (`tenant_id`),
  ADD KEY `idx_push_time` (`push_time`);

--
-- 表的索引 `la_decorate_page`
--
ALTER TABLE `la_decorate_page`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_decorate_tabbar`
--
ALTER TABLE `la_decorate_tabbar`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_dept`
--
ALTER TABLE `la_dept`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_dev_crontab`
--
ALTER TABLE `la_dev_crontab`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_dict_data`
--
ALTER TABLE `la_dict_data`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_dict_type`
--
ALTER TABLE `la_dict_type`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_file`
--
ALTER TABLE `la_file`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_file_cate`
--
ALTER TABLE `la_file_cate`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_generate_column`
--
ALTER TABLE `la_generate_column`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_generate_table`
--
ALTER TABLE `la_generate_table`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_hot_search`
--
ALTER TABLE `la_hot_search`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_jobs`
--
ALTER TABLE `la_jobs`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_message_config`
--
ALTER TABLE `la_message_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_key` (`key`);

--
-- 表的索引 `la_notice_record`
--
ALTER TABLE `la_notice_record`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_notice_setting`
--
ALTER TABLE `la_notice_setting`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_official_account_reply`
--
ALTER TABLE `la_official_account_reply`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_operation_log`
--
ALTER TABLE `la_operation_log`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_pay_config`
--
ALTER TABLE `la_pay_config`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_pay_way`
--
ALTER TABLE `la_pay_way`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_recharge_order`
--
ALTER TABLE `la_recharge_order`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_refund_log`
--
ALTER TABLE `la_refund_log`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_refund_record`
--
ALTER TABLE `la_refund_record`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_sms_log`
--
ALTER TABLE `la_sms_log`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_system_menu`
--
ALTER TABLE `la_system_menu`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_system_role`
--
ALTER TABLE `la_system_role`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_system_role_menu`
--
ALTER TABLE `la_system_role_menu`
  ADD PRIMARY KEY (`role_id`,`menu_id`) USING BTREE;

--
-- 表的索引 `la_tenant`
--
ALTER TABLE `la_tenant`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_tenant_admin`
--
ALTER TABLE `la_tenant_admin`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_tenant_admin_dept`
--
ALTER TABLE `la_tenant_admin_dept`
  ADD PRIMARY KEY (`admin_id`,`dept_id`) USING BTREE;

--
-- 表的索引 `la_tenant_admin_jobs`
--
ALTER TABLE `la_tenant_admin_jobs`
  ADD PRIMARY KEY (`admin_id`,`jobs_id`) USING BTREE;

--
-- 表的索引 `la_tenant_admin_role`
--
ALTER TABLE `la_tenant_admin_role`
  ADD PRIMARY KEY (`admin_id`,`role_id`) USING BTREE;

--
-- 表的索引 `la_tenant_admin_session`
--
ALTER TABLE `la_tenant_admin_session`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `admin_id_client` (`admin_id`,`terminal`) USING BTREE COMMENT '一个用户在一个终端只有一个token',
  ADD UNIQUE KEY `token` (`token`) USING BTREE COMMENT 'token是唯一的';

--
-- 表的索引 `la_tenant_banner`
--
ALTER TABLE `la_tenant_banner`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `uid` (`uid`) USING BTREE;

--
-- 表的索引 `la_tenant_config`
--
ALTER TABLE `la_tenant_config`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_tenant_dept`
--
ALTER TABLE `la_tenant_dept`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_tenant_dict_data`
--
ALTER TABLE `la_tenant_dict_data`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_tenant_dict_type`
--
ALTER TABLE `la_tenant_dict_type`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `la_tenant_exam_activation_code`
--
ALTER TABLE `la_tenant_exam_activation_code`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_code` (`code`) COMMENT '激活码唯一',
  ADD KEY `idx_batch_id` (`batch_id`) COMMENT '批次ID索引',
  ADD KEY `idx_status` (`status`) COMMENT '状态索引',
  ADD KEY `idx_tenant_id` (`tenant_id`) COMMENT '租户ID索引',
  ADD KEY `idx_used_user_id` (`used_user_id`) COMMENT '已使用用户ID索引',
  ADD KEY `idx_tenant_status` (`tenant_id`,`status`) COMMENT '租户ID+状态联合索引';

--
-- 表的索引 `la_tenant_exam_activation_code_batch`
--
ALTER TABLE `la_tenant_exam_activation_code_batch`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`) COMMENT '状态索引',
  ADD KEY `idx_tenant_id` (`tenant_id`) COMMENT '租户ID索引';

--
-- 表的索引 `la_tenant_exam_activation_record`
--
ALTER TABLE `la_tenant_exam_activation_record`
  ADD PRIMARY KEY (`id`),
  ADD KEY `code_id` (`code_id`),
  ADD KEY `batch_id` (`batch_id`),
  ADD KEY `idx_tenant_activation` (`tenant_id`,`code_id`) COMMENT '租户+激活码联合索引',
  ADD KEY `idx_tenant_user` (`tenant_id`,`user_id`) COMMENT '租户+用户联合索引',
  ADD KEY `idx_activation_time` (`activation_time`) COMMENT '激活时间索引',
  ADD KEY `idx_expiration_time` (`expiration_time`) COMMENT '过期时间索引',
  ADD KEY `idx_user_id` (`user_id`) COMMENT '用户ID索引';

--
-- 表的索引 `la_tenant_exam_category`
--
ALTER TABLE `la_tenant_exam_category`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `uid` (`uid`) USING BTREE;

--
-- 表的索引 `la_tenant_exam_chapter`
--
ALTER TABLE `la_tenant_exam_chapter`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uid` (`uid`) USING BTREE;

--
-- 表的索引 `la_tenant_exam_comment`
--
ALTER TABLE `la_tenant_exam_comment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_qid_pid` (`qid`,`pid`) USING BTREE,
  ADD KEY `idx_user_id` (`user_id`) USING BTREE,
  ADD KEY `idx_createtime` (`create_time`) USING BTREE,
  ADD KEY `idx_status_deletetime` (`status`,`delete_time`),
  ADD KEY `idx_tenant_id` (`tenant_id`);

--
-- 表的索引 `la_tenant_exam_comment_like`
--
ALTER TABLE `la_tenant_exam_comment_like`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_user_comment` (`user_id`,`comment_id`),
  ADD KEY `idx_tenant_id` (`tenant_id`),
  ADD KEY `idx_comment_id` (`comment_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_qid_type` (`qid`,`type`),
  ADD KEY `idx_create_time` (`create_time`) COMMENT '点赞时间索引';

--
-- 表的索引 `la_tenant_exam_comment_reward`
--
ALTER TABLE `la_tenant_exam_comment_reward`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_comment_id` (`comment_id`),
  ADD KEY `idx_comment_user_id` (`comment_user_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_tenant_id` (`tenant_id`),
  ADD KEY `idx_create_time` (`create_time`);

--
-- 表的索引 `la_tenant_exam_diyfields`
--
ALTER TABLE `la_tenant_exam_diyfields`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_diyform_id` (`diyform_id`) USING BTREE,
  ADD KEY `idx_diyform` (`diyform`) USING BTREE,
  ADD KEY `idx_status` (`status`) USING BTREE;

--
-- 表的索引 `la_tenant_exam_diyform`
--
ALTER TABLE `la_tenant_exam_diyform`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`) USING BTREE,
  ADD KEY `idx_createtime` (`create_time`) USING BTREE,
  ADD KEY `idx_tenant_id` (`tenant_id`);

--
-- 表的索引 `la_tenant_exam_diyform_cewshi`
--
ALTER TABLE `la_tenant_exam_diyform_cewshi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tenant_user` (`tenant_id`,`user_id`),
  ADD KEY `idx_tenant_create` (`tenant_id`,`create_time`),
  ADD KEY `idx_status_create` (`status`,`create_time`);

--
-- 表的索引 `la_tenant_exam_diyform_dierge`
--
ALTER TABLE `la_tenant_exam_diyform_dierge`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tenant_id` (`tenant_id`),
  ADD KEY `idx_tenant_create` (`tenant_id`,`create_time`),
  ADD KEY `idx_status_create` (`status`,`create_time`);

--
-- 表的索引 `la_tenant_exam_diyform_qq3`
--
ALTER TABLE `la_tenant_exam_diyform_qq3`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tenant_user` (`tenant_id`,`user_id`),
  ADD KEY `idx_tenant_create` (`tenant_id`,`create_time`),
  ADD KEY `idx_status_create` (`status`,`create_time`);

--
-- 表的索引 `la_tenant_exam_examination`
--
ALTER TABLE `la_tenant_exam_examination`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_uid` (`uid`),
  ADD KEY `idx_tenant_id` (`tenant_id`),
  ADD KEY `idx_create_time` (`create_time`),
  ADD KEY `idx_start_end_time` (`start_time`,`end_time`),
  ADD KEY `idx_paper_uid` (`paper_uid`),
  ADD KEY `idx_delete_time` (`delete_time`);

--
-- 表的索引 `la_tenant_exam_examination_history`
--
ALTER TABLE `la_tenant_exam_examination_history`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `uid` (`uid`) USING BTREE,
  ADD KEY `idx_tenant_time_stat` (`tenant_id`,`create_time`,`delete_time`),
  ADD KEY `idx_tenant_user_time` (`tenant_id`,`user_uid`,`create_time`),
  ADD KEY `idx_user_submit` (`user_uid`,`submit_time`),
  ADD KEY `idx_questions_type` (`questions_type`);

--
-- 表的索引 `la_tenant_exam_help`
--
ALTER TABLE `la_tenant_exam_help`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `la_tenant_exam_knowledge`
--
ALTER TABLE `la_tenant_exam_knowledge`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `la_tenant_exam_label`
--
ALTER TABLE `la_tenant_exam_label`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `la_tenant_exam_library`
--
ALTER TABLE `la_tenant_exam_library`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `uid` (`uid`) USING BTREE,
  ADD KEY `cate_uid` (`category_uid`) USING BTREE;

--
-- 表的索引 `la_tenant_exam_mock_examination`
--
ALTER TABLE `la_tenant_exam_mock_examination`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_uid` (`uid`),
  ADD KEY `idx_tenant_user` (`tenant_id`,`user_uid`),
  ADD KEY `idx_question_uid` (`question_uid`),
  ADD KEY `idx_delete_time` (`delete_time`);

--
-- 表的索引 `la_tenant_exam_other_settings`
--
ALTER TABLE `la_tenant_exam_other_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_create_time` (`create_time`);

--
-- 表的索引 `la_tenant_exam_paper`
--
ALTER TABLE `la_tenant_exam_paper`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tenant` (`tenant_id`),
  ADD KEY `idx_create_time` (`create_time`);

--
-- 表的索引 `la_tenant_exam_question`
--
ALTER TABLE `la_tenant_exam_question`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `uid` (`uid`) USING BTREE,
  ADD KEY `idx_statistics` (`total_attempts`,`total_correct`),
  ADD KEY `idx_exam_type` (`exam_type`),
  ADD KEY `idx_level` (`level`),
  ADD KEY `idx_accuracy` (`accuracy_rate`);

--
-- 表的索引 `la_tenant_exam_question_collection`
--
ALTER TABLE `la_tenant_exam_question_collection`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `uk_user_question_collect` (`user_uid`,`question_uid`,`tenant_id`) COMMENT '用户题目收藏唯一索引，防止重复收藏',
  ADD KEY `library_uid` (`library_uid`,`user_uid`),
  ADD KEY `idx_question_uid_collect` (`question_uid`) COMMENT '题目ID索引，用于查询收藏用户列表',
  ADD KEY `idx_create_time_collect` (`create_time`) COMMENT '收藏时间索引';

--
-- 表的索引 `la_tenant_exam_question_corrections`
--
ALTER TABLE `la_tenant_exam_question_corrections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_question_id` (`question_uid`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_create_time` (`create_time`);

--
-- 表的索引 `la_tenant_exam_question_error`
--
ALTER TABLE `la_tenant_exam_question_error`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `library_uid` (`library_uid`,`user_uid`),
  ADD KEY `idx_questions_type` (`questions_type`),
  ADD KEY `idx_exam_type` (`exam_type`),
  ADD KEY `idx_chapter_uid` (`chapter_uid`),
  ADD KEY `idx_level` (`level`);

--
-- 表的索引 `la_tenant_exam_question_error_eliminate_log`
--
ALTER TABLE `la_tenant_exam_question_error_eliminate_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_error_uid` (`question_error_uid`);

--
-- 表的索引 `la_tenant_exam_question_like`
--
ALTER TABLE `la_tenant_exam_question_like`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_user_question` (`user_uid`,`question_uid`,`tenant_id`) COMMENT '用户题目唯一索引，防止重复点赞',
  ADD KEY `idx_question_uid` (`question_uid`) COMMENT '题目ID索引，用于查询点赞用户列表',
  ADD KEY `idx_create_time` (`create_time`) COMMENT '点赞时间索引';

--
-- 表的索引 `la_tenant_exam_ranking_daily`
--
ALTER TABLE `la_tenant_exam_ranking_daily`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_tenant_user_date` (`tenant_id`,`user_id`,`create_date`),
  ADD KEY `idx_tenant_date_ranking` (`tenant_id`,`create_date`,`ranking`),
  ADD KEY `idx_tenant_date_correct` (`tenant_id`,`create_date`,`correct_count`,`total_count`),
  ADD KEY `idx_date_ranking` (`create_date`,`ranking`);

--
-- 表的索引 `la_tenant_exam_ranking_monthly`
--
ALTER TABLE `la_tenant_exam_ranking_monthly`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_tenant_user_year_month` (`tenant_id`,`user_id`,`year`,`month`),
  ADD KEY `idx_tenant_year_month_ranking` (`tenant_id`,`year`,`month`,`ranking`),
  ADD KEY `idx_tenant_year_month_correct` (`tenant_id`,`year`,`month`,`correct_count`,`total_count`),
  ADD KEY `idx_year_month_ranking` (`year`,`month`,`ranking`);

--
-- 表的索引 `la_tenant_exam_ranking_settings`
--
ALTER TABLE `la_tenant_exam_ranking_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_tenant_type` (`tenant_id`,`ranking_type`);

--
-- 表的索引 `la_tenant_exam_ranking_total`
--
ALTER TABLE `la_tenant_exam_ranking_total`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_tenant_user` (`tenant_id`,`user_id`),
  ADD KEY `idx_tenant_ranking` (`tenant_id`,`ranking`),
  ADD KEY `idx_tenant_correct` (`tenant_id`,`correct_count`,`total_count`);

--
-- 表的索引 `la_tenant_exam_ranking_weekly`
--
ALTER TABLE `la_tenant_exam_ranking_weekly`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_tenant_user_year_week` (`tenant_id`,`user_id`,`year`,`week_number`),
  ADD KEY `idx_tenant_year_week_ranking` (`tenant_id`,`year`,`week_number`,`ranking`),
  ADD KEY `idx_tenant_year_week_correct` (`tenant_id`,`year`,`week_number`,`correct_count`,`total_count`),
  ADD KEY `idx_year_week_ranking` (`year`,`week_number`,`ranking`);

--
-- 表的索引 `la_tenant_exam_record`
--
ALTER TABLE `la_tenant_exam_record`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `la_tenant_exam_user_activation_record`
--
ALTER TABLE `la_tenant_exam_user_activation_record`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `idx_tenant_id` (`tenant_id`) USING BTREE,
  ADD KEY `idx_user_uid` (`user_uid`) USING BTREE,
  ADD KEY `idx_activation_type` (`activation_type`) USING BTREE,
  ADD KEY `idx_activation_time` (`activation_time`) USING BTREE;

--
-- 表的索引 `la_tenant_file`
--
ALTER TABLE `la_tenant_file`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_tenant_file_cate`
--
ALTER TABLE `la_tenant_file_cate`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_tenant_integral_settings`
--
ALTER TABLE `la_tenant_integral_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_tenant_id` (`tenant_id`) COMMENT '租户ID唯一索引，确保每个租户只有一条设置记录',
  ADD KEY `idx_updated_at` (`update_time`) COMMENT '更新时间索引，方便按更新时间查询';

--
-- 表的索引 `la_tenant_jobs`
--
ALTER TABLE `la_tenant_jobs`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_tenant_notice_record`
--
ALTER TABLE `la_tenant_notice_record`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_tenant_notice_setting`
--
ALTER TABLE `la_tenant_notice_setting`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_tenant_pay_config`
--
ALTER TABLE `la_tenant_pay_config`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_tenant_pay_way`
--
ALTER TABLE `la_tenant_pay_way`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_tenant_resource`
--
ALTER TABLE `la_tenant_resource`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `uid` (`uid`) USING BTREE,
  ADD KEY `cate_uid` (`category_uid`) USING BTREE;

--
-- 表的索引 `la_tenant_resource_category`
--
ALTER TABLE `la_tenant_resource_category`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `uid` (`uid`) USING BTREE;

--
-- 表的索引 `la_tenant_resource_collect`
--
ALTER TABLE `la_tenant_resource_collect`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `idx_ur` (`user_uid`,`resource_uid`) USING BTREE;

--
-- 表的索引 `la_tenant_sms_log`
--
ALTER TABLE `la_tenant_sms_log`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_tenant_system_menu`
--
ALTER TABLE `la_tenant_system_menu`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_tenant_system_role`
--
ALTER TABLE `la_tenant_system_role`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_tenant_system_role_menu`
--
ALTER TABLE `la_tenant_system_role_menu`
  ADD PRIMARY KEY (`role_id`,`menu_id`) USING BTREE;

--
-- 表的索引 `la_tenant_user_chapter_statistics`
--
ALTER TABLE `la_tenant_user_chapter_statistics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_chapter` (`user_uid`,`chapter_uid`,`library_uid`,`tenant_id`) COMMENT '用户章节唯一索引',
  ADD KEY `idx_user_uid` (`user_uid`) COMMENT '用户索引',
  ADD KEY `idx_chapter_uid` (`chapter_uid`) COMMENT '章节索引',
  ADD KEY `idx_library_uid` (`library_uid`) COMMENT '题库索引',
  ADD KEY `idx_tenant_id` (`tenant_id`) COMMENT '租户索引',
  ADD KEY `idx_last_practice_time` (`last_practice_time`) COMMENT '最后练习时间索引',
  ADD KEY `idx_composite` (`tenant_id`,`library_uid`,`user_uid`) COMMENT '复合索引用于常见查询';

--
-- 表的索引 `la_tenant_user_exam_progress`
--
ALTER TABLE `la_tenant_user_exam_progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_library` (`user_id`,`library_uid`,`questions_type`),
  ADD KEY `idx_updated` (`updated_at`);

--
-- 表的索引 `la_tenant_user_integral_log`
--
ALTER TABLE `la_tenant_user_integral_log`
  ADD PRIMARY KEY (`id`) COMMENT '主键索引',
  ADD KEY `idx_tenant_user` (`tenant_id`,`user_id`) COMMENT '租户+用户联合索引，优化查询用户积分记录',
  ADD KEY `idx_create_time` (`create_time`) COMMENT '创建时间索引，优化时间范围查询',
  ADD KEY `idx_tenant_delete` (`tenant_id`,`delete_time`) COMMENT '租户+删除状态索引，优化租户维度的有效记录查询';

--
-- 表的索引 `la_user`
--
ALTER TABLE `la_user`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `sn` (`sn`) USING BTREE COMMENT '编号唯一',
  ADD UNIQUE KEY `account` (`account`) USING BTREE COMMENT '账号唯一';

--
-- 表的索引 `la_user_account_log`
--
ALTER TABLE `la_user_account_log`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `la_user_auth`
--
ALTER TABLE `la_user_auth`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `openid` (`openid`) USING BTREE;

--
-- 表的索引 `la_user_message`
--
ALTER TABLE `la_user_message`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_create_time` (`create_time`);

--
-- 表的索引 `la_user_session`
--
ALTER TABLE `la_user_session`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `admin_id_client` (`user_id`,`terminal`) USING BTREE COMMENT '一个用户在一个终端只有一个token',
  ADD UNIQUE KEY `token` (`token`) USING BTREE COMMENT 'token是唯一的';

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `la_admin`
--
ALTER TABLE `la_admin`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_admin_session`
--
ALTER TABLE `la_admin_session`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_article`
--
ALTER TABLE `la_article`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '文章id';

--
-- 使用表AUTO_INCREMENT `la_article_cate`
--
ALTER TABLE `la_article_cate`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '文章分类id';

--
-- 使用表AUTO_INCREMENT `la_article_collect`
--
ALTER TABLE `la_article_collect`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键';

--
-- 使用表AUTO_INCREMENT `la_config`
--
ALTER TABLE `la_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_content_push_record`
--
ALTER TABLE `la_content_push_record`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '记录ID';

--
-- 使用表AUTO_INCREMENT `la_decorate_page`
--
ALTER TABLE `la_decorate_page`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键';

--
-- 使用表AUTO_INCREMENT `la_decorate_tabbar`
--
ALTER TABLE `la_decorate_tabbar`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键';

--
-- 使用表AUTO_INCREMENT `la_dept`
--
ALTER TABLE `la_dept`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id';

--
-- 使用表AUTO_INCREMENT `la_dev_crontab`
--
ALTER TABLE `la_dev_crontab`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_dict_data`
--
ALTER TABLE `la_dict_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id';

--
-- 使用表AUTO_INCREMENT `la_dict_type`
--
ALTER TABLE `la_dict_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id';

--
-- 使用表AUTO_INCREMENT `la_file`
--
ALTER TABLE `la_file`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID';

--
-- 使用表AUTO_INCREMENT `la_file_cate`
--
ALTER TABLE `la_file_cate`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID';

--
-- 使用表AUTO_INCREMENT `la_generate_column`
--
ALTER TABLE `la_generate_column`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id';

--
-- 使用表AUTO_INCREMENT `la_generate_table`
--
ALTER TABLE `la_generate_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id';

--
-- 使用表AUTO_INCREMENT `la_hot_search`
--
ALTER TABLE `la_hot_search`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键';

--
-- 使用表AUTO_INCREMENT `la_jobs`
--
ALTER TABLE `la_jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id';

--
-- 使用表AUTO_INCREMENT `la_message_config`
--
ALTER TABLE `la_message_config`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '配置ID';

--
-- 使用表AUTO_INCREMENT `la_notice_record`
--
ALTER TABLE `la_notice_record`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- 使用表AUTO_INCREMENT `la_notice_setting`
--
ALTER TABLE `la_notice_setting`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_official_account_reply`
--
ALTER TABLE `la_official_account_reply`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_operation_log`
--
ALTER TABLE `la_operation_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_pay_config`
--
ALTER TABLE `la_pay_config`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_pay_way`
--
ALTER TABLE `la_pay_way`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_recharge_order`
--
ALTER TABLE `la_recharge_order`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id';

--
-- 使用表AUTO_INCREMENT `la_refund_log`
--
ALTER TABLE `la_refund_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id';

--
-- 使用表AUTO_INCREMENT `la_refund_record`
--
ALTER TABLE `la_refund_record`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id';

--
-- 使用表AUTO_INCREMENT `la_sms_log`
--
ALTER TABLE `la_sms_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id';

--
-- 使用表AUTO_INCREMENT `la_system_menu`
--
ALTER TABLE `la_system_menu`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键';

--
-- 使用表AUTO_INCREMENT `la_system_role`
--
ALTER TABLE `la_system_role`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_tenant`
--
ALTER TABLE `la_tenant`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键';

--
-- 使用表AUTO_INCREMENT `la_tenant_admin`
--
ALTER TABLE `la_tenant_admin`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_tenant_admin_session`
--
ALTER TABLE `la_tenant_admin_session`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_tenant_banner`
--
ALTER TABLE `la_tenant_banner`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_tenant_config`
--
ALTER TABLE `la_tenant_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_tenant_dept`
--
ALTER TABLE `la_tenant_dept`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id';

--
-- 使用表AUTO_INCREMENT `la_tenant_dict_data`
--
ALTER TABLE `la_tenant_dict_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id';

--
-- 使用表AUTO_INCREMENT `la_tenant_dict_type`
--
ALTER TABLE `la_tenant_dict_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id';

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_activation_code`
--
ALTER TABLE `la_tenant_exam_activation_code`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '激活码ID';

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_activation_code_batch`
--
ALTER TABLE `la_tenant_exam_activation_code_batch`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '批次ID';

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_activation_record`
--
ALTER TABLE `la_tenant_exam_activation_record`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '记录ID';

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_category`
--
ALTER TABLE `la_tenant_exam_category`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_chapter`
--
ALTER TABLE `la_tenant_exam_chapter`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_comment`
--
ALTER TABLE `la_tenant_exam_comment`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_comment_like`
--
ALTER TABLE `la_tenant_exam_comment_like`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID';

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_comment_reward`
--
ALTER TABLE `la_tenant_exam_comment_reward`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '赞赏ID';

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_diyfields`
--
ALTER TABLE `la_tenant_exam_diyfields`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_diyform`
--
ALTER TABLE `la_tenant_exam_diyform`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID';

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_diyform_cewshi`
--
ALTER TABLE `la_tenant_exam_diyform_cewshi`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_diyform_dierge`
--
ALTER TABLE `la_tenant_exam_diyform_dierge`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_diyform_qq3`
--
ALTER TABLE `la_tenant_exam_diyform_qq3`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_examination`
--
ALTER TABLE `la_tenant_exam_examination`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增主键';

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_examination_history`
--
ALTER TABLE `la_tenant_exam_examination_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_help`
--
ALTER TABLE `la_tenant_exam_help`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '帮助中心id';

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_knowledge`
--
ALTER TABLE `la_tenant_exam_knowledge`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_label`
--
ALTER TABLE `la_tenant_exam_label`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_library`
--
ALTER TABLE `la_tenant_exam_library`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_mock_examination`
--
ALTER TABLE `la_tenant_exam_mock_examination`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID';

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_other_settings`
--
ALTER TABLE `la_tenant_exam_other_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键';

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_paper`
--
ALTER TABLE `la_tenant_exam_paper`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_question`
--
ALTER TABLE `la_tenant_exam_question`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_question_collection`
--
ALTER TABLE `la_tenant_exam_question_collection`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_question_corrections`
--
ALTER TABLE `la_tenant_exam_question_corrections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '序号';

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_question_error`
--
ALTER TABLE `la_tenant_exam_question_error`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_question_error_eliminate_log`
--
ALTER TABLE `la_tenant_exam_question_error_eliminate_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_question_like`
--
ALTER TABLE `la_tenant_exam_question_like`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_ranking_daily`
--
ALTER TABLE `la_tenant_exam_ranking_daily`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID';

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_ranking_monthly`
--
ALTER TABLE `la_tenant_exam_ranking_monthly`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID';

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_ranking_settings`
--
ALTER TABLE `la_tenant_exam_ranking_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID';

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_ranking_total`
--
ALTER TABLE `la_tenant_exam_ranking_total`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID';

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_ranking_weekly`
--
ALTER TABLE `la_tenant_exam_ranking_weekly`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID';

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_record`
--
ALTER TABLE `la_tenant_exam_record`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键';

--
-- 使用表AUTO_INCREMENT `la_tenant_exam_user_activation_record`
--
ALTER TABLE `la_tenant_exam_user_activation_record`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID';

--
-- 使用表AUTO_INCREMENT `la_tenant_file`
--
ALTER TABLE `la_tenant_file`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID';

--
-- 使用表AUTO_INCREMENT `la_tenant_file_cate`
--
ALTER TABLE `la_tenant_file_cate`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID';

--
-- 使用表AUTO_INCREMENT `la_tenant_integral_settings`
--
ALTER TABLE `la_tenant_integral_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID';

--
-- 使用表AUTO_INCREMENT `la_tenant_jobs`
--
ALTER TABLE `la_tenant_jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id';

--
-- 使用表AUTO_INCREMENT `la_tenant_notice_record`
--
ALTER TABLE `la_tenant_notice_record`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID';

--
-- 使用表AUTO_INCREMENT `la_tenant_notice_setting`
--
ALTER TABLE `la_tenant_notice_setting`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_tenant_pay_config`
--
ALTER TABLE `la_tenant_pay_config`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_tenant_pay_way`
--
ALTER TABLE `la_tenant_pay_way`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_tenant_resource`
--
ALTER TABLE `la_tenant_resource`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_tenant_resource_category`
--
ALTER TABLE `la_tenant_resource_category`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_tenant_resource_collect`
--
ALTER TABLE `la_tenant_resource_collect`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_tenant_sms_log`
--
ALTER TABLE `la_tenant_sms_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id';

--
-- 使用表AUTO_INCREMENT `la_tenant_system_menu`
--
ALTER TABLE `la_tenant_system_menu`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键';

--
-- 使用表AUTO_INCREMENT `la_tenant_system_role`
--
ALTER TABLE `la_tenant_system_role`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_tenant_user_chapter_statistics`
--
ALTER TABLE `la_tenant_user_chapter_statistics`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID';

--
-- 使用表AUTO_INCREMENT `la_tenant_user_exam_progress`
--
ALTER TABLE `la_tenant_user_exam_progress`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_tenant_user_integral_log`
--
ALTER TABLE `la_tenant_user_integral_log`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID';

--
-- 使用表AUTO_INCREMENT `la_user`
--
ALTER TABLE `la_user`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键';

--
-- 使用表AUTO_INCREMENT `la_user_account_log`
--
ALTER TABLE `la_user_account_log`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_user_auth`
--
ALTER TABLE `la_user_auth`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `la_user_message`
--
ALTER TABLE `la_user_message`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '消息ID';

--
-- 使用表AUTO_INCREMENT `la_user_session`
--
ALTER TABLE `la_user_session`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 限制导出的表
--

--
-- 限制表 `la_tenant_exam_activation_code`
--
ALTER TABLE `la_tenant_exam_activation_code`
  ADD CONSTRAINT `la_tenant_exam_activation_code_ibfk_1` FOREIGN KEY (`batch_id`) REFERENCES `la_tenant_exam_activation_code_batch` (`id`) ON DELETE CASCADE;

--
-- 限制表 `la_tenant_exam_activation_record`
--
ALTER TABLE `la_tenant_exam_activation_record`
  ADD CONSTRAINT `la_tenant_exam_activation_record_ibfk_1` FOREIGN KEY (`code_id`) REFERENCES `la_tenant_exam_activation_code` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `la_tenant_exam_activation_record_ibfk_2` FOREIGN KEY (`batch_id`) REFERENCES `la_tenant_exam_activation_code_batch` (`id`) ON DELETE CASCADE;

--
-- 限制表 `la_tenant_exam_question_error_eliminate_log`
--
ALTER TABLE `la_tenant_exam_question_error_eliminate_log`
  ADD CONSTRAINT `la_tenant_exam_question_error_eliminate_log_ibfk_1` FOREIGN KEY (`question_error_uid`) REFERENCES `la_tenant_exam_question_error` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
