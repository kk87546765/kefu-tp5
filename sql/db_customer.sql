/*
Navicat MySQL Data Transfer

Source Server         : 客服rds（读）
Source Server Version : 50737
Source Host           : rm-wz921s325108jf0bm8o.mysql.rds.aliyuncs.com:3306
Source Database       : db_customer

Target Server Type    : MYSQL
Target Server Version : 50737
File Encoding         : 65001

Date: 2023-02-24 10:19:44
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for gamekey
-- ----------------------------
DROP TABLE IF EXISTS `gamekey`;
CREATE TABLE `gamekey` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `game_name` varchar(150) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '游戏名',
  `game_code` varchar(200) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '游戏代码',
  `type` tinyint(4) DEFAULT '2' COMMENT 'type=1是模式一（sdk封禁+踢下线） type=2是模式二（sdk封禁+cp封禁）',
  `key` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `config` text CHARACTER SET utf8mb4 COMMENT '配置选项',
  `add_time` bigint(20) DEFAULT '0' COMMENT '添加时间',
  `edit_time` bigint(20) DEFAULT '0' COMMENT '修改时间',
  `admin_id` int(11) DEFAULT '0' COMMENT '操作人',
  `status` tinyint(4) DEFAULT '1' COMMENT '状态 0 关闭 1开启',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for loss_user
-- ----------------------------
DROP TABLE IF EXISTS `loss_user`;
CREATE TABLE `loss_user` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `platform_id` int(11) NOT NULL DEFAULT '0' COMMENT '平台id',
  `phone` varchar(15) DEFAULT '' COMMENT '手机号',
  `udid` varchar(150) DEFAULT '0' COMMENT '唯一设备号',
  `account_money` decimal(10,0) DEFAULT '0' COMMENT '用户全产品充值金额',
  `send_num` smallint(6) DEFAULT '0' COMMENT '已发送次数',
  `last_send_time` bigint(20) DEFAULT '0' COMMENT '最后发送时间',
  `last_login_time` bigint(20) DEFAULT '0' COMMENT '最后登录时间',
  `reg_time` bigint(20) DEFAULT '0' COMMENT '注册时间',
  `add_time` bigint(20) DEFAULT '0' COMMENT '数据添加时间',
  `remark1` varchar(200) DEFAULT '' COMMENT '备用字段',
  `remark2` varchar(200) DEFAULT '' COMMENT '备用字段2',
  `ext` text COMMENT '备用文本字段',
  PRIMARY KEY (`id`,`platform_id`),
  UNIQUE KEY `p_p` (`platform_id`,`phone`) USING BTREE,
  KEY `udid` (`udid`) USING BTREE,
  KEY `last_send_time` (`last_send_time`) USING BTREE,
  KEY `last_login_time` (`last_login_time`) USING BTREE,
  KEY `reg_time` (`reg_time`) USING BTREE,
  KEY `add_time` (`reg_time`) USING BTREE,
  KEY `account_money` (`account_money`) USING BTREE,
  KEY `send_num` (`send_num`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1265131 DEFAULT CHARSET=utf8mb4
/*!50100 PARTITION BY RANGE (`platform_id`)
(PARTITION p0 VALUES LESS THAN (1) ENGINE = InnoDB,
 PARTITION p1 VALUES LESS THAN (2) ENGINE = InnoDB,
 PARTITION p2 VALUES LESS THAN (3) ENGINE = InnoDB,
 PARTITION p3 VALUES LESS THAN (4) ENGINE = InnoDB,
 PARTITION p4 VALUES LESS THAN (5) ENGINE = InnoDB,
 PARTITION p5 VALUES LESS THAN (6) ENGINE = InnoDB,
 PARTITION p6 VALUES LESS THAN (7) ENGINE = InnoDB,
 PARTITION p7 VALUES LESS THAN (8) ENGINE = InnoDB,
 PARTITION p8 VALUES LESS THAN (9) ENGINE = InnoDB,
 PARTITION p9 VALUES LESS THAN (10) ENGINE = InnoDB,
 PARTITION p10 VALUES LESS THAN MAXVALUE ENGINE = InnoDB) */;

-- ----------------------------
-- Table structure for loss_user_copy
-- ----------------------------
DROP TABLE IF EXISTS `loss_user_copy`;
CREATE TABLE `loss_user_copy` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `platform_id` int(11) NOT NULL DEFAULT '0' COMMENT '平台id',
  `phone` varchar(15) DEFAULT '' COMMENT '手机号',
  `udid` varchar(150) DEFAULT '0' COMMENT '唯一设备号',
  `account_money` decimal(10,0) DEFAULT '0' COMMENT '用户全产品充值金额',
  `send_num` smallint(6) DEFAULT '0' COMMENT '已发送次数',
  `last_send_time` bigint(20) DEFAULT '0' COMMENT '最后发送时间',
  `last_login_time` bigint(20) DEFAULT '0' COMMENT '最后登录时间',
  `reg_time` bigint(20) DEFAULT '0' COMMENT '注册时间',
  `add_time` bigint(20) DEFAULT '0' COMMENT '数据添加时间',
  `remark1` varchar(200) DEFAULT '' COMMENT '备用字段',
  `remark2` varchar(200) DEFAULT '' COMMENT '备用字段2',
  `ext` text COMMENT '备用文本字段',
  PRIMARY KEY (`id`,`platform_id`),
  UNIQUE KEY `p_p` (`platform_id`,`phone`) USING BTREE,
  KEY `udid` (`udid`) USING BTREE,
  KEY `last_send_time` (`last_send_time`) USING BTREE,
  KEY `last_login_time` (`last_login_time`) USING BTREE,
  KEY `reg_time` (`reg_time`) USING BTREE,
  KEY `add_time` (`reg_time`) USING BTREE,
  KEY `account_money` (`account_money`) USING BTREE,
  KEY `send_num` (`send_num`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1088674 DEFAULT CHARSET=utf8mb4
/*!50100 PARTITION BY RANGE (`platform_id`)
(PARTITION p0 VALUES LESS THAN (1) ENGINE = InnoDB,
 PARTITION p1 VALUES LESS THAN (2) ENGINE = InnoDB,
 PARTITION p2 VALUES LESS THAN (3) ENGINE = InnoDB,
 PARTITION p3 VALUES LESS THAN (4) ENGINE = InnoDB,
 PARTITION p4 VALUES LESS THAN (5) ENGINE = InnoDB,
 PARTITION p5 VALUES LESS THAN (6) ENGINE = InnoDB,
 PARTITION p6 VALUES LESS THAN (7) ENGINE = InnoDB,
 PARTITION p7 VALUES LESS THAN (8) ENGINE = InnoDB,
 PARTITION p8 VALUES LESS THAN (9) ENGINE = InnoDB,
 PARTITION p9 VALUES LESS THAN (10) ENGINE = InnoDB,
 PARTITION p10 VALUES LESS THAN MAXVALUE ENGINE = InnoDB) */;

-- ----------------------------
-- Table structure for loss_user_sub
-- ----------------------------
DROP TABLE IF EXISTS `loss_user_sub`;
CREATE TABLE `loss_user_sub` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `platform_id` tinyint(4) DEFAULT '0' COMMENT '平台id',
  `phone` varchar(15) DEFAULT '' COMMENT '手机号码',
  `udid` varchar(200) DEFAULT '' COMMENT '唯一设备号',
  `uid` bigint(20) DEFAULT '0' COMMENT '账号uid',
  `str_uid` varchar(100) DEFAULT '' COMMENT 'uid字符型（用于客服统计表相连）',
  `username` varchar(100) DEFAULT '' COMMENT '账号名',
  `add_time` bigint(20) DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ppu` (`platform_id`,`phone`,`uid`) USING BTREE,
  KEY `pid` (`platform_id`) USING BTREE,
  KEY `phone` (`phone`) USING BTREE,
  KEY `udid` (`udid`(191)) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `str_uid` (`str_uid`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2190161 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for loss_user_sub_copy
-- ----------------------------
DROP TABLE IF EXISTS `loss_user_sub_copy`;
CREATE TABLE `loss_user_sub_copy` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `platform_id` tinyint(4) DEFAULT '0' COMMENT '平台id',
  `phone` varchar(15) DEFAULT '' COMMENT '手机号码',
  `udid` varchar(200) DEFAULT '' COMMENT '唯一设备号',
  `uid` bigint(20) DEFAULT '0' COMMENT '账号uid',
  `str_uid` varchar(100) DEFAULT '' COMMENT 'uid字符型（用于客服统计表相连）',
  `username` varchar(100) DEFAULT '' COMMENT '账号名',
  `add_time` bigint(20) DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `pid` (`platform_id`) USING BTREE,
  KEY `phone` (`phone`) USING BTREE,
  KEY `udid` (`udid`(191)) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `str_uid` (`str_uid`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1515935 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for platform_list
-- ----------------------------
DROP TABLE IF EXISTS `platform_list`;
CREATE TABLE `platform_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `platform_id` smallint(6) NOT NULL COMMENT '平台id',
  `platform_name` varchar(32) NOT NULL COMMENT '平台名称',
  `platform_suffix` varchar(6) NOT NULL COMMENT '平台后缀',
  `static` tinyint(2) NOT NULL DEFAULT '1' COMMENT '状态（1：默认 开启 2：关闭）',
  `add_user_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '添加用户id',
  `add_time` int(11) NOT NULL COMMENT '添加时间',
  `edit_user_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '编辑用户id',
  `edit_time` int(11) NOT NULL DEFAULT '0' COMMENT '编辑时间',
  `config` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `platform_id` (`platform_id`),
  UNIQUE KEY `platform_suffix` (`platform_suffix`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for qc_appeal_log
-- ----------------------------
DROP TABLE IF EXISTS `qc_appeal_log`;
CREATE TABLE `qc_appeal_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `log_id` int(10) NOT NULL DEFAULT '0' COMMENT '质检记录id',
  `status` tinyint(1) DEFAULT '0' COMMENT '-1申诉驳回 0正常 1申诉中 2申诉成功',
  `reason` text,
  `add_time` int(10) DEFAULT '0',
  `admin_id` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `log_id` (`log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for qc_config
-- ----------------------------
DROP TABLE IF EXISTS `qc_config`;
CREATE TABLE `qc_config` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `key` char(30) DEFAULT NULL,
  `val` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=77 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='质检配置表';

-- ----------------------------
-- Table structure for qc_question
-- ----------------------------
DROP TABLE IF EXISTS `qc_question`;
CREATE TABLE `qc_question` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `kf_id` int(10) DEFAULT '0' COMMENT '客服uid',
  `kf_source` tinyint(30) DEFAULT '0' COMMENT '客服来源',
  `kf_score` tinyint(3) DEFAULT '-1' COMMENT '-1 未评价',
  `server_start` int(10) DEFAULT '0' COMMENT '对话开始时间',
  `server_end` int(10) DEFAULT '0' COMMENT '对话结束时间',
  `server_long` smallint(5) DEFAULT '0' COMMENT '对话时长（分钟）',
  `question_type` varchar(50) DEFAULT NULL COMMENT '问题类型',
  `game_type` varchar(50) DEFAULT NULL COMMENT '咨询游戏',
  `admin_id` int(10) DEFAULT '0' COMMENT '质检员',
  `text` text COMMENT '会话内容',
  `text_id` varchar(100) DEFAULT NULL COMMENT '会话id',
  `qc_num` varchar(50) DEFAULT NULL,
  `create_time` int(10) DEFAULT '0',
  `qc_question_log_id` int(10) DEFAULT '0' COMMENT '质检记录id',
  `kf_platform` char(10) NOT NULL DEFAULT '' COMMENT '会话主体',
  `partnerid` varchar(50) DEFAULT '0',
  `text_user_id` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `text_id` (`text_id`),
  KEY `qc_num` (`qc_num`),
  KEY `kf_platform` (`kf_platform`),
  KEY `server_start` (`server_start`),
  KEY `kf_source` (`kf_source`)
) ENGINE=InnoDB AUTO_INCREMENT=6313995 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='质检池';

-- ----------------------------
-- Table structure for qc_question_log
-- ----------------------------
DROP TABLE IF EXISTS `qc_question_log`;
CREATE TABLE `qc_question_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `kf_id` int(10) DEFAULT '0' COMMENT '客服id',
  `question_type` varchar(50) DEFAULT NULL COMMENT '会话分类',
  `game_type` varchar(50) DEFAULT NULL COMMENT '咨询游戏',
  `admin_id` int(10) DEFAULT '0' COMMENT '质检员',
  `text` text COMMENT '会话内容',
  `qc_question_id` int(10) DEFAULT '0' COMMENT '质检池id ',
  `qc_type` varchar(30) DEFAULT NULL COMMENT '质检类型',
  `qc_level` varchar(30) DEFAULT '0' COMMENT '质检等级',
  `qc_score` tinyint(3) DEFAULT '0',
  `qc_comments` text COMMENT '质检点评',
  `kf_adventage` varchar(255) DEFAULT NULL COMMENT '服务亮点',
  `kf_is_solve` tinyint(1) DEFAULT '0' COMMENT '客服是否解决 0 未解决',
  `kf_nosolve_res` varchar(255) DEFAULT NULL COMMENT '客服未解决原因',
  `question_is_solve` tinyint(1) DEFAULT '0' COMMENT '问题是否解决 0 未解决',
  `question_nosolve_res` varchar(255) DEFAULT NULL COMMENT '问题未解决原因',
  `create_time` int(10) DEFAULT NULL,
  `is_example` tinyint(1) DEFAULT '0' COMMENT '是否案例',
  `status` tinyint(1) DEFAULT '0' COMMENT '0 正常 1 申诉中',
  `appeal_reason` varchar(255) DEFAULT NULL COMMENT '申诉原因',
  `reject_appeal_reason` varchar(255) DEFAULT NULL COMMENT '拒绝原因',
  `is_del` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `update_time` int(10) DEFAULT '0',
  `update_user` int(10) DEFAULT '0',
  `kf_source` tinyint(30) DEFAULT '0' COMMENT '客服来源',
  `server_start` int(10) DEFAULT '0' COMMENT '对话开始时间',
  `server_end` int(10) DEFAULT '0' COMMENT '对话结束时间',
  `platform_id` tinyint(3) NOT NULL DEFAULT '0' COMMENT '会话主体',
  `appeal_log_id` int(10) NOT NULL DEFAULT '0' COMMENT '申诉最后记录',
  PRIMARY KEY (`id`),
  KEY `platform_id` (`platform_id`)
) ENGINE=InnoDB AUTO_INCREMENT=53021 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='质检记录';

-- ----------------------------
-- Table structure for qc_question_score_log
-- ----------------------------
DROP TABLE IF EXISTS `qc_question_score_log`;
CREATE TABLE `qc_question_score_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `qc_question_log_id` int(10) DEFAULT '0' COMMENT '质检记录id',
  `reason` varchar(100) DEFAULT NULL COMMENT '评分原因',
  `score` tinyint(3) DEFAULT '0' COMMENT '变化分数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8236 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='质检分数评估';

-- ----------------------------
-- Table structure for qc_sell_question
-- ----------------------------
DROP TABLE IF EXISTS `qc_sell_question`;
CREATE TABLE `qc_sell_question` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `kf_id` int(10) DEFAULT '0' COMMENT '客服uid',
  `kf_source` tinyint(30) DEFAULT '0' COMMENT '客服来源',
  `kf_score` tinyint(3) DEFAULT '-1' COMMENT '-1 未评价',
  `server_start` int(10) DEFAULT '0' COMMENT '对话开始时间',
  `server_end` int(10) DEFAULT '0' COMMENT '对话结束时间',
  `server_long` smallint(5) DEFAULT '0' COMMENT '对话时长（分钟）',
  `question_type` varchar(50) DEFAULT NULL COMMENT '问题类型',
  `game_type` varchar(50) DEFAULT NULL COMMENT '咨询游戏',
  `admin_id` int(10) DEFAULT '0' COMMENT '质检员',
  `text` text COMMENT '会话内容',
  `text_id` varchar(100) DEFAULT NULL COMMENT '会话id',
  `qc_num` varchar(50) DEFAULT NULL,
  `create_time` int(10) DEFAULT '0',
  `qc_question_log_id` int(10) DEFAULT '0' COMMENT '质检记录id',
  `kf_platform` char(10) NOT NULL DEFAULT '' COMMENT '会话主体',
  `partnerid` varchar(50) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='营销会话质检池';

-- ----------------------------
-- Table structure for recall_code
-- ----------------------------
DROP TABLE IF EXISTS `recall_code`;
CREATE TABLE `recall_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `platform_id` tinyint(4) DEFAULT '0' COMMENT '平台id',
  `up_id` int(11) DEFAULT '0' COMMENT '唯一产品id',
  `code` varchar(200) DEFAULT '' COMMENT '礼包码',
  `admin_id` int(11) DEFAULT '0' COMMENT '操作人',
  `add_time` bigint(20) DEFAULT '0' COMMENT '添加时间',
  `status` tinyint(4) DEFAULT '1' COMMENT '状态 0：关闭 1：开启',
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `up_id` (`up_id`) USING BTREE,
  KEY `code` (`code`(191)) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COMMENT='召回礼包码表';

-- ----------------------------
-- Table structure for recall_code_group
-- ----------------------------
DROP TABLE IF EXISTS `recall_code_group`;
CREATE TABLE `recall_code_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `platform_id` tinyint(4) DEFAULT '0' COMMENT '平台id',
  `up_id` int(11) DEFAULT '0' COMMENT '唯一产品id',
  `type` tinyint(4) DEFAULT '0' COMMENT '召回礼包类型',
  `title` varchar(100) DEFAULT '' COMMENT '召回码组标题',
  `admin_id` int(11) DEFAULT '0' COMMENT '操作人',
  `add_time` bigint(20) DEFAULT '0' COMMENT '添加时间',
  `status` tinyint(4) DEFAULT '1' COMMENT '状态 0：关闭 1：开启',
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `p_p` (`platform_id`,`up_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COMMENT='召回礼包组表';

-- ----------------------------
-- Table structure for recall_code_re
-- ----------------------------
DROP TABLE IF EXISTS `recall_code_re`;
CREATE TABLE `recall_code_re` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_code_id` int(11) DEFAULT NULL,
  `code_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for recall_group_code_copy
-- ----------------------------
DROP TABLE IF EXISTS `recall_group_code_copy`;
CREATE TABLE `recall_group_code_copy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `platform_id` tinyint(4) DEFAULT '0' COMMENT '平台id',
  `up_id` int(11) DEFAULT '0' COMMENT '唯一产品id',
  `type` tinyint(4) DEFAULT '0' COMMENT '召回礼包类型',
  `title` varchar(100) DEFAULT '' COMMENT '召回码组标题',
  `admin_id` int(11) DEFAULT '0' COMMENT '操作人',
  `add_time` bigint(20) DEFAULT '0' COMMENT '添加时间',
  `status` tinyint(4) DEFAULT '1' COMMENT '状态 0：关闭 1：开启',
  PRIMARY KEY (`id`),
  KEY `platform_id` (`platform_id`) USING BTREE,
  KEY `up_id` (`up_id`) USING BTREE,
  KEY `admin_id` (`admin_id`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for recall_link
-- ----------------------------
DROP TABLE IF EXISTS `recall_link`;
CREATE TABLE `recall_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `platform_id` tinyint(4) DEFAULT '0' COMMENT '平台id',
  `up_id` int(11) DEFAULT '0' COMMENT '唯一产品id',
  `ver_id` int(10) NOT NULL DEFAULT '0',
  `link` varchar(200) DEFAULT '' COMMENT '召回链接',
  `admin_id` int(11) DEFAULT '0' COMMENT '操作人',
  `add_time` bigint(20) DEFAULT '0' COMMENT '添加时间',
  `status` tinyint(4) DEFAULT '1' COMMENT '状态 0：关闭 1：开启',
  `update_time` int(10) NOT NULL DEFAULT '0',
  `ver_title` varchar(255) NOT NULL DEFAULT '',
  `ver_config` text COMMENT '广告位配置，预留字段',
  PRIMARY KEY (`id`),
  UNIQUE KEY `pv_id` (`platform_id`,`ver_id`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `admin_id` (`admin_id`) USING BTREE,
  KEY `link` (`link`(191)) USING BTREE,
  KEY `up_id` (`up_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COMMENT='召回礼包链接表';

-- ----------------------------
-- Table structure for recall_link_group
-- ----------------------------
DROP TABLE IF EXISTS `recall_link_group`;
CREATE TABLE `recall_link_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `platform_id` tinyint(4) DEFAULT '0' COMMENT '平台id',
  `up_id` int(11) DEFAULT '0' COMMENT '平台唯一产品id',
  `title` varchar(50) DEFAULT '' COMMENT '广告组标题',
  `admin_id` int(11) DEFAULT '0' COMMENT '操作人id',
  `add_time` bigint(20) DEFAULT '0' COMMENT '添加时间',
  `status` tinyint(4) DEFAULT '1' COMMENT '包状态 0：关闭 1：开启',
  `update_time` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `p_p` (`platform_id`,`up_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COMMENT='召回链接组表';

-- ----------------------------
-- Table structure for recall_link_re
-- ----------------------------
DROP TABLE IF EXISTS `recall_link_re`;
CREATE TABLE `recall_link_re` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_link_id` int(11) DEFAULT NULL,
  `link_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=92 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for recall_plan
-- ----------------------------
DROP TABLE IF EXISTS `recall_plan`;
CREATE TABLE `recall_plan` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `platform_id` tinyint(4) DEFAULT '0' COMMENT '平台id',
  `title` varchar(255) DEFAULT '' COMMENT '计划名',
  `loss_up_id` varchar(255) DEFAULT '' COMMENT '流失唯一产品id,可多个',
  `min_account_money` decimal(10,0) DEFAULT '0' COMMENT '用户全产品充值下限',
  `max_account_money` decimal(10,0) DEFAULT '0' COMMENT '用户全产品充值上限',
  `min_loss_up_money` decimal(10,0) DEFAULT '0' COMMENT '用户流失产品充值下限（单一产品符合即可）',
  `max_loss_up_money` decimal(10,0) DEFAULT '0' COMMENT '用户流失产品充值上限（单一产品符合即可）',
  `min_loss_day` smallint(6) DEFAULT '0' COMMENT '流失天数下限',
  `max_loss_day` smallint(6) DEFAULT '0' COMMENT '流失天数上限',
  `min_level` smallint(6) DEFAULT '0' COMMENT '角色等级上限',
  `max_level` smallint(6) DEFAULT '0' COMMENT '角色等级上限',
  `send_num` smallint(6) DEFAULT '0' COMMENT '发送次数限制',
  `interval_day` smallint(6) DEFAULT '0' COMMENT '间隔日',
  `recall_up_id` int(11) DEFAULT '0' COMMENT '召回唯一产品id',
  `recall_ver_group_id` int(11) DEFAULT '0' COMMENT '召回链接组id',
  `recall_code_group_id` int(11) DEFAULT '0' COMMENT '召回礼包组id',
  `execute_type` tinyint(4) DEFAULT '1' COMMENT '执行类型 1：自动执行 2：手动执行',
  `limit_num` int(11) DEFAULT '0' COMMENT '发送限制数量 0：无限制',
  `add_time` bigint(20) DEFAULT '0' COMMENT '添加时间',
  `admin_id` int(11) DEFAULT '0' COMMENT '操作人',
  `status` tinyint(4) DEFAULT '1' COMMENT '状态 0：关闭 1：开启',
  `remark1` varchar(255) DEFAULT '' COMMENT '备用字段1',
  `remark2` varchar(255) DEFAULT '' COMMENT '备用字段2',
  `ext` text COMMENT '备用字段3',
  PRIMARY KEY (`id`),
  KEY `platform_id` (`platform_id`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `admin_id` (`admin_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for recall_plan_log
-- ----------------------------
DROP TABLE IF EXISTS `recall_plan_log`;
CREATE TABLE `recall_plan_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `exec_date` varchar(255) DEFAULT '' COMMENT '执行日期',
  `plan_id` bigint(20) DEFAULT '0' COMMENT '计划id',
  `platform_id` tinyint(4) DEFAULT '0' COMMENT '平台id',
  `title` varchar(200) DEFAULT '' COMMENT '标题名',
  `loss_up_id` varchar(200) DEFAULT '' COMMENT '流失唯一产品',
  `recall_up_id` int(11) DEFAULT '0' COMMENT '召回唯一产品id',
  `min_account_money` decimal(10,0) DEFAULT '0' COMMENT '用户全产品充值下限',
  `max_account_money` decimal(10,0) DEFAULT '0' COMMENT '用户全产品充值上限',
  `min_loss_up_money` decimal(10,0) DEFAULT '0' COMMENT '用户流失产品充值下限（单一产品符合即可）',
  `max_loss_up_money` decimal(10,0) DEFAULT '0' COMMENT '用户流失产品充值下限（单一产品符合即可）',
  `min_loss_day` smallint(6) DEFAULT '0' COMMENT '流失天数下限',
  `max_loss_day` smallint(6) DEFAULT '0' COMMENT '流失天数上限',
  `min_level` smallint(6) DEFAULT '0' COMMENT '角色等级下限',
  `max_level` smallint(6) DEFAULT '0' COMMENT '角色等级上限',
  `send_num` smallint(6) DEFAULT '0' COMMENT '发送次数限制',
  `interval_day` smallint(6) DEFAULT '0' COMMENT '间隔日',
  `recall_ver_id` text COMMENT '召回链接广告位id(多个)',
  `recall_code_id` text COMMENT '召回礼包id(多个)',
  `execute_type` tinyint(4) DEFAULT '0' COMMENT '执行类型',
  `limit_num` int(11) DEFAULT '0' COMMENT '发送数量限制 0：无限制',
  `should_be_send` int(11) DEFAULT '0' COMMENT '应发数量',
  `have_send` int(11) DEFAULT '0' COMMENT '实际发送数量',
  `recall_num` int(11) DEFAULT '0' COMMENT '召回数量',
  `add_time` bigint(20) DEFAULT '0' COMMENT '生成记录时间',
  `status` tinyint(4) DEFAULT '1' COMMENT '执行状态 1：执行中 2：执行结束',
  `remark1` varchar(255) DEFAULT '' COMMENT '备用字段1',
  `remark2` varchar(255) DEFAULT '' COMMENT '备用字段2',
  `ext` text COMMENT '备用字段3',
  PRIMARY KEY (`id`),
  KEY `exec_date` (`exec_date`(191)) USING BTREE,
  KEY `plan_id` (`plan_id`) USING BTREE,
  KEY `platform_id` (`platform_id`,`recall_up_id`) USING BTREE,
  KEY `loss_up_id` (`loss_up_id`(191)) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=153 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for recall_plan_people_log
-- ----------------------------
DROP TABLE IF EXISTS `recall_plan_people_log`;
CREATE TABLE `recall_plan_people_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `exec_date` varchar(100) DEFAULT '' COMMENT '日期',
  `plan_log_id` bigint(20) DEFAULT '0' COMMENT '计划执行记录日志主键id',
  `platform_id` tinyint(4) DEFAULT '0' COMMENT '平台id',
  `uid` bigint(20) DEFAULT NULL,
  `phone` varchar(30) DEFAULT '' COMMENT '手机号',
  `account_money` decimal(10,0) DEFAULT '0' COMMENT '用户全产品充值',
  `product_money` decimal(10,0) DEFAULT '0' COMMENT '用户流失产品充值',
  `level` smallint(6) DEFAULT NULL,
  `last_login_time` bigint(20) DEFAULT '0' COMMENT '最后登录时间',
  `reback` tinyint(4) DEFAULT '0' COMMENT '是否回流 0：否 1：是',
  `reback_time` bigint(20) DEFAULT '0' COMMENT '回流时间',
  `reback_ver_id` int(11) DEFAULT '0' COMMENT '回流广告位id',
  `reback_up_id` int(11) DEFAULT '0' COMMENT '回流唯一产品id',
  `add_time` bigint(20) DEFAULT '0' COMMENT '添加时间',
  `status` tinyint(4) DEFAULT '0' COMMENT '状态 是否发送成功 0：否 1：成功',
  PRIMARY KEY (`id`),
  UNIQUE KEY `exec_date` (`exec_date`,`plan_log_id`,`platform_id`,`phone`) USING BTREE,
  KEY `plan_log_id` (`plan_log_id`) USING BTREE,
  KEY `reback_time` (`reback_time`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `last_login_time` (`last_login_time`) USING BTREE,
  KEY `p_p` (`platform_id`,`uid`) USING BTREE,
  KEY `phone` (`phone`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2010525 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for recall_plan_people_log_bak
-- ----------------------------
DROP TABLE IF EXISTS `recall_plan_people_log_bak`;
CREATE TABLE `recall_plan_people_log_bak` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `exec_date` varchar(20) DEFAULT '' COMMENT '日期',
  `plan_log_id` bigint(20) DEFAULT '0' COMMENT '计划执行记录日志主键id',
  `platform_id` tinyint(4) DEFAULT '0' COMMENT '平台id',
  `uid` bigint(20) DEFAULT NULL,
  `phone` varchar(30) DEFAULT '' COMMENT '手机号',
  `account_money` decimal(10,0) DEFAULT '0' COMMENT '用户全产品充值',
  `product_money` decimal(10,0) DEFAULT '0' COMMENT '用户流失产品充值',
  `level` smallint(6) DEFAULT NULL,
  `last_login_time` bigint(20) DEFAULT '0' COMMENT '最后登录时间',
  `reback` tinyint(4) DEFAULT '0' COMMENT '是否回流 0：否 1：是',
  `reback_time` bigint(20) DEFAULT '0' COMMENT '回流时间',
  `reback_ver_id` int(11) DEFAULT '0' COMMENT '回流广告位id',
  `reback_up_id` int(11) DEFAULT '0' COMMENT '回流唯一产品id',
  `add_time` bigint(20) DEFAULT '0' COMMENT '添加时间',
  `status` tinyint(4) DEFAULT '0' COMMENT '状态 是否发送成功 0：否 1：成功',
  PRIMARY KEY (`id`),
  UNIQUE KEY `exec_date` (`exec_date`,`plan_log_id`,`platform_id`,`phone`) USING BTREE,
  KEY `plan_log_id` (`plan_log_id`) USING BTREE,
  KEY `reback_time` (`reback_time`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `last_login_time` (`last_login_time`) USING BTREE,
  KEY `p_p` (`platform_id`,`uid`) USING BTREE,
  KEY `phone` (`phone`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=32768 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for sobot_conversation
-- ----------------------------
DROP TABLE IF EXISTS `sobot_conversation`;
CREATE TABLE `sobot_conversation` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `companyid` varchar(100) DEFAULT '0',
  `cid` varchar(100) DEFAULT NULL,
  `start_time` bigint(16) DEFAULT '0',
  `end_time` bigint(16) DEFAULT '0',
  `first_response_time` bigint(16) DEFAULT '0',
  `conversation_duration` int(10) DEFAULT '0',
  `staff_name` varchar(100) DEFAULT NULL,
  `lastgroupid` varchar(100) DEFAULT NULL,
  `lastgroup_name` varchar(100) DEFAULT NULL,
  `invite_evaluation_flags` tinyint(1) DEFAULT '0',
  `score` tinyint(3) DEFAULT '-1',
  `solved` tinyint(3) DEFAULT '-1',
  `add_time` int(10) DEFAULT '0',
  `is_do` tinyint(1) DEFAULT '0',
  `need_update` tinyint(1) DEFAULT '0' COMMENT '需要更新状态 1 要更新',
  `update_time` int(10) DEFAULT '0',
  `session_human_duration` int(10) DEFAULT '0' COMMENT '人工接待时长',
  `robot_name` varchar(255) DEFAULT NULL,
  `city_name` varchar(100) DEFAULT NULL,
  `offline_type` tinyint(1) DEFAULT '0' COMMENT '会话结束方式 1 客服离线，2 客户被客服移除 3 客户被客服加入黑名单 4 客户会话超时 5 客户关闭了聊天页面 6 客户打开新的页面',
  `partnerid` varchar(100) DEFAULT NULL COMMENT '合作方用户ID',
  `staff_reply_msg_count` smallint(5) DEFAULT '0' COMMENT '人工回复数 0-pc；1-微信；2-sdk；3-微博；4-移动网站；9-企业微信；10-微信小程序',
  `consult_robot_msg_count` smallint(5) DEFAULT '0' COMMENT '咨询机器人消息数',
  `robot_reply_msg_count` smallint(5) DEFAULT '0' COMMENT '机器人回复数',
  `consult_staff_msg_count` smallint(5) DEFAULT '0' COMMENT '咨询人工消息数',
  `transfer_tohuman_time` bigint(15) DEFAULT '0' COMMENT '机器人转人工时间',
  `sources` tinyint(1) DEFAULT '0' COMMENT '用户来源',
  `evaluation_remark` text COMMENT '评论备注信息',
  `evaluation_date_time` bigint(15) DEFAULT '0' COMMENT '评论时间',
  `os` tinyint(4) DEFAULT '0' COMMENT '终端 1 Windows XP；2 Windows 7；3 Windows 8；4 Windows Vista；5 Windows 其他；6 Linux；7 macOS；8 Android；9 iOS；11 Windows 2000；12 Windows 10 ；其他 其他',
  `visitorid` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partnerid` (`partnerid`),
  KEY `cid` (`cid`)
) ENGINE=InnoDB AUTO_INCREMENT=3454063 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for sobot_msg
-- ----------------------------
DROP TABLE IF EXISTS `sobot_msg`;
CREATE TABLE `sobot_msg` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `cid` varchar(100) DEFAULT NULL,
  `companyid` varchar(100) DEFAULT NULL,
  `timems` bigint(16) DEFAULT NULL,
  `senderid` varchar(100) DEFAULT NULL,
  `sender_name` varchar(100) DEFAULT NULL,
  `receiverid` varchar(100) DEFAULT NULL,
  `receiver_name` varchar(100) DEFAULT NULL,
  `msg` text,
  `doc_name` varchar(255) DEFAULT NULL,
  `sender_type` tinyint(1) NOT NULL DEFAULT '0',
  `receiver_type` tinyint(1) DEFAULT '0',
  `msg_offline` tinyint(1) DEFAULT '0',
  `add_time` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `cid` (`cid`)
) ENGINE=InnoDB AUTO_INCREMENT=34173819 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for sobot_summary
-- ----------------------------
DROP TABLE IF EXISTS `sobot_summary`;
CREATE TABLE `sobot_summary` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `companyid` varchar(100) DEFAULT NULL COMMENT '企业ID',
  `cid` varchar(100) DEFAULT NULL COMMENT '会话ID',
  `operation_name` varchar(255) DEFAULT NULL COMMENT '业务单元名称',
  `req_type_name` varchar(255) DEFAULT NULL COMMENT '业务类型名称列表',
  `summary_description` varchar(255) DEFAULT NULL COMMENT '备注',
  `add_time` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `companyid` (`companyid`,`cid`)
) ENGINE=InnoDB AUTO_INCREMENT=1972281 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for sobot_users
-- ----------------------------
DROP TABLE IF EXISTS `sobot_users`;
CREATE TABLE `sobot_users` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `companyid` varchar(100) DEFAULT NULL,
  `userid` varchar(100) DEFAULT NULL COMMENT '用户ID',
  `partnerid` varchar(100) DEFAULT NULL COMMENT '合作方用户ID',
  `remark` text,
  `summary_params` text,
  `nick` varchar(255) DEFAULT '' COMMENT '用户昵称',
  `sources` tinyint(1) DEFAULT '0' COMMENT '用户来源 0 pc；1 微信；2 sdk；3 微博；4 移动网站；9 企业微信；10 微信小程序',
  `visitorids` varchar(100) DEFAULT '' COMMENT '访客ID',
  `add_time` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `companyid` (`companyid`,`userid`)
) ENGINE=InnoDB AUTO_INCREMENT=2551126 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for system_city
-- ----------------------------
DROP TABLE IF EXISTS `system_city`;
CREATE TABLE `system_city` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `city_id` int(11) NOT NULL DEFAULT '0' COMMENT '城市id',
  `level` int(11) NOT NULL DEFAULT '0' COMMENT '省市级别',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '父级id',
  `citycode` varchar(30) NOT NULL DEFAULT '' COMMENT '区号',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '名称',
  `mername` varchar(255) NOT NULL DEFAULT '' COMMENT '合并名称',
  `lng` varchar(50) NOT NULL DEFAULT '' COMMENT '经度',
  `lat` varchar(50) NOT NULL DEFAULT '' COMMENT '纬度',
  `is_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否展示',
  `pinyin` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3971 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='城市表';

-- ----------------------------
-- Table structure for test1
-- ----------------------------
DROP TABLE IF EXISTS `test1`;
CREATE TABLE `test1` (
  `id` int(11) NOT NULL,
  `msg` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for work_sheet
-- ----------------------------
DROP TABLE IF EXISTS `work_sheet`;
CREATE TABLE `work_sheet` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `sheet_num` varchar(30) NOT NULL COMMENT '工单编号',
  `sheet_type` smallint(5) NOT NULL DEFAULT '0' COMMENT '工单类型 1账号密码 2问题跟进 3建议收集 4投诉预警',
  `sheet_item_type` varchar(100) NOT NULL DEFAULT '0' COMMENT '不同工单的子项类型（如sheet_type为1时为 1找回账号2找回密码3无充值找回4手机换绑5被盗申诉，多个类型用,号隔开）sheet_type为其他时相对应等',
  `sheet_item_type_sub` varchar(100) NOT NULL DEFAULT '0' COMMENT 'sheet_item_type的子项',
  `role_id` varchar(32) NOT NULL COMMENT '角色id',
  `user_account` varchar(50) NOT NULL COMMENT '用户账号',
  `platform_id` smallint(5) NOT NULL DEFAULT '0',
  `product_id` int(10) NOT NULL DEFAULT '0',
  `game_id` int(10) NOT NULL DEFAULT '0' COMMENT '游戏名称',
  `server_id` varchar(30) NOT NULL COMMENT '区服名称',
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '玩家UID',
  `pay_money_total` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总充值金额',
  `user_source` tinyint(1) NOT NULL DEFAULT '0' COMMENT '用户来源 1智齿 2企点',
  `visitor_id` varchar(100) NOT NULL DEFAULT '' COMMENT '访客ID',
  `contact_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '联系方式',
  `contact` varchar(100) NOT NULL DEFAULT '' COMMENT '联系方式',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态（sheet_type不同时表示不同意义）',
  `other_info` text COMMENT '不同工单类型的其他参数',
  `content` text,
  `is_top` int(11) NOT NULL DEFAULT '0' COMMENT '是否置顶 0否 >0是',
  `record_user` int(11) unsigned NOT NULL COMMENT '记录人',
  `follow_user` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '跟进人',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '新增时间',
  `edit_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `apply_time` int(11) NOT NULL DEFAULT '0' COMMENT '申请时间',
  `handle_time` int(11) NOT NULL DEFAULT '0' COMMENT '处理时间',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除，0否，1是',
  `p_p` varchar(50) NOT NULL DEFAULT '',
  `p_g` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sheet_num` (`sheet_num`),
  KEY `sheet_type` (`sheet_type`),
  KEY `p_p` (`p_p`),
  KEY `handle_time` (`handle_time`),
  KEY `edit_time` (`edit_time`),
  KEY `apply_time` (`apply_time`),
  KEY `is_top` (`is_top`),
  KEY `sheet_item_type` (`sheet_item_type`),
  KEY `role_id` (`role_id`),
  KEY `condition_filter` (`sheet_type`,`sheet_num`,`add_time`,`sheet_item_type`,`status`,`edit_time`),
  KEY `p_g` (`p_g`),
  KEY `add_time` (`add_time`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=1883 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='工单系统数据表';

-- ----------------------------
-- Table structure for work_sheet_log
-- ----------------------------
DROP TABLE IF EXISTS `work_sheet_log`;
CREATE TABLE `work_sheet_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `work_sheet_id` int(11) NOT NULL DEFAULT '0' COMMENT '工单表ID',
  `sheet_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '工单类型 1账号密码 2问题跟进 3建议收集 4意向投诉',
  `operation_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '操作类型 1新增 2修改',
  `admin_user` int(11) NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `content` text NOT NULL COMMENT '操作内容描述',
  `ip` varchar(30) NOT NULL DEFAULT '' COMMENT '当前操作IP',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '记录创建时间',
  `file_data` text,
  PRIMARY KEY (`id`),
  KEY `wsi_user_index` (`work_sheet_id`,`admin_user`),
  KEY `admin_user_index` (`admin_user`),
  KEY `work_sheet_id_index` (`work_sheet_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5304 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='工单操作日志表';

-- ----------------------------
-- Table structure for work_sheet_type
-- ----------------------------
DROP TABLE IF EXISTS `work_sheet_type`;
CREATE TABLE `work_sheet_type` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `pid` int(10) NOT NULL DEFAULT '0' COMMENT '附近',
  `children_title` varchar(100) DEFAULT NULL,
  `children_val` varchar(255) DEFAULT NULL,
  `status_val` varchar(255) DEFAULT NULL,
  `field_config` text,
  `is_del` tinyint(1) NOT NULL DEFAULT '0',
  `create_time` int(10) NOT NULL DEFAULT '0',
  `update_time` int(10) NOT NULL DEFAULT '0',
  `admin_id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for work_sheet_type_input
-- ----------------------------
DROP TABLE IF EXISTS `work_sheet_type_input`;
CREATE TABLE `work_sheet_type_input` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '配置id',
  `menu_name` varchar(255) NOT NULL COMMENT '字段名称',
  `type` varchar(255) NOT NULL DEFAULT '' COMMENT '类型(文本框,单选按钮...)',
  `input_type` varchar(20) DEFAULT 'input' COMMENT '表单类型',
  `work_sheet_type_id` int(10) unsigned NOT NULL COMMENT '配置分类id',
  `parameter` varchar(255) DEFAULT NULL COMMENT '规则 单选框和多选框',
  `upload_type` tinyint(1) unsigned DEFAULT NULL COMMENT '上传文件格式1单图2多图3文件',
  `required` varchar(255) DEFAULT NULL COMMENT '规则',
  `width` int(10) unsigned DEFAULT NULL COMMENT '多行文本框的宽度',
  `high` int(10) unsigned DEFAULT NULL COMMENT '多行文框的高度',
  `value` varchar(5000) DEFAULT NULL COMMENT '默认值',
  `info` varchar(255) NOT NULL DEFAULT '' COMMENT '配置名称',
  `desc` varchar(255) DEFAULT NULL COMMENT '配置简介',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否隐藏',
  PRIMARY KEY (`id`),
  UNIQUE KEY `menu_name` (`menu_name`,`work_sheet_type_id`),
  KEY `work_sheet_type_id` (`work_sheet_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='配置表';
