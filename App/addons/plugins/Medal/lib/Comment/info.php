<?php
return array(
	'path_name'		=> 'Comment',
	'title'			=> '吐槽选手',
	'data'			=> serialize(array(
									'icon_url'		=> SITE_URL . '/addons/plugins/Medal/lib/Star/icon.gif',
									'big_icon_url'	=> SITE_URL . '/addons/plugins/Medal/lib/Star/big_icon.gif',
									'description'	=> '发布评论达到500条以上，可获得“吐槽选手”称号。
',
									'alert_message'	=> '<a href="'.U('home/Account/medal',array('addon'=>'Medal','hook'=>'home_account_show')).'">吐槽很厉害么？</a>',
								)),
);