<?php
return array(
	'path_name'		=> 'LSWeibo',
	'title'			=> '真·微博控',
	'data'			=> serialize(array(
									'icon_url'		=> SITE_URL . '/addons/plugins/Medal/lib/trueWeiboer/icon.gif',
									'big_icon_url'	=> SITE_URL . '/addons/plugins/Medal/lib/trueWeiboer/big_icon.gif',
									'description'	=> '连续30天发布原创微博即可获得该勋章（期间中断将重新计算天数）',
									'alert_message'	=> '你是真货还是伪物？ <a href="'.U('home/Account/medal',array('addon'=>'Medal','hook'=>'home_account_show')).'">Show Me!</a>',
								)),
        );
