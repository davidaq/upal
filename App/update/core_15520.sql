##################################################################################################################
# ThinkSNS 2.2 清除垃圾信息
# ts_user_group_link
#

DELETE FROM ts_user_group_link WHERE uid NOT IN (SELECT uid FROM ts_user);

##################################################################################################################
# ThinkSNS 2.2 新增的表
# ts_invite_record, ts_user_verified
#

DROP TABLE IF EXISTS `ts_invite_record`;

CREATE TABLE IF NOT EXISTS `ts_invite_record` (
  `invite_record_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) unsigned NOT NULL,
  `fid` int(11) unsigned NOT NULL,
  `ctime` int(11) unsigned NOT NULL,
  PRIMARY KEY (`invite_record_id`),
  UNIQUE KEY `uid` (`uid`,`fid`),
  KEY `ctime` (`ctime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

#------------------------------------#

DROP TABLE IF EXISTS `ts_user_verified`;

CREATE TABLE IF NOT EXISTS `ts_user_verified` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) unsigned NOT NULL,
  `realname` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reason` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `info` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `verified` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

##################################################################################################################
# ThinkSNS 2.2 新建的索引
#
ALTER TABLE `ts_user` ADD INDEX ( `location` );