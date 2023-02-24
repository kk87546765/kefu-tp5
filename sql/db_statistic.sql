/*
Navicat MySQL Data Transfer

Source Server         : 客服rds（读）
Source Server Version : 50737
Source Host           : rm-wz921s325108jf0bm8o.mysql.rds.aliyuncs.com:3306
Source Database       : db_statistic

Target Server Type    : MYSQL
Target Server Version : 50737
File Encoding         : 65001

Date: 2023-02-24 10:20:36
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for account_registration
-- ----------------------------
DROP TABLE IF EXISTS `account_registration`;
CREATE TABLE `account_registration` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `garrison_product_id` int(10) DEFAULT '0' COMMENT '产品id',
  `server_id` varchar(100) CHARACTER SET utf8mb4 DEFAULT '0' COMMENT '区服id',
  `role_id` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `role_name` varchar(120) COLLATE utf8_unicode_ci DEFAULT NULL,
  `admin_department_id` smallint(4) DEFAULT '0' COMMENT '负责部门id',
  `admin_id` int(5) DEFAULT '0' COMMENT '负责人',
  `status` tinyint(1) DEFAULT '1' COMMENT '账号状态',
  `account_platform_id` tinyint(3) DEFAULT '0' COMMENT '账号平台id',
  `account` varchar(100) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '账号',
  `password` varchar(100) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '账号密码',
  `add_time` varchar(20) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '首次添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2679 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for account_registration_log
-- ----------------------------
DROP TABLE IF EXISTS `account_registration_log`;
CREATE TABLE `account_registration_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `log_id` int(11) DEFAULT NULL,
  `garrison_product_id` int(10) DEFAULT '0' COMMENT '产品id',
  `server_id` varchar(100) CHARACTER SET utf8mb4 DEFAULT '0' COMMENT '区服id',
  `role_id` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `role_name` varchar(120) COLLATE utf8_unicode_ci DEFAULT NULL,
  `admin_department_id` smallint(4) DEFAULT '0' COMMENT '负责部门id',
  `admin_id` int(5) DEFAULT '0' COMMENT '负责人',
  `status` tinyint(1) DEFAULT '1' COMMENT '账号状态',
  `account_platform_id` tinyint(3) DEFAULT '0' COMMENT '账号平台id',
  `account` varchar(100) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '账号',
  `password` varchar(100) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '账号密码',
  `add_time` varchar(255) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '添加时间',
  `operator_id` int(11) DEFAULT '0' COMMENT '操作人id',
  `operator_name` varchar(255) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '操作人员姓名',
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  KEY `garrison_product_id` (`garrison_product_id`),
  KEY `server_id` (`server_id`),
  KEY `admin_department_id` (`admin_department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for account_search_log
-- ----------------------------
DROP TABLE IF EXISTS `account_search_log`;
CREATE TABLE `account_search_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0' COMMENT '用户uid',
  `platform_id` int(11) DEFAULT '0' COMMENT '平台id',
  `type` tinyint(4) DEFAULT '0' COMMENT '类型 1：修改密码 2：换绑手机',
  `admin_id` int(11) DEFAULT '0' COMMENT '操作人id',
  `admin_name` varchar(120) DEFAULT '' COMMENT '操作人账号',
  `add_time` varchar(13) DEFAULT '' COMMENT '操作时间',
  `change_content` varchar(255) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `admin_id` (`admin_id`),
  KEY `platform_id` (`platform_id`),
  KEY `add_time` (`add_time`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for admin_user_game_server
-- ----------------------------
DROP TABLE IF EXISTS `admin_user_game_server`;
CREATE TABLE `admin_user_game_server` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `ascription_game_server_id` int(11) NOT NULL COMMENT '关联的用户产品游戏区服的范围id',
  `admin_user_id` int(11) NOT NULL COMMENT '专员用户id',
  `vip_game_product_id` int(11) NOT NULL DEFAULT '0' COMMENT '游戏产品id',
  `server_id` varchar(25) NOT NULL DEFAULT '0' COMMENT '游戏区服id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=211987 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='vip专员分配的游戏区服列表';

-- ----------------------------
-- Table structure for become_vip_standard
-- ----------------------------
DROP TABLE IF EXISTS `become_vip_standard`;
CREATE TABLE `become_vip_standard` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `vip_game_product_id` int(11) NOT NULL DEFAULT '0' COMMENT '游戏产品id',
  `game_id` int(10) NOT NULL DEFAULT '0',
  `day_pay` int(11) DEFAULT '0' COMMENT '单日充值金额',
  `thirty_day_pay` int(11) DEFAULT '0' COMMENT '近30天充值金额',
  `total_pay` int(11) DEFAULT '0' COMMENT '历史累充金额',
  `static` tinyint(2) DEFAULT '1' COMMENT '状态（1：默认 开启  2：关闭）',
  `add_admin_id` smallint(6) DEFAULT '0' COMMENT '添加用户id',
  `add_time` int(11) DEFAULT '0' COMMENT '添加时间',
  `edit_admin_id` int(11) DEFAULT '0' COMMENT '修改用户id',
  `edit_time` int(11) DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `vgp` (`vip_game_product_id`,`game_id`)
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for distributional_game_server
-- ----------------------------
DROP TABLE IF EXISTS `distributional_game_server`;
CREATE TABLE `distributional_game_server` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `garrison_product_id` int(11) DEFAULT NULL COMMENT '进驻产品id',
  `product_id` int(11) DEFAULT '0' COMMENT '产品id',
  `garrison_product_name` varchar(120) DEFAULT '' COMMENT '进驻gs的产品名称',
  `game_id` int(11) DEFAULT '0' COMMENT '游戏id',
  `platform_id` tinyint(4) DEFAULT '0' COMMENT '平台id',
  `server_id` varchar(120) DEFAULT '' COMMENT '区服id',
  `server_name` varchar(120) DEFAULT '' COMMENT '区服名称',
  `role_id` varchar(120) DEFAULT '' COMMENT '游戏角色id',
  `role_name` varchar(120) DEFAULT '' COMMENT '游戏角色名',
  `admin_id` int(11) DEFAULT '0' COMMENT '负责人id',
  `admin_name` varchar(120) DEFAULT '' COMMENT '负责人名称',
  `add_time` varchar(15) DEFAULT '' COMMENT '添加时间',
  `start_time` varchar(15) DEFAULT '' COMMENT '进驻时间',
  `end_time` varchar(15) DEFAULT '' COMMENT '退服时间',
  `open_time` varchar(15) DEFAULT '' COMMENT '开服时间',
  `is_end` tinyint(1) DEFAULT '0' COMMENT '是否抛服 0：否 1：是',
  `status` tinyint(5) DEFAULT '0' COMMENT '分配状态 0：未进驻 1：进驻中 2:已抛服 3：已退服',
  PRIMARY KEY (`id`),
  UNIQUE KEY `p_g_s` (`garrison_product_id`,`game_id`,`server_id`),
  KEY `admin_name` (`admin_name`),
  KEY `product_id_platform_id` (`product_id`,`platform_id`),
  KEY `game_id` (`game_id`)
) ENGINE=InnoDB AUTO_INCREMENT=59713 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='产品分配区服信息表(子游戏维度)';

-- ----------------------------
-- Table structure for distributional_server
-- ----------------------------
DROP TABLE IF EXISTS `distributional_server`;
CREATE TABLE `distributional_server` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `garrison_product_id` int(11) DEFAULT NULL COMMENT '进驻产品id',
  `product_id` int(11) DEFAULT '0' COMMENT '产品id',
  `garrison_product_name` varchar(120) DEFAULT '' COMMENT '进驻gs的产品名称',
  `platform_id` tinyint(4) DEFAULT '0' COMMENT '平台id',
  `server_id` varchar(120) DEFAULT '' COMMENT '区服id',
  `server_name` varchar(120) DEFAULT '' COMMENT '区服名称',
  `role_id` varchar(120) DEFAULT '' COMMENT '游戏角色id',
  `role_name` varchar(120) DEFAULT '' COMMENT '游戏角色名',
  `admin_id` int(11) DEFAULT '0' COMMENT '负责人id',
  `admin_name` varchar(120) DEFAULT '' COMMENT '负责人名称',
  `add_time` varchar(15) DEFAULT '' COMMENT '添加时间',
  `start_time` varchar(15) DEFAULT '' COMMENT '进驻时间',
  `end_time` varchar(15) DEFAULT '' COMMENT '退服时间',
  `open_time` varchar(15) DEFAULT '' COMMENT '开服时间',
  `is_end` tinyint(1) DEFAULT '0' COMMENT '是否抛服 0：否 1：是',
  `status` tinyint(5) DEFAULT '0' COMMENT '分配状态 0：未进驻 1：进驻中 2:已抛服 3：已退服',
  PRIMARY KEY (`id`),
  UNIQUE KEY `p_s` (`garrison_product_id`,`server_id`),
  KEY `admin_name` (`admin_name`),
  KEY `product_id_platform_id` (`product_id`,`platform_id`)
) ENGINE=InnoDB AUTO_INCREMENT=33312 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='产品分配区服信息表';

-- ----------------------------
-- Table structure for every_day_order_count
-- ----------------------------
DROP TABLE IF EXISTS `every_day_order_count`;
CREATE TABLE `every_day_order_count` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `date` date NOT NULL COMMENT '统计日期',
  `uid` varchar(32) NOT NULL COMMENT '平台用户id',
  `platform_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '平台id(0:默认，没有对应的平台 1：mh 2:ll 3:bx 4:zw)',
  `role_id` varchar(64) NOT NULL COMMENT '最后充值游戏角色id',
  `role_name` varchar(32) NOT NULL COMMENT '最后充值的角色名称',
  `game_id` int(11) NOT NULL DEFAULT '0' COMMENT '平台游戏id',
  `game_name` varchar(64) NOT NULL DEFAULT '' COMMENT '充值的游戏名称',
  `server_id` varchar(16) NOT NULL DEFAULT '0' COMMENT '区服id',
  `server_name` varchar(16) NOT NULL DEFAULT '' COMMENT '最后充值所在的游戏区服名称',
  `channel_id` int(11) DEFAULT '0' COMMENT '平台分包id',
  `amount_count` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '累计充值金额',
  `day_hign_pay` decimal(10,2) NOT NULL COMMENT '单日最高充值',
  `pay_time` int(11) NOT NULL COMMENT '最后支付时间',
  `order_count` smallint(6) NOT NULL DEFAULT '0' COMMENT '订单总次数',
  `big_order_count` smallint(6) NOT NULL DEFAULT '0' COMMENT '大单总次数',
  `big_order_sum` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '大单充值总数',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `p_u` varchar(100) DEFAULT NULL COMMENT 'platform_id和uid组成的字符串（方便查询）',
  `p_g` varchar(25) DEFAULT NULL COMMENT 'platform_id和game_id组合的字符串（方便查询使用索引）',
  `p_g_s` varchar(100) DEFAULT NULL COMMENT 'platform_id和game_id和sever_id组合成的字符串（方便查询使用索引）',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`platform_id`,`uid`,`game_id`,`server_id`,`channel_id`,`date`),
  KEY `date` (`date`),
  KEY `one_uid` (`uid`),
  KEY `p_u` (`p_u`),
  KEY `platform_id` (`platform_id`),
  KEY `p_g` (`p_g`),
  KEY `p_g_s` (`p_g_s`),
  KEY `pgs` (`platform_id`,`game_id`,`server_id`),
  KEY `add_time` (`add_time`),
  KEY `game_id` (`game_id`)
) ENGINE=InnoDB AUTO_INCREMENT=73997087 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='每天每个用户的充值总计';

-- ----------------------------
-- Table structure for every_day_role_amount_statistics
-- ----------------------------
DROP TABLE IF EXISTS `every_day_role_amount_statistics`;
CREATE TABLE `every_day_role_amount_statistics` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL COMMENT '日期',
  `uid` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT '0' COMMENT '产品id',
  `platform_id` tinyint(3) DEFAULT '0' COMMENT '平台id',
  `role_id` varchar(100) DEFAULT '' COMMENT '角色id',
  `role_name` varchar(100) DEFAULT '' COMMENT '角色名',
  `game_id` int(15) DEFAULT '0' COMMENT '游戏id',
  `server_id` varchar(100) DEFAULT '' COMMENT '区服id',
  `server_name` varchar(120) DEFAULT '' COMMENT '区服名',
  `amount` decimal(10,2) DEFAULT '0.00' COMMENT '金额',
  `orders` int(11) DEFAULT '0' COMMENT '订单数',
  `add_time` varchar(15) DEFAULT '' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `r_g_d` (`role_id`,`game_id`,`date`),
  KEY `date` (`date`),
  KEY `uid` (`uid`),
  KEY `platformid_productid` (`platform_id`,`product_id`),
  KEY `server_id` (`server_id`),
  KEY `add_time` (`add_time`)
) ENGINE=InnoDB AUTO_INCREMENT=32029886 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for game_product
-- ----------------------------
DROP TABLE IF EXISTS `game_product`;
CREATE TABLE `game_product` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `platform_id` smallint(6) NOT NULL COMMENT '平台id',
  `product_id` int(8) NOT NULL COMMENT '游戏产品id（游戏大类）',
  `product_name` varchar(25) NOT NULL COMMENT 'vip游戏产品名称',
  `up_id` int(11) DEFAULT '0' COMMENT '唯一产品id',
  `up_name` varchar(30) DEFAULT '' COMMENT '唯一产品名称',
  `customer_product_code` varchar(255) DEFAULT '' COMMENT '客服产品标识',
  `static` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态（1：默认 开启 2：关闭）',
  `add_time` int(11) NOT NULL COMMENT '添加时间',
  `edit_time` int(11) NOT NULL COMMENT '修改时间',
  `add_admin_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '添加者后台用户id',
  `edit_admin_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '编辑者后台用户id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unpfpd` (`platform_id`,`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14739717 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='游戏产品(游戏大类)表';

-- ----------------------------
-- Table structure for game_server_statistic
-- ----------------------------
DROP TABLE IF EXISTS `game_server_statistic`;
CREATE TABLE `game_server_statistic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `game_id` int(11) DEFAULT '0' COMMENT '游戏id',
  `center_product_id` int(11) DEFAULT '0' COMMENT '中心游戏产品id',
  `product_id` int(11) DEFAULT '0' COMMENT '产品id',
  `product_name` varchar(200) DEFAULT '' COMMENT '产品名',
  `server_name` varchar(200) DEFAULT '' COMMENT '区服名',
  `server_id` varchar(120) DEFAULT '' COMMENT '区服id',
  `count_money` decimal(10,2) DEFAULT NULL,
  `reg_num` int(11) unsigned DEFAULT NULL,
  `role_reg_num` int(11) DEFAULT '0' COMMENT '角色注册数',
  `add_time` varchar(15) DEFAULT '' COMMENT '数据添加时间',
  `platform_id` tinyint(2) DEFAULT '0' COMMENT '平台id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `d_g_p_p_s` (`date`,`game_id`,`product_id`,`platform_id`,`server_id`),
  KEY `date` (`date`),
  KEY `product_id` (`product_id`),
  KEY `platform_id` (`platform_id`),
  KEY `server_id` (`server_id`),
  KEY `add_time` (`add_time`),
  KEY `game_id` (`game_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5985247 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='区服统计(子游戏维度)';

-- ----------------------------
-- Table structure for garrison_product
-- ----------------------------
DROP TABLE IF EXISTS `garrison_product`;
CREATE TABLE `garrison_product` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) DEFAULT '0' COMMENT '产品id',
  `product_name` varchar(255) DEFAULT '' COMMENT '产品名称',
  `platform_id` tinyint(3) unsigned DEFAULT '0' COMMENT '平台id',
  `admin_id` int(11) DEFAULT '0' COMMENT '操作人',
  `admin_name` varchar(100) DEFAULT '' COMMENT '操作人',
  `update_time` int(11) DEFAULT '0' COMMENT '最后操作时间',
  `add_time` int(11) DEFAULT '0' COMMENT '首次添加时间',
  `garrison_period` tinyint(1) DEFAULT '0' COMMENT '进驻周期（天）',
  `status` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='gs进驻产品表';

-- ----------------------------
-- Table structure for inner_account
-- ----------------------------
DROP TABLE IF EXISTS `inner_account`;
CREATE TABLE `inner_account` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_department_id` smallint(4) DEFAULT '0' COMMENT '负责部门id',
  `uid` varchar(200) DEFAULT '' COMMENT '账号',
  `admin_id` int(5) DEFAULT '0' COMMENT '负责人',
  `status` tinyint(1) DEFAULT '1' COMMENT '账号状态',
  `platform_id` tinyint(3) DEFAULT '0' COMMENT '账号平台id',
  `reg_ip` varchar(20) DEFAULT '' COMMENT '注册ip',
  `reg_equipment` varchar(150) DEFAULT '' COMMENT '注册设备号',
  `often_login_ip` text COMMENT '常登陆ip',
  `often_login_equipment` text COMMENT '常登陆设备',
  `login_ip` varchar(20) DEFAULT '' COMMENT '当前登陆ip',
  `login_equipment` varchar(150) DEFAULT '',
  `login_time` bigint(12) DEFAULT '0' COMMENT '最后登陆时间',
  `add_time` varchar(20) DEFAULT '' COMMENT '首次添加时间',
  `remarks` varchar(255) DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for kefu_user_recharge
-- ----------------------------
DROP TABLE IF EXISTS `kefu_user_recharge`;
CREATE TABLE `kefu_user_recharge` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `platform_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '平台id',
  `uid` varchar(64) NOT NULL COMMENT '平台用户id',
  `user_name` varchar(50) NOT NULL COMMENT '用户名',
  `mobile` varchar(25) NOT NULL DEFAULT '' COMMENT '绑定的手机号码',
  `game_id` int(10) NOT NULL DEFAULT '0' COMMENT '注册账号的游戏ID',
  `server_id` varchar(25) NOT NULL DEFAULT '0' COMMENT '最近充值区服ID',
  `server_name` varchar(64) NOT NULL COMMENT '最近充值的服务器名称',
  `last_pay_game_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户最近充值的游戏id',
  `role_id` varchar(32) NOT NULL DEFAULT '' COMMENT '最后充值时的角色ID',
  `role_name` varchar(64) NOT NULL COMMENT '最近充值的角色名称',
  `reg_ip` varchar(32) NOT NULL COMMENT '注册ip',
  `reg_time` int(11) NOT NULL DEFAULT '0' COMMENT '注册时间',
  `day_hign_pay` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '单日最高充值',
  `single_hign_pay` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '单次最高充值',
  `total_pay` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '累计充值',
  `last_day_pay` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '最近一天的充值',
  `thirty_day_pay` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '近30天累充',
  `is_vip` int(11) NOT NULL DEFAULT '0' COMMENT '是否是VIP(0否 1是)',
  `last_pay_time` int(11) NOT NULL COMMENT '最后一次充值时间',
  `total_time` int(11) NOT NULL COMMENT '更新统计时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `vip_time` int(11) NOT NULL DEFAULT '0' COMMENT '达成vip时间',
  `last_login_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `month_pay` decimal(10,2) NOT NULL DEFAULT '0.00',
  `role_level` smallint(5) NOT NULL DEFAULT '0',
  `role_zs_level` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `pu` (`platform_id`,`uid`),
  KEY `uid` (`uid`),
  KEY `user_name` (`user_name`),
  KEY `is_vip` (`is_vip`),
  KEY `reg_time` (`reg_time`),
  KEY `plg` (`platform_id`,`last_pay_game_id`),
  KEY `last_login_time` (`last_login_time`) USING BTREE,
  KEY `last_pay_time` (`last_pay_time`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=276294259 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='客服用户充值表';

-- ----------------------------
-- Table structure for platform_channel_info
-- ----------------------------
DROP TABLE IF EXISTS `platform_channel_info`;
CREATE TABLE `platform_channel_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自动增长',
  `channel_id` int(11) NOT NULL DEFAULT '0' COMMENT '添加用户id(0：默认 程序自动添加)',
  `channel_name` varchar(255) NOT NULL COMMENT '添加时间',
  `channel_ver` varchar(255) NOT NULL DEFAULT '0' COMMENT '修改用户id',
  `platform_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '平台id',
  `game_id` int(8) NOT NULL COMMENT '平台游戏id',
  `game_name` varchar(25) NOT NULL COMMENT '平台游戏名称',
  `product_id` int(10) NOT NULL DEFAULT '0' COMMENT '游戏产品(大类)id',
  `product_name` varchar(25) DEFAULT '' COMMENT '游戏产品名称',
  `edit_time` int(11) DEFAULT '0' COMMENT '修改时间',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '状态（1：默认 开启  2：关闭 -1：弃用）',
  PRIMARY KEY (`id`),
  UNIQUE KEY `channel_id` (`platform_id`,`channel_id`)
) ENGINE=InnoDB AUTO_INCREMENT=240207 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='平台包号列表';

-- ----------------------------
-- Table structure for platform_game_info
-- ----------------------------
DROP TABLE IF EXISTS `platform_game_info`;
CREATE TABLE `platform_game_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自动增长',
  `platform_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '平台id',
  `game_id` int(8) NOT NULL COMMENT '平台游戏id',
  `game_name` varchar(25) NOT NULL COMMENT '平台游戏名称',
  `product_id` int(10) NOT NULL DEFAULT '0' COMMENT '游戏产品(大类)id',
  `product_name` varchar(25) DEFAULT '' COMMENT '游戏产品名称',
  `add_admin_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '添加用户id(0：默认 程序自动添加)',
  `add_time` int(11) NOT NULL COMMENT '添加时间',
  `edit_admin_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '修改用户id',
  `edit_time` int(11) DEFAULT '0' COMMENT '修改时间',
  `static` tinyint(2) NOT NULL DEFAULT '1' COMMENT '状态（1：默认 开启  2：关闭 -1：弃用）',
  PRIMARY KEY (`id`),
  UNIQUE KEY `game` (`platform_id`,`game_id`)
) ENGINE=InnoDB AUTO_INCREMENT=94894796 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='平台游戏列表';

-- ----------------------------
-- Table structure for platform_payment_info
-- ----------------------------
DROP TABLE IF EXISTS `platform_payment_info`;
CREATE TABLE `platform_payment_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_id` int(11) DEFAULT '0',
  `payment_name` varchar(255) DEFAULT '' COMMENT '支付方式名称',
  `platform_id` tinyint(5) DEFAULT NULL,
  `add_time` varchar(15) DEFAULT '' COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for product_prop_config
-- ----------------------------
DROP TABLE IF EXISTS `product_prop_config`;
CREATE TABLE `product_prop_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `product_name` varchar(64) NOT NULL DEFAULT '' COMMENT '产品游戏名称',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '返利标题',
  `title_md5` varchar(32) NOT NULL DEFAULT '' COMMENT '标题md5加密',
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '返利内容',
  `prop_id` varchar(32) NOT NULL DEFAULT '0' COMMENT '道具id',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '状态（默认：1：开启  2：关闭）',
  `add_admin_id` int(6) NOT NULL DEFAULT '0' COMMENT '添加人id',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `edit_admin_id` int(6) NOT NULL DEFAULT '0' COMMENT '修改人id',
  `edit_time` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='vip返利道具配置表';

-- ----------------------------
-- Table structure for pro_server_statistic_day
-- ----------------------------
DROP TABLE IF EXISTS `pro_server_statistic_day`;
CREATE TABLE `pro_server_statistic_day` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `reg_date` date DEFAULT '0000-00-00' COMMENT '首登日期',
  `platform_id` int(11) DEFAULT '0' COMMENT '产品id',
  `gid` int(11) DEFAULT '0' COMMENT '游戏id',
  `server_id` varchar(50) DEFAULT '' COMMENT '首登推广包号',
  `pay_date` date DEFAULT '0000-00-00' COMMENT '付款日期',
  `days` mediumint(9) DEFAULT '0' COMMENT '从0开始，0为第一天充值，即当天充值',
  `pay_money` decimal(10,2) DEFAULT '0.00' COMMENT '总充值金额',
  `pay_people` int(11) DEFAULT '0' COMMENT '总充值唯一人数',
  `pay_num` int(11) DEFAULT '0' COMMENT '总充值次数',
  `new_pay_money` decimal(10,2) DEFAULT '0.00' COMMENT '新用户充值金额',
  `new_pay_people` int(11) DEFAULT '0' COMMENT '新用户充值唯一人数',
  `new_pay_num` int(11) DEFAULT '0' COMMENT '新用户充值次数',
  `add_time` int(11) DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `reg` (`reg_date`,`platform_id`,`gid`,`server_id`,`pay_date`),
  KEY `pay_date` (`pay_date`)
) ENGINE=InnoDB AUTO_INCREMENT=5390187 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='产品区服ltv统计';

-- ----------------------------
-- Table structure for seller_commission_config
-- ----------------------------
DROP TABLE IF EXISTS `seller_commission_config`;
CREATE TABLE `seller_commission_config` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `game_product_id` int(11) NOT NULL DEFAULT '0' COMMENT '游戏产品id',
  `money_min` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额下限',
  `money_max` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额上限',
  `commission_rate` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '提成比例',
  `commission_num` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '提成值',
  `start_time` int(10) NOT NULL DEFAULT '0' COMMENT '执行时间',
  `end_time` int(10) NOT NULL DEFAULT '0' COMMENT '结束时间',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '状态（1：默认 开启  2：关闭）',
  `add_admin_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '添加用户id',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `edit_admin_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '修改编辑用户id',
  `edit_time` int(11) NOT NULL DEFAULT '0' COMMENT '修改编辑时间',
  `desc` varchar(255) DEFAULT '' COMMENT '备注',
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除 0 否 1 是',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for seller_kpi_config
-- ----------------------------
DROP TABLE IF EXISTS `seller_kpi_config`;
CREATE TABLE `seller_kpi_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `group_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '用户组id',
  `admin_id` int(11) NOT NULL COMMENT '专员用户id(后台用户id)',
  `kpi_date` int(11) NOT NULL COMMENT 'kpi月份',
  `kpi_value` decimal(10,2) NOT NULL COMMENT 'kpi值',
  `game_product_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '游戏产品id',
  `status` tinyint(2) DEFAULT '1' COMMENT '状态（1：默认 开启  2：关闭）',
  `add_admin_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '添加用户id',
  `add_time` int(11) NOT NULL COMMENT '添加时间',
  `edit_admin_id` smallint(6) DEFAULT NULL COMMENT '修改编辑用户id',
  `edit_time` int(11) DEFAULT NULL COMMENT '修改编辑时间',
  `product_id` int(10) DEFAULT '0',
  `platform_id` smallint(5) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ppad` (`product_id`,`admin_id`,`kpi_date`,`platform_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1259 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='vip专员kpi考核配置表';

-- ----------------------------
-- Table structure for sell_statistic
-- ----------------------------
DROP TABLE IF EXISTS `sell_statistic`;
CREATE TABLE `sell_statistic` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `day_num` int(10) DEFAULT '0' COMMENT '每天凌晨时间戳',
  `product_id` int(10) DEFAULT '0' COMMENT '游戏产品id',
  `total_money` decimal(10,2) DEFAULT '0.00' COMMENT '总金额',
  `big_order_count` smallint(6) DEFAULT '0' COMMENT '大订单数量',
  `big_order_total` decimal(10,2) DEFAULT '0.00' COMMENT '大订单总金额',
  `order_count` smallint(6) DEFAULT '0' COMMENT '订单总数',
  `admin_id` int(10) DEFAULT '0' COMMENT '后台账号id',
  `add_time` int(10) DEFAULT '0' COMMENT '添加时间',
  `update_time` int(10) DEFAULT '0' COMMENT '更新时间',
  `platform_id` smallint(10) DEFAULT '0' COMMENT '平台id(0:默认，没有对应的平台 1：mh 2:ll 3:bx 4:zw)',
  `game_id` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  KEY `product_id` (`product_id`,`platform_id`),
  KEY `day_num` (`day_num`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='营销每月游戏数据统计表';

-- ----------------------------
-- Table structure for sell_work_order
-- ----------------------------
DROP TABLE IF EXISTS `sell_work_order`;
CREATE TABLE `sell_work_order` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `type` tinyint(2) DEFAULT '0' COMMENT '工单类型  0 未知 1登记 2销售 3维护 4流失召回',
  `sell_type` tinyint(2) DEFAULT '0' COMMENT '销售类型 0 未知 1普通 2召回',
  `sell_amount` decimal(10,2) DEFAULT '0.00' COMMENT '销售金额',
  `is_first` tinyint(3) DEFAULT '0' COMMENT '是否首次提销售工单',
  `pay_time` int(10) DEFAULT NULL,
  `recall_account` varchar(50) DEFAULT NULL COMMENT '召回账号',
  `recall_time` int(10) DEFAULT '0' COMMENT '召回实践',
  `product_id` int(10) DEFAULT '0' COMMENT '游戏大类id',
  `game_id` int(10) DEFAULT '0' COMMENT '子游戏id',
  `server_id` varchar(20) DEFAULT NULL,
  `kf_id` int(10) DEFAULT '0' COMMENT 'VIP专员id',
  `status` tinyint(2) DEFAULT '0' COMMENT '审核状态 -2二审拒绝 -1审核拒绝 0待审核  1初审通过 2完成（二审抽查）',
  `uid` int(10) DEFAULT '0' COMMENT '玩家uid(uid+platform_id 确定唯一)',
  `platform_id` smallint(6) DEFAULT '0' COMMENT '平台id',
  `contact_type` tinyint(2) DEFAULT '0' COMMENT '0 未知 1QQ 2电话 3微信',
  `contact_way` varchar(40) DEFAULT NULL COMMENT '联系方式',
  `content` text COMMENT '工单内容',
  `qc_first_admin_id` int(10) DEFAULT '0' COMMENT '一审人员',
  `qc_first_time` int(10) DEFAULT '0' COMMENT '一审时间',
  `qc_second_admin_id` int(10) DEFAULT '0' COMMENT '二审人员',
  `qc_second_time` int(10) DEFAULT '0' COMMENT '二审时间',
  `is_del` tinyint(4) DEFAULT '0' COMMENT '是否删除 0正常 1删除',
  `add_time` int(10) DEFAULT '0' COMMENT '添加时间',
  `update_time` int(10) DEFAULT NULL,
  `update_admin_id` int(10) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `p_u` varchar(50) DEFAULT NULL,
  `p_p` varchar(50) DEFAULT NULL,
  `chum_reason` varchar(255) DEFAULT NULL,
  `order_id_str` text,
  `pay_time_first_day` int(10) DEFAULT '0' COMMENT '订单区间第一天（零点）',
  `pay_time_last_day` int(10) DEFAULT '0' COMMENT '订单区间最后一天（23:59:59 +1）',
  PRIMARY KEY (`id`),
  KEY `p_p` (`p_p`),
  KEY `uid` (`uid`),
  KEY `u_p_id` (`uid`,`platform_id`),
  KEY `p_u` (`p_u`),
  KEY `platform_id` (`platform_id`),
  KEY `kf_id` (`kf_id`),
  KEY `game_id` (`game_id`)
) ENGINE=InnoDB AUTO_INCREMENT=275263 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for sell_work_order_pay_order
-- ----------------------------
DROP TABLE IF EXISTS `sell_work_order_pay_order`;
CREATE TABLE `sell_work_order_pay_order` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `platform_id` int(10) DEFAULT '0' COMMENT '平台id',
  `work_order_id` int(10) DEFAULT '0' COMMENT '工单id',
  `kpo_id` varchar(100) DEFAULT '0' COMMENT '订单id',
  `add_time` int(10) DEFAULT '0' COMMENT '添加时间',
  `pay_time` int(10) DEFAULT '0',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '充值金额',
  `game_id` int(11) NOT NULL DEFAULT '0' COMMENT '所属游戏ID',
  PRIMARY KEY (`id`),
  KEY `work_order_id` (`work_order_id`),
  KEY `platform_id` (`platform_id`,`kpo_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1208743 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for sell_work_order_statistic_game_month
-- ----------------------------
DROP TABLE IF EXISTS `sell_work_order_statistic_game_month`;
CREATE TABLE `sell_work_order_statistic_game_month` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `date` int(10) DEFAULT '0' COMMENT '日期(月)',
  `admin_id` int(10) DEFAULT '0',
  `admin_name` varchar(100) DEFAULT '' COMMENT '专员',
  `p_p_g` varchar(30) DEFAULT '' COMMENT '平台_产品id_游戏联合id(platform-id_product-id_game_id)',
  `p_p` varchar(30) DEFAULT '' COMMENT '平台-产品',
  `p_g` varchar(30) DEFAULT '' COMMENT '平台_游戏联合id(platform-id_game_id)',
  `platform_id` tinyint(1) unsigned DEFAULT '0' COMMENT '平台id',
  `product_id` tinyint(1) unsigned DEFAULT '0' COMMENT '产品id',
  `product_name` varchar(100) DEFAULT '' COMMENT '产品名称',
  `group_id` tinyint(6) DEFAULT '0' COMMENT '分组id',
  `game_id` int(11) DEFAULT '0' COMMENT '游戏id',
  `game_name` varchar(100) DEFAULT '' COMMENT '游戏名',
  `big_order_count` smallint(6) DEFAULT '0' COMMENT '大单数量',
  `big_order_sum` decimal(10,2) DEFAULT '0.00' COMMENT '大单充值数',
  `maintenance_all` int(10) DEFAULT '0' COMMENT '活跃人数',
  `maintenance_count` int(10) DEFAULT '0' COMMENT '活跃用户维护工单数',
  `month_uphold_user_count` int(10) DEFAULT '0' COMMENT '月维护用户数',
  `month_uphold_user_amount` decimal(10,0) DEFAULT '0' COMMENT '月应维护金额',
  `month_sell_work_uphold_user_count` int(10) DEFAULT NULL COMMENT '月工单维护人数',
  `month_sell_work_uphold_user_amount` decimal(10,0) DEFAULT NULL COMMENT '月工单维护金额',
  `all_uphold_user_count` int(10) DEFAULT '0' COMMENT '总维护用户数',
  `month_new_contact_count` int(11) DEFAULT '0' COMMENT '月新建联维护人数',
  `month_new_contact_amount` decimal(10,0) DEFAULT '0' COMMENT '月新建联维护金额',
  `month_new_distribution_contact_count` int(11) DEFAULT '0' COMMENT '月新分配建联人数',
  `month_new_distribution_contact_amount` decimal(10,0) DEFAULT '0' COMMENT '月新分配建联金额',
  `order_count` int(10) DEFAULT '0' COMMENT '订单数',
  `order_sum` decimal(10,2) DEFAULT '0.00' COMMENT '订单充值数',
  `remark_count` int(10) DEFAULT '0' COMMENT '当月分配用户登记工单数',
  `sell_commission` decimal(10,2) DEFAULT '0.00' COMMENT '提成',
  `sum_kpi_value` decimal(10,2) DEFAULT '0.00' COMMENT '月总kpi',
  `type` tinyint(1) DEFAULT '1' COMMENT '1 游戏',
  PRIMARY KEY (`id`),
  UNIQUE KEY `kapt` (`date`,`admin_id`,`p_p_g`,`type`),
  KEY `admin_id` (`admin_id`),
  KEY `p_g` (`p_g`),
  KEY `p_p_g` (`p_p_g`)
) ENGINE=InnoDB AUTO_INCREMENT=107823 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for sell_work_order_statistic_month
-- ----------------------------
DROP TABLE IF EXISTS `sell_work_order_statistic_month`;
CREATE TABLE `sell_work_order_statistic_month` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `kpi_date` int(10) DEFAULT '0' COMMENT '月份',
  `admin_id` int(10) DEFAULT '0',
  `p_p` varchar(30) DEFAULT NULL COMMENT '平台_游戏联合id(platform-id_product-id)',
  `platform_id` varchar(30) DEFAULT '0' COMMENT '平台id',
  `product_id` varchar(50) DEFAULT '0' COMMENT '产品id',
  `group_id` tinyint(6) DEFAULT '0' COMMENT '分组id',
  `big_order_count` smallint(6) DEFAULT '0' COMMENT '大单数量',
  `big_order_sum` decimal(10,2) DEFAULT '0.00' COMMENT '大单充值数',
  `maintenance_all` int(10) DEFAULT '0' COMMENT '活跃人数',
  `maintenance_count` int(10) DEFAULT '0' COMMENT '活跃用户维护工单数',
  `month_user_all` int(10) DEFAULT '0' COMMENT '当月分配用户总数数（废弃）',
  `month_user_count` int(10) DEFAULT '0' COMMENT '维护用户数',
  `order_count` int(10) DEFAULT '0' COMMENT '订单数',
  `order_sum` decimal(10,2) DEFAULT '0.00' COMMENT '订单充值数',
  `remark_all` int(10) DEFAULT '0' COMMENT '当月分配用户总数数（废弃）',
  `remark_count` int(10) DEFAULT '0' COMMENT '当月分配用户登记工单数',
  `sell_commission` decimal(10,2) DEFAULT '0.00' COMMENT '提成',
  `sum_kpi_value` decimal(10,2) DEFAULT '0.00' COMMENT '月总kpi',
  `type` tinyint(1) DEFAULT '0' COMMENT '1 总 2 游戏',
  PRIMARY KEY (`id`),
  UNIQUE KEY `kapt` (`kpi_date`,`admin_id`,`p_p`,`type`),
  KEY `p_p` (`p_p`),
  KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1357 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for server_statistic
-- ----------------------------
DROP TABLE IF EXISTS `server_statistic`;
CREATE TABLE `server_statistic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `center_product_id` int(11) DEFAULT '0' COMMENT '中心游戏产品id',
  `product_id` int(11) DEFAULT '0' COMMENT '产品id',
  `product_name` varchar(200) DEFAULT '' COMMENT '产品名',
  `server_name` varchar(200) DEFAULT '' COMMENT '区服名',
  `server_id` varchar(120) DEFAULT '' COMMENT '区服id',
  `count_money` decimal(10,2) DEFAULT NULL,
  `reg_num` int(11) unsigned DEFAULT NULL,
  `add_time` varchar(15) DEFAULT '' COMMENT '数据添加时间',
  `platform_id` tinyint(2) DEFAULT '0' COMMENT '平台id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `d_p_p_s` (`date`,`product_id`,`platform_id`,`server_id`),
  KEY `date` (`date`),
  KEY `product_id` (`product_id`),
  KEY `platform_id` (`platform_id`),
  KEY `server_id` (`server_id`),
  KEY `add_time` (`add_time`)
) ENGINE=InnoDB AUTO_INCREMENT=6752281 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for transfer_accounts
-- ----------------------------
DROP TABLE IF EXISTS `transfer_accounts`;
CREATE TABLE `transfer_accounts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `platform_id` tinyint(3) DEFAULT NULL,
  `game_id` int(11) DEFAULT NULL,
  `game_name` varchar(255) DEFAULT '' COMMENT '游戏名',
  `uid` int(11) DEFAULT NULL,
  `uname` varchar(120) DEFAULT '' COMMENT '账户名',
  `server_id` varchar(100) DEFAULT '' COMMENT '区服id',
  `server_name` varchar(255) DEFAULT '' COMMENT '区服名称',
  `role_id` varchar(120) DEFAULT '' COMMENT '角色id',
  `role_name` varchar(120) DEFAULT '' COMMENT '角色名',
  `payment` varchar(120) DEFAULT '' COMMENT '支付方式',
  `remarks` varchar(255) DEFAULT '' COMMENT '备注',
  `main_body` tinyint(5) DEFAULT '0' COMMENT '支付主体',
  `img` varchar(255) DEFAULT '' COMMENT '截图',
  `total_money` decimal(10,2) DEFAULT NULL,
  `add_time` varchar(13) DEFAULT '0' COMMENT '添加时间',
  `check_admin` varchar(50) DEFAULT '' COMMENT '审核人',
  `check_time` varchar(13) DEFAULT '' COMMENT '审核时间',
  `admin` varchar(50) DEFAULT '' COMMENT '申请人',
  `update_admin` varchar(50) DEFAULT '' COMMENT '最后编辑人',
  `update_time` varchar(13) DEFAULT '' COMMENT '修改时间',
  `apply_status` tinyint(5) DEFAULT '0' COMMENT '人工申请状态',
  `provide_status` tinyint(5) DEFAULT '0' COMMENT '发放状态',
  PRIMARY KEY (`id`),
  KEY `role_name` (`role_name`),
  KEY `uname` (`uname`),
  KEY `role_id` (`role_id`),
  KEY `platform_id` (`platform_id`),
  KEY `server_id` (`server_id`),
  KEY `add_time` (`add_time`),
  KEY `game_id` (`game_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for transfer_accounts_orders
-- ----------------------------
DROP TABLE IF EXISTS `transfer_accounts_orders`;
CREATE TABLE `transfer_accounts_orders` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `transfer_account_id` int(11) DEFAULT NULL COMMENT '工单id',
  `order_id` varchar(255) DEFAULT '' COMMENT '订单id',
  `cp_order_id` varchar(255) DEFAULT '' COMMENT 'cp订单号',
  `money` decimal(10,2) DEFAULT NULL,
  `add_time` bigint(12) DEFAULT '0' COMMENT '添加时间',
  `pay_time` bigint(12) DEFAULT '0' COMMENT '成功下单时间',
  `call_back` text,
  `status` tinyint(5) DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`(191)),
  KEY `add_time` (`add_time`),
  KEY `transfer_account_id` (`transfer_account_id`),
  KEY `pay_time` (`pay_time`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for up_game_product
-- ----------------------------
DROP TABLE IF EXISTS `up_game_product`;
CREATE TABLE `up_game_product` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `platform_id` smallint(6) NOT NULL COMMENT '平台id',
  `up_id` int(8) NOT NULL COMMENT '游戏产品id（游戏大类）',
  `up_name` varchar(25) NOT NULL COMMENT 'vip游戏产品名称',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态（1：默认 开启 2：关闭）',
  `add_time` int(11) NOT NULL COMMENT '添加时间',
  `edit_time` int(11) NOT NULL COMMENT '修改时间',
  `add_admin_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '添加者后台用户id',
  `edit_admin_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '编辑者后台用户id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unpfpd` (`platform_id`,`up_id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='游戏唯一产品表';

-- ----------------------------
-- Table structure for user_privacy_info
-- ----------------------------
DROP TABLE IF EXISTS `user_privacy_info`;
CREATE TABLE `user_privacy_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `platform_id` tinyint(2) NOT NULL DEFAULT '0' COMMENT '平台id（1：流连 2：茂宏 4、掌玩 5、蛟龙）',
  `uid` varchar(64) NOT NULL DEFAULT '' COMMENT '平台用户id',
  `game_id` int(8) NOT NULL DEFAULT '0' COMMENT '子游戏id',
  `product_id` int(6) NOT NULL DEFAULT '0' COMMENT '产品id',
  `server_id` varchar(24) NOT NULL DEFAULT '0' COMMENT '区服id',
  `server_name` varchar(64) DEFAULT '' COMMENT '区服名称',
  `role_id` varchar(64) DEFAULT '0' COMMENT '角色id',
  `role_name` varchar(64) DEFAULT '' COMMENT '昵称',
  `qq` varchar(64) DEFAULT '0' COMMENT 'qq号',
  `qq_question_answer` varchar(255) DEFAULT '' COMMENT 'qq验证问题答案',
  `phone_num` varchar(11) DEFAULT '0',
  `birthday` int(10) DEFAULT '0' COMMENT '生日',
  `wechat` varchar(64) DEFAULT '0' COMMENT '微信号',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '处理状态（默认 0：未处理 1：已添加待用户通过 2：添加成功 3：添加失败）',
  `remarks` text COMMENT '备注',
  `add_admin_id` int(6) NOT NULL DEFAULT '-1' COMMENT '添加用户id（默认 -1：客服端入口用户自动录入）',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `handler_id` int(6) NOT NULL DEFAULT '0' COMMENT '处理人id',
  `handler_time` int(11) NOT NULL DEFAULT '0' COMMENT '处理时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `pu` (`platform_id`,`uid`),
  KEY `birthday` (`birthday`),
  KEY `phone_num` (`phone_num`)
) ENGINE=InnoDB AUTO_INCREMENT=19433 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='用户录入通讯私人信息';

-- ----------------------------
-- Table structure for vip_ascription_game_server_range
-- ----------------------------
DROP TABLE IF EXISTS `vip_ascription_game_server_range`;
CREATE TABLE `vip_ascription_game_server_range` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `vip_game_product_id` int(11) NOT NULL DEFAULT '0' COMMENT '游戏产品id',
  `start_server_id` varchar(15) NOT NULL DEFAULT '0' COMMENT '游戏区服开始区间',
  `end_server_id` varchar(15) NOT NULL DEFAULT '0' COMMENT '游戏区服结束区间',
  `server_prefix` varchar(15) DEFAULT NULL COMMENT '游戏区服前缀',
  `server_suffix` varchar(15) DEFAULT NULL COMMENT '游戏区服后缀',
  `is_server_remainder` tinyint(2) NOT NULL DEFAULT '0' COMMENT '区服余数(0：默认 全部、没有  1：单数 2:双数)',
  `special_server_id` varchar(64) DEFAULT '0' COMMENT '特殊区服，多个用逗号隔开',
  `remove_server_id` varchar(64) DEFAULT '0' COMMENT '区间内排除掉的区服，多个用逗号隔开',
  `static` tinyint(2) DEFAULT '1' COMMENT '状态 0 1分配中 2分配完成',
  `admin_user_id` int(11) NOT NULL COMMENT 'vip用户专员（后台用户id）',
  `add_admin_id` int(11) NOT NULL COMMENT '添加操作的后台用户id',
  `add_time` int(11) NOT NULL COMMENT '添加时间',
  `edit_admin_id` int(11) DEFAULT NULL COMMENT '修改编辑后台用户id',
  `edit_time` int(11) DEFAULT NULL COMMENT '修改编辑时间',
  `ext` text COMMENT '额外字段',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2367 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='vip专员分配的游戏产品区服区间范围';

-- ----------------------------
-- Table structure for vip_game_product
-- ----------------------------
DROP TABLE IF EXISTS `vip_game_product`;
CREATE TABLE `vip_game_product` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `game_product_name` varchar(25) NOT NULL COMMENT 'vip游戏产品名称',
  `platform_game_list` varchar(255) NOT NULL COMMENT '平台游戏产品列',
  `platform_id` smallint(6) NOT NULL COMMENT '平台id',
  `static` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态（1：默认 开启 2：关闭）',
  `add_time` int(11) NOT NULL COMMENT '添加时间',
  `edit_time` int(11) NOT NULL COMMENT '修改时间',
  `add_admin_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '添加者后台用户id',
  `edit_admin_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '编辑者后台用户id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `platform_id` (`platform_id`,`game_product_name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='vip游戏产品表';

-- ----------------------------
-- Table structure for vip_kf_day_statistic
-- ----------------------------
DROP TABLE IF EXISTS `vip_kf_day_statistic`;
CREATE TABLE `vip_kf_day_statistic` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `day` int(10) NOT NULL DEFAULT '0',
  `admin_id` int(10) NOT NULL DEFAULT '0',
  `group_id` tinyint(3) NOT NULL DEFAULT '0',
  `platform_id` tinyint(3) NOT NULL DEFAULT '0',
  `product_id` int(10) NOT NULL DEFAULT '0',
  `game_id` int(10) NOT NULL DEFAULT '0',
  `p_p` varchar(50) NOT NULL,
  `p_g` varchar(60) NOT NULL,
  `add_user_count` smallint(6) NOT NULL DEFAULT '0' COMMENT '当天新增分配用户',
  `add_user_str` text COMMENT '当天新增分配用户id',
  `add_washed_away` smallint(6) NOT NULL DEFAULT '0' COMMENT '当天、新增流失',
  `add_washed_away_str` text,
  `add_login_lost_count` smallint(6) NOT NULL DEFAULT '0' COMMENT '新增登录流失人数',
  `add_login_lost_str` text COMMENT '新增流失uid',
  `add_user_count_month` smallint(6) NOT NULL DEFAULT '0' COMMENT '当月累计新增',
  `add_user_str_month` text COMMENT '月新增分配用户',
  `add_user_active_month` int(10) NOT NULL DEFAULT '0',
  `add_user_active_str_month` text,
  `add_user_active_amount_month` decimal(10,2) DEFAULT '0.00',
  `add_washed_away_month` smallint(6) NOT NULL DEFAULT '0' COMMENT '当月、新增流失',
  `add_login_lost_month` smallint(6) NOT NULL DEFAULT '0' COMMENT '月新增登录流失',
  `add_user_count_all` int(10) NOT NULL DEFAULT '0' COMMENT '历史累计分配用户',
  `active_count_all` int(10) NOT NULL DEFAULT '0' COMMENT '历史已分配、活跃用户',
  `active_user_str_all` text,
  `washed_away_all` int(10) NOT NULL DEFAULT '0' COMMENT '总流失',
  `login_lost_all` int(10) NOT NULL DEFAULT '0' COMMENT '历史总流失',
  `maintain_user_all` int(10) NOT NULL DEFAULT '0' COMMENT '历史总联系人数',
  `update_time` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `day` (`day`,`admin_id`,`product_id`,`game_id`,`platform_id`),
  KEY `p_p` (`p_p`),
  KEY `admin_id` (`admin_id`),
  KEY `platform_id` (`platform_id`),
  KEY `p_g` (`p_g`)
) ENGINE=InnoDB AUTO_INCREMENT=409806 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for vip_user_info
-- ----------------------------
DROP TABLE IF EXISTS `vip_user_info`;
CREATE TABLE `vip_user_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `platform_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '平台id',
  `uid` varchar(64) NOT NULL COMMENT '平台用户id',
  `user_name` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名',
  `mobile` varchar(25) NOT NULL DEFAULT '' COMMENT '绑定的手机号码',
  `game_id` int(11) NOT NULL DEFAULT '0' COMMENT '注册账号的游戏ID',
  `server_id` varchar(25) NOT NULL DEFAULT '0' COMMENT '最近充值区服ID',
  `server_name` varchar(64) NOT NULL DEFAULT '' COMMENT '最近充值的服务器名称',
  `last_pay_game_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户最近充值的游戏id',
  `role_id` varchar(32) NOT NULL DEFAULT '' COMMENT '最后充值时的角色ID',
  `role_name` varchar(64) NOT NULL DEFAULT '' COMMENT '最近充值的角色名称',
  `reg_ip` varchar(32) NOT NULL DEFAULT '' COMMENT '注册ip',
  `reg_time` int(11) NOT NULL DEFAULT '0' COMMENT '注册时间',
  `day_hign_pay` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '单日最高充值',
  `single_hign_pay` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '单次最高充值',
  `total_pay` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '累计充值',
  `last_day_pay` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '最近一天的充值',
  `thirty_day_pay` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '近30天累充',
  `is_record_static` tinyint(2) NOT NULL DEFAULT '2' COMMENT '是否录入资料（1：是  2：默认 否）',
  `ascription_vip` smallint(6) DEFAULT '0' COMMENT '所属VIP专员',
  `last_pay_time` int(11) NOT NULL COMMENT '最后一次充值时间',
  `p_l_g` varchar(64) DEFAULT NULL COMMENT 'platform_id、last_pay_game_id组成的字符串',
  `p_l_g_s` varchar(64) DEFAULT NULL COMMENT 'platform_id、last_pay_game_id、server_id组成的字符串',
  `total_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新统计时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `last_distribute_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后分配时间',
  `vip_time` int(11) NOT NULL DEFAULT '0' COMMENT '达成vip时间',
  `last_record_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后录单时间',
  `last_login_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `first_distribute_time` int(11) NOT NULL DEFAULT '0' COMMENT '第一次分配时间',
  `remark_time` int(11) NOT NULL DEFAULT '0' COMMENT '客服登记时间',
  `remark_pay` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '维护业绩',
  `month_pay` decimal(10,2) NOT NULL DEFAULT '0.00',
  `role_level` smallint(5) NOT NULL DEFAULT '0',
  `role_zs_level` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `pu` (`platform_id`,`uid`),
  KEY `uid` (`uid`),
  KEY `p_l_g` (`p_l_g`),
  KEY `user_name` (`user_name`),
  KEY `reg_time` (`reg_time`),
  KEY `platform_id` (`platform_id`),
  KEY `p_l_g_s` (`p_l_g_s`),
  KEY `game_id` (`game_id`)
) ENGINE=InnoDB AUTO_INCREMENT=61914733 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='客服vip用户表';

-- ----------------------------
-- Table structure for vip_user_info_copy
-- ----------------------------
DROP TABLE IF EXISTS `vip_user_info_copy`;
CREATE TABLE `vip_user_info_copy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `platform_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '平台id',
  `uid` varchar(64) NOT NULL COMMENT '平台用户id',
  `user_name` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名',
  `mobile` varchar(25) NOT NULL DEFAULT '' COMMENT '绑定的手机号码',
  `game_id` int(11) NOT NULL DEFAULT '0' COMMENT '注册账号的游戏ID',
  `server_id` varchar(25) NOT NULL DEFAULT '0' COMMENT '最近充值区服ID',
  `server_name` varchar(64) NOT NULL DEFAULT '' COMMENT '最近充值的服务器名称',
  `last_pay_game_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户最近充值的游戏id',
  `role_id` varchar(32) NOT NULL DEFAULT '' COMMENT '最后充值时的角色ID',
  `role_name` varchar(64) NOT NULL DEFAULT '' COMMENT '最近充值的角色名称',
  `reg_ip` varchar(32) NOT NULL DEFAULT '' COMMENT '注册ip',
  `reg_time` int(11) NOT NULL DEFAULT '0' COMMENT '注册时间',
  `day_hign_pay` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '单日最高充值',
  `single_hign_pay` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '单次最高充值',
  `total_pay` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '累计充值',
  `last_day_pay` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '最近一天的充值',
  `thirty_day_pay` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '近30天累充',
  `is_record_static` tinyint(2) NOT NULL DEFAULT '2' COMMENT '是否录入资料（1：是  2：默认 否）',
  `ascription_vip` smallint(6) DEFAULT '0' COMMENT '所属VIP专员',
  `last_pay_time` int(11) NOT NULL COMMENT '最后一次充值时间',
  `p_l_g` varchar(64) DEFAULT NULL COMMENT 'platform_id、last_pay_game_id组成的字符串',
  `p_l_g_s` varchar(64) DEFAULT NULL COMMENT 'platform_id、last_pay_game_id、server_id组成的字符串',
  `total_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新统计时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `last_distribute_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后分配时间',
  `vip_time` int(11) NOT NULL DEFAULT '0' COMMENT '达成vip时间',
  `last_record_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后录单时间',
  `last_login_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `first_distribute_time` int(11) NOT NULL DEFAULT '0' COMMENT '第一次分配时间',
  `remark_time` int(11) NOT NULL DEFAULT '0' COMMENT '客服登记时间',
  `remark_pay` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '维护业绩',
  `month_pay` decimal(10,2) NOT NULL DEFAULT '0.00',
  `role_level` smallint(5) NOT NULL DEFAULT '0',
  `role_zs_level` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `pu` (`platform_id`,`uid`),
  KEY `uid` (`uid`),
  KEY `p_l_g` (`p_l_g`),
  KEY `user_name` (`user_name`),
  KEY `reg_time` (`reg_time`),
  KEY `platform_id` (`platform_id`),
  KEY `p_l_g_s` (`p_l_g_s`),
  KEY `game_id` (`game_id`)
) ENGINE=InnoDB AUTO_INCREMENT=52179358 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='客服vip用户表';

-- ----------------------------
-- Table structure for vip_user_other_info
-- ----------------------------
DROP TABLE IF EXISTS `vip_user_other_info`;
CREATE TABLE `vip_user_other_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `uid` varchar(64) NOT NULL COMMENT '平台用户id',
  `user_name` varchar(64) NOT NULL COMMENT '平台用户账号',
  `platform_id` smallint(6) NOT NULL COMMENT '平台id',
  `game_name` varchar(32) DEFAULT NULL COMMENT '所属游戏',
  `product_id` int(10) NOT NULL DEFAULT '0' COMMENT '游戏大类id',
  `sex` tinyint(2) NOT NULL DEFAULT '1' COMMENT '性别（1：默认 男  2：女）',
  `birth_month` tinyint(2) unsigned DEFAULT '0' COMMENT '生日月份',
  `birth_day` tinyint(2) DEFAULT '0' COMMENT '生日日（天）',
  `mobile` varchar(25) NOT NULL DEFAULT '0' COMMENT '手机号码',
  `qq_number` varchar(32) NOT NULL COMMENT 'QQ号码',
  `wechat` varchar(32) NOT NULL DEFAULT '0' COMMENT '微信',
  `pursue_game` varchar(255) DEFAULT NULL COMMENT '追求游戏',
  `remarks` text COMMENT '备注',
  `add_time` int(11) NOT NULL COMMENT '添加时间',
  `edit_time` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  `add_admin_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '添加用户id',
  `edit_admin_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '修改用户id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`,`platform_id`)
) ENGINE=InnoDB AUTO_INCREMENT=87225 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='录入用户其它信息表';

-- ----------------------------
-- Table structure for vip_user_rebate_prop
-- ----------------------------
DROP TABLE IF EXISTS `vip_user_rebate_prop`;
CREATE TABLE `vip_user_rebate_prop` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `platform_id` tinyint(4) NOT NULL DEFAULT '0' COMMENT '平台id',
  `uid` varchar(64) NOT NULL COMMENT '平台用户id',
  `date` date NOT NULL DEFAULT '0000-00-00' COMMENT '日期',
  `game_id` int(10) NOT NULL DEFAULT '0' COMMENT '子游戏id',
  `rebate_prop_config_id` int(10) NOT NULL DEFAULT '0' COMMENT '返利道具配置表id',
  `apply_status` tinyint(2) NOT NULL DEFAULT '2' COMMENT '申请状态（默认：2：已申请  1：未申请）',
  `examine_status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '审核状态（默认：0：未审核  1：审核通过 2：拒审）',
  `examine_time` int(10) NOT NULL DEFAULT '0' COMMENT '审核时间',
  `examine_admin_id` int(6) NOT NULL DEFAULT '0' COMMENT '审核人后台用户id',
  `add_admin_id` int(6) NOT NULL DEFAULT '0' COMMENT '添加人id',
  `add_time` int(10) NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='vip用户返利道具记录表';

-- ----------------------------
-- Table structure for vip_user_welfare
-- ----------------------------
DROP TABLE IF EXISTS `vip_user_welfare`;
CREATE TABLE `vip_user_welfare` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `platform_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '平台id',
  `uid` varchar(64) NOT NULL COMMENT '平台用户id',
  `p_u` varchar(64) NOT NULL COMMENT 'platform_id_uid组成的字符串',
  `qq` varchar(50) DEFAULT NULL,
  `goods` varchar(255) DEFAULT NULL COMMENT '物品（商品）',
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_send` tinyint(1) DEFAULT '0' COMMENT '0 未发放 1 已发放',
  `desc` varchar(255) DEFAULT NULL,
  `content` text,
  `imgs` varchar(255) DEFAULT NULL,
  `admin_id` int(10) NOT NULL DEFAULT '0' COMMENT '添加人',
  `add_time` int(10) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `send_time` int(10) NOT NULL DEFAULT '0' COMMENT '发放时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `p_u` (`p_u`),
  KEY `platform_id` (`platform_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;
