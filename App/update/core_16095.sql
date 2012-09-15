##################################################################################################################
# 以下为 ThinkSNS 2.3 新增的数据表
# ts_user_visited, ts_weibo_topics
#

DROP TABLE IF EXISTS `ts_user_visited`;

CREATE TABLE IF NOT EXISTS `ts_user_visited` (
  `visited_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `fid` int(11) NOT NULL,
  `ctime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`visited_id`),
  UNIQUE KEY `uid_2` (`uid`,`fid`),
  KEY `uid` (`uid`),
  KEY `fid` (`fid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

#------------------------------------#

DROP TABLE IF EXISTS `ts_weibo_topics`;

CREATE TABLE IF NOT EXISTS `ts_weibo_topics` (
  `topics_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `topic_id` int(11) unsigned NOT NULL,
  `domain` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pic` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `note` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8_unicode_ci,
  `recommend` enum('1','0') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `ctime` int(11) DEFAULT NULL,
  `isdel` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`topics_id`),
  UNIQUE KEY `page` (`domain`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;
