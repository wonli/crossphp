CREATE TABLE IF NOT EXISTS `cpa_acl_menu` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pid` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `type` tinyint(4) UNSIGNED NOT NULL DEFAULT 1 COMMENT '0,系统 1,用户',
  `name` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `link` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `display` tinyint(4) UNSIGNED NOT NULL DEFAULT 1 COMMENT '0,不显示 1,显示',
  `order` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

DELETE FROM `cpa_acl_menu`;
INSERT INTO `cpa_acl_menu` (`id`, `pid`, `type`, `name`, `link`, `display`, `order`) VALUES
	(1, 0, 1, '权限', 'acl', 1, 980),
	(2, 0, 1, 'admin', 'admin', 0, 0),
	(3, 0, 1, 'main', 'main', 0, 0),
	(4, 0, 1, '面板', 'panel', 1, 0),
	(5, 0, 1, '安全', 'security', 1, 970),
	(6, 1, 1, '', 'index', 0, 0),
	(7, 1, 1, '', 'editMenu', 0, 0),
	(8, 1, 1, '导航菜单', 'navManager', 1, 0),
	(9, 1, 1, '', 'del', 0, 0),
	(10, 1, 1, '添加角色', 'addRole', 1, 0),
	(11, 1, 1, '角色管理', 'roleList', 1, 0),
	(12, 1, 1, '', 'editRole', 0, 0),
	(13, 1, 1, '', 'delRole', 0, 0),
	(14, 1, 1, '后台管理员', 'user', 1, 0),
	(15, 1, 1, '', 'delUser', 0, 0),
	(16, 4, 1, '主页', 'index', 1, 0),
	(17, 5, 1, '', 'index', 0, 0),
	(18, 5, 1, '密保卡', 'securityCard', 1, 1),
	(19, 5, 1, '更改密码', 'changePassword', 1, 10),
	(20, 5, 1, '', 'create', 0, 0),
	(22, 1, 1, '', 'userSecurityCard', 0, 0),
	(23, 5, 1, '个人信息', 'profile', 1, 5),
	(24, 0, 1, '文档', 'doc', 1, 990),
	(25, 24, 1, '', 'index', 0, 0),
	(26, 24, 1, '', 'codeSegment', 0, 0),
	(27, 24, 1, '', 'changeApiServer', 0, 0),
	(28, 24, 1, '', 'saveCommonParams', 0, 0),
	(29, 24, 1, '', 'setting', 0, 0),
	(30, 24, 1, '', 'action', 0, 0),
	(31, 24, 1, '', 'makeDevServerNode', 0, 0),
	(32, 24, 1, '', 'makeParamsNode', 0, 0),
	(33, 24, 1, '', 'initApiData', 0, 0);

CREATE TABLE IF NOT EXISTS `cpa_acl_role` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '角色名称',
  `behavior` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '允许的行为',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '角色管理' ROW_FORMAT = Dynamic;

DELETE FROM `cpa_acl_role`;
INSERT INTO `cpa_acl_role` (`id`, `name`, `behavior`) VALUES
	(1, '默认用户', '2,3,4,16,5,17,18,19,23');

CREATE TABLE IF NOT EXISTS `cpa_act_log` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `type` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `controller` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `action` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `params` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `date` timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0) ON UPDATE CURRENT_TIMESTAMP(0),
  `ip` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `name`(`name`) USING BTREE,
  INDEX `type`(`type`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

DELETE FROM `cpa_act_log`;

CREATE TABLE IF NOT EXISTS `cpa_admin` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `nickname` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '对外显示名称',
  `real_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '真实姓名',
  `avatar` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '头像',
  `cellphone` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '手机号',
  `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '授权访问时身份验证令牌',
  `theme` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '主题风格名称',
  `salt` char(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `usc` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否允许解绑密保卡',
  `last_login_date` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0),
  `last_login_ip` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `rid` int(11) UNSIGNED NOT NULL DEFAULT 1 COMMENT '角色ID',
  `t` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '0,禁用 1,正常',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

DELETE FROM `cpa_admin`;
INSERT INTO `cpa_admin` (`id`, `name`, `nickname`, `real_name`, `avatar`, `cellphone`, `password`, `token`, `theme`, `salt`, `usc`, `last_login_date`, `last_login_ip`, `rid`, `t`) VALUES
	(1, 'admin', 'admin', 'admin', '', '13800138000', '5f77498804fde517ba653162490cc4e5ca204779754f974078e35d3407b32bce', 'B16um0dnBF4qqy0DqZ0eBuuyBFA3d080', 'skin-black', '1234567887654321', 1, '2018-01-16 14:59:26', '127.0.0.1', 0, 1);

CREATE TABLE IF NOT EXISTS `cpa_security_card` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `card_data` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `bind_user` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `ext_time` tinyint(4) NOT NULL DEFAULT 0 COMMENT '已过期,-1',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `bind_user`(`bind_user`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

DELETE FROM `cpa_security_card`;

CREATE TABLE IF NOT EXISTS `cpa_doc` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `doc_token` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `servers` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `role_limit` varchar(128) NOT NULL DEFAULT '' COMMENT '限制查看角色ID逗号分隔',
  `global_params` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `header_params` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `last_update_admin` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `last_update_time` timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0) ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `cpa_doc_user` (
  `id` INT(10) NOT NULL AUTO_INCREMENT,
  `u` VARCHAR(32) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `doc_id` INT(10) NOT NULL DEFAULT '0',
  `name` VARCHAR(128) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `value` TEXT NOT NULL COLLATE 'utf8_general_ci',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `u` (`u`) USING BTREE,
  INDEX `name` (`name`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `cpa_doc_data`  (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `doc_id` INT(10) UNSIGNED NOT NULL,
  `enable_mock` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0,关闭mock 1,开启mock',
  `global_params` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '全局参数是否生效',
  `group_key` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '分类（类名）' COLLATE 'utf8_general_ci',
  `group_name` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '分类名称' COLLATE 'utf8_general_ci',
  `api_path` VARCHAR(128) NOT NULL COLLATE 'utf8_general_ci',
  `api_name` VARCHAR(64) NOT NULL COMMENT '接口名称' COLLATE 'utf8_general_ci',
  `api_params` TEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
  `api_method` VARCHAR(8) NOT NULL COLLATE 'utf8_general_ci',
  `api_response_struct` LONGTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
  `mock_response_data` LONGTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
  `update_user` VARCHAR(50) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
  `update_at` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `doc_api_path_uindex` (`doc_id`, `api_path`) USING BTREE,
  INDEX `group_key` (`group_key`) USING BTREE
  INDEX `group_name` (`group_name`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;