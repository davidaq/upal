SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `ts_wiki`;

CREATE TABLE `ts_wiki` (
  `id` int(11) NOT NULL auto_increment,
  `keyword` varchar(255) NOT NULL,
  `creator`	int(11) NOT NULL,
  `num` int(11) NOT NULL DEFAULT 0,
  `cTime` int(11) NOT NULL,
  `verified` int(1) NOT NULL DEFAULT 0,
  `vote` int(11) NOT NULL DEFAULT 0,
  `description` text NOT NULL,
  PRIMARY KEY  (`id`),
  INDEX(keyword),
  INDEX(creator),
  INDEX(cTime),
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_wiki_tag`;

CREATE TABLE `ts_wiki_tag` (
  `wiki_id` int(11) NOT NULL auto_increment,
  `tag` char(50) NOT NULL,
  PRIMARY KEY  (`wiki_id`,`tag`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_wiki_post`;

CREATE TABLE `ts_wiki_post` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `author`	int(11) NOT NULL,
  `content` int(11) NOT NULL,
  `wiki_id` int(11) NOT NULL,
  `cTime` int(11) NOT NULL,
  `order` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  INDEX(author)
  INDEX(page_id)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_wiki_member`;

CREATE TABLE `ts_wiki_member` (
  `wiki_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `contribution` int(11) NOT NULL
  PRIMARY KEY  (`wiki_id`,`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

