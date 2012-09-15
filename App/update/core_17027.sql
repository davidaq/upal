##################################################################################################################
# ThinkSNS 2.4 新增的表
# ts_addons
#

DROP TABLE IF EXISTS `ts_addons`;

CREATE TABLE IF NOT EXISTS `ts_addons` (
  `addonId` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `pluginName` varchar(255) NOT NULL DEFAULT '',
  `author` varchar(255) NOT NULL DEFAULT '',
  `info` tinytext,
  `version` varchar(50) DEFAULT NULL,
  `status` enum('0','1') NOT NULL DEFAULT '1',
  `lastupdate` varchar(255) DEFAULT '',
  `site` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`addonId`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

