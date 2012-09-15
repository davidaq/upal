<?php if (!defined('THINK_PATH')) exit();?><?php if(is_array($star_list)): ?><?php $i = 0;?><?php $__LIST__ = $star_list?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$s): ?><?php ++$i;?><?php $mod = ($i % 2 )?><dl>
    <dd>
      <div class="userPic" style="float:none; margin:0 auto"><?php echo getUserSpace($s["uid"],'','','{uavatar}') ?></div>
      <h4>
        <?php echo getUserSpace($s["uid"],'','','{uname}') ?><br />
        <a href="#">&nbsp;</a>
      </h4>
    </dd>
   </dl><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>