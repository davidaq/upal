<?php if (!defined('THINK_PATH')) exit();?><script type="text/javascript">
// 动态加载
$(document).ready(function(){
	var url = "<?php echo Addons::createAddonUrl('Medal', 'hook_ajax');?>";
	$.post(url, {uid:'<?php echo $uid; ?>'}, function(res){
		$('#_widget_medal').html(res);
	});
});
</script>
<div class="box_Medal app_line_w"><div id="_widget_medal"></div></div>