<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<!-- QQ登录 -->
<meta property="qc:admins" content="61701556566401633636375" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>
<?php if(($ts['site']['page_title'])  !=  ""): ?><?php echo ($ts['site']['page_title']); ?> <?php echo ($ts['site']['site_name']); ?>
<?php else: ?>
    <?php echo ($ts['site']['site_name']); ?><?php endif; ?>    
</title>
<link rel="shortcut icon" href="__THEME__/favicon.ico" />
<meta name="keywords" content="<?php echo ($ts['site']['site_header_keywords']); ?>" />
<meta name="description" content="<?php echo ($ts['site']['site_header_description']); ?>" />
<script>
	var _UID_   = <?php echo (int) $uid; ?>;
	var _MID_   = <?php echo (int) $mid; ?>;
	var _ROOT_  = '__ROOT__';
	var _THEME_ = '__THEME__';
	var _PUBLIC_ = '__PUBLIC__';
	var _LENGTH_ = <?php echo (int) $GLOBALS['ts']['site']['length']; ?>;
	var _LANG_SET_ = '<?php echo LANG_SET; ?>';
	var $CONFIG = {};
		$CONFIG['uid'] = _UID_;
		$CONFIG['mid'] = _MID_;
		$CONFIG['root_path'] =_ROOT_;
		$CONFIG['theme_path'] = _THEME_;
		$CONFIG['public_path'] = _PUBLIC_;
		$CONFIG['weibo_length'] = <?php echo (int) $GLOBALS['ts']['site']['length']; ?>;
		$CONFIG['lang'] =  '<?php echo LANG_SET; ?>';
    var bgerr;
    try { document.execCommand('BackgroundImageCache', false, true);} catch(e) {  bgerr = e;}
</script>
<!-- 全局风格CSS -->
<link href="__THEME__/public.css?20110429" rel="stylesheet" type="text/css" />
<link href="__THEME__/layout.css?20110429" rel="stylesheet" type="text/css" />
<link href="__THEME__/main.css?20110429" rel="stylesheet" type="text/css" />
<link href="__PUBLIC__/js/tbox/box.css" rel="stylesheet" type="text/css" />
<!-- 核心JS加载 -->
<script type="text/javascript" src="__PUBLIC__/js/jquery.js"></script>
<script type="text/javascript" src="__PUBLIC__/js/common.js"></script>
<script type="text/javascript" src="__PUBLIC__/js/tbox/box.js"></script>
<script type="text/javascript" src="__PUBLIC__/js/scrolltopcontrol.js"></script>
<script type="text/javascript" src="__PUBLIC__/js/weibo.js"></script>
<script src="__PUBLIC__/js/jquery.jgrow.min.js"></script>
<script src="__PUBLIC__/js/jquery.isotope.min.js"></script>

<!-- 编辑器样式文件 -->
<link href="__PUBLIC__/js/editor/editor/theme/base-min.css" rel="stylesheet"/>
<!--[if lt IE 8]><!-->
<link href="__PUBLIC__/js/editor/editor/theme/cool/editor-pkg-sprite-min.css" rel="stylesheet"/>
<!--<![endif]-->
<!--[if gte IE 8]><!-->
<link href="__PUBLIC__/js/editor/editor/theme/cool/editor-pkg-min-datauri.css" rel="stylesheet"/>
<!--<![endif]-->
<?php echo Addons::hook('public_head',array('uid'=>$uid));?>
</head>

<body class="page_home">
<div class="wrap">

<?php if(isset($_SESSION["userInfo"])): ?><?php if(isMobile()){ ?>
<!--顶部导航-->
<style>
.page_home{background:#e4e4e4 repeat center top;_padding:0}
.content_holder{margin-top:10px;}
</style>
<div class="top_holder">
 <div class="header">
 <div class="logo_holder">
    <!--个人信息区-->
    <ul class="person per-info">
    <li><?php echo getUserSpace($mid,'fb14 username nocard info-bg','','',false);?></li>
    <li class="header_dropdown"><a href="#" class="application li-bg">消息<span class="ico_arrow arrow-bg"></span></a>
          <div class="dropmenu ip-dropmenu">
                <ul class="message_list_container message_list_new">
                </ul>
                <dl class="message">
          <dd><a href="<?php echo U('home/message/index');?>">查看私信<?php if(($userCount['message'])  >  "0"): ?>(<?php echo ($userCount["message"]); ?>)<?php endif; ?></a></dd> 
          <dd><a href="<?php echo U('home/user/atme');?>">查看@我<?php if(($userCount['atme'])  >  "0"): ?>(<?php echo ($userCount["atme"]); ?>)<?php endif; ?></a></dd> 
          <dd><a href="<?php echo U('home/user/comments');?>">查看评论<?php if(($userCount['comment'])  >  "0"): ?>(<?php echo ($userCount["comment"]); ?>)<?php endif; ?></a></dd> 
          <dd><a href="<?php echo U('home/message/notify');?>">系统通知<?php if(($userCount['notify'])  >  "0"): ?>(<?php echo ($userCount["notify"]); ?>)<?php endif; ?></a></dd> 
          <dd><a href="<?php echo U('home/message/appmessage');?>">应用消息<?php if(($userCount['appmessage'])  >  "0"): ?>(<?php echo ($userCount["appmessage"]); ?>)<?php endif; ?></a></dd> 
                </dl>
                <dl class="square_list">
                <dd><a href="javascript:ui.sendmessage(0)">发私信</a></dd>
                </dl>
          </div>
        </li>
    <li class="header_dropdown"><a href="#" class="application li-bg">帐号<span class="ico_arrow arrow-bg"></span></a>
          <div class="dropmenu ip-dropmenu">
                <dl class="setup">
                <dd><a href="<?php echo U('home/User/findfriend');?>"><span class="ico_pub ico_pub_find"></span>找人</a></dd>
                <dd><a href="<?php echo U('home/Account');?>"><span class="ico_pub ico_pub_set"></span>设置</a></dd>
                <dd><a href="<?php echo U('home/Account/invite');?>"><span class="ico_pub ico_pub_invitation"></span>邀请</a></dd>
                <dd><a href="<?php echo U('home/Account/weiboshare');?>"><span class="ico_pub ico_pub_tool"></span>小工具</a></dd>
                <?php echo Addons::hook('header_account_tab', array('menu' => & $header_account_drop_menu));?>
                <?php if(is_array($header_account_drop_menu)): ?><?php $i = 0;?><?php $__LIST__ = $header_account_drop_menu?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><dd><a href="<?php echo ($vo['url']); ?>"><span class="ico_pub ico_pub_<?php echo ($vo['act']); ?>"></span><?php echo ($vo['name']); ?></a></dd><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
                <?php if(($isSystemAdmin)  ==  "TRUE"): ?><dd><a href="<?php echo U('admin/index/index');?>"><span class="ico_pub"><img src="__THEME__/images/audit.png" /></span>后台管理</a></dd><?php endif; ?>
                </dl>
                <dl class="square_list_add">
                <dd><a href="<?php echo U('home/Public/logout');?>"><span class="ico_pub ico_pub_signout"></span>退出</a></dd>
                </dl>
          </div>
        </li>
    </ul>
  <!--/个人信息区-->
  <!--消息提示框-->
    <div id="message_list_container" class="layer_massage_box" style="display:none;">
      <ul id="is_has_message" class="message_list_container">
        </ul>
        <a href="javascript:void(0)" onclick="ui.closeCountList(this)" class="del"></a>
    </div>
  <!--/消息提示框-->
    
    <div class="nav nav-left">
      <ul>
        <li><a href="<?php echo U('home');?>" class="fb14 nav-bg">首页</a></li>
    <li class="header_dropdown"><a href="#" class="application li-bg">广场<span class="ico_arrow arrow-bg"></span></a>
          <div class="dropmenu ip-dropmenu">
                <dl class="square_list">
                <dd><a href="<?php echo U('home/Square/top');?>"><span class="ico_pub ico_pub_billboard"></span>风云榜</a></dd>
                <dd><a href="<?php echo U('home/Square/star');?>"><span class="ico_pub ico_pub_hall"></span>名人堂</a></dd>
                <?php echo Addons::hook('header_square_tab', array('menu' => & $header_square_expend_menu));?>
                <?php if(is_array($header_square_expend_menu)): ?><?php $i = 0;?><?php $__LIST__ = $header_square_expend_menu?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><dd><a href="<?php echo U('home/Square/' . $vo['act'], $vo['param']);?>"><span class="ico_pub ico_pub_<?php echo ($vo['act']); ?>"></span><?php echo ($vo['name']); ?></a></dd><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
            </dl>
          </div>
        </li>
        <li class="header_dropdown"><a href="#" class="application li-bg">应用<span class="ico_arrow arrow-bg"></span></a>
          <div class="dropmenu ip-dropmenu">
            <dl class="app_list">
                <?php foreach ($ts['user_app'] as $_temp_type => $_temp_apps) { ?>
                <?php foreach ($_temp_apps as $_temp_app) { ?>
                    <dd>
                        <?php if($_temp_type == 'local_app' || $_temp_type == 'local_default_app') { ?>
                        <a href="<?php echo $_temp_app['app_entry'];?>" class="a14">
                            <img class="app_ico" src="<?php echo $_temp_app['icon_url'];?>" />
                            <?php echo $_temp_app['app_alias'];?>
                        </a>
                        <?php }else { ?>
                        <a href="__ROOT__/apps/myop/userapp.php?id=<?php echo $_temp_app['app_id'];?>" class="a14">
                            <img class="app_ico" src="http://appicon.manyou.com/icons/<?php echo $_temp_app['app_id'];?>" />
                            <?php echo $_temp_app['app_alias'];?>
                        </a>
                        <?php }?>
                    </dd>
                <?php } // end of foreach?>
                <?php } // end of foreach?>
                </dl>
                <dl class="app_list_add">
                <dd><a href="<?php echo U('home/Index/addapp');?>"><span class="ico_app_add"></span>添加更多应用</a></dd>
                </dl>
          </div>
        </li>
		
      </ul>
    </div>
 </div>
  <form action="<?php echo U('home/user/search');?>" id="quick_search_form" method="post">
    <div>
    <div class="soso br3 line"><label id="_header_search_label" style="display: block;" onclick="$(this).hide();$('#_header_search_text').focus();">搜名字/标签/微博</label><input type="text" class="line-text" value="" name="k" id="_header_search_text" onblur="if($(this).val()=='') $('#_header_search_label').show();"/></div><input name="" type="button" onclick="$('#quick_search_form').submit()" class="ip-serach hand br3"/></div>
  <script>
  if($('#_header_search_text').val()=='')
    $('#_header_search_label').show();
  else
    $('#_header_search_label').hide();
  </script>
    </form>
  </div>
</div>
<?php }else{ ?>
<!--顶部导航-->
<div class="header_holder">
 <div class="header">
 <div class="logo_holder" style="width:700px">
    <div class="logo"><a href="<?php echo U('home/Index');?>"><img src="<?php echo $ts['site']['site_logo']?$ts['site']['site_logo']:__THEME__.'/images/logo.png'; ?>" style="_filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true,sizingMethod=crop)" /></a></div>
    <form action="<?php echo U('home/user/search');?>" id="quick_search_form" method="post">
    <div class="soso br3"><label id="_header_search_label" style="display: block;" onclick="$(this).hide();$('#_header_search_text').focus();"><?php echo L('搜名字/标签/微博');?></label><input type="text" class="so_text" value="" name="k" id="_header_search_text" onblur="if($(this).val()=='') $('#_header_search_label').show();"/><input name="" type="button" onclick="$('#quick_search_form').submit()" class="so_btn hand br3"/></div>
	<script>
	if($('#_header_search_text').val()=='')
		$('#_header_search_label').show();
	else
		$('#_header_search_label').hide();
	</script>
    </form>
    <div class="nav">
      <ul>
        <li><a href="<?php echo U('home');?>" class="fb14"><?php echo L('首页');?></a></li>
		<li class="header_dropdown"><a href="<?php echo U('home/Square/index');?>" class="application"><?php echo L('广场');?><span class="ico_arrow"></span></a>
          <div class="dropmenu">
                <dl class="square_list">
                <dd><a href="<?php echo U('home/Square/top');?>"><span class="ico_pub ico_pub_billboard"></span><?php echo L('风云榜');?></a></dd>
                <dd><a href="<?php echo U('home/Square/star');?>"><span class="ico_pub ico_pub_hall"></span><?php echo L('名人堂');?></a></dd>
                <?php echo Addons::hook('header_square_tab', array('menu' => & $header_square_expend_menu));?>
				<?php if(is_array($header_square_expend_menu)): ?><?php $i = 0;?><?php $__LIST__ = $header_square_expend_menu?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><dd><a href="<?php echo U('home/Square/' . $vo['act'], $vo['param']);?>"><span class="ico_pub ico_pub_<?php echo ($vo['act']); ?>"></span><?php echo ($vo['name']); ?></a></dd><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
            </dl>
          </div>
        </li>
        <?php if(!empty($my_group_list)){ ?>
		<li id="iframe_group_li" class="header_dropdown"><a href="<?php echo U('group/index/newIndex');?>" class="application"><?php echo L('群组');?><span class="ico_arrow"></span></a>
          <div id="iframe_group" class="dropmenu"><iframe id="iframe_g" style="position:absolute;_filter:alpha(opacity=0);opacity=0;z-index:-1;width:100%;height:100%;top:0;left:0;scrolling:no;" frameborder="0" src="about:blank"></iframe>
                <dl class="group_list">
                            <?php $moreGroup = false; ?>
                            <?php foreach($my_group_list as $key=>$value){ ?>
                                <dd><a href="<?php echo U('group/group/index',array('gid'=>$value['id']));?>"><?php echo ($value['name']); ?></a></dd>
                                 <?php if($key>=5){
                                       $moreGroup = true;
                                       break;
                                       } ?>
                            <?php } ?>
                </dl>
                <dl class="group_list_add">
                <dd><?php if($moreGroup){ ?><a href="<?php echo U('group/SomeOne');?>" class="right"><?php echo L('more');?>&raquo;</a><?php } ?><a href="<?php echo U('group/Index/add');?>"><?php echo L('创建群组');?></a></dd>
                </dl>
          </div>
        </li>
        <?php } ?>
        <?php
        /*
        <li id="iframe_app_li" class="header_dropdown"><a href="<?php echo U('home/Index/addapp');?>" class="application"><?php echo L('app');?><span class="ico_arrow"></span></a>
          <div id="iframe_app" class="dropmenu"><iframe id="iframe_a" style="position:absolute;_filter:alpha(opacity=0);opacity=0;z-index:-1;width:100%;height:100%;top:0;left:0;scrolling:no;" frameborder="0" src="about:blank"></iframe>
            <dl class="app_list">
                <?php foreach ($ts['user_app'] as $_temp_type => $_temp_apps) { ?>
                <?php foreach ($_temp_apps as $_temp_app) { ?>
                    <dd>
                        <?php if($_temp_type == 'local_app' || $_temp_type == 'local_default_app') { ?>
                        <a href="<?php echo $_temp_app['app_entry'];?>" class="a14">
                            <img class="app_ico" src="<?php echo $_temp_app['icon_url'];?>" />
                            <?php echo $_temp_app['app_alias'];?>
                        </a>
                        <?php }else { ?>
                        <a href="__ROOT__/apps/myop/userapp.php?id=<?php echo $_temp_app['app_id'];?>" class="a14">
                            <img class="app_ico" src="http://appicon.manyou.com/icons/<?php echo $_temp_app['app_id'];?>" />
                            <?php echo $_temp_app['app_alias'];?>
                        </a>
                        <?php }?>
                    </dd>
                <?php } // end of foreach?>
                <?php } // end of foreach?>
                </dl>
                <dl class="app_list_add">
                <dd><a href="<?php echo U('home/Index/addapp');?>"><span class="ico_app_add"></span><?php echo L('add_apps');?></a></dd>
                </dl>
          </div>
        </li>
        */
        ?>
        <?php if(L('change_language')!='change_language'){ ?>
        <li><a href="<?php echo U('home/index/switch_lan');?>" class="fb14"><?php echo L('change_language');?></a></li>
        <?php } ?>
  		<?php echo Addons::hook('header_topnav', array('menu' => & $header_topnav));?>
  		<?php if(is_array($header_topnav)): ?><?php $i = 0;?><?php $__LIST__ = $header_topnav?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><li><a href="<?php echo ($vo['url']); ?>" class="fb14"><?php echo ($vo['name']); ?></a></li><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
      </ul>
    </div>
 </div>
	<!--个人信息区-->
    <ul class="person">
		<li><?php echo getUserSpace($mid,'fb14 username nocard','','',false);?></li>
		<li class="header_dropdown" id="message_show"><a href="<?php echo U('home/message/index');?>" class="application"><?php echo L('message');?><span class="ico_arrow"></span></a>
          <div class="dropmenu">
                <ul class="message_list_container message_list_new">
                </ul>
                <dl class="message">
      					<dd><a href="<?php echo U('home/message/index');?>"><?php echo L('查看私信');?><?php if(($userCount['message'])  >  "0"): ?>(<?php echo ($userCount["message"]); ?>)<?php endif; ?></a></dd> 
      					<dd><a href="<?php echo U('home/user/atme');?>"><?php echo L('查看@我');?><?php if(($userCount['atme'])  >  "0"): ?>(<?php echo ($userCount["atme"]); ?>)<?php endif; ?></a></dd> 
      					<dd><a href="<?php echo U('home/user/comments');?>"><?php echo L('查看评论');?><?php if(($userCount['comment'])  >  "0"): ?>(<?php echo ($userCount["comment"]); ?>)<?php endif; ?></a></dd> 
      					<dd><a href="<?php echo U('home/message/notify');?>"><?php echo L('系统通知');?><?php if(($userCount['notify'])  >  "0"): ?>(<?php echo ($userCount["notify"]); ?>)<?php endif; ?></a></dd> 
      					<dd><a href="<?php echo U('home/message/appmessage');?>"><?php echo L('app_message');?><?php if(($userCount['appmessage'])  >  "0"): ?>(<?php echo ($userCount["appmessage"]); ?>)<?php endif; ?></a></dd> 
                </dl>
                <dl class="square_list">
                <dd><a href="javascript:ui.sendmessage(0)"><?php echo L('发私信');?></a></dd>
                </dl>
          </div>
        </li>
		<li class="header_dropdown" id="account_show"><a href="<?php echo U('home/Account');?>" class="application"><?php echo L('帐号');?><span class="ico_arrow"></span></a>
          <div class="dropmenu">
                <dl class="setup">
                <dd><a href="<?php echo U('home/User/findfriend');?>"><span class="ico_pub ico_pub_find"></span><?php echo L('find_people');?></a></dd>
                <dd><a href="<?php echo U('home/Account');?>"><span class="ico_pub ico_pub_set"></span><?php echo L('setting');?></a></dd>
                <dd><a href="<?php echo U('home/Account/invite');?>"><span class="ico_pub ico_pub_invitation"></span><?php echo L('invite');?></a></dd>
                <dd><a href="<?php echo U('home/Account/weiboshare');?>"><span class="ico_pub ico_pub_tool"></span><?php echo L('小工具');?></a></dd>
                <?php echo Addons::hook('header_account_tab', array('menu' => & $header_account_drop_menu));?>
				        <?php if(is_array($header_account_drop_menu)): ?><?php $i = 0;?><?php $__LIST__ = $header_account_drop_menu?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><dd><a href="<?php echo ($vo['url']); ?>"><span class="ico_pub ico_pub_<?php echo ($vo['act']); ?>"></span><?php echo ($vo['name']); ?></a></dd><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
                <?php if(($isSystemAdmin)  ==  "TRUE"): ?><dd><a href="<?php echo U('admin/index/index');?>"><span class="ico_pub ico_pub_admin"></span><?php echo L('后台管理');?></a></dd><?php endif; ?>
                </dl>
                <dl class="square_list_add">
                <dd><a href="<?php echo U('home/Public/logout');?>"><span class="ico_pub ico_pub_signout"></span><?php echo L('退出');?></a></dd>
                </dl>
          </div>
        </li>
    </ul>
	<!--/个人信息区-->
	<!--消息提示框-->
    <div id="message_list_container" class="layer_massage_box" style="display:none;">
    	<ul id="is_has_message" class="message_list_container">
        </ul>
        <a href="javascript:void(0)" onclick="ui.closeCountList(this)" class="del"></a>
    </div>
	<!--/消息提示框-->
  </div>
</div>
<!--/顶部导航-->
<?php } ?><?php endif; ?>
<?php if( !isset($_SESSION["userInfo"])): ?><div class="header_holder">
    <div class="header">
      <div class="logo"><a href="<?php echo U('home/Index');?>"><img src="<?php echo $ts['site']['site_logo']?$ts['site']['site_logo']:__THEME__.'/images/logo.png'; ?>" style="_filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true,sizingMethod=crop)" /></a></div>
      <div id="indt" class="nav_sub br3">
        <p>
	      <a href="<?php echo __ROOT__;?>/?language=<?php
			if(isset($_SESSION['language'])&&$_SESSION['language']=='en')
				echo 'zh_cn';
			else
				echo 'en';
	      ?>" class="fb14"><?php echo L('change_language');?></a>&nbsp;|&nbsp;
      	<?php if(($ts['site']['site_anonymous_square'])  ==  "1"): ?><a href="<?php echo U('home/Square');?>"><?php echo L('微博广场');?></a>&nbsp;|&nbsp;<?php endif; ?>
      	<a href="<?php echo U('home/Public/register');?>"><?php echo L('reg');?></a>&nbsp;|&nbsp;
      	<a href="javascript:ui.quicklogin();"><?php echo L('login');?></a>
        <p>
      </div>
  </div>
</div><?php endif; ?>
<?php echo constant(" 头部广告 *");?>
<?php if(is_array($ts['ad']['header'])): ?><?php $i = 0;?><?php $__LIST__ = $ts['ad']['header']?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><div class="ad_header"><div class="ke-post"><?php echo ($vo['content']); ?></div></div><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>  

<script>
$(document).ready(function(){
	$(".header_dropdown").hover(
		function(){ 
      var type = $(this).attr('id');
      if(type == 'message_show' || type == 'account_show') {
        var obj = document.getElementById('message_list_container');
        if(obj !== null) {
          var isHas = $('#is_has_message').html();
          if(isHas) {
            $('#message_list_container').css("display", 'none');
          }
        }
      }
      $(this).addClass("hover"); 
    },
		function(){ 
      var type = $(this).attr('id');
      if(type == 'message_show' || type == 'account_show') {
        var obj = document.getElementById('message_list_container');
        if(obj !== null) {
          var isHas = $('#is_has_message').html();
          if(isHas) {
            $('#message_list_container').css("display", '');
          }
        }
      }
      $(this).removeClass("hover"); 
    }
	);
	
	<?php if($mid > 0) { ?>
		ui.countNew();
		setInterval("ui.countNew()",120000);
	<?php } ?>
});
</script>

<?php echo constant(" 注册引导 *");?>
<?php if(!$mid && APP_NAME.'/'.MODULE_NAME != 'home/Public' && APP_NAME.'/'.MODULE_NAME != 'home/Index'){ ?>
<div class="content no_bg" style=" margin-bottom:10px;overflow:hidden;zoom:1">
  <div  style="padding:10px 15px;zoom:1">
    <div style="float:right; width:220px; text-align:center; padding-top:5px;font-size:14px"><a class="regbtn" title="立即注册" href="<?php echo U('home/Public/register');?>"> &nbsp;</a><br />
      <?php echo L('有帐号？');?><a href="<?php echo U('home/Public/login');?>"><strong><?php echo L('马上登录');?></strong></a></div>
    <div style=" margin-right:250px;">
      <h2 class="f18px lh30 fB"><?php echo L('欢迎来到');?><?php echo ($ts['site']['site_name']); ?><?php echo L('，赶紧注册吧！');?></h2>
      <p class="f14px cGray2"><?php echo L('与大家交友，一起分享点点滴滴的快乐！');?></p>
    </div>
  </div>
</div>
<?php } ?>

<script type="text/javascript">
$(function() {
  $('#iframe_group_li').live('mousemove', function() {
    var group_width = $('#iframe_group').width();
    var group_height = $('#iframe_group').height();
    $('#iframe_g').css('width', group_width);
    $('#iframe_g').css('height', group_height);
  });
  $('#iframe_app_li').live('mousemove', function() {
    var app_width = $('#iframe_app').width();
    var app_height = $('#iframe_app').height();
    $('#iframe_a').css('width', app_width);
    $('#iframe_a').css('height', app_height);
  });
  $('.btn_blog_c, .btn_blog_s, .btn_photo_c, .btn_photo_s, .btn_group_c, .btn_group_s, .btn_vote_c, .btn_event_c, .btn_poster_c, .btn_faq_c, .btn_forum_c, .btn_company_c, .btn_company_c:hover').css('background-image','url(public/themes/newstyle/images/create_btn.php/<?php echo time() ?>.png)');
});
</script>

<script type="text/javascript" src="__PUBLIC__/js/jquery.form.js"></script>
<script type="text/javascript" src="__PUBLIC__/js/weibo.js"></script>
<script type="text/javascript" src="__PUBLIC__/js/jquery.autocomplete.js"></script>
<script src="../Public/js/slides.min.jquery.js"></script>
<div class="content_holder"> 
<div class="content"><!-- 内容 begin  --> 
     <?php echo Addons::hook('home_index_left_top_outline');?>
<div class="user_app"><!-- 用户组件列表 begin -->
    <?php echo Addons::hook('home_index_left_top');?>

	<div class="user_app_top"></div>
    <div class="user_app_box">
  <div class="userinfo">
    	<div class="userpic" onmouseover="this.className='userpic over'" onmouseout="this.className='userpic'" >
			<span id="my_face"><?php echo getUserSpace($mid,'nocard','','{uavatar}') ?></span>
			<a class="pic" href="<?php echo U('home/account/index');?>#face"><?php echo L('setting');?></a>
		</div>
  		<div class="user_name">
        	<h2><?php echo ($userInfoCache['uname']); ?><?php Addons::hook('user_name_end', array('uid'=>$mid, 'html'=>&$user_name_end));echo $user_name_end; ?><?php echo (getUserGroupIcon($mid)); ?></h2>
            <?php $user_credit = $userInfoCache['credit'];
            	foreach($user_credit as $k => $v) { ?>
           		<p><?php echo L($v['alias']);?>: <a href="<?php echo U('home/Account/credit');?>" title="<?php echo ($v['credit']); ?>"><span class="cRed"><?php echo ($v['credit']); ?></span></a></p>
            <?php }
                unset($user_credit); ?>
        </div>
  </div>
  <?php echo Addons::hook('home_index_left_avatar_bottom');?>
  <!--关注-->
  <div class="user_follow app_line">
  	<span><a href="<?php echo U('home/space/follow',array('type'=>'following', 'uid'=>$mid));?>"><strong><?php echo ($userInfoCache['following']); ?></strong><br /><?php echo L('follow');?></a></span>
    <span class="app_lineL"><a href="<?php echo U('home/space/follow',array('type'=>'follower', 'uid'=>$mid));?>"><strong><?php echo ($userInfoCache['follower']); ?></strong><br /><?php echo L('fans');?></a></span>
    <span class="app_lineL"><a href="<?php echo U('home/space/index',array('uid'=>$uid));?>"><strong id="miniblog_count"><?php echo ($userInfoCache['miniNum']); ?></strong><br /><?php echo L('微博 ');?></a></span>
  </div>
  <!--关注 end-->
  <div class="celerity_menu app_line">
  	<ul>
        <li><a href="<?php echo U('home/user/index');?>" <?php echo getMenuState('user/index');?>><span class="ico_side ico_home"></span><?php echo L('我的首页');?></a></li>
        <li><a href="<?php echo U('home/user/atme');?>" <?php echo getMenuState('user/atme');?>><span class="ico_side ico_atme"></span><?php echo L('提到我的');?> 
        <span id="app_left_count_atme"><?php if(($userCount['atme'])  >  "0"): ?>(<font color="red"><?php echo ($userCount["atme"]); ?></font>)<?php endif; ?></span>
        </a>
        </li>
        <li><a href="<?php echo U('home/user/collection');?>" <?php echo getMenuState('user/collection');?>><span class="ico_side ico_favorites"></span><?php echo L('my_fav');?></a></li>
        <li><a href="<?php echo U('home/user/comments');?>" <?php echo getMenuState('user/comments');?>><span class="ico_side ico_comment"></span><?php echo L('我的评论');?> 
        <span id="app_left_count_comment"><?php if(($userCount['comment'])  >  "0"): ?>(<font color="red"><?php echo ($userCount["comment"]); ?></font>)<?php endif; ?></span>
        </a>
        </li>
        <?php if(Addons::requireHooks('home_index_left_tab')): ?><?php echo Addons::hook('home_index_left_tab', array(&$menu));?>
            <?php if(is_array($menu)): ?><?php $i = 0;?><?php $__LIST__ = $menu?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><li><a href="<?php echo U('home/user/' . $vo['act']);?>" <?php echo getMenuState('user/' . $key);?>><?php echo L($vo['name']);?></a></li><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?><?php endif; ?>
  	</ul>
  </div>
  <div class="celerity_menu app_line">
  	<ul>
  	<?php
  	function myLang($txt){
  		if(isset($_SESSION['language'])&&'en'==$_SESSION['language'])
  		{
  			static $_L=array(
  				'日志'=>'Blog',
  				'相册'=>'Blog',
  			);
  			if(isset($_L[$txt]))
	  			return $_L[$txt];
  		}
		return $txt;
  	}
  	?>
  	    <?php if(is_array($install_app)): ?><?php $i = 0;?><?php $__LIST__ = $install_app?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><?php if(empty($vo['app_alias'])) continue; ?>
  	        <li>
				<a href="<?php echo ($vo["app_entry"]); ?>" title="<?php echo ($vo["description"]); ?>" class="user_app_link" >
				<?php /*if($vo['sidebar_entry']){ ?>
				    <span class="user_app_entry" target="_blank" url="<?php echo ($vo["sidebar_entry"]); ?>"><?php echo ($vo["sidebar_title"]); ?></span>
                <?php }*/ ?>
                <img src="<?php echo getAppIconUrl($vo['icon_url'],$vo['app_name']);;?>" class="user_app_icon" /><?php echo getShort(myLang($vo['app_alias']),5,'...');?></a>
  	        </li><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
  	</ul>
  </div>
    <?php
    /*
	<div class="app_add app_line">
    <span class="right"><span class="ico_app_manage"></span><a href="<?php echo U('home/Index/editapp');?>"><?php echo L('管理');?></a></span>
    <span><span class="ico_app_add"></span><a href="<?php echo U('home/Index/addapp');?>"><?php echo L('add');?></a></span>
    </div>
    */
    ?>

    <?php echo Addons::hook('home_index_left_middle');?>

    </div>
	<div class="user_app_btm"></div>
    <?php echo Addons::hook('home_index_left_bottom');?>
    <?php if (Addons::requireHooks('home_index_left_advert')) { ?>
    	<?php echo Addons::hook('home_index_left_advert', array($ts['ad']['left']));?>
    <?php } else { ?>
		<?php if(is_array($ts['ad']['left'])): ?><?php $i = 0;?><?php $__LIST__ = $ts['ad']['left']?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><div class="ad_left"><div class="ke-post"><?php echo ($vo["content"]); ?></div></div><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
    <?php } ?>
	</div><!-- 用户组件列表 end -->
<?php function getMenuState($type){
	$type = split('/',$type);
	if( strtolower(MODULE_NAME)==strtolower($type[0]) && ( strtolower(ACTION_NAME)==strtolower($type[1]) || $type[1]=='*') ){
		return 'class="on"';
	}
} ?>

  <div class="main">
    <div class="mainbox">
      <div class="mainbox_R">
		<?php if(is_array($ts['ad']['right_top'])): ?><?php $i = 0;?><?php $__LIST__ = $ts['ad']['right_top']?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><div class="ke-post setskinbox lineS_btm">
			<?php echo ($vo['content']); ?>
		</div><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
		  <?php echo Addons::hook('home_index_right_top');?>
        <div class="right_box lineS_btm">
          <h2><a href="javascript:void(0)" onclick="$('.quick_win').show()" class="right" style="font-weight:400;font-size:12px"><?php echo L('add');?></a><?php echo L('follow_topic');?></h2>
          <div style="display:none;" class="quick_win">
            <a href="javascript:void(0)" onclick="$('.quick_win').hide()" class="del" title="<?php echo L('close');?>"></a>
            <p>
              <input type="text" class="text" name="quick_name" style=" width:130px;display:block;margin:0 0 5px 0"/>
              <input type="button" onclick="addFollowTopic()" value="<?php echo L('save');?>" class="btn_b" />
            </p>
            <p class="cGray2"><?php echo L('add_follow_topic');?></p>
          </div>
          <ul class="topic_list" rel="followTopicArea">
            <?php if(is_array($followTopic)): ?><?php $i = 0;?><?php $__LIST__ = $followTopic?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><li onmouseover="$(this).find('.del').show()" onmouseout="$(this).find('.del').hide()"><a class="del right" style="display:none" title="<?php echo L('del');?>" href="javascript:void(0)" onclick="deleteFollowTopic(this,'<?php echo ($vo["topic_id"]); ?>')"></a><a href="<?php echo U('home/user/topics',array('k'=>urlencode($vo['name'])));?>" title="<?php echo ($vo["name"]); ?>"><?php echo ($vo["name"]); ?></a></li><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
          </ul>
        </div>
        <div class="right_box">
          <?php echo W('HotTopic', array('type'=>'recommend','limit'=>5));?>
          <?php if(ACTION_NAME == "index"){ ?>
          <?php echo W('HotTopic', array('type'=>'auto','limit'=>5));?>
          <?php } ?>
        </div>
          <?php echo Addons::hook('home_index_right_bottom');?>
        <?php if (Addons::requireHooks('home_index_right_advert')) { ?>
        	<?php echo Addons::hook('home_index_right_advert', array($ts['ad']['right']));?>
        <?php } else { ?>
			<?php if(is_array($ts['ad']['right'])): ?><?php $i = 0;?><?php $__LIST__ = $ts['ad']['right']?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><div class="ad_right"><div class="ke-post lineS_btm"><?php echo ($vo["content"]); ?></div></div><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
		<?php } ?>
      </div>
      <div class="mainbox_C main_pad" style="padding-top:10px;">
      <div class="clearfix">
        <?php if ($announcement['is_open'] && !empty($announcement['content'])) { ?>
        <div class="iine_warning" id="announcement"><a class="del right hover" href="javascript:void(0);" onclick="close_announcement();"></a><b class="ico_speaker"></b>
          <?php echo (formatUrl($announcement['content'])); ?>
        </div>
        <?php } ?>
        <?php switch(ACTION_NAME): ?><?php case "index":  ?><?php echo Addons::hook('home_index_middle_top');?>
          <div class="talkBox send_weibo" style="height:150px">
            <form method="post" action="<?php echo U('weibo/operate/publish');?>" id="miniblog_publish" oncomplete="false">
              <input type="hidden" name="publish_type" value="0">
              <h2>
                <div class="wordNum numObj"><?php echo L('you_can_input');?><strong id="strconunt"><?php echo ($GLOBALS['ts']['site']['length']); ?></strong><?php echo L('more_characters');?></div>
                <span><?php echo L('virtue_of_recording');?></span>
                <!--<div class="title"><img src="../Public/images/show_img.jpg" /></div>-->
              </h2>
              <div class="cntBox">
                <input type="button" disabled="true" error="<?php echo L('failed_to_publish');?>" class="btn_big_disable hand buttonObj" value="&nbsp;" id="publish_handle"  />
                <textarea name="content" id="content_publish" cols="" rows="" class="contentObj" style="max-width:83.5%;min-width:83.5%;width:83.5%;height:55px; max-height:55px;min-height:55px;padding:5px; margin:0; _padding:5;overflow: hidden;overflow-x:hidden;overflow-y:auto;line-height:18px"></textarea>
                <div class="txtShadow" style="z-index:-1000"></div>
              </div>
              <div class="funBox">
              		
                  <div class="right" style="padding-bottom:5px;_margin-top:-8px;_padding-top:10px;">
              		<?php echo Addons::hook('home_index_weibo_func');?>
              		</div>
                  <?php /*if(Addons::requireHooks("home_index_middle_publish")){ ?>
                  <div class="right" style="padding-bottom:5px;_margin-top:-8px; cursor:hand;width:80px;_padding-top:10px; cursor:pointer"  onclick='weibo.showAndHideMenu("Sync", this, "", "");'>
                        <?php echo L('sync');?><a href="#" class="ico_sync_on"></a>
                    </div>
                    <div id="Sync" style="display:none;position:absolute;right:23px;top:30px;z-index:9999">
                        <div class="topic_app"></div>
                        <div class="pop_inner">
                                    <?php echo Addons::hook('home_index_middle_publish');?>
                        </div>
                    </div>
                    <?php }*/ ?>
                <div id="publish_type_content_before" class="kind">
                  <?php echo Addons::hook('home_index_middle_publish_type',array('position'=>'index'));?>
                </div>
              </div>
        <input type="text" style="display:none" />
            </form>
          </div>
            <?php if (Addons::requireHooks('home_index_middle_advert')) { ?>
            <?php echo Addons::hook('home_index_middle_advert', array($ts['ad']['middle']));?>
            <?php } else { ?>

 <?php if(is_array($ts['ad']['middle'])): ?><?php $i = 0;?><?php $__LIST__ = $ts['ad']['middle']?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><div class="ad_middle">
          <div class="ke-post"> 
            <?php echo ($vo['content']); ?>
          </div>
        </div><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
            <?php } ?>
            
      <form action="" method="get">
      </form>
            
            <!--我关注的/群组动态/正在发生-->
            <div class="tabMenu mt10">
              <?php if(!empty($weibo_menu)){ ?>
              <a href="javascript:void(0)" class="right  ico_show_<?php echo ($typeClass); ?>" onclick='weibo.showAndHideMenu("MenuSub", this, "ico_show_off", "ico_show_on");'></a>
              <?php } ?>              
              <ul>
                <li class="tab_dropdown">
                <a href="<?php echo U('home/user/index',array('type'=>UserAction::INDEX_TYPE_WEIBO,'weibo_type'=>$weibo_type,'follow_gid'=>$group_now['follow_group_id']));?>" class="<?php echo ($weibo_tab); ?>"><span><?php echo ($group_now["title"]); ?><?php if(!empty($follow_group_list)){ ?><b class="more"></b><?php } ?></span></a>
                <?php if(!empty($follow_group_list)){ ?>
                  <div class="dropmenu">
                        <dl class="Att_list">
                            <?php if(is_array($follow_group_list)): ?><?php $i = 0;?><?php $__LIST__ = $follow_group_list?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><dd><a href="<?php echo U('home/user/index',array('type'=>UserAction::INDEX_TYPE_WEIBO,'weibo_type'=>$weibo_type,'follow_gid'=>$vo['follow_group_id']));?>"><?php echo ($vo["title"]); ?></a></dd><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
                        </dl>
                  </div>
                 <?php } ?>
                </li>
                <?php if($hasGroupWeibo){ ?>
                <li class="tab_dropdown"><a href="#" class="<?php echo ($group_tab); ?>"><span><?php echo L('group_feed');?><b class="more"></b></span></a>
                  <div class="dropmenu">
                        <dl class="Att_list">
                            <?php if(is_array($group_list)): ?><?php $i = 0;?><?php $__LIST__ = $group_list?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><dd><a href="<?php echo U('home/user/index',array('type'=>UserAction::INDEX_TYPE_GROUP,'gid'=>$vo['id'],'weibo_type'=>$weibo_type));?>"><?php echo ($vo["name"]); ?></a></dd>
                                 <?php if($key>=4){
                                     $hasMoreGroup = true;
                                     break;
                                     } ?><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
                            <?php if($hasMoreGroup){ ?><dd><a href="<?php echo U('group/SomeOne');?>" class="right"><?php echo L('more');?>&raquo;</a></dd><?php } ?>
                        </dl>
                  </div>
                </li>
                <?php } ?>
                <li><a href="<?php echo U('home/user/index',array('type'=>UserAction::INDEX_TYPE_ALL,'weibo_type'=>$weibo_type));?>" class="<?php echo ($all_tab); ?>"><span><?php echo L('happen_now');?></span></a></li>
                </ul>
            </div>
            <!--/我关注的/群组动态/正在发生-->
      			<div class="MenuSub" id="MenuSub" style="display:<?php echo ($view); ?>">
      			<!-- 切换标签 begin  -->
      			<?php if(is_array($weibo_menu)): ?><?php $i = 0;?><?php $__LIST__ = $weibo_menu?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><a id="feed_all_item" rel="all" class="<?php if(($weibo_type)  ==  $key): ?>on<?php endif; ?> feed_item" href="<?php echo U('home/User/index',array('follow_gid'=>$group_now['follow_group_id'],'type'=>$type,'weibo_type'=>$key));?>"><span><?php echo ($vo); ?></span></a><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
      			</div><?php break;?>
          <?php case "atme":  ?><div class="tab-menu">
              <ul>
                <li><a href="###" class="on"><span><?php echo L('at_me');?></span></a></li>
              </ul>
            </div><?php break;?>
          <?php case "collection":  ?><div class="tab-menu">
              <ul>
                <li><a href="###" class="on"><span><?php echo L('my_favorites');?></span></a></li>
              </ul>
            </div><?php break;?><?php endswitch;?>
		<!-- 微博列表 feedbox  -->
        <div class="feedBox">
        	<?php echo Addons::hook('weibo_feedbox');?>
			<div id="countNew"></div>
			<?php if($gid){ ?>
				<?php echo W('WeiboList', array('mid'=>$mid, 'list'=>$list['data'], 'insert'=>1,'simple'=>2));?>
			<?php }else{ ?>
				<?php echo W('WeiboList', array('mid'=>$mid, 'list'=>$list['data'], 'insert'=>1));?>
			<?php } ?>

			<?php if(ACTION_NAME=="index"){ ?>
				<?php if($list['data']){ ?>
				<p class="moreFoot">
					<a id="loadMoreDiv" href="javascript:void(0)"><span class="ico_morefoot"></span><?php echo L('more');?></a>
				</p>
				<?php }else{ ?>
					<p class="moreFoot"><?php echo L('no_record');?></p>
				<?php } ?>
			<?php }else{ ?>
				<div class="page"><?php echo ($list['html']); ?></div>
			<?php } ?>
        </div>
		<!-- 微博列表 feedbox end  -->
        </div>
        <div class="c"></div>
      </div>
    </div>
  </div>
</div>
</div>
<!-- 用@提到的人 end --> 
<?php if(is_array($ts['ad']['footer'])): ?><?php $i = 0;?><?php $__LIST__ = $ts['ad']['footer']?><?php if( count($__LIST__)==0 ) : echo "" ; ?><?php else: ?><?php foreach($__LIST__ as $key=>$vo): ?><?php ++$i;?><?php $mod = ($i % 2 )?><div class="ad_footer"><div class="ke-post"><?php echo ($vo['content']); ?></div></div><?php endforeach; ?><?php endif; ?><?php else: echo "" ;?><?php endif; ?>
<div class="footer_bg">
<div class="footer">
	<div class="menu">
		<?php foreach($ts['footer_document'] as $k => $v) {
            $v['url'] = isset($v['url']) ? $v['url'] : U('home/Public/document',array('id'=>$v['document_id']));
            $ts['footer_document'][$k] = '<a href="'.$v['url'].'" target="_blank">'.$v['title'].'</a>';
        }
        echo implode('&nbsp;&nbsp;|&nbsp;&nbsp', $ts['footer_document']); ?>
	</div>
	<div>
		<?php echo ($ts['site']['site_icp']); ?> 
		<?php if(isMobile()) { ?>
			<a href="<?php echo U('home/Public/toWap');?>">WAP</a>
		<?php } ?>
	</div>
</div>
</div>
</div>
<?php $ts['cnzz'] = getCnzz(false);
if (!empty($ts['cnzz'])) { ?>
<div style="display:none;">
<script src="http://s87.cnzz.com/stat.php?id=<?php echo ($ts['cnzz']['cnzz_id']); ?>&web_id=<?php echo ($ts['cnzz']['cnzz_id']); ?>" language="JavaScript" charset="gb2312"></script>
</div>
<?php } ?>
<?php echo Addons::hook('public_footer');?>
</body>
</html>
 
<script>
$(document).ready(function(){
	$(".header_dropdown").hover(
		function(){ $(this).addClass("hover"); },
		function(){ $(this).removeClass("hover"); }
	);
});

var weibo = $.weibo({
      sinceId: parseInt('<?php echo ($sinceId); ?>'),
      
	  <?php if(ACTION_NAME=="index"){ ?>
      timeStep : 30000,
      initForm:true,
      <?php } ?>

      lastId:parseInt('<?php echo ($lastId); ?>'),
      show_feed:parseInt('<?php echo ($show_feed); ?>'),
      follow_gid:parseInt('<?php echo ($follow_gid); ?>'),
      gid:parseInt('<?php echo ($gid); ?>'),
      weiboType:'<?php echo ($weibo_type); ?>',
      type:parseInt('<?php echo ($type); ?>'),
      typeList:{
          WEIBO:parseInt(<?php echo UserAction::INDEX_TYPE_WEIBO; ?>),
          GROUP:parseInt(<?php echo UserAction::INDEX_TYPE_GROUP; ?>),
          ALL:parseInt(<?php echo UserAction::INDEX_TYPE_ALL; ?>)
      }
});
	
function close_announcement() {
	$('#announcement').hide('slow');
	$.post("<?php echo U('home/User/closeAnnouncement');?>",{},function(res){});
}
</script>
<?php echo Addons::hook('weibo_js_plugin');?>