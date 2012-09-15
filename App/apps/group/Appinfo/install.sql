-- --------------------------------------------------------

--
-- 表的结构 `ts_group`
--

DROP TABLE IF EXISTS `ts_group`;
CREATE TABLE IF NOT EXISTS `ts_group` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `uid` int(11) unsigned NOT NULL default '0',
  `name` varchar(32) NOT NULL,
  `intro` text NOT NULL,
  `logo` varchar(255) NOT NULL,
  `announce` text NOT NULL,
  `cid0` smallint(6) unsigned NOT NULL,
  `cid1` smallint(6) unsigned NOT NULL,
  `membercount` smallint(6) unsigned NOT NULL default '0',
  `threadcount` smallint(6) unsigned NOT NULL default '0',
  `type` enum('open','limit','close') NOT NULL,
  `need_invite` tinyint(1) NOT NULL default '2',
  `need_verify` tinyint(4) NOT NULL,
  `actor_level` tinyint(4) NOT NULL,
  `brower_level` tinyint(4) NOT NULL default '-1',
  `openWeibo` tinyint(1) NOT NULL default '1',
  `openBlog` tinyint(1) NOT NULL default '1',
  `openUploadFile` tinyint(1) NOT NULL default '1',
  `whoUploadFile` tinyint(1) NOT NULL default '1',
  `whoDownloadFile` tinyint(1) NOT NULL default '2',
  `openAlbum` tinyint(1) NOT NULL default '1',
  `whoCreateAlbum` tinyint(1) NOT NULL default '1',
  `whoUploadPic` tinyint(1) NOT NULL default '0',
  `anno` tinyint(1) NOT NULL default '0',
  `ipshow` tinyint(1) NOT NULL default '0',
  `invitepriv` tinyint(1) NOT NULL default '0',
  `createalbumpriv` tinyint(1) NOT NULL default '1',
  `uploadpicpriv` tinyint(1) NOT NULL default '0',
  `ctime` int(11) NOT NULL default '0',
  `mtime` int(11) unsigned NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '1',
  `isrecom` tinyint(1) NOT NULL default '0',
  `is_del` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- 表的结构 `ts_group_album`
--

DROP TABLE IF EXISTS `ts_group_album`;
CREATE TABLE IF NOT EXISTS `ts_group_album` (
  `id` int(11) NOT NULL auto_increment,
  `gid` int(11) NOT NULL,
  `userId` int(11) default NULL,
  `name` varchar(255) default NULL,
  `info` text,
  `cTime` int(11) unsigned default NULL,
  `mTime` int(11) unsigned default NULL,
  `coverImageId` int(11) NOT NULL,
  `coverImagePath` varchar(255) default NULL,
  `photoCount` int(11) default '0',
  `status` tinyint(2) unsigned NOT NULL default '1',
  `share` tinyint(1) NOT NULL default '0',
  `is_del` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `uid` (`userId`),
  KEY `cTime` (`cTime`),
  KEY `mTime` (`mTime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `ts_group_album`
--


-- --------------------------------------------------------

--
-- 表的结构 `ts_group_attachment`
--

DROP TABLE IF EXISTS `ts_group_attachment`;
CREATE TABLE IF NOT EXISTS `ts_group_attachment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gid` int(11) unsigned NOT NULL,
  `uid` int(11) unsigned NOT NULL,
  `attachId` int(11) unsigned NOT NULL,
  `name` varchar(32) NOT NULL,
  `note` text NOT NULL,
  `filesize` int(10) NOT NULL DEFAULT '0',
  `filetype` varchar(10) NOT NULL,
  `fileurl` varchar(255) NOT NULL,
  `totaldowns` mediumint(6) NOT NULL DEFAULT '0',
  `ctime` int(11) NOT NULL,
  `mtime` varchar(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `is_del` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `gid` (`gid`),
  KEY `gid_2` (`gid`,`attachId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- 表的结构 `ts_group_category`
--

DROP TABLE IF EXISTS `ts_group_category`;
CREATE TABLE IF NOT EXISTS `ts_group_category` (
  `id` mediumint(5) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `type` tinyint(1) NOT NULL default '1',
  `pid` mediumint(5) NOT NULL default '0',
  `module` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=71 ;

--
-- 转存表中的数据 `ts_group_category`
--

INSERT INTO `ts_group_category` (`id`, `title`, `type`, `pid`, `module`) VALUES
(1, '明星粉丝', 1, 0, ''),
(2, '行业交流', 1, 0, ''),
(3, '兴趣爱好', 1, 0, ''),
(4, '科教人文', 1, 0, ''),
(5, '生活时尚', 1, 0, ''),
(6, '同城会', 1, 0, ''),
(7, '老友记', 1, 0, ''),
(8, '房产汽车', 1, 0, ''),
(9, '港台', 1, 1, ''),
(10, '内地', 1, 1, ''),
(11, '日韩', 1, 1, ''),
(12, '欧美', 1, 1, ''),
(13, '网络红人', 1, 1, ''),
(14, '其它', 1, 1, ''),
(15, 'IT互联网', 1, 2, ''),
(16, '商业财经', 1, 2, ''),
(17, '传媒公关', 1, 2, ''),
(18, '机构&公益', 1, 2, ''),
(19, '创意联盟', 1, 2, ''),
(20, '其它行业', 1, 2, ''),
(21, '第三方应用', 1, 2, ''),
(22, '囧笑话', 1, 3, ''),
(23, '动漫', 1, 3, ''),
(24, '游戏', 1, 3, ''),
(25, '体育', 1, 3, ''),
(26, '购物', 1, 3, ''),
(27, '旅游', 1, 3, ''),
(28, '摄影', 1, 3, ''),
(29, '音乐', 1, 3, ''),
(30, '电影', 1, 3, ''),
(31, '电视', 1, 3, ''),
(32, '数码', 1, 3, ''),
(33, '稀奇古怪', 1, 3, ''),
(34, '文学阅读', 1, 4, ''),
(35, '社科文艺', 1, 4, ''),
(36, '科学技术', 1, 4, ''),
(37, '教育考试', 1, 4, ''),
(38, '历史军事', 1, 4, ''),
(39, '潮流时尚', 1, 5, ''),
(40, '七八九零', 1, 5, ''),
(41, '帅哥美女', 1, 5, ''),
(42, '情感', 1, 5, ''),
(43, '健康', 1, 5, ''),
(44, '星座', 1, 5, ''),
(45, '宠物', 1, 5, ''),
(46, '美食', 1, 5, ''),
(47, '休闲', 1, 5, ''),
(48, '家庭亲子', 1, 5, ''),
(49, '生活信息', 1, 5, ''),
(50, '北京', 1, 6, ''),
(51, '上海', 1, 6, ''),
(52, '广东', 1, 6, ''),
(53, '江苏', 1, 6, ''),
(54, '山东', 1, 6, ''),
(55, '安徽', 1, 6, ''),
(56, '浙江', 1, 6, ''),
(57, '福建', 1, 6, ''),
(58, '河北', 1, 6, ''),
(59, '河南', 1, 6, ''),
(60, '辽宁', 1, 6, ''),
(61, '湖北', 1, 6, ''),
(62, '四川', 1, 6, ''),
(63, '同学', 1, 7, ''),
(64, '老乡', 1, 7, ''),
(65, '同事', 1, 7, ''),
(66, '好友', 1, 7, ''),
(67, '互粉', 1, 7, ''),
(68, '小区', 1, 8, ''),
(69, '房产家居', 1, 8, ''),
(70, '汽车', 1, 8, '');

-- --------------------------------------------------------

--
-- 表的结构 `ts_group_invite_verify`
--

DROP TABLE IF EXISTS `ts_group_invite_verify`;
CREATE TABLE IF NOT EXISTS `ts_group_invite_verify` (
  `invite_id` int(11) NOT NULL auto_increment,
  `gid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `is_used` int(11) NOT NULL default '0',
  PRIMARY KEY  (`invite_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- 表的结构 `ts_group_log`
--

DROP TABLE IF EXISTS `ts_group_log`;
CREATE TABLE IF NOT EXISTS `ts_group_log` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `gid` int(11) unsigned NOT NULL,
  `uid` int(11) unsigned NOT NULL,
  `type` varchar(10) NOT NULL,
  `content` text NOT NULL,
  `ctime` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- 表的结构 `ts_group_member`
--

DROP TABLE IF EXISTS `ts_group_member`;
CREATE TABLE IF NOT EXISTS `ts_group_member` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `gid` int(11) unsigned NOT NULL,
  `uid` int(11) unsigned NOT NULL default '0',
  `name` char(10) NOT NULL,
  `reason` text NOT NULL,
  `status` tinyint(1) default '1',
  `level` tinyint(2) unsigned default '1',
  `ctime` int(11) NOT NULL default '0',
  `mtime` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `gid` (`gid`,`uid`),
  KEY `mid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- 表的结构 `ts_group_photo`
--

DROP TABLE IF EXISTS `ts_group_photo`;
CREATE TABLE IF NOT EXISTS `ts_group_photo` (
  `id` int(11) NOT NULL auto_increment,
  `gid` int(11) NOT NULL,
  `attachId` int(11) NOT NULL,
  `albumId` int(11) NOT NULL,
  `userId` int(11) default NULL,
  `status` tinyint(2) unsigned NOT NULL default '1',
  `name` varchar(255) NOT NULL,
  `cTime` int(11) unsigned default NULL,
  `mTime` int(11) unsigned default NULL,
  `info` text,
  `commentCount` int(11) unsigned default '0',
  `readCount` int(11) unsigned default '0',
  `savepath` varchar(255) NOT NULL,
  `size` int(11) NOT NULL default '0',
  `tags` text,
  `order` int(11) NOT NULL,
  `is_del` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `gid` (`gid`,`albumId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- 表的结构 `ts_group_post`
--

DROP TABLE IF EXISTS `ts_group_post`;
CREATE TABLE IF NOT EXISTS `ts_group_post` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `gid` int(11) unsigned NOT NULL,
  `uid` int(11) unsigned NOT NULL,
  `tid` int(11) unsigned NOT NULL,
  `content` text NOT NULL,
  `ip` char(16) NOT NULL,
  `istopic` tinyint(1) NOT NULL default '0',
  `ctime` int(11) NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '0',
  `quote` int(11) unsigned NOT NULL default '0',
  `is_del` varchar(1) NOT NULL default '0',
  `attach` text,
  PRIMARY KEY  (`id`),
  KEY `gid` (`gid`,`tid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- 表的结构 `ts_group_tag`
--

DROP TABLE IF EXISTS `ts_group_tag`;
CREATE TABLE IF NOT EXISTS `ts_group_tag` (
  `group_tag_id` int(11) NOT NULL auto_increment,
  `gid` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY  (`group_tag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- 表的结构 `ts_group_topic`
--

DROP TABLE IF EXISTS `ts_group_topic`;
CREATE TABLE IF NOT EXISTS `ts_group_topic` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `gid` int(11) unsigned NOT NULL,
  `uid` int(11) unsigned NOT NULL,
  `name` varchar(36) NOT NULL,
  `title` varchar(255) NOT NULL,
  `cid` int(11) unsigned NOT NULL,
  `viewcount` smallint(6) unsigned NOT NULL default '0',
  `replycount` smallint(6) unsigned NOT NULL default '0',
  `dist` tinyint(1) NOT NULL default '0',
  `top` tinyint(1) NOT NULL default '0',
  `lock` tinyint(1) NOT NULL default '0',
  `addtime` int(11) NOT NULL default '0',
  `replytime` int(11) NOT NULL default '0',
  `mtime` int(11) unsigned NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '0',
  `isrecom` tinyint(1) NOT NULL default '0',
  `is_del` tinyint(1) NOT NULL default '0',
  `attach` text,
  PRIMARY KEY  (`id`),
  KEY `gid` (`gid`),
  KEY `gid_2` (`gid`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- 表的结构 `ts_group_topic_category`
--

DROP TABLE IF EXISTS `ts_group_topic_category`;
CREATE TABLE IF NOT EXISTS `ts_group_topic_category` (
  `id` mediumint(6) unsigned NOT NULL auto_increment,
  `gid` int(11) unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- 表的结构 `ts_group_topic_collect`
--

DROP TABLE IF EXISTS `ts_group_topic_collect`;
CREATE TABLE IF NOT EXISTS `ts_group_topic_collect` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `tid` int(11) unsigned NOT NULL default '0',
  `mid` int(11) unsigned NOT NULL default '0',
  `addtime` int(11) unsigned NOT NULL default '0',
  `is_del` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `tid` (`tid`,`mid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- 表的结构 `ts_group_user_count`
--

DROP TABLE IF EXISTS `ts_group_user_count`;
CREATE TABLE IF NOT EXISTS `ts_group_user_count` (
  `uid` int(11) NOT NULL,
  `atme` mediumint(6) NOT NULL,
  `comment` mediumint(6) NOT NULL,
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `ts_group_weibo`
--

DROP TABLE IF EXISTS `ts_group_weibo`;
CREATE TABLE IF NOT EXISTS `ts_group_weibo` (
  `weibo_id` int(11) NOT NULL auto_increment,
  `gid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `content` text NOT NULL,
  `ctime` int(11) NOT NULL,
  `from` tinyint(1) NOT NULL,
  `from_data` text,
  `comment` mediumint(8) NOT NULL,
  `transpond_id` int(11) NOT NULL default '0',
  `transpond` mediumint(8) NOT NULL,
  `type` varchar(255) default '0',
  `type_data` text,
  `isdel` tinyint(1) NOT NULL,
  PRIMARY KEY  (`weibo_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- 表的结构 `ts_group_weibo_atme`
--

DROP TABLE IF EXISTS `ts_group_weibo_atme`;
CREATE TABLE IF NOT EXISTS `ts_group_weibo_atme` (
  `uid` int(11) NOT NULL,
  `gid` int(11) NOT NULL,
  `weibo_id` int(11) NOT NULL,
  UNIQUE KEY `uid` (`uid`,`weibo_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `ts_group_weibo_comment`
--

DROP TABLE IF EXISTS `ts_group_weibo_comment`;
CREATE TABLE IF NOT EXISTS `ts_group_weibo_comment` (
  `comment_id` int(11) NOT NULL auto_increment,
  `gid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `reply_comment_id` int(11) NOT NULL,
  `reply_uid` int(11) NOT NULL,
  `weibo_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `ctime` int(11) NOT NULL,
  `isdel` tinyint(1) NOT NULL,
  PRIMARY KEY  (`comment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- 表的结构 `ts_group_weibo_topic`
--

DROP TABLE IF EXISTS `ts_group_weibo_topic`;
CREATE TABLE IF NOT EXISTS `ts_group_weibo_topic` (
  `topic_id` int(11) unsigned NOT NULL auto_increment,
  `gid` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `count` int(11) NOT NULL,
  `ctime` int(11) NOT NULL,
  PRIMARY KEY  (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;


#添加ts_system_data数据
REPLACE INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`)
VALUES
	(0, 'group', 'close_invite', 's:1:\"2\";', '2011-06-28 11:46:49'),
	(0, 'group', 'createAudit', 's:1:\"1\";', '2011-06-28 11:46:47'),
	(0, 'group', 'createGroup', 's:1:\"1\";', '2011-06-28 11:46:47'),
	(0, 'group', 'createMaxGroup', 's:1:\"3\";', '2011-06-28 11:46:47'),
	(0, 'group', 'creditType', 's:10:\"experience\";', '2011-06-28 11:46:47'),
	(0, 'group', 'editSubmit', 's:1:\"1\";', '2011-06-28 11:46:49'),
	(0, 'group', 'hotTags', 's:0:\"\";', '2011-06-28 11:46:47'),
	(0, 'group', 'joinMaxGroup', 's:2:\"10\";', '2011-06-28 11:46:47'),
	(0, 'group', 'openBlog', 's:1:\"1\";', '2011-06-28 11:46:49'),
	(0, 'group', 'openUploadFile', 's:1:\"1\";', '2011-06-28 11:46:49'),
	(0, 'group', 'open_invite', 's:1:\"0\";', '2011-06-28 11:46:49'),
	(0, 'group', 'uploadFile', 's:1:\"1\";', '2011-06-28 11:46:47'),
	(0, 'group', 'simpleFileSize', 's:1:\"2\";', '2011-06-28 11:46:47'),
	(0, 'group', 'spaceSize', 's:2:\"10\";', '2011-06-28 11:46:47'),
	(0, 'group', 'uploadFileType', 's:59:\"jpg|gif|png|jpeg|bmp|zip|rar|doc|xls|ppt|docx|xlsx|pptx|pdf\";', '2011-06-28 11:46:47'),
	(0, 'group', 'userCredit', 's:3:\"100\";', '2011-06-28 11:46:47'),
	(0, 'group', 'whoDownloadFile', 's:1:\"3\";', '2011-06-28 11:46:49'),
	(0, 'group', 'whoUploadFile', 's:1:\"3\";', '2011-06-28 11:46:49');

#模板数据
DELETE FROM `ts_template` WHERE `type` = 'group';
INSERT INTO `ts_template` (`name`, `alias`, `title`, `body`, `lang`, `type`, `type2`, `is_cache`, `ctime`) 
VALUES
    ('group_share_weibo', '分享群组', '','我在@{author} 的群组 【{name}】 里玩得很嗨， {url} 推荐大家也来看看哦~', 'zh', 'group', 'weibo', 0, 1307590430),
	('group_post_share_weibo', '分享帖子', '','分享@{author} 的帖子:【{title}】 {url}', 'zh', 'group', 'weibo', 0, 1307415524),
	('group_post_create_weibo', '发布帖子', '','我发起了一份帖子:【{title}】 {url}', 'zh', 'group', 'weibo', 0, 1307417128);

#积分配置
DELETE FROM `ts_credit_setting` WHERE `type` = 'group';
INSERT INTO `ts_credit_setting` (`id`, `name`, `alias`, `type`, `info`, `score`, `experience`) 
VALUES  
	('', 'add_group', '创建群组', 'group', '{action}{sign}了{score}{typecn}', '5', '5'),
	('', 'delete_group', '解散群租', 'group', '{action}{sign}了{score}{typecn}', '-5', '-5'),
	('', 'join_group', '加入群组', 'group', '{action}{sign}了{score}{typecn}', '2', '2'),
	('', 'quit_group', '退出群组', 'group', '{action}{sign}了{score}{typecn}', '-2', '-2'),
	('', 'group_add_topic', '发表帖子', 'group', '{action}{sign}了{score}{typecn}', '5', '5'),
	('', 'group_reply_topic', '回复帖子', 'group', '{action}{sign}了{score}{typecn}', '2', '2'),
	('', 'group_delete_topic', '删除帖子', 'group', '{action}{sign}了{score}{typecn}', '-5', '-5'),
	('', 'group_upload_file', '上传文件', 'group', '{action}{sign}了{score}{typecn}', '5', '5'),
	('', 'group_download_file', '下载文件', 'group', '{action}{sign}了{score}{typecn}', '2', '2'),
	('', 'group_delete_file', '删除文件', 'group', '{action}{sign}了{score}{typecn}', '-5', '-5');

REPLACE INTO `ts_system_data` (`uid`,`list`,`key`,`value`,`mtime`) 
VALUES 
    (0,'group','version_number','s:5:"33566";','2012-07-12 00:00:00');