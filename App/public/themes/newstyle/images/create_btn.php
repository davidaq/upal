<?php
session_start();
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
if(isset($_SESSION['language'])&&$_SESSION['language']=='en')
{
	readfile('create_btn_en.png');
}else
{
	readfile('create_btn.png');
}
?>
