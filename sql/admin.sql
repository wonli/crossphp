CREATE TABLE IF NOT EXISTS `cp_acl_menu` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT '0',
  `type` tinyint(4) DEFAULT '1' COMMENT '0系统,1用户',
  `name` varchar(128) COLLATE utf8_unicode_ci DEFAULT '',
  `link` varchar(64) COLLATE utf8_unicode_ci DEFAULT '',
  `status` tinyint(4) DEFAULT '-1' COMMENT '状态-1:未激活',
  `display` tinyint(4) DEFAULT '1' COMMENT '1:显示,0:不显示',
  `order` int(11) DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DELETE FROM `cp_acl_menu`;
INSERT INTO `cp_acl_menu` (`id`, `pid`, `type`, `name`, `link`, `status`, `display`, `order`) VALUES
	(1, 0, 1, '权限', 'acl', 1, 1, 90),
	(2, 0, 1, 'admin', 'admin', 0, 1, 0),
	(3, 0, 1, 'main', 'main', 0, 1, 0),
	(4, 0, 1, '面板', 'panel', 1, 1, 10),
	(5, 0, 1, '安全', 'security', 1, 1, 80),
	(6, 4, 1, '默认主页', 'index', 1, 1, 0),
	(7, 1, 1, '', 'index', 1, 0, 0),
	(8, 1, 1, '', 'editMenu', 1, 0, 0),
	(9, 1, 1, '导航管理', 'navManager', 1, 1, 10),
	(10, 1, 1, '', 'del', 1, 0, 0),
	(11, 1, 1, '添加角色', 'addRole', 1, 1, 20),
	(12, 1, 1, '角色列表', 'roleList', 1, 1, 30),
	(13, 1, 1, '', 'editRole', 1, 0, 0),
	(14, 1, 1, '用户列表', 'user', 1, 1, 50),
	(15, 5, 1, '', 'index', 1, 0, 0),
	(16, 5, 1, '密保卡预览', 'printSecurityCard', 1, 1, 10),
	(17, 5, 1, '下载密保卡', 'makeSecurityImage', 1, 1, 30),
	(18, 5, 1, '绑定密保卡', 'bind', 1, 1, 20),
	(19, 5, 1, '重置密保卡', 'refresh', 1, 1, 40),
	(20, 5, 1, '密保卡解绑', 'kill', 1, 1, 50),
	(21, 5, 1, '', 'create', 1, 0, 0),
	(22, 5, 1, '更改登录密码', 'changePassword', 1, 1, 60);

CREATE TABLE IF NOT EXISTS `cp_acl_role` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '角色名称',
  `behavior` text COMMENT '允许的行为',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='角色管理';


DELETE FROM `cp_acl_role`;
INSERT INTO `cp_acl_role` (`id`, `name`, `behavior`) VALUES
	(1, '管理员', '2,3,4,6,5,15,16,17,18,19,20,21,22');

CREATE TABLE IF NOT EXISTS `cp_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `t` int(11) NOT NULL DEFAULT '1' COMMENT '状态 1:正常',
  `rid` int(11) NOT NULL DEFAULT '1' COMMENT '角色',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

DELETE FROM `cp_admin`;
INSERT INTO `cp_admin` (`id`, `name`, `password`, `t`, `rid`) VALUES
	(1, 'admin', '1f604490cbdd4ec35cfa681bcf3df8fac26e0cb5', 1, 0);

CREATE TABLE IF NOT EXISTS `cp_security_card` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `card_data` text COLLATE utf8_unicode_ci NOT NULL,
  `bind_user` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ext_time` int(11) NOT NULL DEFAULT '0' COMMENT '已过期,-1',
  PRIMARY KEY (`id`),
  KEY `bind_user` (`bind_user`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DELETE FROM `cp_security_card`;
