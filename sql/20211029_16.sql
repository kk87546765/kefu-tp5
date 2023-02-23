CREATE TABLE `gr_role_menu_new` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色菜单表';

CREATE TABLE `gr_run_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `controller` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `add_time` int(10) NOT NULL DEFAULT '0',
  `update_time` int(10) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `input_param` text,
  `out_param` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB AUTO_INCREMENT=317 DEFAULT CHARSET=utf8mb4 COMMENT='菜单列表';

INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('9', '系统权限管理', '0', '', '', '1', '100', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('10', '菜单管理', '9', 'Sysmanage', 'menu', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('11', '角色管理', '9', 'Sysmanage', 'role', '1', '1', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('12', '用户管理', '9', 'user', 'list', '1', '2', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('13', '游戏监控管理', '0', '', '', '1', '6', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('14', 'GS内服管理', '0', '', '', '1', '8', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('15', '负责人管理', '14', 'gsuser', 'user', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('16', '游戏区服管理', '14', 'gameserver', 'servers', '1', '1', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('17', '游戏区服数据', '14', 'gsdata', 'index', '1', '2', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('18', '区服数据汇总', '14', 'gsdata', 'data_gather', '1', '3', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('19', '关键词列表', '13', 'keyword', 'index_n', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('20', '封禁列表', '13', 'block', 'index_n', '1', '1', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('21', '实时聊天系统', '13', 'chat', 'index_n', '1', '2', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('22', '实时聊天列表(关键词)', '13', 'chat', 'index2_n', '1', '3', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('23', '封禁日志', '13', 'log', 'index_n', '1', '5', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('37', '综合管理系统', '13', '', '', '1', '5', 'http://opsy.86mmo.com/?action=sys&opt=login&action=sys&opt=login', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('48', '详情', '10', 'Sysmanage', 'menuDetail', '1', '1', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('51', '关键IP', '13', 'keyword', 'ipKeyword', '1', '7', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('55', '实时聊天系统(IP)', '13', 'chat', 'ipkey_chat', '1', '8', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('56', '实时聊天列表(IP&关键词)', '13', 'chat', 'ip_keyword_chat', '1', '9', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('59', 'VIP营销管理', '0', '', '', '1', '7', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('91', '账号封禁管理', '0', '', '', '1', '5', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('92', '账号封/解禁', '91', 'player', 'ban_user_list', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('95', '预警功能', '13', 'waring', 'index_n', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('96', 'IP封禁列表', '91', 'player', 'ban_ip_list', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('97', 'IMEI封禁列表', '91', 'player', 'ban_imei_list', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('100', '客服质检管理', '0', '', '', '1', '10', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('101', '质检池', '100', 'qc', 'index', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('104', '质检配置', '100', 'qc', 'qc_config_list', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('106', '质检记录', '100', 'qc', 'log', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('112', '公共接口', '100', '', '', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('117', '基础配置管理', '0', '', '', '1', '99', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('119', '行为封禁', '13', 'action_block', 'index_n', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('120', '白名单列表', '13', 'keyword', 'whitelist', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('121', '平台列表', '117', 'sysmanage', 'platform_list', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('122', '通用配置', '117', 'sysmanage', 'common_config', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('123', '用户分组', '117', 'Sysmanage', 'user_group_list', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('124', '待检记录', '100', 'qc', 'index_wait', '1', '0', 'qc/index.html?type=1', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('130', 'VIP用户管理', '59', 'vip', 'vip_user_info', '1', '1', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('132', 'VIP标准配置', '59', 'vip', 'become_vip_standard_list', '1', '5', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('135', '区服分配列表', '59', 'vip', 'game_product_server', '1', '5', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('138', '阿斯加德ID反查', '91', 'admin', 'checkid', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('139', '营销KPI配置', '59', 'vip', 'seller_kpi_config', '1', '5', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('140', 'VIP提成配置', '59', 'vip', 'seller_commission_config_list', '1', '5', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('142', 'SDK账号反查', '91', 'admin', 'checkSDKid', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('143', '操作日志', '9', 'admin_log', 'list', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('161', '营销数据报表', '59', 'vip', 'sell_statistic_month_list', '1', '7', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('174', 'VIP工单管理', '59', 'vip', 'sell_work_order_list', '1', '3', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('188', '平台用户列表', '59', 'vip', 'user_recharge_info', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('189', '会话导入', '100', 'qc', 'question_leading_in', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('190', 'VIP充值列表', '59', 'vip', 'vip_user_recharge_list', '1', '2', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('194', '进驻管理', '14', 'garrison_product', 'index_n', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('195', '区服管理', '14', 'distributional_server', 'index_n', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('196', '区服统计', '14', 'server_turnover', 'index_n', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('201', '营销KPI统计', '59', 'vip', 'sell_statistic_month_list_1', '1', '8', 'vip/sell_statistic_month_list.html?data_type=2', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('202', 'VIP福利记录', '59', 'vipwelfare', 'vip_user_welfare_list', '1', '6', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('206', 'VIP生日管理', '59', 'vip', 'vip_user_other_info_list', '1', '5', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('207', '营销单笔报表', '59', 'vip', 'statistic_swo_product_amount_area', '1', '8', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('208', '返利道具配置', '59', 'vipwelfare', 'rebate_config', '1', '4', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('217', '会话类型分析', '100', 'qc_statistic', 'conversation_type', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('219', '扣分原因分析', '100', 'qc_statistic', 'qc_score_dec', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('220', '质检统计-客服', '100', 'qc_statistic', 'qc_kf', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('224', 'VIP自助登记', '59', 'vip', 'vip_user_input_info', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('229', '营销阶段统计', '59', 'vipdatareport', 'marketing_stage_report', '1', '9', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('230', '质检统计-质检员', '100', 'qc_statistic', 'qc_admin', '1', '0', 'qc_statistic/qc_kf.html?search_type=admin', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('235', '销售阶段报表', '59', 'vipdatareport', 'marketing_stage_report_recharge', '1', '10', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('239', '营销时段统计', '59', 'vipdatareport', 'marketing_stage_report_time', '1', '10', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('243', '新用户分析', '59', 'vip', 'vip_kf_day_statistic_list', '1', '1', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('245', '日入驻率统计', '14', 'settled', 'index_n', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('246', '日入驻LTV数据', '14', 'settled', 'ltv_index_n', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('247', '内部账号管理', '0', '', '', '1', '9', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('249', '账号查询', '247', 'account_registration', 'index_n', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('256', '营销产品月统计', '59', 'vip', 'vip_kf_month_product_statistic_list', '1', '7', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('259', '脚本工具', '117', 'test', 'ajax_link', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('261', '质检申诉记录', '100', 'qc', 'appeal_log', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('262', '工单系统管理', '0', '', '', '1', '6', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('263', '工单统计', '262', 'work_sheet', 'index', '1', '1', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('264', '工单类型配置', '262', 'work_sheet', 'type_list', '1', '2', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('265', '工单管理', '262', 'work_sheet', 'list', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('266', '跟进记录', '262', 'work_sheet', 'log_list', '1', '3', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('267', '工单类型配置列表', '262', 'work_sheet', 'type_input_list', '1', '4', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('291', '游戏直冲', '0', 'transfer_accounts', 'index_n', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('292', '人工申请列表', '291', 'transfer_accounts', 'index_n', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('299', '封禁统计', '13', 'count_block', 'index_n', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('306', '流失用户预警', '59', 'vip', 'vip_wash_user_list', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('310', '实时聊天监控-el', '13', 'elastic_search_chat', 'index_n', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('312', '添加', '10', 'Sysmanage', 'menuAdd', '1', '2', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('313', '修改', '10', 'Sysmanage', 'menuEdit', '1', '3', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('314', '删除', '10', 'Sysmanage', 'menuDel', '1', '4', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('316', '列表', '10', 'Sysmanage', 'menuList', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('317', '列表', '121', 'Sysmanage', 'platformList', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('318', '详情', '121', 'Sysmanage', 'platformDetail', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('319', '添加', '121', 'Sysmanage', 'platformAdd', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('320', '修改', '121', 'Sysmanage', 'platformEdit', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('321', '删除', '121', 'Sysmanage', 'platformDel', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('322', '列表', '123', 'Sysmanage', 'userGroupList', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('323', '详情', '123', 'Sysmanage', 'userGroupDetail', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('324', '添加', '123', 'Sysmanage', 'userGroupAdd', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('325', '修改', '123', 'Sysmanage', 'userGroupEdit', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('326', '用户详情', '123', 'Sysmanage', 'getAdminIdsByGroupId', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('327', '用户详情修改', '123', 'Sysmanage', 'setAdminIdsByGroupId', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('328', '列表', '12', 'User', 'list', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('329', '详情', '12', 'User', 'detail', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('330', '添加', '12', 'User', 'add', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('331', '修改', '12', 'User', 'edit', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('332', '列表', '11', 'Sysmanage', 'roleList', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('333', '详情', '11', 'Sysmanage', 'roleDetail', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('334', '添加', '11', 'Sysmanage', 'roleAdd', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('335', '修改', '11', 'Sysmanage', 'roleEdit', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('336', '授权', '11', 'Sysmanage', 'roleSetPermission', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('337', '删除', '11', 'Sysmanage', 'roleDel', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('338', '会话详情', '101', 'Qc', 'detail', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('339', '质检', '106', 'Qc', 'logDetail', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('340', '设置会话更新', '101', 'Qc', 'setConversationNeedUpdate', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('341', '取消分配', '101', 'Qc', 'delAdminIds', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('342', '分配', '101', 'Qc', 'addAdminIds', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('343', '列表', '106', 'Qc', 'logList', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('344', '添加', '106', 'Qc', 'logAdd', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('345', '修改', '106', 'Qc', 'logEdit', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('346', '删除', '106', 'Qc', 'logDel', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('347', '申诉', '106', 'Qc', 'appeal', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('348', '申诉一审', '106', 'Qc', 'doAppealFirst', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('349', '申诉二审', '106', 'Qc', 'doAppealSecond', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('350', '列表', '143', 'Admin', 'adminLogList', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('351', '列表', '101', 'Qc', 'list', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('352', '详情', '104', 'Qc', 'qcConfigList', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('353', '保存', '104', 'Qc', 'configSave', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('354', '复制', '10', 'Sysmanage', 'menuCopyDetail', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('360', '列表', '261', 'Qc', 'appealLogList', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('361', '列表', '230', 'QcStatistic', 'qcAdmin', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('362', '列表', '220', 'QcStatistic', 'qcKf', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('363', '列表', '219', 'QcStatistic', 'qcScoreDec', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('364', '列表', '217', 'QcStatistic', 'conversationType', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('365', '导入', '189', 'Qc', 'questionLeadingIn', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('366', '列表', '188', 'Vip', 'userRechargeList', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('367', '导出', '188', 'Vip', 'userRechargeExcel', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('368', '列表', '224', 'Vip', 'userInputInfoList', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('369', '详情', '224', 'Vip', 'userInputInfoDetail', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('370', '添加', '224', 'Vip', 'userInputInfoAdd', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('371', '修改', '224', 'Vip', 'userInputInfoEdit', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('372', '删除', '224', 'Vip', 'userInputInfoDelete', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('373', '列表', '306', 'Vip', 'washUserList', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('374', '导出', '306', 'Vip', 'washUserListExcel', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('375', '公共接口', '0', '', '', '2', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('376', 'Vip', '375', 'c', 'a', '1', '0', '', '1');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('377', '手机号解密', '376', 'Vip', 'decryptMobile', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('378', '列表', '130', 'Vip', 'vipUserInfoList', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('379', '导出', '130', 'Vip', 'vipUserInfoListExcel', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('380', '分配用户', '130', 'Vip', 'vipDistribution', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('381', '剔除分配用户', '130', 'Vip', 'vipDistributionDelete', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('382', '录入资料-详情', '130', 'Vip', 'vipUserOtherInfoDetail', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('383', '录入资料-添加', '130', 'Vip', 'vipUserOtherInfoAdd', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('384', '录入资料-修改', '130', 'Vip', 'vipUserOtherInfoEdit', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('385', '列表', '243', 'VipStatistic', 'kfDayStatisticList', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('386', '列表', '190', 'VipStatistic', 'userRechargeList', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('387', '导出', '190', 'VipStatistic', 'userRechargeListExcel', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('389', '返利-列表', '190', 'Vip', 'propRebateList', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('390', '返利-审核', '190', 'Vip', 'propRebateExamine', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('391', '返利-申请审核', '190', 'Vip', 'propRebateApply', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('392', '返利-详情', '190', 'Vip', 'propRebateDetail', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('393', '返利-添加', '190', 'Vip', 'propRebateAdd', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('394', '列表', '208', 'VipConfig', 'rebateConfigList', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('395', '导入', '208', 'VipConfig', 'rebateConfigImport', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('396', '删除', '208', 'VipConfig', 'batchDeleteRebate', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('397', '列表', '132', 'VipConfig', 'becomeVipStandardList', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('398', '详情', '132', 'VipConfig', 'becomeVipStandardDetail', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('399', '添加', '132', 'VipConfig', 'becomeVipStandardAdd', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('400', '修改', '132', 'VipConfig', 'becomeVipStandardEdit', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('401', '列表', '135', 'VipConfig', 'serverManageList', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('402', '详情', '135', 'VipConfig', 'serverManageDetail', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('403', '添加', '135', 'VipConfig', 'serverManageAdd', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('404', '修改', '135', 'VipConfig', 'serverManageEdit', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('405', '删除', '135', 'VipConfig', 'serverManageDelete', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('406', '批量转移', '135', 'VipConfig', 'serverManageChangeAdmin', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('407', '关联分配区服数据列表', '135', 'VipConfig', 'adminGameServerList', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('408', '月kpi列表', '139', 'VipConfig', 'getKpiConfigList', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('409', '获取kpi设置', '139', 'VipConfig', 'getKpiConfigByMonth', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('410', '设置用户kpi', '139', 'VipConfig', 'setUsersKpi', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('411', '列表', '140', 'VipConfig', 'sellerCommissionConfigList', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('412', '详情', '140', 'VipConfig', 'sellerCommissionConfigDetail', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('413', '添加', '140', 'VipConfig', 'sellerCommissionConfigAdd', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('414', '修改', '140', 'VipConfig', 'sellerCommissionConfigEdit', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('415', '删除', '140', 'VipConfig', 'sellerCommissionConfigDel', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('416', '列表', '206', 'Vip', 'userOtherInfoList', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('417', '添加福利', '206', 'Vip', 'userWelfareAdd', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('418', '列表', '202', 'Vip', 'userWelfareList', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('419', '详情', '202', 'Vip', 'userWelfareDetail', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('420', '添加', '202', 'Vip', 'userWelfareAdd', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('421', '修改', '202', 'Vip', 'userWelfareEdit', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('422', '列表', '174', 'Vip', 'sellWorkOrderList', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('423', '详情', '174', 'Vip', 'sellWorkOrderDetail', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('424', '添加', '174', 'Vip', 'sellWorkOrderAdd', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('425', '修改', '174', 'Vip', 'sellWorkOrderEdit', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('426', '一审', '174', 'Vip', 'sellWorkOrderQcFirst', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('427', '二审', '174', 'Vip', 'sellWorkOrderQcSecond', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('428', '营销-详情', '130', 'Vip', 'sellWorkOrderInfo', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('429', '导出', '174', 'Vip', 'sellWorkOrderListExcel', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('430', '列表', '161', 'VipStatistic', 'sellStatisticMonthList', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('431', '导出', '161', 'VipStatistic', 'sellStatisticMonthListExcel', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('432', '详情', '161', 'VipStatistic', 'sellStatisticMonthDetail', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('433', '列表', '256', 'VipStatistic', 'kfMonthProductStatisticList', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('434', '列表', '207', 'VipStatistic', 'statisticSwoProductAmountArea', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('435', '营销汇总', '229', 'VipStatistic', 'marketingStageReport', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('436', '个人汇总', '229', 'VipStatistic', 'marketingStageReportPersonal', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('437', '营销汇总', '235', 'VipStatistic', 'marketingStageReportRecharge', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('438', '个人汇总', '235', 'VipStatistic', 'marketingStageReportRechargePersonal', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('439', '营销汇总', '239', 'VipStatistic', 'marketingStageReportTime', '1', '0', '', '2');
INSERT INTO `gr_chat`.`gr_menu_new` (`menu_id`, `menu_name`, `p_id`, `controller`, `action`, `is_show`, `sort`, `url`, `type`) VALUES ('440', '个人汇总', '239', 'VipStatistic', 'marketingStageReportTime', '1', '0', '', '2');
