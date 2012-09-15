<?php if (!defined('THINK_PATH')) exit();?><?php if($async == 1): ?><script type="text/javascript">
	// 动态加载
	$(document).ready(function(){
		var url = "<?php echo U('home/Widget/renderWidget');?>";
		$.post(url, {name:'RelatedUser',param:'<?php echo $param; ?>'}, function(res){
			$('#_widget_relate_user').html(res);
		});
	});
	</script>
	<span id="_widget_relate_user"></span>
<?php else: ?>
	<div class="right_box">
	    <h2><span class="right fn f12px"><a href="javascript:void(0);" onclick="replace_user('<?php echo ($limit); ?>');"><?php echo L('make_change');?></a></span><?php echo ($title); ?></h2>
	    <ul class="interest_person" id="related_user">
	        <?php for ( $i = 0; $i < $limit; $i++ ) { ?>
	        <?php $vo = array_shift($user);
	            if (empty($vo)) break; ?>
	        <li id="related_user_<?php echo ($vo['uid']); ?>">
	            <div class="userPic"><a class="userface" href="<?php echo U('home/Space/index',array('uid'=>$vo['uid']));?>" rel="face" uid="<?php echo ($vo["uid"]); ?>">
	                <img src="<?php echo (getUserFace($vo['uid'],'m')); ?>" />
	            </a></div>
	            <div class="interest_info" style="width:120px;*width:110px">
	            <p><?php echo getUserSpace($vo["uid"],'fn','','{uname}') ?></p>
	          	<p><a href="javascript:void(0);" onclick="subscribe(<?php echo ($vo['uid']); ?>);" class="guanzhu"><?php echo L('add_follow');?></a></p>
	          <p class="cGray2"><?php echo ($vo['reason']); ?></p></div>
	        </li>
	        <?php } ?>
	    </ul>
	</div>
	
	<script type="text/javascript">
	    function subscribe(uid) {
	        $.post("<?php echo U('home/Widget/doFollowRelatedUser');?>", {uid:uid}, function(res){
	        	if('14' == res){
	        		ui.error('关注人数已超过配置最大数量，关注失败！');
	        	}else{
		            if ('0' == res) {
		                ui.success('关注失败');
		            }else{
						//followGroupSelectorBox(uid);
		                $('#related_user_'+uid).remove();
		                $('#related_user').append(res);
		            }
	        	}
	        });
	    }
	    
	    function replace_user(limit){
	    	$.post("<?php echo U('home/Widget/replaceRelatedUser');?>", {limit:limit}, function(res){
	            if ('0' == res) {
	                ui.success('没有更多推荐了');
	            }else {
	                //$('#related_user').html('');
	                $('#related_user').html(res);
	            }
	        });
	    }
	</script><?php endif; ?>