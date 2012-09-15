<?php if (!defined('THINK_PATH')) exit();?><?php if($list): ?><div class="feedBox">
		<?php echo W('WeiboList', array('mid' => $mid, 'list' => $list['data'], 'type' => 'index' == $type ? '' : $type, 'insert'=>'index' == $type ? 1 : 0));?>
	</div>
	<div class="c"></div>
	<div class="page" id="square_list_page"><?php echo ($list["html"]); ?></div>
<?php else: ?>
	<?php echo Addons::hook('home_square_index_list', array($type));?><?php endif; ?>