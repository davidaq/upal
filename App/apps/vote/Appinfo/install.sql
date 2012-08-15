DROP TABLE IF EXISTS `ts_vote`;
CREATE TABLE `ts_vote` (
  `id` int(11) NOT NULL auto_increment,
  `uid` int(11) NOT NULL,
  `title` text NOT NULL,
  `explain` text NOT NULL,
  `type` tinyint(4) NOT NULL,
  `glimit` tinyint(4) NOT NULL default '0',
  `deadline` int(11) NOT NULL,
  `onlyfriend` tinyint(4) NOT NULL,
  `cTime` int(11) NOT NULL,
  `isHot` varchar(1) NOT NULL,
  `rTime` int(11) NOT NULL,
  `status` varchar(1) NOT NULL,
  `vote_num` int(11) NOT NULL default '0',
  `commentCount` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_vote_opt`;
CREATE TABLE `ts_vote_opt` (
  `id` int(11) NOT NULL auto_increment,
  `vote_id` int(11) NOT NULL,
  `name` text NOT NULL,
  `num` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_vote_user`;
CREATE TABLE `ts_vote_user` (
  `id` int(11) NOT NULL auto_increment,
  `vote_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `opts` text NOT NULL,
  `cTime` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

#添加ts_system_data数据
REPLACE INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`)
VALUES
	(0, 'vote', 'limitpage', 's:2:\"20\";', '2010-12-03 13:11:32'),
	(0, 'vote', 'defaultTime', 's:7:\"7776000\";', '2010-12-02 18:18:16'),
	(0, 'vote', 'join', 's:3:\"all\";', '2010-12-02 18:18:16');

#模板数据
DELETE FROM `ts_template` WHERE `name` = 'vote_create_weibo' OR `name` = 'vote_share_weibo';
INSERT INTO `ts_template` (`name`, `alias`, `title`, `body`, `lang`, `type`, `type2`, `is_cache`, `ctime`) 
VALUES
	('vote_create_weibo', '发起投票', '', '我发起了一个投票:【{title}】 {url}', 'zh', 'vote', 'weibo', 0, 1290417734),
	('vote_share_weibo', '分享投票', '', '分享@{author} 的投票:【{title}】{url}', 'zh', 'vote', 'weibo', 0, 1290595552);

#积分配置
DELETE FROM `ts_credit_setting` WHERE `type` = 'vote';
INSERT INTO `ts_credit_setting` (`id`, `name`, `alias`, `type`, `info`, `score`, `experience`) 
VALUES
	('', 'add_vote', '发起投票', 'vote', '{action}{sign}了{score}{typecn}', '20', '20'),
	('', 'join_vote', '参与投票', 'vote', '{action}{sign}了{score}{typecn}', '1', '5'),
	('', 'joined_vote', '投票被参与', 'vote', '{action}{sign}了{score}{typecn}', '1', '1'),
	('', 'delete_vote', '删除投票', 'vote', '{action}{sign}了{score}{typecn}', '-20', '-20');

INSERT INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`) 
VALUES 
    (0,'vote','version_number','s:5:"33566";','2012-07-12 00:00:00');