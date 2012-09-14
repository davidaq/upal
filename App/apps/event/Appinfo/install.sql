/*
Navicat MySQL Data Transfer
Source Host     : localhost:3306
Source Database : sociax_2_0
Target Host     : localhost:3306
Target Database : sociax_2_0
Date: 2011-01-20 15:16:57
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for ts_event
-- ----------------------------
DROP TABLE IF EXISTS `ts_event`;
CREATE TABLE `ts_event` (
  `id` int(11) NOT NULL auto_increment,
  `uid` int(11) NOT NULL,
  `title` text NOT NULL,
  `explain` text NOT NULL,
  `contact` varchar(32) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `sTime` int(11) default NULL,
  `eTime` int(11) default NULL,
  `address` varchar(255) default NULL,
  `cTime` int(11) NOT NULL,
  `deadline` int(11) NOT NULL,
  `joinCount` int(11) NOT NULL default '0',
  `attentionCount` int(11) NOT NULL default '0',
  `limitCount` int(11) NOT NULL default '0',
  `commentCount` int(11) NOT NULL default '0',
  `coverId` int(11) NOT NULL default '0',
  `optsId` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ts_event_opts
-- ----------------------------
DROP TABLE IF EXISTS `ts_event_opts`;
CREATE TABLE `ts_event_opts` (
  `id` int(11) NOT NULL auto_increment,
  `cost` char(10) NOT NULL default '0',
  `costExplain` varchar(255) default '0',
  `province` char(10) default NULL,
  `city` char(10) default NULL,
  `area` varchar(10) default NULL,
  `opts` varchar(50) NOT NULL default '0',
  `isHot` tinyint(1) NOT NULL default '0',
  `rTime` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ts_event_photo
-- ----------------------------
DROP TABLE IF EXISTS `ts_event_photo`;
CREATE TABLE `ts_event_photo` (
  `id` int(11) NOT NULL auto_increment,
  `eventId` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `filepath` varchar(255) NOT NULL,
  `savename` varchar(255) NOT NULL,
  `aid` int(11) NOT NULL,
  `cTime` int(11) NOT NULL,
  `commentCount` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ts_event_type
-- ----------------------------
DROP TABLE IF EXISTS `ts_event_type`;
CREATE TABLE `ts_event_type` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ts_event_type
-- ----------------------------
INSERT INTO `ts_event_type` VALUES ('1', '音乐/演出');
INSERT INTO `ts_event_type` VALUES ('2', '展览');
INSERT INTO `ts_event_type` VALUES ('3', '电影');
INSERT INTO `ts_event_type` VALUES ('4', '讲座/沙龙');
INSERT INTO `ts_event_type` VALUES ('5', '戏剧/曲艺');
INSERT INTO `ts_event_type` VALUES ('8', '体育');
INSERT INTO `ts_event_type` VALUES ('9', '旅行');
INSERT INTO `ts_event_type` VALUES ('10', '公益');
INSERT INTO `ts_event_type` VALUES ('11', '其它');

-- ----------------------------
-- Table structure for ts_event_user
-- ----------------------------
DROP TABLE IF EXISTS `ts_event_user`;
CREATE TABLE `ts_event_user` (
  `id` int(11) NOT NULL auto_increment,
  `eventId` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `contact` text,
  `action` char(10) NOT NULL default 'attention',
  `status` tinyint(1) NOT NULL default '1',
  `cTime` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

#添加ts_system_data数据
REPLACE INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`)
VALUES
    (0, 'event', 'limitpage', 's:2:"10";', '2011-01-20 15:19:10'),
    (0, 'event', 'canCreate', 's:1:"1";', '2011-01-20 15:19:10'),
    (0, 'event', 'credit', 's:3:"100";', '2011-01-20 15:19:10'),
    (0, 'event', 'credit_type', 's:10:"experience";', '2011-01-20 15:19:10'),
    (0, 'event', 'limittime', 's:2:"24";', '2011-01-20 15:19:10');

#模板数据
DELETE FROM `ts_template` WHERE `type` = 'event';
INSERT INTO `ts_template` (`name`, `alias`, `title`, `body`, `lang`, `type`, `type2`, `is_cache`, `ctime`) 
VALUES
    ('event_create_weibo', '发起活动', '','我发起了一个活动：【{title}】{url}', 'zh', 'event', 'weibo', 0, 1290417734),
    ('event_share_weibo', '分享活动', '', '分享@{author} 的活动:【{title}】 {url}', 'zh',  'event', 'weibo', 0, 1290595552);

# 增加默认积分配置
DELETE FROM `ts_credit_setting` WHERE `type` = 'event';
INSERT INTO `ts_credit_setting` (`id`,`name`, `alias`, `type`, `info`, `score`, `experience`) 
VALUES
    ('', 'add_event', '发起活动', 'event', '{action}{sign}了{score}{typecn}', '10', '10'),
    ('', 'delete_event', '删除活动', 'event', '{action}{sign}了{score}{typecn}', '-10', '-10'),
    ('', 'join_event', '参加活动', 'event', '{action}{sign}了{score}{typecn}', '3', '2'),
    ('', 'cancel_join_event', '取消参加活动', 'event', '{action}{sign}了{score}{typecn}', '-3', '-2');

REPLACE INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`) 
VALUES 
    (0,'event','version_number','s:5:"33566";','2012-07-12 10:00:00');