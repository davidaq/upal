<?php if (!defined('THINK_PATH')) exit();?><div class="medal_list">
    <?php if($show_alert && !empty($alert)) { ?>
    <div class="iine_warning lh20" style="margin:0 0 5px 0; padding:5px" id="_widget_medal_alert_div">
        <a title="关闭" class="del right" onclick="_widget_medal_close_alert();" href="javascript:void(0)" style="margin-right:0"></a><?php echo ($alert['content']); ?>
    </div>
    <?php } ?>
    <?php if(is_array($user_medal)): ?><?php $i = 0;?><?php $__LIST__ = $user_medal?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><div class="li">
	    <a href="javascript:void(0);" rel="_widget_medal" medal_id="<?php echo ($key); ?>" style="height:25px;">
	        <img src="<?php echo ($vo['icon_url']); ?>" width="25" height="25" alt="<?php echo ($vo['title']); ?>" />
	    </a>
        <div style="position:relative; width:100%; height:1px; z-index:99999" >
        <table id="_widget_medal_popbox_<?php echo ($key); ?>" rel="_widget_medal" medal_id="<?php echo ($key); ?>" cellspacing="0" cellpadding="0" border="0" class="boxy-wrapper" style="left:-159px;top:0px;display:none;" >
        <tbody>
	        <tr>
	            <td class="boxy-top-left"></td>
	            <td class="boxy-top"></td>
	            <td class="boxy-top-right"></td>
	        </tr>
	        <tr>
	            <td class="boxy-left"><img src="<?php echo __THEME__; ?>/images/zw_img.gif" width="6px" /></td>
	            <td class="boxy-inner">
	                <div style="position:relative; height:0; width:100%;"><div class="q_ico_arrow3" style="left:49%;"></div></div>
	                <div style="padding:15px; width:300px;">
	                    <div class="left" style="width:70px;"><img src="<?php echo ($vo['big_icon_url']); ?>"/></div>
	                    <div class="left" style="width:229px;">
	                        <h3 class="lh25 f14px"><strong><?php echo ($vo['title']); ?></strong></h3>
	                        <p><?php echo ($vo['description']); ?></p>
	                    </div>
	                    <div class="c"></div>
	                </div>
	               <div style="padding:5px 15px; background-color:#F7F7F7; border-top:1px dashed #9F9F9F; clear:both;">
                   <div class="honortip">
                    <?php if($vo['received_time'] != '0') { ?>
	                <div class="risucc">于 <span><?php echo ($vo['received_time']); ?></span> 获得</div>
	                <?php } ?>
	                <?php if($vo['next_level_time'] != '0') { ?>
	                <div class="upd"><span class="ico_upd"></span>升级：还需要 <span><?php echo ($vo['next_level_time']); ?></span></div>
	                <?php } ?> 
                   </div>
                   <?php if(!empty($_SESSION['mid'])){ ?>
	                <div class="operat">
	                   <span class="right"><a href="<?php echo U('home/Account/medal',array('type'=>'my','addon'=>'Medal','hook'=>'home_account_show'));?>" class="btn_w">查看勋章</a></span>
	                   <span class="ico_app_manage"></span>
	                   <a href="<?php echo U('home/Account/medal',array('type'=>'manage','addon'=>'Medal','hook'=>'home_account_show'));?>">显示设置</a>
	                </div>
	              <?php } ?>
	                </div>
	            </td>
	            <td class="boxy-right"><img src="<?php echo __THEME__; ?>/images/zw_img.gif" width="6px" /></td>
	        </tr>
	        <tr>
	            <td class="boxy-bottom-left"></td>
	            <td class="boxy-bottom"></td>
	            <td class="boxy-bottom-right"></td>
	        </tr>
	    </tbody>
	    </table>
	    </div>
    </div><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
    <div class="c"></div>
</div>
 
<script>
var _widget_medal_delay    = 0;
var _widget_medal_shown_id = 0;
var closeUrl = "<?php echo Addons::createAddonUrl('Medal','hook_ajax',array('do'=>'medalCloseAlert'));?>";
var medal_id = "<?php echo ($alert['medal_id']); ?>";
$(document).ready(function(){
    $("a[rel='_widget_medal']").hover(
        function(){
        	var medal_id = $(this).attr('medal_id');
        	
        	clearTimeout(_widget_medal_delay);
        	_widget_medal_delay = setTimeout(function(){
        		_widget_medal_show(medal_id);
        	}, 200);
        },
        function(){
        	clearTimeout(_widget_medal_delay);
        	_widget_medal_delay = setTimeout(function(){
        		_widget_medal_hide();
        	}, 200);
        }
    );
    
    $("table[rel='_widget_medal']").hover(
        function(){
        	clearTimeout(_widget_medal_delay);
        },
        function(){
        	_widget_medal_delay = setTimeout(function(){
                _widget_medal_hide();
            }, 200);
        }
    );
});

function _widget_medal_show(medal_id) {
	if (medal_id != _widget_medal_shown_id) {
        _widget_medal_hide();
		_widget_medal_shown_id = medal_id;
	    $("#_widget_medal_popbox_"+medal_id).fadeIn();
	}
}

function _widget_medal_hide() {
    _widget_medal_shown_id = 0;
	$("table[rel='_widget_medal']:visible").fadeOut();
}

function _widget_medal_close_alert() {
    $.post(closeUrl,{medal_id:medal_id},function(res){});
    $('#_widget_medal_alert_div').hide('slow');}
</script>