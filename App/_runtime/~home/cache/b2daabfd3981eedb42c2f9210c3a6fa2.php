<?php if (!defined('THINK_PATH')) exit();?><h2><?php echo ($title); ?></h2> 
<ul class="topic_list lineS_btm">
  <?php if('recommend' == $type): ?><?php if(is_array($hotTopic)): ?><?php $i = 0;?><?php $__LIST__ = $hotTopic?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><li>
		<a href="<?php echo U('home/User/topics',array('k'=>urlencode($vo['name'])));?>" title="<?php echo ($vo["name"]); ?>"><?php echo ($vo["name"]); ?>(<?php echo ($vo["count"]); ?>)</a><br />
	    <?php if($vo['note']): ?><div class="topic_tips">
	    	<div class="topic_arrt"><div class="topic_arr"></div></div>
	        <div class="topic_info"><?php echo ($vo['note']); ?></div>
	    </div><?php endif; ?>
    </li><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
  <?php else: ?>
  <?php if(is_array($hotTopic)): ?><?php $i = 0;?><?php $__LIST__ = $hotTopic?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><li>
    	<a href="<?php echo U('home/User/topics',array('k'=>urlencode($vo['name'])));?>" title="<?php echo ($vo["name"]); ?>"><?php echo ($vo["name"]); ?>(<?php echo ($vo["count"]); ?>)</a><br />
    </li><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?><?php endif; ?>
</ul>