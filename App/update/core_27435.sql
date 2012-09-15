##################################################################################################################
#   ThinkSNS 2.5 正式版
# `ts_weibo_topic_link`
#

DROP TABLE IF EXISTS `ts_weibo_topic_link`;
CREATE TABLE `ts_weibo_topic_link` (
  `weibo_topic_id` int(11) NOT NULL auto_increment,
  `weibo_id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL default '0',
  `transpond_id` int(11) NOT NULL default 0,
  PRIMARY KEY  (`weibo_topic_id`),
  KEY `topic_type` (`topic_id`,`type`),
  KEY `topic_transpond` (`topic_id`,`transpond_id`),
  KEY `weibo` (`weibo_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

