<?php
return array(
	'path_name'		=> 'developer',
	'title'			=> '开发达人',
	'data'			=> serialize(array(
									'icon_url'		=> SITE_URL . '/addons/plugins/Medal/lib/developer/icon.gif',
									'big_icon_url'	=> SITE_URL . '/addons/plugins/Medal/lib/developer/big_icon.gif',
									'description'	=> '提交Thinksns扩展组件，获得开发者勋章。尊贵标示，与众不同',
									'alert_message'	=> '我是开发者，我要展示自我！ <a href="'.U('develop/index/apply').'">提交新的扩展!</a>',
								)),
);