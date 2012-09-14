<?php 
return  array(
	'admin_sendinvitecode'   => array(
		'title' => '系统通知' ,
		'body'  => '系统向您发放了'.$num.'个邀请码',
		'other' =>'<a href="'.U('home/Account/invite').'" target="_blank">去看看</a></title>',
	),
	'admin_verified'   => array(
		'title' => '用户认证' ,
		'body'  => '恭喜您通过身份认证。',
	),
	'admin_rejectverified'   => array(
		'title' => '用户认证' ,
		'body'  => '抱歉，您的认证申请已被管理员驳回，请修改认证信息后重新提交。',
		'other' => '原因：' . $reason . ' <a href="'.U('home/Account/verified').'" target="_blank">重新认证</a></title>',
	),
	'admin_delverified'   => array(
		'title' => '用户认证' ,
		'body'  => '抱歉，您的用户认证已被管理员取消。',
		'other' => '原因：' . $reason,
	),
	'admin_notification'   => array(
		'title' => '<p style="color:red">系统通知：'.$title.'</p>' ,
		'body'  => $content,
	),
);