/*
Navicat MySQL Data Transfer

Source Server         : 客服rds（读）
Source Server Version : 50737
Source Host           : rm-wz921s325108jf0bm8o.mysql.rds.aliyuncs.com:3306
Source Database       : db_customer_bx

Target Server Type    : MYSQL
Target Server Version : 50737
File Encoding         : 65001

Date: 2023-02-24 10:20:01
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for kefu_common_member
-- ----------------------------
DROP TABLE IF EXISTS `kefu_common_member`;
CREATE TABLE `kefu_common_member` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT 'uid',
  `user_name` varchar(20) NOT NULL COMMENT '用户名',
  `reg_date` int(11) DEFAULT NULL COMMENT '注册时间',
  `login_date` int(11) DEFAULT NULL COMMENT '最后登录时间',
  `reg_channel` varchar(150) NOT NULL COMMENT '注册渠道',
  `mobile` varchar(100) DEFAULT NULL COMMENT '绑定的手机号码',
  `reg_gid` int(11) NOT NULL DEFAULT '0' COMMENT '注册游戏ID',
  `status` smallint(5) NOT NULL DEFAULT '1' COMMENT '是否审核，0为封禁，1正常',
  `imei` varchar(50) NOT NULL DEFAULT '' COMMENT '手机设备号',
  `idfa` varchar(50) NOT NULL DEFAULT '' COMMENT '手机设备号',
  `udid` varchar(100) DEFAULT '' COMMENT '唯一设备号',
  `reg_ip` varchar(50) NOT NULL DEFAULT '' COMMENT '注册ip',
  `login_ip` varchar(50) NOT NULL DEFAULT '' COMMENT '登录ip',
  `real_name` varchar(255) DEFAULT '' COMMENT '实名',
  `id_card` varchar(255) DEFAULT '' COMMENT '身份证',
  `real_name_time` int(11) DEFAULT '0' COMMENT '实名时间',
  `user_type` tinyint(5) DEFAULT '0' COMMENT '用户类型 无记录0， 1：账号注册 2：手机验证码注册 3：手机账号注册',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`),
  KEY `reg_date` (`reg_date`),
  KEY `reg_info` (`reg_gid`,`reg_date`),
  KEY `ip` (`reg_ip`),
  KEY `mobile` (`mobile`),
  KEY `login_date` (`login_date`),
  KEY `udid` (`udid`),
  KEY `login_ip` (`login_ip`),
  KEY `reg_channel` (`reg_channel`),
  KEY `username` (`user_name`),
  KEY `id_card` (`id_card`(191))
) ENGINE=InnoDB AUTO_INCREMENT=1579027 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='用户核心表';

-- ----------------------------
-- Table structure for kefu_game_list
-- ----------------------------
DROP TABLE IF EXISTS `kefu_game_list`;
CREATE TABLE `kefu_game_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自动增长',
  `game_id` int(10) NOT NULL DEFAULT '0' COMMENT '平台游戏id',
  `game_name` varchar(25) NOT NULL COMMENT '平台游戏名称',
  `product_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '游戏产品(大类)id',
  `product_name` varchar(25) DEFAULT '' COMMENT '游戏产品名称',
  `add_admin_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '添加用户id(0：默认 程序自动添加)',
  `add_time` int(11) NOT NULL COMMENT '添加时间',
  `edit_admin_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '修改用户id',
  `edit_time` int(11) DEFAULT '0' COMMENT '修改时间',
  `static` tinyint(2) NOT NULL DEFAULT '1' COMMENT '状态（1：默认 开启  2：关闭 -1：弃用）',
  PRIMARY KEY (`id`),
  UNIQUE KEY `game` (`game_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='游戏列表';

-- ----------------------------
-- Table structure for kefu_login_log
-- ----------------------------
DROP TABLE IF EXISTS `kefu_login_log`;
CREATE TABLE `kefu_login_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `month` tinyint(2) NOT NULL COMMENT '月份',
  `uid` int(11) NOT NULL COMMENT 'uid',
  `uname` varchar(32) NOT NULL COMMENT '会员账号',
  `gid` int(10) NOT NULL COMMENT '游戏ID',
  `login_date` int(10) NOT NULL COMMENT '登录时间',
  `login_channel` varchar(32) NOT NULL COMMENT '登录渠道',
  `game_name` varchar(255) NOT NULL DEFAULT '' COMMENT '游戏名',
  `imei` varchar(100) NOT NULL DEFAULT '' COMMENT 'imei',
  `idfa` varchar(100) NOT NULL,
  `login_ip` varchar(25) NOT NULL DEFAULT '' COMMENT '登录ip地址',
  `mobile` varchar(15) DEFAULT '' COMMENT '用户手机号码',
  `real_name` varchar(255) DEFAULT '' COMMENT '实名',
  `id_card` varchar(255) DEFAULT '' COMMENT '身份证',
  `real_name_time` int(11) DEFAULT '0' COMMENT '实名时间',
  `udid` varchar(255) DEFAULT '' COMMENT '手机唯一设备码',
  `ext1` varchar(255) DEFAULT '' COMMENT '备用字段1',
  `ext2` text COMMENT '备用字段2',
  PRIMARY KEY (`id`,`month`),
  KEY `uid` (`uid`,`login_date`),
  KEY `login_ver` (`login_channel`),
  KEY `loginreg` (`login_date`),
  KEY `ugl` (`uid`,`gid`,`login_date`),
  KEY `username` (`uname`)
) ENGINE=InnoDB AUTO_INCREMENT=19574476 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='用户登录日志表'
/*!50100 PARTITION BY RANGE (`month`)
(PARTITION p1 VALUES LESS THAN (1) ENGINE = InnoDB,
 PARTITION p2 VALUES LESS THAN (2) ENGINE = InnoDB,
 PARTITION p3 VALUES LESS THAN (3) ENGINE = InnoDB,
 PARTITION p4 VALUES LESS THAN (4) ENGINE = InnoDB,
 PARTITION p5 VALUES LESS THAN (5) ENGINE = InnoDB,
 PARTITION p6 VALUES LESS THAN (6) ENGINE = InnoDB,
 PARTITION p7 VALUES LESS THAN (7) ENGINE = InnoDB,
 PARTITION p8 VALUES LESS THAN (8) ENGINE = InnoDB,
 PARTITION p9 VALUES LESS THAN (9) ENGINE = InnoDB,
 PARTITION p10 VALUES LESS THAN (10) ENGINE = InnoDB,
 PARTITION p11 VALUES LESS THAN (11) ENGINE = InnoDB,
 PARTITION p12 VALUES LESS THAN (12) ENGINE = InnoDB,
 PARTITION p13 VALUES LESS THAN MAXVALUE ENGINE = InnoDB) */;

-- ----------------------------
-- Table structure for kefu_pay_order
-- ----------------------------
DROP TABLE IF EXISTS `kefu_pay_order`;
CREATE TABLE `kefu_pay_order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` varchar(255) NOT NULL COMMENT '平台流水号',
  `third_party_order_id` varchar(255) DEFAULT '' COMMENT '第三方订单号',
  `uid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '充值账号UID',
  `user_name` varchar(100) DEFAULT '' COMMENT '用户名',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '充值金额',
  `gid` int(11) NOT NULL COMMENT '所属游戏ID',
  `game_name` varchar(255) NOT NULL DEFAULT '' COMMENT '游戏名称',
  `server_id` varchar(100) DEFAULT '0' COMMENT '所属游戏服务区',
  `server_name` varchar(100) DEFAULT '',
  `role_id` varchar(255) NOT NULL DEFAULT '0' COMMENT '角色id',
  `role_name` varchar(100) NOT NULL DEFAULT '' COMMENT '角色名称',
  `reg_channel` varchar(100) DEFAULT '' COMMENT '注册包版本号id',
  `pay_channel` varchar(100) DEFAULT '' COMMENT '包版本号',
  `dateline` int(11) NOT NULL COMMENT '记录生成时间',
  `imei` varchar(100) DEFAULT '',
  `idfa` varchar(100) DEFAULT NULL,
  `pay_time` int(11) NOT NULL DEFAULT '0' COMMENT '支付时间',
  `payment` varchar(100) DEFAULT '' COMMENT '支付方式 1:微信 2:支付宝 3:IOS 4:平台币 5:代金券 6:其他 ',
  `yuanbao_status` tinyint(1) DEFAULT '-1' COMMENT '元宝状态',
  `first_login_game_id` int(10) DEFAULT '0' COMMENT '首登游戏id',
  `first_login_game_time` int(11) DEFAULT '0' COMMENT '首登时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `gid` (`gid`),
  KEY `dateline` (`dateline`),
  KEY `user_name` (`user_name`),
  KEY `pay_channel` (`pay_channel`),
  KEY `payment` (`payment`),
  KEY `third_party_order_id` (`third_party_order_id`(191)),
  KEY `server_id` (`server_id`),
  KEY `order_id` (`order_id`(191)),
  KEY `reg_channel` (`reg_channel`),
  KEY `first_login_game_id` (`first_login_game_id`),
  KEY `pay_time` (`pay_time`)
) ENGINE=InnoDB AUTO_INCREMENT=1927198 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='用户充值记录';

-- ----------------------------
-- Table structure for kefu_server_list
-- ----------------------------
DROP TABLE IF EXISTS `kefu_server_list`;
CREATE TABLE `kefu_server_list` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gid` int(11) DEFAULT '0',
  `server_id` varchar(120) DEFAULT '',
  `server_name` varchar(120) DEFAULT '',
  `open_time` varchar(15) DEFAULT '' COMMENT '开服时间',
  `platform_id` tinyint(3) DEFAULT '5' COMMENT '平台id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `gid_server_id` (`gid`,`server_id`),
  KEY `server_id` (`server_id`)
) ENGINE=InnoDB AUTO_INCREMENT=84081505 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for kefu_user_role
-- ----------------------------
DROP TABLE IF EXISTS `kefu_user_role`;
CREATE TABLE `kefu_user_role` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `uname` varchar(120) DEFAULT '' COMMENT '用户名',
  `role_id` varchar(100) DEFAULT '' COMMENT '角色id',
  `role_name` varchar(120) DEFAULT '' COMMENT '角色名称',
  `trans_level` smallint(6) unsigned DEFAULT '0' COMMENT '转生等级',
  `role_level` smallint(6) unsigned DEFAULT '0' COMMENT '角色等级',
  `reg_gid` int(11) unsigned DEFAULT '0' COMMENT '注册游戏id',
  `reg_channel` int(11) DEFAULT '0' COMMENT '注册渠道id',
  `reg_time` int(11) DEFAULT '0' COMMENT '注册时间',
  `server_id` varchar(120) DEFAULT '' COMMENT '区服id',
  `server_name` varchar(120) DEFAULT '' COMMENT '区服名称',
  `login_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`,`role_id`),
  KEY `role_name` (`role_name`),
  KEY `uname` (`uname`),
  KEY `role_id` (`role_id`),
  KEY `reg_time` (`reg_time`),
  KEY `login_date` (`login_date`),
  KEY `server_id` (`server_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1335124 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;
