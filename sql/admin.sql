CREATE TABLE IF NOT EXISTS `cp_acl_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(4) unsigned NOT NULL DEFAULT '1' COMMENT '0,系统 1,用户',
  `name` varchar(128) NOT NULL DEFAULT '',
  `link` varchar(64) NOT NULL DEFAULT '',
  `display` tinyint(4) unsigned NOT NULL DEFAULT '1' COMMENT '0,不显示 1,显示',
  `order` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DELETE FROM `cp_acl_menu`;
INSERT INTO `cp_acl_menu` (`id`, `pid`, `type`, `name`, `link`, `display`, `order`) VALUES
	(1, 0, 1, '权限', 'acl', 1, 990),
	(2, 0, 1, 'admin', 'admin', 0, 0),
	(3, 0, 1, 'main', 'main', 0, 0),
	(4, 0, 1, '面板', 'panel', 1, 0),
	(5, 0, 1, '安全', 'security', 1, 980),
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
	(18, 5, 1, '密保卡', 'securityCard', 1, 0),
	(19, 5, 1, '更改密码', 'changePassword', 1, 0),
	(20, 5, 1, '', 'create', 0, 0);

CREATE TABLE IF NOT EXISTS `cp_acl_role` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '角色名称',
  `behavior` mediumtext COMMENT '允许的行为',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='角色管理';

DELETE FROM `cp_acl_role`;
INSERT INTO `cp_acl_role` (`id`, `name`, `behavior`) VALUES
	(1, '默认用户', '2,3,4,16,5,17,18,19,20');

CREATE TABLE IF NOT EXISTS `cp_admin` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `salt` char(16) NOT NULL,
  `usc` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否允许解绑密保卡',
  `t` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '0,禁用 1,正常',
  `rid` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '角色ID',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DELETE FROM `cp_admin`;
INSERT INTO `cp_admin` (`id`, `name`, `password`, `salt`, `usc`, `t`, `rid`) VALUES
	(1, 'admin', '5f77498804fde517ba653162490cc4e5ca204779754f974078e35d3407b32bce', '1234567887654321', 1, 1, 0);

CREATE TABLE IF NOT EXISTS `cp_security_card` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `card_data` text NOT NULL,
  `bind_user` varchar(255) NOT NULL,
  `ext_time` tinyint(4) NOT NULL DEFAULT '0' COMMENT '已过期,-1',
  PRIMARY KEY (`id`),
  KEY `bind_user` (`bind_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DELETE FROM `cp_security_card`;
