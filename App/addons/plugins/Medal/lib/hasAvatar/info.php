<?php
return array(
	'path_name'		=> 'hasAvatar',
	'title'			=> '有头有脸',
	'data'			=> serialize(array(
									'icon_url'		=> SITE_URL . '/addons/plugins/Medal/lib/hasAvatar/icon.gif',
									'big_icon_url'	=> SITE_URL . '/addons/plugins/Medal/lib/hasAvatar/big_icon.gif',
									'description'	=> '上传头像即可获得此勋章',
									'alert_message'	=> '<a href="'.U('home/Account/index').'#face">上传头像</a>, 做有头有脸的好公民',
								)),
);