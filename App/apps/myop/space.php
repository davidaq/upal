<?php
//初始化
require_once './common.php';

$uid = intval($_GET['uid']);
if ($uid <= 0) {
	redirect(U('home'), 5, '参数错误, 5秒后跳转至首页');
}else {
	redirect(U('home'), 0, '');
}