
CREATE TABLE `ts_webpage` (
	`webpage_id`  int(11) NOT NULL AUTO_INCREMENT ,
	`url`  text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	`hash`  char(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	`title`  varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	`comment_count`  int(11) NOT NULL DEFAULT 0 ,
	PRIMARY KEY (`webpage_id`),
	UNIQUE INDEX `hash` USING BTREE (`hash`)
) ENGINE=MyISAM DEFAULT CHARACTER SET=utf8;

ALTER TABLE `ts_weibo_topic` ADD COLUMN `status`  tinyint(1) NOT NULL DEFAULT 0 AFTER `ctime`;

ALTER TABLE `ts_weibo_topic` ADD COLUMN `lock`  tinyint(1) NOT NULL DEFAULT 0 AFTER `status`;

ALTER TABLE `ts_invite_record` ADD COLUMN `valid`  tinyint(1) NULL DEFAULT 1 COMMENT '是否有效邀请  0：无效、1：有效' AFTER `ctime`;

ALTER TABLE `ts_ad` ADD COLUMN `display_type`  tinyint(1) UNSIGNED NULL DEFAULT 1 AFTER `display_order`;

ALTER TABLE `ts_user` ADD COLUMN `face`  tinyint(1) NULL DEFAULT 0 COMMENT '是否有头像  0：无、1：有' AFTER `domain`;

CREATE TABLE IF NOT EXISTS  `ts_user_verified` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) unsigned NOT NULL,
  `realname` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reason` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `info` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `verified` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `ts_user_verified` ADD COLUMN `attachment`  varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `verified`;