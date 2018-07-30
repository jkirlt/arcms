/*
Navicat MySQL Data Transfer

Source Server         : homestand
Source Server Version : 50719
Source Host           : 127.0.0.1:33060
Source Database       : arcms

Target Server Type    : MYSQL
Target Server Version : 50719
File Encoding         : 65001

Date: 2018-05-02 10:21:03
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for nav
-- ----------------------------
DROP TABLE IF EXISTS `nav`;
CREATE TABLE `nav` (
  `nav_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '菜单表id',
  `title` varchar(255) DEFAULT NULL COMMENT '标题',
  `icon` varchar(255) DEFAULT NULL COMMENT '图标',
  `href` varchar(255) DEFAULT NULL COMMENT '链接',
  `spread` int(11) DEFAULT '0' COMMENT 'spread 0 false 1 true',
  `target` varchar(255) CHARACTER SET latin1 DEFAULT NULL COMMENT '是否打开新页面 _blank代表是',
  `cate` int(11) DEFAULT '1' COMMENT '分类 1为一级菜单 2为二级菜单 3为三级菜单',
  `fid` int(11) DEFAULT '0' COMMENT '父级id 0为一级分类',
  `children_code` int(11) DEFAULT '0' COMMENT '是否存在子级菜单 0为否 1为是',
  PRIMARY KEY (`nav_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of nav
-- ----------------------------
INSERT INTO `nav` VALUES ('1', '内容管理', '&#xe63c;', 'contentManagement', '0', '', '1', '0', '1');
INSERT INTO `nav` VALUES ('2', '用户中心', '&#xe613;', 'memberCenter', '0', '', '1', '0', '1');
INSERT INTO `nav` VALUES ('3', '系统设置', '&#xe620;', 'systemeSttings', '0', '', '1', '0', '1');
INSERT INTO `nav` VALUES ('4', '使用文档', '&#xe705;', 'seraphApi', '0', '', '1', '0', '1');
INSERT INTO `nav` VALUES ('5', '文章列表', 'icon-text', 'news/newsList', '0', '', '2', '1', '0');
INSERT INTO `nav` VALUES ('6', '图片管理', '&#xe634;', 'img/images', '0', '', '2', '1', '0');
INSERT INTO `nav` VALUES ('7', '其他页面', '&#xe630;', null, '0', '', '2', '1', '1');
INSERT INTO `nav` VALUES ('8', '404页面', '&#xe61c;', 'index/p404', '0', '', '3', '7', '0');
INSERT INTO `nav` VALUES ('9', '登录', '&#xe609;', 'login/login', '0', '_blank', '3', '7', '0');
INSERT INTO `nav` VALUES ('10', '用户中心', '&#xe612;', 'user/userList', '0', '', '2', '2', '0');
INSERT INTO `nav` VALUES ('11', '会员等级', 'icon-vip', 'user/userGrade', '0', '', '2', '2', '0');
INSERT INTO `nav` VALUES ('12', '系统基本参数', '&#xe631;', 'systemSetting/basicParameter', '0', '', '2', '3', '0');
INSERT INTO `nav` VALUES ('13', '系统日志', 'icon-log', 'systemSetting/logs', '0', '', '2', '3', '0');
INSERT INTO `nav` VALUES ('14', '友情链接', '&#xe64c;', 'systemSetting/linkList', '0', '', '2', '3', '0');
INSERT INTO `nav` VALUES ('15', '图标管理', '&#xe857;', 'systemSetting/icons', '0', '', '2', '3', '0');
INSERT INTO `nav` VALUES ('16', '三级联动模块', 'icon-mokuai', 'doc/addressDoc', '0', '', '2', '4', '0');
INSERT INTO `nav` VALUES ('17', 'bodyTab模块', 'icon-mokuai', 'doc/bodyTabDoc', '0', '', '2', '4', '0');
INSERT INTO `nav` VALUES ('18', '三级菜单', 'icon-mokuai', 'doc/navDoc', '0', '', '2', '4', '0');
INSERT INTO `nav` VALUES ('19', '菜单列表', 'icon-mokuai', 'systemSetting/menuList', '0', null, '2', '3', '0');

-- ----------------------------
-- Table structure for news
-- ----------------------------
DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
  `newsId` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `newsName` varchar(255) NOT NULL,
  `newsAuthor` varchar(255) NOT NULL,
  `abstract` varchar(255) NOT NULL,
  `newsStatus` tinyint(1) NOT NULL,
  `newsImg` varchar(255) NOT NULL,
  `newsLook` varchar(255) NOT NULL,
  `newsTop` tinyint(1) NOT NULL,
  `newsTime` varchar(45) NOT NULL,
  `content` mediumtext NOT NULL,
  PRIMARY KEY (`newsId`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of news
-- ----------------------------
INSERT INTO `news` VALUES ('16', '谈谈未来二十年', '阿。sir', '', '0', '/arphp-layuicms/arcms/upload/main/20180319/b278c7eb70b12fbf5750dfe48de82149.png', '', '0', '2018-03-19 16:14:21', '未来二十年我想公司应该上市了<img src=\\\"http://localhost/arphp-layuicms/arcms/assets/layui/images/face/50.gif\\\" alt=\\\"[熊猫]\\\">，&nbsp; &nbsp;“喂喂，该醒醒了”， “奥，公交又坐过站了”');
INSERT INTO `news` VALUES ('17', '如果你想了解学习一门新的框架', '阿。sir', '', '0', '', '', '0', '2018-03-19 16:13:20', '那么arphp将非常适合你');
INSERT INTO `news` VALUES ('18', '比特币遭遇跳崖式暴跌，来看黑客导演的这场烧脑大片（全解读）', '阿。sir', '', '0', '', '', '1', '2018-03-19 16:12:40', '<p><span>想着今天早上起来，应该是关于3.8妇女节的各种文案刷屏，却又一次被比特币抢了头条，比特币一夜暴跌10%，再次跌破10000美元，而几乎在昨天深夜同一时间全球第二大虚拟货币交易平台币安（Binance）疑似遭受黑客攻击，而这剧情始末，更像是黑客主导的一场烧脑大片。</span></p>');
INSERT INTO `news` VALUES ('19', '315黑名单之夜，信息安全谁能逃过此劫？', '阿。sir', '', '0', '', '', '0', '2018-03-19 16:12:16', '<p><span>今年315晚会的主题是“品质消费，美好生活”，唤醒消费者权益意识，规范市场秩序依然是不变的宗旨。</span><span>互联网已经深入人们的日常生活，与之相关的安全信息领域也逐渐成为315晚会重点关注对象。</span><span>2017年的315晚会上，互动百科虚假广告、科视公司收集学生信息以及不安全的密码等消费预警。由此也可以发现，普通消费者对于个人隐私、信息保护等方面的安全意识比较薄弱，2017年依旧衍生了不少风波，又会有哪些案例或者公司成为315重点照顾对象呢？</span></p>');
INSERT INTO `news` VALUES ('20', 'PHP+MySQL，语言优势及特点', '阿。sir', '', '1', '', '', '1', '2018-04-23 15:08:29', '<ul><li><span>PHP+MySQL，LAMP模式</span></li></ul><p align=\\\"left\\\" style=\\\"text-align: justify;\\\">　　采用PHP+MySQL进行开发，基于Web开发的最佳组合“LAMP模式”——（Linux 操作系统、Apache网络服务器、MySQL 数据库、PHP语言）。</p><ul><li><span>技术成熟、开发迅速</span></li></ul><p style=\\\"text-align: justify;\\\">PHP+MySQL是目前最为成熟、稳定、安全的企业级WEB开发技术，广泛应用于超大型站点（百度前端使用PHP，可输入:www.baidu.com/index.php）。其成熟的架构、稳定的性能、嵌入式开发方式、简洁的语法，使得系统能迅速开发。</p><ul><li><span>高效执行、安全可靠</span></li></ul><p style=\\\"text-align: justify;\\\">　　PHP结合MySQL运行于Linux平台，执行效率相对其他语言更高；安全性较NT（Windows）平台更强。PHP在安全性的性能表现不俗，帐号、密码以MD5数据加密技术的采用，确保数据帐号信息安全。关键数据采用多层加密技术，有效保证数据安全。</p><ul><li><span>跨平台移植、无附件成本</span></li></ul><p style=\\\"text-align: justify;\\\">PHP+MySQL可跨UNIX、Linux、Windows NT等平台运行，降低了系统及数据迁移的风险。如果架设Linux / UNIX服务器，可节约操作系统费用，降低了软件成本。</p><h1><a name=\\\"t2\\\"></a><br></h1>');
INSERT INTO `news` VALUES ('21', '人为什么要学习???', '阿。sir', '', '0', '', '', '1', '2018-04-27 13:00:42', '大概人只有不断的学习，才能避免掉很多坑吧');
INSERT INTO `news` VALUES ('22', '请不要随便删除之前有的文章，谢谢', '阿。sir', '', '1', '', '', '1', '2018-04-23 15:09:48', '不要删除我 thanks<img src=\\\"http://localhost/arphp-layuicms/arcms/assets/layui/images/face/14.gif\\\" alt=\\\"[亲亲]\\\">');

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL COMMENT '用户名',
  `password` varchar(255) NOT NULL COMMENT '密码',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES ('0', 'admin', 'baab42a126a685b39b19a791195b7a66');
