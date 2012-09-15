SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `ts_buy`;

CREATE TABLE `ts_buy` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `owner` int(11) NOT NULL,
  `img`	varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `cTime` int(11) NOT NULL,
  `verified` int(1) NOT NULL DEFAULT 0,
  `description` text NOT NULL,
  PRIMARY KEY  (`id`),
  INDEX(`id`),
  INDEX(`owner`),
  INDEX(`cTime`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


