SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `ts_buy`;

CREATE TABLE `ts_buy` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `owner` int(11) NOT NULL,
  `img` text NOT NULL,
  `cTime` int(11) NOT NULL,
  `count` int(11) NOT NULL,
  `description` text NOT NULL,
  `vote` int(1) NOT NULL DEFAULT 3,
  PRIMARY KEY  (`id`),
  INDEX(`id`),
  INDEX(`owner`),
  INDEX(`cTime`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_buy_comment`;

CReATE TABLE `ts_buy_comment` (
	`id` int(11) NOT NULL auto_increment,
	`uid` int(11) NOT NULL,
	`bid` int(11) NOT NULL,
	`cTime` int(11) NOT NULL,
	`content` text NOT NULL,
  `vote` int(1) NOT NULL DEFAULT 3,
	PRIMARY KEY  (`id`),
	INDEX(`uid`),
	INDEX(`bid`),
	INDEX(`cTime`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
