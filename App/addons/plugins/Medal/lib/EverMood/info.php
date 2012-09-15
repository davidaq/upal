<?php
return array(
	'path_name'		=> 'EverMood',
	'title'			=> '心情控',
	'data'			=> serialize(array(
									'icon_url'		=> SITE_URL . '/addons/plugins/Medal/lib/EverMood/icon.png',
									'big_icon_url'	=> SITE_URL . '/addons/plugins/Medal/lib/EverMood/icon.jpg',
									'description'	=> '发布15条心情微博，即可获得该勋章',
									'alert_message'	=> '你是心情控么? <a href="'.U('home/Account/medal',array('addon'=>'Medal','hook'=>'home_account_show')).'">Show me!</a>',
								)),
        );
