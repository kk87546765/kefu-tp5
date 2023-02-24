/*
Navicat MySQL Data Transfer

Source Server         : 客服rds（读）
Source Server Version : 50737
Source Host           : rm-wz921s325108jf0bm8o.mysql.rds.aliyuncs.com:3306
Source Database       : gr_chat

Target Server Type    : MYSQL
Target Server Version : 50737
File Encoding         : 65001

Date: 2023-02-24 10:20:49
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for gr_action_block
-- ----------------------------
DROP TABLE IF EXISTS `gr_action_block`;
CREATE TABLE `gr_action_block` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL DEFAULT '' COMMENT '规则名称',
  `type` tinyint(5) NOT NULL DEFAULT '0' COMMENT '封禁类型 1：封登录 2：封禁言',
  `ban_time` int(11) NOT NULL DEFAULT '0' COMMENT '封禁时长（分）',
  `ban_object` tinyint(5) NOT NULL DEFAULT '1' COMMENT '封禁对象 1:账号 2:IP 3:设备',
  `ban_reason` tinyint(5) NOT NULL DEFAULT '1' COMMENT '封禁原因 1：广告号 2：恶意刷屏 3：恶意辱骂他人 4：疑似广告号',
  `product` int(11) NOT NULL DEFAULT '0' COMMENT '游戏产品id',
  `product_name` varchar(30) NOT NULL DEFAULT '' COMMENT '产品标识',
  `min_level` int(11) NOT NULL DEFAULT '0' COMMENT '最小等级',
  `max_level` int(11) NOT NULL DEFAULT '0' COMMENT '最大等级',
  `min_money` int(11) NOT NULL DEFAULT '0' COMMENT '最小金额',
  `max_money` int(11) NOT NULL DEFAULT '0' COMMENT '最大等级',
  `check_type` tinyint(5) NOT NULL DEFAULT '1' COMMENT '验证规则类型 1：私聊人数 2：重复信息 3：纯数字次数 4：超过字符长度',
  `limit_time` int(11) NOT NULL DEFAULT '0' COMMENT '规定的时间范围',
  `private_chat_num` varchar(20) NOT NULL DEFAULT '' COMMENT '达到私聊人数范围',
  `repeat_msg_num` varchar(20) NOT NULL DEFAULT '' COMMENT '达到重复信息范围',
  `limit_figure_length` varchar(20) NOT NULL DEFAULT '' COMMENT '限制数字位数',
  `figure_num` varchar(20) NOT NULL DEFAULT '' COMMENT '达到限制数字位数范围',
  `limit_char_length` varchar(20) NOT NULL DEFAULT '' COMMENT '累积长度字符',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 0：关闭 1：开启',
  PRIMARY KEY (`id`),
  KEY `product` (`product`),
  KEY `add_time` (`add_time`)
) ENGINE=InnoDB AUTO_INCREMENT=9681 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for gr_admin
-- ----------------------------
DROP TABLE IF EXISTS `gr_admin`;
CREATE TABLE `gr_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` char(32) NOT NULL DEFAULT '',
  `password` char(32) NOT NULL DEFAULT '',
  `realname` char(32) NOT NULL DEFAULT '',
  `add_time` int(11) NOT NULL DEFAULT '0',
  `last_login_time` int(11) NOT NULL DEFAULT '0',
  `last_ip` char(15) NOT NULL DEFAULT '',
  `login_times` int(11) NOT NULL DEFAULT '0',
  `phone` varchar(13) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '手机号码',
  `extra` text,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `role_id` int(11) NOT NULL DEFAULT '0' COMMENT '角色ID',
  `platform` varchar(100) NOT NULL DEFAULT '' COMMENT '归属平台',
  `group_id` varchar(255) DEFAULT '' COMMENT '分组',
  `position_grade` tinyint(2) DEFAULT '0' COMMENT '职位等级，更多作用已数据权限（0：默认 普通员工/不需要用该字段 1：组长 2：主管 3：经理）',
  `vip_game_product_ids` text COMMENT '用户游戏归属，逗号分开 ',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=410 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for gr_admin_log
-- ----------------------------
DROP TABLE IF EXISTS `gr_admin_log`;
CREATE TABLE `gr_admin_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) NOT NULL DEFAULT '0',
  `ip` varchar(30) NOT NULL,
  `url` varchar(255) NOT NULL,
  `controller` varchar(100) DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `data` text COMMENT '提交数据',
  `add_time` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `controller` (`controller`,`action`),
  KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=41474860 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for gr_admin_log_n
-- ----------------------------
DROP TABLE IF EXISTS `gr_admin_log_n`;
CREATE TABLE `gr_admin_log_n` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) NOT NULL DEFAULT '0',
  `ip` varchar(30) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `controller` varchar(100) NOT NULL DEFAULT '',
  `action` varchar(100) NOT NULL DEFAULT '',
  `data` longtext COMMENT '提交数据',
  `add_time` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `controller` (`controller`,`action`),
  KEY `admin_id` (`admin_id`),
  KEY `add_time` (`add_time`)
) ENGINE=InnoDB AUTO_INCREMENT=2977445 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for gr_api_log
-- ----------------------------
DROP TABLE IF EXISTS `gr_api_log`;
CREATE TABLE `gr_api_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `method` varchar(32) DEFAULT NULL COMMENT '请求方式',
  `url` varchar(512) DEFAULT NULL COMMENT '请求的url地址',
  `info` text COMMENT '日志',
  `addtime` bigint(20) DEFAULT '0' COMMENT '日志创建时间',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`id`),
  KEY `method` (`method`),
  KEY `url` (`url`(191)),
  KEY `addtime` (`addtime`)
) ENGINE=InnoDB AUTO_INCREMENT=109 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for gr_ban_imei_log
-- ----------------------------
DROP TABLE IF EXISTS `gr_ban_imei_log`;
CREATE TABLE `gr_ban_imei_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `imei` varchar(150) NOT NULL COMMENT '玩家账号',
  `admin_user` varchar(35) NOT NULL COMMENT '处理人',
  `dateline` int(11) NOT NULL COMMENT '处理时间',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型 1封禁,2解封',
  `ban_time` int(11) NOT NULL DEFAULT '0' COMMENT '封禁时长',
  `reason` varchar(150) NOT NULL DEFAULT '' COMMENT '说明',
  `platform` varchar(100) NOT NULL DEFAULT '' COMMENT '平台标识',
  PRIMARY KEY (`id`),
  KEY `dateline` (`dateline`),
  KEY `imei` (`imei`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='封禁/解封用户记录表';

-- ----------------------------
-- Table structure for gr_ban_ip_log
-- ----------------------------
DROP TABLE IF EXISTS `gr_ban_ip_log`;
CREATE TABLE `gr_ban_ip_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(20) NOT NULL COMMENT '玩家账号',
  `admin_user` varchar(35) NOT NULL COMMENT '处理人',
  `dateline` int(11) NOT NULL COMMENT '处理时间',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型 1封禁,2解封',
  `ban_time` int(11) NOT NULL DEFAULT '0' COMMENT '封禁时长',
  `reason` varchar(150) NOT NULL DEFAULT '' COMMENT '说明',
  `platform` varchar(100) NOT NULL DEFAULT '' COMMENT '平台标识',
  PRIMARY KEY (`id`),
  KEY `dateline` (`dateline`),
  KEY `ip` (`ip`)
) ENGINE=InnoDB AUTO_INCREMENT=287 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='封禁/解封用户记录表';

-- ----------------------------
-- Table structure for gr_ban_user_log
-- ----------------------------
DROP TABLE IF EXISTS `gr_ban_user_log`;
CREATE TABLE `gr_ban_user_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(25) CHARACTER SET utf8mb4 NOT NULL COMMENT '平台用户ID',
  `user_name` varchar(35) NOT NULL COMMENT '玩家账号',
  `admin_user` varchar(35) NOT NULL COMMENT '处理人',
  `dateline` int(11) NOT NULL COMMENT '处理时间',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型 1封禁,2解封',
  `ban_time` int(11) NOT NULL DEFAULT '0' COMMENT '封禁时长',
  `reason` varchar(150) NOT NULL DEFAULT '' COMMENT '说明',
  `platform` varchar(100) NOT NULL DEFAULT '' COMMENT '平台标识',
  PRIMARY KEY (`id`),
  KEY `dateline` (`dateline`),
  KEY `user_name` (`user_name`)
) ENGINE=InnoDB AUTO_INCREMENT=543836 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='封禁/解封用户记录表';

-- ----------------------------
-- Table structure for gr_batch_block
-- ----------------------------
DROP TABLE IF EXISTS `gr_batch_block`;
CREATE TABLE `gr_batch_block` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gkey` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT '' COMMENT '游戏产品',
  `role_id` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT '' COMMENT '角色id',
  `server_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT '' COMMENT '区服id',
  `add_time` bigint(20) DEFAULT '0' COMMENT '添加时间',
  `ban_time` bigint(20) DEFAULT '0' COMMENT '封禁时长',
  `expect_unblock_time` bigint(20) DEFAULT '0' COMMENT '预计解封日期',
  `op_admin` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT '' COMMENT '操作人员',
  `reason` varchar(255) DEFAULT '' COMMENT '操作理由',
  `type` tinyint(4) DEFAULT '1' COMMENT '1:封禁 2:解封',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for gr_black_block
-- ----------------------------
DROP TABLE IF EXISTS `gr_black_block`;
CREATE TABLE `gr_black_block` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `phone` varchar(20) DEFAULT '' COMMENT '手机号',
  `idcard` varchar(200) DEFAULT '' COMMENT '身份证',
  `udid` varchar(200) DEFAULT '' COMMENT '设备号',
  `check_val` varchar(200) DEFAULT '' COMMENT '查询值',
  `type` tinyint(4) DEFAULT '0' COMMENT '限制类型 1：手机号 2：身份证号 3：设备号',
  `platform_id` tinyint(2) DEFAULT '0' COMMENT '操作平台',
  `admin_id` int(11) DEFAULT '0' COMMENT '真实操作人id',
  `block_admin_id` int(11) DEFAULT '0' COMMENT '操作人id',
  `block_time` bigint(20) DEFAULT '0' COMMENT '添加时间',
  `block_ip` varchar(50) DEFAULT '' COMMENT '添加ip地址',
  `reason_type` varchar(100) DEFAULT '' COMMENT '封禁原因类别',
  `reason` varchar(255) DEFAULT '' COMMENT '封禁理由',
  `unblock_admin_id` int(11) DEFAULT '0' COMMENT '解封人',
  `unblock_reason` varchar(255) DEFAULT '' COMMENT '解封原因',
  `unblock_time` bigint(20) DEFAULT '0' COMMENT '解封时间',
  `status` tinyint(4) DEFAULT '1' COMMENT '状态 1：封禁 0：解封',
  PRIMARY KEY (`id`),
  KEY `phone` (`phone`),
  KEY `idcard` (`idcard`(191)),
  KEY `udid` (`udid`(191)),
  KEY `platform_id` (`platform_id`),
  KEY `unblock_admin_id` (`unblock_admin_id`),
  KEY `admin_id` (`admin_id`),
  KEY `block_time` (`block_time`),
  KEY `unblock_time` (`unblock_time`)
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for gr_block
-- ----------------------------
DROP TABLE IF EXISTS `gr_block`;
CREATE TABLE `gr_block` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gkey` char(35) NOT NULL DEFAULT '',
  `sid` char(35) NOT NULL DEFAULT '',
  `uid` int(11) NOT NULL DEFAULT '0',
  `platform_uid` int(11) NOT NULL DEFAULT '0' COMMENT '平台的uid',
  `uname` char(35) NOT NULL DEFAULT '',
  `roleid` char(35) NOT NULL DEFAULT '',
  `rolename` varchar(50) NOT NULL DEFAULT '' COMMENT '角色名',
  `role_level` smallint(6) NOT NULL DEFAULT '0' COMMENT '角色等级',
  `count_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '充值金额',
  `reg_channel_id` int(10) DEFAULT '0' COMMENT '注册包id',
  `ip` char(15) NOT NULL DEFAULT '',
  `tkey` char(35) NOT NULL DEFAULT '' COMMENT '聊天信息上的平台key',
  `platform_tkey` varchar(35) DEFAULT '' COMMENT '平台的平台key',
  `addtime` int(11) NOT NULL DEFAULT '0',
  `expect_unblock_time` int(11) DEFAULT '0' COMMENT '预计解封日期',
  `op_admin_id` char(30) NOT NULL DEFAULT '',
  `status` int(11) NOT NULL DEFAULT '0',
  `type` enum('IP','USER','CHAT','UID','IMEI','AUTO','ACTION','AUTOCHAT','ACTIONCHAT') NOT NULL DEFAULT 'CHAT',
  `op_ip` char(15) NOT NULL,
  `imei` varchar(120) NOT NULL DEFAULT '' COMMENT 'IMEI',
  `blocktime` int(11) DEFAULT NULL,
  `keyword` varchar(255) DEFAULT NULL COMMENT '关键词',
  `unblock_admin` varchar(30) NOT NULL DEFAULT '' COMMENT '解封人',
  `unblock_time` int(11) NOT NULL DEFAULT '0' COMMENT '解封日期',
  `ext` varchar(50) CHARACTER SET utf8mb4 NOT NULL COMMENT '接口回传参数',
  `reason` varchar(255) DEFAULT '' COMMENT '封禁原因',
  `ban_type` tinyint(3) DEFAULT '1' COMMENT '封禁模式 1：cp踢下线+sdk封禁 2：cp封禁+sdk封禁',
  `openid` varchar(100) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '聚合id（如贪玩）',
  `hit_keyword_id` int(11) DEFAULT '0' COMMENT '命中关键词id',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `platfrom_uid` (`platform_uid`),
  KEY `reg_channel_id` (`reg_channel_id`),
  KEY `rolename` (`rolename`),
  KEY `addtime` (`addtime`),
  KEY `ip` (`ip`),
  KEY `imei` (`imei`),
  KEY `expect_unblock_time` (`expect_unblock_time`)
) ENGINE=InnoDB AUTO_INCREMENT=761821 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for gr_block_waring
-- ----------------------------
DROP TABLE IF EXISTS `gr_block_waring`;
CREATE TABLE `gr_block_waring` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `es_id` varchar(255) DEFAULT '' COMMENT 'id',
  `gkey` varchar(255) DEFAULT '' COMMENT '游戏key',
  `tkey` varchar(255) DEFAULT '' COMMENT '平台key',
  `sid` varchar(255) DEFAULT '' COMMENT '区服id',
  `uid` varchar(255) DEFAULT '' COMMENT 'uid',
  `uname` varchar(255) DEFAULT '' COMMENT '角色名',
  `roleid` varchar(255) DEFAULT '' COMMENT '角色id',
  `type` tinyint(4) DEFAULT NULL COMMENT '聊天类型',
  `block_type` tinyint(4) DEFAULT '0' COMMENT '封禁类型 1：封禁+禁言 2：禁言 3：封禁',
  `content` text COMMENT '聊天信息',
  `keyword` varchar(255) DEFAULT '' COMMENT '关键词',
  `keyword_id` int(11) DEFAULT '0' COMMENT '命中关键词id',
  `time` bigint(20) DEFAULT NULL,
  `ip` varchar(255) DEFAULT '' COMMENT 'ip',
  `to_uid` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT '' COMMENT '私聊用户uid',
  `to_uname` varchar(255) DEFAULT '' COMMENT '私聊用户角色名',
  `role_level` varchar(255) DEFAULT '' COMMENT '角色等级',
  `imei` varchar(255) DEFAULT '' COMMENT 'imei',
  `count_money` varchar(255) DEFAULT '' COMMENT '充值金额',
  `reg_channel_id` int(11) DEFAULT '0' COMMENT '注册渠道id',
  `ext` text COMMENT '透传参数',
  `openid` varchar(255) DEFAULT '' COMMENT '第三方id',
  `is_sensitive` tinyint(4) DEFAULT '0' COMMENT '是否敏感',
  `sensitive_keyword` varchar(255) DEFAULT '' COMMENT '触发敏感词',
  `request_time` bigint(20) DEFAULT '0' COMMENT '请求时间',
  `status` tinyint(4) DEFAULT '0' COMMENT '是否已处理  0：未处理 1：已处理',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`(191)),
  KEY `roleid` (`roleid`(191)),
  KEY `gkey` (`gkey`(191)),
  KEY `time` (`time`)
) ENGINE=InnoDB AUTO_INCREMENT=24327 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for gr_chat_msg
-- ----------------------------
DROP TABLE IF EXISTS `gr_chat_msg`;
CREATE TABLE `gr_chat_msg` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gkey` varchar(10) NOT NULL DEFAULT '' COMMENT '游戏标识',
  `tkey` varchar(20) NOT NULL DEFAULT '' COMMENT '平台标识',
  `chat_time` char(10) NOT NULL DEFAULT '' COMMENT '聊天时间',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发送者uid',
  `uname` varchar(100) NOT NULL DEFAULT '' COMMENT '发送者昵称',
  `sid` varchar(100) NOT NULL DEFAULT '0' COMMENT '游戏区服',
  `type` tinyint(3) NOT NULL COMMENT '消息类型：1 私聊；2 喇叭；3 邮件；4 世界；5 国家；6 工会/帮会；7 队伍；8 附近；9 其他',
  `to_uname` varchar(100) NOT NULL COMMENT '接受者昵称',
  `to_uid` int(10) unsigned NOT NULL COMMENT '接受者uid',
  `content` text NOT NULL COMMENT '发送内容',
  `create_time` char(10) NOT NULL COMMENT '入库时间',
  `imei` varchar(100) NOT NULL COMMENT '手机设备号',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP',
  `roleid` int(10) NOT NULL DEFAULT '0' COMMENT '角色id',
  `role_level` smallint(6) NOT NULL DEFAULT '0' COMMENT '角色等级',
  PRIMARY KEY (`id`),
  KEY `g_s_t_n` (`gkey`,`sid`,`chat_time`,`uname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for gr_gs_game_server
-- ----------------------------
DROP TABLE IF EXISTS `gr_gs_game_server`;
CREATE TABLE `gr_gs_game_server` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `game_sign` varchar(32) NOT NULL DEFAULT '' COMMENT '游戏大类标识',
  `server_id` int(11) NOT NULL DEFAULT '0' COMMENT '区服ID',
  `server_name` varchar(64) NOT NULL DEFAULT '' COMMENT '区服名称',
  `open_time` int(11) NOT NULL DEFAULT '0' COMMENT '开服时间',
  `main_user` int(11) NOT NULL DEFAULT '0' COMMENT '主负责人id',
  `second_user` int(11) NOT NULL DEFAULT '0' COMMENT '次负责人',
  PRIMARY KEY (`id`),
  UNIQUE KEY `game_sign` (`game_sign`,`server_id`),
  KEY `open_time` (`open_time`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='GS游戏区服管理';

-- ----------------------------
-- Table structure for gr_ip_keyword
-- ----------------------------
DROP TABLE IF EXISTS `gr_ip_keyword`;
CREATE TABLE `gr_ip_keyword` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_key` varchar(16) NOT NULL DEFAULT '' COMMENT 'IP',
  `addtime` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `add_user` varchar(30) NOT NULL DEFAULT '' COMMENT '添加人',
  PRIMARY KEY (`id`),
  KEY `ip_key` (`ip_key`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='IP关键词表';

-- ----------------------------
-- Table structure for gr_keyword
-- ----------------------------
DROP TABLE IF EXISTS `gr_keyword`;
CREATE TABLE `gr_keyword` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `keyword` varchar(30) NOT NULL DEFAULT '',
  `addtime` int(11) NOT NULL DEFAULT '0',
  `add_user` varchar(30) NOT NULL DEFAULT '' COMMENT '添加人',
  `game` varchar(100) NOT NULL DEFAULT 'common',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '开启状态（1开启，0关闭）',
  `resemble_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '谐音/谐形匹配 开启状态（1开启，0关闭）',
  `money_min` int(11) NOT NULL COMMENT '最小充值金额',
  `money_max` int(11) NOT NULL COMMENT '最大充值金额',
  `num` int(11) NOT NULL COMMENT '触发次数',
  `level_min` int(11) NOT NULL COMMENT '最小等级',
  `level_max` int(11) NOT NULL COMMENT '最高等级',
  `type` tinyint(2) DEFAULT '1' COMMENT '1:封号+禁言 2:禁言 3：封号',
  `block_time` int(11) DEFAULT '31536000' COMMENT '封禁时长（秒）',
  `ban_time` int(11) DEFAULT '864000' COMMENT '禁言时长（秒）',
  PRIMARY KEY (`id`),
  KEY `keyword` (`keyword`)
) ENGINE=InnoDB AUTO_INCREMENT=17492 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for gr_keyword_log
-- ----------------------------
DROP TABLE IF EXISTS `gr_keyword_log`;
CREATE TABLE `gr_keyword_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `uname` char(35) NOT NULL COMMENT '账号',
  `gkey` char(35) NOT NULL COMMENT '游戏唯一标示',
  `sid` char(35) NOT NULL COMMENT '区服',
  `keyword` varchar(255) NOT NULL COMMENT '触发关键词',
  `content` varchar(255) NOT NULL COMMENT '聊天内容',
  `roleid` char(30) NOT NULL COMMENT '角色id',
  `rolename` varchar(50) NOT NULL COMMENT '角色名',
  `role_level` int(11) NOT NULL COMMENT '角色等级',
  `count_money` decimal(10,2) NOT NULL COMMENT '累计充值金额',
  `addtime` int(11) NOT NULL COMMENT '触发时间',
  `type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '类型 0:普通自动封禁 1：上下自动封禁',
  `tkey` varchar(50) NOT NULL DEFAULT '' COMMENT '平台',
  PRIMARY KEY (`id`),
  KEY `addtime` (`addtime`),
  KEY `uid_tkey` (`uid`,`tkey`),
  KEY `keyword` (`keyword`)
) ENGINE=InnoDB AUTO_INCREMENT=33361514 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='关键词触发记录';

-- ----------------------------
-- Table structure for gr_log
-- ----------------------------
DROP TABLE IF EXISTS `gr_log`;
CREATE TABLE `gr_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '操作类型： 1 封用户; 2 封IP; 3 封IMEI; 4 禁言; 5 解封用户；6 解封IP；7 解封IMEI； 8 解禁言',
  `date` int(11) NOT NULL DEFAULT '0' COMMENT '操作时间',
  `admin_user` varchar(32) NOT NULL DEFAULT '' COMMENT '操作用户',
  `uname` varchar(32) NOT NULL DEFAULT '' COMMENT '被操作的玩家账号',
  `role_name` varchar(50) NOT NULL DEFAULT '' COMMENT '角色名',
  `role_sid` varchar(30) NOT NULL DEFAULT '' COMMENT '角色区服',
  `ip` char(15) NOT NULL DEFAULT '',
  `imei` varchar(200) NOT NULL DEFAULT '' COMMENT 'IMEI',
  `game_sign` varchar(30) NOT NULL DEFAULT '' COMMENT '所属游戏（游戏标识 如：dszf）',
  `ban_time` int(11) NOT NULL COMMENT '封禁时长',
  `reason` varchar(150) NOT NULL DEFAULT '' COMMENT '封禁原因',
  PRIMARY KEY (`id`),
  KEY `date` (`date`,`type`),
  KEY `role_name` (`role_name`),
  KEY `ip` (`ip`),
  KEY `imei` (`imei`(191)),
  KEY `admin_user` (`admin_user`)
) ENGINE=InnoDB AUTO_INCREMENT=13889 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='操作日志表';

-- ----------------------------
-- Table structure for gr_menu
-- ----------------------------
DROP TABLE IF EXISTS `gr_menu`;
CREATE TABLE `gr_menu` (
  `menu_id` int(11) NOT NULL AUTO_INCREMENT,
  `menu_name` varchar(128) NOT NULL DEFAULT '' COMMENT '菜单名',
  `p_id` int(11) NOT NULL DEFAULT '0' COMMENT '父菜单ID，一级菜单父ID为0',
  `controller` varchar(64) NOT NULL DEFAULT '' COMMENT '控制器名',
  `action` varchar(64) NOT NULL DEFAULT '' COMMENT '方法名',
  `is_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示 1是 2否',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `url` varchar(500) NOT NULL DEFAULT '' COMMENT '外部链接',
  PRIMARY KEY (`menu_id`)
) ENGINE=InnoDB AUTO_INCREMENT=332 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='菜单列表';

-- ----------------------------
-- Table structure for gr_menu_new
-- ----------------------------
DROP TABLE IF EXISTS `gr_menu_new`;
CREATE TABLE `gr_menu_new` (
  `menu_id` int(11) NOT NULL AUTO_INCREMENT,
  `menu_name` varchar(128) NOT NULL DEFAULT '' COMMENT '菜单名',
  `p_id` int(11) NOT NULL DEFAULT '0' COMMENT '父菜单ID，一级菜单父ID为0',
  `controller` varchar(64) NOT NULL DEFAULT '' COMMENT '控制器名',
  `action` varchar(64) NOT NULL DEFAULT '' COMMENT '方法名',
  `is_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示 1是 2否',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `url` varchar(500) NOT NULL DEFAULT '' COMMENT '外部链接',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '类型:1=>菜单,2=>操作',
  PRIMARY KEY (`menu_id`)
) ENGINE=InnoDB AUTO_INCREMENT=635 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='菜单列表';

-- ----------------------------
-- Table structure for gr_monitoring_uid
-- ----------------------------
DROP TABLE IF EXISTS `gr_monitoring_uid`;
CREATE TABLE `gr_monitoring_uid` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(30) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `addtime` int(11) NOT NULL DEFAULT '0',
  `add_user` varchar(30) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '添加人',
  `platform` char(10) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '归属平台',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '开启状态（1开启，0关闭）',
  `ban_time` int(11) DEFAULT '864000' COMMENT '禁言时长（秒）',
  PRIMARY KEY (`id`),
  UNIQUE KEY `p_u` (`platform`,`uid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for gr_role_list
-- ----------------------------
DROP TABLE IF EXISTS `gr_role_list`;
CREATE TABLE `gr_role_list` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(128) NOT NULL COMMENT '角色名',
  `p_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父级',
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='角色列表';

-- ----------------------------
-- Table structure for gr_role_menu
-- ----------------------------
DROP TABLE IF EXISTS `gr_role_menu`;
CREATE TABLE `gr_role_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27940 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='角色菜单表';

-- ----------------------------
-- Table structure for gr_role_menu_new
-- ----------------------------
DROP TABLE IF EXISTS `gr_role_menu_new`;
CREATE TABLE `gr_role_menu_new` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9519 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='角色菜单表';

-- ----------------------------
-- Table structure for gr_role_name_block
-- ----------------------------
DROP TABLE IF EXISTS `gr_role_name_block`;
CREATE TABLE `gr_role_name_block` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gkey` char(35) NOT NULL DEFAULT '',
  `sid` char(35) NOT NULL DEFAULT '',
  `uid` int(11) NOT NULL DEFAULT '0',
  `uname` varchar(150) DEFAULT '' COMMENT '账号名',
  `roleid` char(35) NOT NULL DEFAULT '',
  `rolename` varchar(50) NOT NULL DEFAULT '' COMMENT '角色名',
  `role_level` smallint(6) NOT NULL DEFAULT '0' COMMENT '角色等级',
  `count_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '充值金额',
  `reg_gid` int(11) DEFAULT '0' COMMENT '游戏id',
  `reg_channel_id` int(10) DEFAULT '0' COMMENT '注册包id',
  `tkey` char(35) NOT NULL DEFAULT '' COMMENT '平台key',
  `addtime` int(11) NOT NULL DEFAULT '0',
  `expect_unblock_time` int(11) DEFAULT '0' COMMENT '预计解封日期',
  `op_admin_id` char(30) NOT NULL DEFAULT '',
  `status` int(11) NOT NULL DEFAULT '0',
  `type` enum('USER','CHAT','AUTO','AUTOCHAT') NOT NULL DEFAULT 'CHAT',
  `check_type` tinyint(4) DEFAULT '0' COMMENT '检测类型 1：完全匹配（自动封禁） 2：部分匹配（人工处理）',
  `op_ip` char(15) NOT NULL,
  `blocktime` int(11) DEFAULT NULL,
  `keyword` varchar(255) DEFAULT NULL COMMENT '关键词',
  `unblock_admin` varchar(30) NOT NULL DEFAULT '' COMMENT '解封人',
  `unblock_time` int(11) NOT NULL DEFAULT '0' COMMENT '解封日期',
  `ext` varchar(50) NOT NULL COMMENT '接口回传参数',
  `reason` varchar(255) DEFAULT '' COMMENT '封禁原因',
  `ban_type` tinyint(3) DEFAULT '1' COMMENT '封禁模式 1：cp踢下线+sdk封禁 2：cp封禁+sdk封禁',
  `openid` varchar(100) DEFAULT '' COMMENT '聚合id（如贪玩）',
  `hit_keyword_id` int(11) DEFAULT '0' COMMENT '命中关键词id',
  PRIMARY KEY (`id`),
  KEY `rolename` (`rolename`) USING BTREE,
  KEY `uid` (`uid`),
  KEY `reg_channel_id` (`reg_channel_id`) USING BTREE,
  KEY `addtime` (`addtime`) USING BTREE,
  KEY `expect_unblock_time` (`expect_unblock_time`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for gr_role_name_block_waring
-- ----------------------------
DROP TABLE IF EXISTS `gr_role_name_block_waring`;
CREATE TABLE `gr_role_name_block_waring` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gkey` varchar(255) DEFAULT '' COMMENT '游戏key',
  `platform_id` tinyint(4) DEFAULT '0' COMMENT '平台id',
  `platform` varchar(255) DEFAULT '' COMMENT '平台key',
  `reg_gid` int(11) DEFAULT '0' COMMENT '游戏id',
  `reg_channel_id` mediumint(9) DEFAULT NULL,
  `server_id` varchar(255) DEFAULT '' COMMENT '区服id',
  `uid` varchar(255) DEFAULT '' COMMENT 'uid',
  `uname` varchar(150) DEFAULT '' COMMENT '用户名',
  `role_name` varchar(255) DEFAULT '' COMMENT '角色名',
  `role_id` varchar(255) DEFAULT '' COMMENT '角色id',
  `block_type` tinyint(4) DEFAULT '0' COMMENT '封禁类型 1：封禁+禁言 2：禁言 3：封禁',
  `keyword` varchar(255) DEFAULT '' COMMENT '关键词',
  `keyword_id` int(11) DEFAULT '0' COMMENT '命中关键词id',
  `time` bigint(20) DEFAULT NULL,
  `role_level` varchar(255) DEFAULT '' COMMENT '角色等级',
  `count_money` varchar(255) DEFAULT '' COMMENT '充值金额',
  `ext` text COMMENT '透传参数',
  `openid` varchar(255) DEFAULT '' COMMENT '第三方id',
  `status` tinyint(4) DEFAULT '0' COMMENT ' 是否已处理  0：未审核 1：已封禁 2：已删除',
  `check_type` tinyint(4) DEFAULT '1' COMMENT '检测类型 1：精准匹配（自动）2：部分匹配（人工）',
  PRIMARY KEY (`id`),
  KEY `gkey` (`gkey`(191)) USING BTREE,
  KEY `uid` (`uid`(191)) USING BTREE,
  KEY `roleid` (`role_id`(191)) USING BTREE,
  KEY `time` (`time`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=798 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for gr_role_name_keyword
-- ----------------------------
DROP TABLE IF EXISTS `gr_role_name_keyword`;
CREATE TABLE `gr_role_name_keyword` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `keyword` varchar(30) NOT NULL DEFAULT '',
  `addtime` int(11) NOT NULL DEFAULT '0',
  `add_user` varchar(30) NOT NULL DEFAULT '' COMMENT '添加人',
  `game` varchar(100) NOT NULL DEFAULT 'common',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '开启状态（1开启，0关闭）',
  `check_type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '检测类型 1：精准匹配（自动）2：部分匹配（人工）',
  `money_min` int(11) NOT NULL COMMENT '最小充值金额',
  `money_max` int(11) NOT NULL COMMENT '最大充值金额',
  `num` int(11) NOT NULL COMMENT '触发次数',
  `level_min` int(11) NOT NULL COMMENT '最小等级',
  `level_max` int(11) NOT NULL COMMENT '最高等级',
  `type` tinyint(2) DEFAULT '1' COMMENT '1:封号+禁言 2:禁言 3：封号',
  `block_time` int(11) DEFAULT '31536000' COMMENT '封禁时长（秒）',
  `ban_time` int(11) DEFAULT '864000' COMMENT '禁言时长（秒）',
  PRIMARY KEY (`id`),
  KEY `keyword` (`keyword`),
  KEY `addtime` (`addtime`),
  KEY `block_time` (`block_time`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for gr_run_log
-- ----------------------------
DROP TABLE IF EXISTS `gr_run_log`;
CREATE TABLE `gr_run_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `controller` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `add_time` int(10) NOT NULL DEFAULT '0',
  `update_time` int(10) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `input_param` text,
  `out_param` text,
  PRIMARY KEY (`id`),
  KEY `controller` (`controller`),
  KEY `action` (`action`),
  KEY `add_time` (`add_time`)
) ENGINE=InnoDB AUTO_INCREMENT=5605075 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for gr_sensitive
-- ----------------------------
DROP TABLE IF EXISTS `gr_sensitive`;
CREATE TABLE `gr_sensitive` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `keyword` varchar(30) NOT NULL DEFAULT '',
  `addtime` int(11) NOT NULL DEFAULT '0',
  `add_user` varchar(30) NOT NULL DEFAULT '' COMMENT '添加人',
  `game` char(10) NOT NULL DEFAULT '' COMMENT '游戏',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '开启状态（1开启，0关闭）',
  `money_min` int(11) NOT NULL COMMENT '最小充值金额',
  `money_max` int(11) NOT NULL COMMENT '最大充值金额',
  `level_min` int(11) NOT NULL COMMENT '最小等级',
  `level_max` int(11) NOT NULL COMMENT '最高等级',
  PRIMARY KEY (`id`),
  UNIQUE KEY `game` (`game`,`keyword`),
  KEY `keyword` (`keyword`)
) ENGINE=InnoDB AUTO_INCREMENT=610 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for gr_user_group
-- ----------------------------
DROP TABLE IF EXISTS `gr_user_group`;
CREATE TABLE `gr_user_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `name` varchar(25) NOT NULL COMMENT '用户组名称',
  `add_admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '增加后台用户id',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `edit_admin_id` int(11) DEFAULT '0' COMMENT '修改用户id',
  `edit_time` int(11) DEFAULT '0' COMMENT '修改时间',
  `type` tinyint(255) DEFAULT '0' COMMENT '0 所有 1 qc 2 客服',
  `status` tinyint(1) DEFAULT '1' COMMENT '1 启用 2 停用',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for gr_white_id
-- ----------------------------
DROP TABLE IF EXISTS `gr_white_id`;
CREATE TABLE `gr_white_id` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT 'uid',
  `game` varchar(30) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '游戏',
  `add_time` int(11) NOT NULL DEFAULT '0',
  `add_user` varchar(30) NOT NULL DEFAULT '' COMMENT '添加人',
  `status` tinyint(2) DEFAULT '1' COMMENT '状态 1:开始 0:删除',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=372 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for gr_white_keyword
-- ----------------------------
DROP TABLE IF EXISTS `gr_white_keyword`;
CREATE TABLE `gr_white_keyword` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `keyword` varchar(30) NOT NULL DEFAULT '',
  `add_time` int(11) NOT NULL DEFAULT '0',
  `add_user` varchar(30) NOT NULL DEFAULT '' COMMENT '添加人',
  PRIMARY KEY (`id`),
  KEY `keyword` (`keyword`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
