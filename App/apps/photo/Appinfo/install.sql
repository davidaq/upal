DROP TABLE IF EXISTS `ts_photo`;

CREATE TABLE IF NOT EXISTS `ts_photo` (
  `id` int(11) NOT NULL auto_increment,
  `attachId` int(11) default NULL,
  `albumId` int(11) default NULL,
  `userId` int(11) default NULL,
  `status` tinyint(2) unsigned NOT NULL default '1',
  `name` varchar(255) default NULL,
  `cTime` int(11) unsigned default NULL,
  `mTime` int(11) unsigned default NULL,
  `info` text,
  `commentCount` int(11) unsigned default '0',
  `readCount` int(11) unsigned default '0',
  `savepath` varchar(255) default NULL,
  `size` int(11) NOT NULL default '0',
  `privacy` int(1) NOT NULL default '1',
  `tags` text,
  `order` int(11) NOT NULL default '0',
  `isDel` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `ts_photo_album`;

CREATE TABLE IF NOT EXISTS `ts_photo_album` (
  `id` int(11) NOT NULL auto_increment,
  `userId` int(11) default NULL,
  `name` varchar(255) default NULL,
  `info` text,
  `cTime` int(11) unsigned default NULL,
  `mTime` int(11) unsigned default NULL,
  `coverImageId` int(11) default NULL,
  `coverImagePath` varchar(255) default NULL,
  `photoCount` int(11) default '0',
  `readCount` int(11) default '0',
  `status` tinyint(2) unsigned NOT NULL default '1',
  `isHot` varchar(1) NOT NULL default '0',
  `rTime` int(11) NOT NULL default '0',
  `share` tinyint(1) NOT NULL default '0',
  `privacy` tinyint(1) default NULL,
  `privacy_data` text,
  `isDel` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `uid` (`userId`),
  KEY `cTime` (`cTime`),
  KEY `mTime` (`mTime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `ts_photo_index`;

CREATE TABLE IF NOT EXISTS `ts_photo_index` (
  `albumId` int(11) NOT NULL default '0',
  `photoId` int(11) NOT NULL default '0',
  `userId` int(11) default NULL,
  `order` int(11) default NULL,
  `privacy` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`albumId`,`photoId`),
  UNIQUE KEY `album_photo` (`albumId`,`photoId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `ts_photo_mark`;

CREATE TABLE IF NOT EXISTS `ts_photo_mark` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `photoId` int(11) default NULL,
  `userId` int(11) default NULL,
  `userName` varchar(50) default NULL,
  `markedUserId` int(11) default NULL,
  `x` varchar(100) default NULL,
  `y` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

#添加ts_system_data数据
REPLACE INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`)
VALUES
	(0, 'photo', 'album_raws', 's:1:\"6\";', '2010-12-02 18:18:16'),
	(0, 'photo', 'photo_raws', 's:1:\"8\";', '2010-11-19 10:52:26'),
	(0, 'photo', 'photo_preview', 's:1:\"1\";', '2010-11-19 10:52:38'),
	(0, 'photo', 'photo_max_size', 's:1:\"2\";', '2010-11-19 10:52:56'),
	(0, 'photo', 'photo_file_ext', 's:16:\"jpeg,gif,jpg,png\";', '2010-11-19 10:53:05'),
	(0, 'photo', 'max_flash_upload_num', 's:2:\"10\";', '2010-11-19 10:53:27');

#模板数据
DELETE FROM `ts_template` WHERE `name` = 'photo_create_weibo' OR `name` = 'photo_share_weibo' OR `name` = 'album_share_weibo';
INSERT INTO `ts_template` (`name`, `alias`, `title`, `body`, `lang`, `type`, `type2`, `is_cache`, `ctime`) 
VALUES
	('photo_create_weibo', '发表图片', '','我上传了{count}张新图片:【{title}】... {url}', 'zh', 'photo', 'weibo', 0, 1290417734),
	('photo_share_weibo', '分享图片', '', '分享@{author} 的图片:【{title}】{url}', 'zh',  'photo', 'weibo', 0, 1290595552),
	('album_share_weibo', '分享相册', '', ' 分享@{author} 的相册:【{title}】{url}', 'zh',  'album', 'weibo', 0, 1290595552);

# 增加photo的默认积分配置
DELETE FROM `ts_credit_setting` WHERE `type` = 'photo';
INSERT INTO `ts_credit_setting`  (`id`,`name`, `alias`, `type`, `info`, `score`, `experience`) 
VALUES
	('', 'add_album', '创建相册', 'photo', '{action}{sign}了{score}{typecn}', '10', '10'),
	('', 'delete_album', '删除相册', 'photo', '{action}{sign}了{score}{typecn}', '-10', '-10'),
	('', 'add_photo', '上传图片', 'photo', '{action}{sign}了{score}{typecn}', '2', '2');

REPLACE INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`) 
VALUES 
    (0,'photo','version_number','s:5:"33566";','2012-07-12 00:00:00');