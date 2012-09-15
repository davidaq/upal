##################################################################################################################
#   ThinkSNS 2.3  
# ts_addons, ts_message_content, ts_message_list, ts_message_member, ts_user_data, ts_weibo_attach
#

DROP TABLE IF EXISTS `ts_addons`;

CREATE TABLE IF NOT EXISTS  `ts_addons` (
`addonId`  int(4) UNSIGNED NOT NULL AUTO_INCREMENT ,
`name`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' ,
`pluginName`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' ,
`author`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' ,
`info`  tinytext CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
`version`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`status`  enum('0','1') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '1' ,
`lastupdate`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' ,
`site`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`tsVersion`  varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '2.5' ,
PRIMARY KEY (`addonId`),
UNIQUE INDEX `name` (`name`)  
)
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
CHECKSUM=0
ROW_FORMAT=Dynamic
DELAY_KEY_WRITE=0;

CREATE TABLE IF NOT EXISTS  `ts_message_content` (
`message_id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
`list_id`  int(11) UNSIGNED NOT NULL ,
`from_uid`  int(11) UNSIGNED NOT NULL ,
`content`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
`is_del`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 ,
`mtime`  int(11) UNSIGNED NOT NULL ,
PRIMARY KEY (`message_id`),
INDEX `list_id` (`list_id`, `is_del`, `mtime`)  ,
INDEX `list_id_2` (`list_id`, `mtime`)  
)
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
CHECKSUM=0
ROW_FORMAT=Dynamic
DELAY_KEY_WRITE=0;

CREATE TABLE IF NOT EXISTS  `ts_message_list` (
`list_id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
`from_uid`  int(11) UNSIGNED NOT NULL ,
`type`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 ,
`title`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`member_num`  smallint(5) UNSIGNED NOT NULL DEFAULT 0 ,
`min_max`  varchar(17) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`mtime`  int(11) UNSIGNED NOT NULL ,
`last_message`  text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
PRIMARY KEY (`list_id`),
INDEX `type` (`type`)  ,
INDEX `min_max` (`min_max`)  ,
INDEX `from_uid` (`from_uid`, `mtime`)  
)
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
CHECKSUM=0
ROW_FORMAT=Dynamic
DELAY_KEY_WRITE=0;

CREATE TABLE IF NOT EXISTS  `ts_message_member` (
`list_id`  int(11) UNSIGNED NOT NULL ,
`member_uid`  int(11) UNSIGNED NOT NULL ,
`new`  smallint(8) UNSIGNED NOT NULL DEFAULT 0 ,
`message_num`  int(10) UNSIGNED NOT NULL DEFAULT 1 ,
`ctime`  int(11) UNSIGNED NOT NULL DEFAULT 0 ,
`list_ctime`  int(11) UNSIGNED NOT NULL ,
PRIMARY KEY (`list_id`, `member_uid`),
INDEX `new` (`new`)  ,
INDEX `ctime` (`ctime`)  ,
INDEX `list_ctime` (`list_ctime`)  
)
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
CHECKSUM=0
ROW_FORMAT=Fixed
DELAY_KEY_WRITE=0;


CREATE TABLE IF NOT EXISTS  `ts_user_data` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`uid`  int(11) NOT NULL ,
`key`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`value`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
`mtime`  timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
PRIMARY KEY (`id`),
UNIQUE INDEX `user-key` (`uid`, `key`)  
)
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
CHECKSUM=0
ROW_FORMAT=Dynamic
DELAY_KEY_WRITE=0;

CREATE TABLE IF NOT EXISTS  `ts_weibo_attach` (
`weibo_id`  int(11) NOT NULL ,
`attach_id`  int(11) NOT NULL ,
`uid`  int(11) NOT NULL ,
`weibo_type`  tinyint(3) NOT NULL ,
`mtime`  timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
INDEX `user_attach` (`uid`, `attach_id`, `weibo_type`)  ,
INDEX `weibo_index` (`weibo_id`, `weibo_type`)  ,
INDEX `weibo_type` (`weibo_id`, `weibo_type`)  
)
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
CHECKSUM=0
ROW_FORMAT=Fixed
DELAY_KEY_WRITE=0;

##################################################################################################################
#   ThinkSNS 2.5  
#   ts_apps 

UPDATE `ts_app` SET icon_url=REPLACE(icon_url, '.gif', '.png'),large_icon_url=REPLACE(large_icon_url, '.gif', '.png'); 

##################################################################################################################
# ThinkSNS 2.5  
#
#

#ALTER TABLE `ts_denounce` ADD INDEX `from` ( `from`, `state` );
#ALTER TABLE `ts_feed` ADD INDEX `uid` ( `uid` );
#ALTER TABLE `ts_login` ADD INDEX `uid` ( `uid` );
#ALTER TABLE `ts_notify` ADD INDEX `receive_2` (`receive`, `is_read`);
#ALTER TABLE `ts_user_blacklist` ADD INDEX `uid` (`uid`, `fid`);
#ALTER TABLE `ts_user_blacklist` ADD INDEX `fid` (`fid`);
#ALTER TABLE `ts_user_count` ADD INDEX `atme` (`uid`, `atme`);
#ALTER TABLE `ts_user_count` ADD INDEX `comment` (`uid`, `comment`);
#ALTER TABLE `ts_user_group_link` ADD INDEX `uid` (`uid`);
#ALTER TABLE `ts_weibo` ADD INDEX `uid` (`uid`, `isdel`);
#ALTER TABLE `ts_weibo` ADD INDEX `type` (`uid`, `type`);
#ALTER TABLE `ts_weibo` ADD INDEX `transpond` (`uid`, `transpond_id`);
#ALTER TABLE `ts_weibo` ADD INDEX `ctime` (`ctime`);
#ALTER TABLE `ts_weibo_atme` ADD INDEX `weibo_id` (`weibo_id`, `uid`);
#ALTER TABLE `ts_weibo_atme` ADD INDEX `uid_2` (`uid`);
#ALTER TABLE `ts_weibo_comment` ADD INDEX `weibo_id` (`weibo_id`);
#ALTER TABLE `ts_weibo_follow` ADD INDEX `uid_2` (`uid`, `fid`, `type`);
#ALTER TABLE `ts_weibo_topic` ADD INDEX `count` (`count`);
#ALTER TABLE `ts_weibo_topic` ADD INDEX `name` (`name`, `count`);

