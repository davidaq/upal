<?php
return array(
	'path_name'		=> 'Topic',
	'title'			=> '热心用户',
	'data'			=> serialize(array(
									'description'	=> '发布指定话题#新人报道#即可获得该勋章',
									'alert_message'	=> '<a href="'.U('home/Account/medal',array('addon'=>'Medal','hook'=>'home_account_show')).'">快来参与指定话题讨论获得新勋章吧！</a>',
								)),
);