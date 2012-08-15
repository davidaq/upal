<?php
return array(
	'path_name'		=> 'Star',
	'title'			=> '电波之星',
	'data'			=> serialize(array(
									'icon_url'		=> SITE_URL . '/addons/plugins/Medal/lib/Star/icon.gif',
									'big_icon_url'	=> SITE_URL . '/addons/plugins/Medal/lib/Star/big_icon.gif',
									'description'	=> '粉丝达到500人以上，即可获得“电波之星”称号。',
									'alert_message'	=> '<a href="'.U('home/Account/medal',array('addon'=>'Medal','hook'=>'home_account_show')).'">你很受人欢迎吗？</a>',
								)),
);