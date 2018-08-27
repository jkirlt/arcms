/*
Navicat MySQL Data Transfer

Source Server         : CDC
Source Server Version : 50540
Source Host           : 192.168.2.129:3306
Source Database       : value

Target Server Type    : MYSQL
Target Server Version : 50540
File Encoding         : 65001

Date: 2018-07-30 11:19:06
*/

-- ----------------------------
-- Table structure for coopadmin_model_detail
-- ----------------------------
DROP TABLE IF EXISTS `coopadmin_model_detail`;
CREATE TABLE `coopadmin_model_detail` (
  `id` int(255) unsigned NOT NULL AUTO_INCREMENT,
  `tablename` varchar(255) NOT NULL COMMENT '数据表名字，也是模型名字',
  `colname` varchar(255) NOT NULL COMMENT '字段的英文名称',
  `explain` varchar(255) NOT NULL COMMENT '字段说明',
  `isshow` int(255) NOT NULL COMMENT '是否显示',
  `isedit` int(255) NOT NULL DEFAULT '1' COMMENT '是否可以编辑，1可编辑，0不可编辑',
  `ordernum` int(255) NOT NULL DEFAULT '1' COMMENT '排序，值越大，就显示在越前面',
  `isunique` int(11) DEFAULT NULL,
  `type` int(11) DEFAULT '0' COMMENT '字段类型 0为字符串 1为多个状态值 2为两个状态值 3为文章 4为图片 5为时间戳 6为整数 7为浮点数 8为外键',
  `typeexplain` varchar(255) DEFAULT NULL COMMENT '字段类型说明',
  `issort` int(11) DEFAULT '0' COMMENT '是否支持排序 0否 1是',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=982 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of coopadmin_model_detail
-- ----------------------------

-- ----------------------------
-- Table structure for coopadmin_model_fk
-- ----------------------------
DROP TABLE IF EXISTS `coopadmin_model_fk`;
CREATE TABLE `coopadmin_model_fk` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '模型外键关联表id',
  `mid` int(11) DEFAULT NULL COMMENT '主表id',
  `mtablename` varchar(255) DEFAULT NULL COMMENT '主表表名',
  `mmodelname` varchar(255) DEFAULT NULL COMMENT '主表模型名',
  `mcolname` varchar(255) DEFAULT NULL COMMENT '主表字段英文名',
  `mexplain` varchar(255) DEFAULT NULL COMMENT '主表字段说明',
  `fid` int(11) DEFAULT NULL COMMENT '关联表id',
  `ftablename` varchar(255) DEFAULT NULL COMMENT '关联表表名',
  `fmodelname` varchar(255) DEFAULT NULL COMMENT '关联表模型名',
  `funival` varchar(255) DEFAULT NULL COMMENT '关联表映射字段名称',
  `fcolname` varchar(255) DEFAULT NULL COMMENT '关联表字段英文名',
  `fexplain` varchar(255) DEFAULT NULL COMMENT '关联表字段说明',
  `updatetime` int(11) DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of coopadmin_model_fk
-- ----------------------------


-- ----------------------------
-- Table structure for coopadmin_model_menufunc
-- ----------------------------
DROP TABLE IF EXISTS `coopadmin_model_menufunc`;
CREATE TABLE `coopadmin_model_menufunc` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '模型菜单自定义功能表id',
  `mid` int(11) DEFAULT NULL COMMENT '模型id',
  `menuid` int(11) DEFAULT NULL COMMENT '菜单id',
  `title` varchar(255) DEFAULT NULL COMMENT '功能按钮名称',
  `name` varchar(255) DEFAULT NULL COMMENT '功能代号(英文)',
  `apiaddr` varchar(255) DEFAULT NULL COMMENT '接口地址',
  `status` int(11) DEFAULT '1' COMMENT '状态 0关闭 1为开启',
  `updatetime` int(11) DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of coopadmin_model_menufunc
-- ----------------------------


-- ----------------------------
-- Table structure for coopadmin_modellist
-- ----------------------------
DROP TABLE IF EXISTS `coopadmin_modellist`;
CREATE TABLE `coopadmin_modellist` (
  `id` int(255) NOT NULL AUTO_INCREMENT COMMENT '主键,，模型表',
  `modelname` varchar(255) NOT NULL COMMENT '模型名字',
  `tablename` varchar(255) NOT NULL COMMENT '模型对应数据表的名称',
  `explain` varchar(255) DEFAULT NULL COMMENT '说明',
  `status` int(11) DEFAULT '0' COMMENT '是否存在 0为否 1为是',
  `menu` int(11) DEFAULT '0' COMMENT '是否存在菜单 0为不存在 1为存在',
  `isadd` int(11) DEFAULT '1' COMMENT '允许添加 0为不允许 1为允许',
  `isedit` int(11) DEFAULT '1' COMMENT '允许编辑 0为不允许 1为允许',
  `isdel` int(11) DEFAULT '1' COMMENT '允许删除 0为不允许 1为允许',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of coopadmin_modellist
-- ----------------------------

-- ----------------------------
-- Table structure for coopadmin_nav
-- ----------------------------
DROP TABLE IF EXISTS `coopadmin_nav`;
CREATE TABLE `coopadmin_nav` (
  `nav_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '菜单表id',
  `title` varchar(255) DEFAULT NULL COMMENT '标题',
  `icon` varchar(255) DEFAULT NULL COMMENT '图标',
  `href` varchar(255) DEFAULT NULL COMMENT '链接',
  `spread` int(11) DEFAULT '0' COMMENT 'spread 0 false 1 true',
  `target` varchar(255) CHARACTER SET latin1 DEFAULT NULL COMMENT '是否打开新页面 _blank代表是',
  `cate` int(11) DEFAULT '1' COMMENT '分类 1为一级菜单 2为二级菜单 3为三级菜单',
  `fid` int(11) DEFAULT '0' COMMENT '父级id 0为一级分类',
  `children_code` int(11) DEFAULT '0' COMMENT '是否存在子级菜单 0为否 1为是',
  `status` int(11) DEFAULT '1' COMMENT '是否有效 1有效 0无效',
  `issystem` int(11) DEFAULT '0' COMMENT '是否系统菜单 0为否 1为是',
  `modeid` varchar(45) DEFAULT NULL COMMENT '关联模型id',
  `num` int(11) DEFAULT NULL COMMENT '排序',
  PRIMARY KEY (`nav_id`)
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of coopadmin_nav
-- ----------------------------
LOCK TABLES `coopadmin_nav` WRITE;
INSERT INTO `coopadmin_nav` VALUES ('1', '权限设置', '&#xe613;', 'memberCenter', '0', null, '1', '0', '1', '1', '1', null, null);
INSERT INTO `coopadmin_nav` VALUES ('2', '系统设置', '&#xe620;', 'systemeSttings', '0', null, '1', '0', '1', '1', '1', null, null);
INSERT INTO `coopadmin_nav` VALUES ('3', '系统用户列表', '&#xe612;', 'users/userList', '0', null, '2', '1', '0', '1', '1', null, null);
INSERT INTO `coopadmin_nav` VALUES ('4', '系统用户角色', '&#xe770;', 'users/userGrade', '0', null, '2', '1', '0', '1', '1', null, null);
INSERT INTO `coopadmin_nav` VALUES ('5', '菜单列表', '&#xe62a;', 'systems/menuList', '0', null, '2', '2', '0', '1', '1', null, null);
INSERT INTO `coopadmin_nav` VALUES ('6', '数据库表', '&#xe62d;', 'systems/tableList', '0', null, '2', '2', '0', '1', '1', null, null);
INSERT INTO `coopadmin_nav` VALUES ('7', '模型表', '&#xe638;', 'systems/modelList', '0', null, '2', '2', '0', '1', '1', null, null);
INSERT INTO `coopadmin_nav` VALUES ('8', '参数设置', '&#xe614;', 'systems/setSystem', '0', null, '2', '2', '0', '1', '1', null, null);
UNLOCK TABLES;

-- ----------------------------
-- Table structure for coopadmin_role
-- ----------------------------
DROP TABLE IF EXISTS `coopadmin_role`;
CREATE TABLE `coopadmin_role` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '角色表id',
  `name` varchar(255) DEFAULT NULL COMMENT '角色名称',
  `status` int(10) DEFAULT '1' COMMENT '状态 1为可以删除 0为不能删除',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of coopadmin_role
-- ----------------------------
LOCK TABLES `coopadmin_role` WRITE;
INSERT INTO `coopadmin_role` VALUES ('1', '超级管理员', '0');
INSERT INTO `coopadmin_role` VALUES ('2', '普通管理员', '0');
INSERT INTO `coopadmin_role` VALUES ('3', '普通用户', '0');
UNLOCK TABLES;

-- ----------------------------
-- Table structure for coopadmin_role_nav
-- ----------------------------
DROP TABLE IF EXISTS `coopadmin_role_nav`;
CREATE TABLE `coopadmin_role_nav` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '角色权限关系表id',
  `role_id` int(11) DEFAULT NULL COMMENT '对应role表id字段',
  `nav_id` int(11) DEFAULT NULL COMMENT '对应nav表nav_id字段',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=132 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of coopadmin_role_nav
-- ----------------------------
LOCK TABLES `coopadmin_role_nav` WRITE;
INSERT INTO `coopadmin_role_nav` VALUES ('1', '1', '3');
INSERT INTO `coopadmin_role_nav` VALUES ('2', '1', '4');
INSERT INTO `coopadmin_role_nav` VALUES ('3', '1', '5');
INSERT INTO `coopadmin_role_nav` VALUES ('4', '1', '6');
INSERT INTO `coopadmin_role_nav` VALUES ('5', '1', '7');
INSERT INTO `coopadmin_role_nav` VALUES ('6', '1', '8');
UNLOCK TABLES;

-- ----------------------------
-- Table structure for coopadmin_system_setting
-- ----------------------------
DROP TABLE IF EXISTS `coopadmin_system_setting`;
CREATE TABLE `coopadmin_system_setting` (
  `id` int(255) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL COMMENT '系统标题',
  `version` varchar(255) NOT NULL COMMENT '版本号',
  `copyright` varchar(255) NOT NULL COMMENT '版权',
  `author` varchar(255) NOT NULL COMMENT '开发作者',
  `database` varchar(255) NOT NULL COMMENT '数据库版本',
  `logo` varchar(255) DEFAULT NULL COMMENT '网站logo',
  `loginbg` varchar(255) DEFAULT NULL COMMENT '登录背景图',
  `notice` text COMMENT '系统公告',
  `url` varchar(255) DEFAULT NULL COMMENT '网站地址',
  `onelogin` int(11) DEFAULT '0' COMMENT '开启单一登录 1开 0关',
  `settime` int(11) DEFAULT NULL COMMENT '上次修改设定时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of coopadmin_system_setting
-- ----------------------------
LOCK TABLES `coopadmin_system_setting` WRITE;
/*!40000 ALTER TABLE `coopadmin_system_setting` DISABLE KEYS */;
INSERT INTO `coopadmin_system_setting` VALUES (1,'成致后台管理','1.0','成都成达传网络科技有限公司','靠谱云开发官方Team','mysql','https://www.028cdc.cn/icon/1/logo.png','https://www.coopcoder.com/assets/img/home_back.jpg','<b>欢迎使用arcms</b>','https://',0,1528336553);
/*!40000 ALTER TABLE `coopadmin_system_setting` ENABLE KEYS */;
UNLOCK TABLES;

-- ----------------------------
-- Table structure for coopadmin_user
-- ----------------------------
DROP TABLE IF EXISTS `coopadmin_user`;
CREATE TABLE `coopadmin_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL COMMENT '用户名',
  `nickname` varchar(255) DEFAULT NULL COMMENT '昵称',
  `password` varchar(255) NOT NULL COMMENT '密码',
  `realName` varchar(255) DEFAULT NULL COMMENT '真实姓名',
  `userPhone` varchar(20) DEFAULT NULL COMMENT '电话',
  `userEmail` varchar(120) DEFAULT NULL COMMENT '邮箱',
  `myself` varchar(255) DEFAULT NULL COMMENT '自我介绍',
  `userFace` varchar(255) DEFAULT NULL COMMENT '头像',
  `isAdmin` int(11) DEFAULT '0' COMMENT '是否管理员 1是管理员 0不是管理员',
  `status` int(11) DEFAULT '1' COMMENT '用户状态 1有效 0无效',
  `islogin` int(11) DEFAULT '0' COMMENT '开启单一登录后用于判断是否登录 1是 0否',
  `logintime` int(11) DEFAULT NULL COMMENT '登录时间',
  `ip` varchar(255) DEFAULT NULL COMMENT '上次登录ip',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of coopadmin_user
-- ----------------------------
LOCK TABLES `coopadmin_user` WRITE;
/*!40000 ALTER TABLE `coopadmin_user` DISABLE KEYS */;
INSERT INTO `coopadmin_user` VALUES (1,'jkirlt','超级管理员','baab42a126a685b39b19a791195b7a66','','','','','https://www.028cdc.cn/icon/1/logo1.png',1,1,0,1528336537,'');
/*!40000 ALTER TABLE `coopadmin_user` ENABLE KEYS */;
UNLOCK TABLES;

-- ----------------------------
-- Table structure for coopadmin_user_role
-- ----------------------------
DROP TABLE IF EXISTS `coopadmin_user_role`;
CREATE TABLE `coopadmin_user_role` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户角色关系表id',
  `uid` int(11) DEFAULT NULL COMMENT '对应user表id字段',
  `role_id` int(11) DEFAULT NULL COMMENT '对应role表id字段',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=72 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of coopadmin_user_role
-- ----------------------------
LOCK TABLES `coopadmin_user_role` WRITE;
INSERT INTO `coopadmin_user_role` VALUES ('1', '1', '1');
UNLOCK TABLES;



