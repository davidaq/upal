<?php
return array(
	'path_name'		=> 'trueWeiboer',
	'title'			=> '微博达人',
	'data'			=> serialize(array(
									'icon_url'		=> SITE_URL . '/addons/plugins/Medal/lib/trueWeiboer/icon.gif',
									'big_icon_url'	=> SITE_URL . '/addons/plugins/Medal/lib/trueWeiboer/big_icon.gif',
									'description'	=> '连续3天发微博,即可获得这枚勋章(每天都要发原创才有效噢)',
									'alert_message'	=> '你是微博控么? <a href="'.U('home/Account/medal',array('addon'=>'Medal','hook'=>'home_account_show')).'">Show Me!</a>',
								)),
        );
