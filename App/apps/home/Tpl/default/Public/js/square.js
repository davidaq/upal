/*广场首页*/
//搜索
function s_search(type){
	var kwd = encodeURIComponent($('#s_keyword').val().replace('&','&amp;'));
	if(kwd == '')return false;
	var act = (type=='topic')?'search':'searchuser';
	var url = U('home/user/'+act)+'&k='+kwd;
	location.href=url;
}

//名人推荐
function star_list(){
	var $star_list = $('#star_list');
	//setTimeout(function(){if($star_list.html()=='')$star_list.html('<span style="margin:100px 42%;">获取名人失败！</span>');},10000);
	$.post(U('home/square/index_star'),{},function(html){
		if(html){
			$star_list.html(html);
		}
	});
}
//微博翻页点击监控
function square_index_weibo_page(){
	$("#square_list_page a").click(function(o){
		var $a = $(o.target);
		var url = $a.attr("href");
		square_index_weibo(url);
		return false;
	});
}
//微博列表读取
function square_index_weibo(url){
	var url = url?url:U('home/square/index_weibo');
	var $square_list = $('#square_list');
	$square_list.html('<img src="'+_THEME_+'/images/icon_waiting.gif" width="20" style="margin:80px 50%;" />');
	//setTimeout(function(){if($square_list.html()=='')$square_list.html('<span style="margin:100px 42%;">获取微博失败！</span>');},10000);
	$.post(url,{},function(html){
		if(html == -1){
			$square_list.html('<span style="margin:100px 50%;">微博列表为空！</span>');
		}else if(html){
			$square_list.html(html);
			square_index_weibo_page();
		}
	});
}

/*热门话题*/
function follow_topic(name,div){
	var $topic = $(div);
	$.post(U('weibo/operate/followtopic'),{name:name},function(txt){
		txt = eval( '(' + txt + ')' );
		if(txt.code=='12'){
			$topic.html('<a href="javascript:void(0)" onclick="unfollow_topic(\''+name.replace(/\'/g,'\\\'').replace(/\"/g,'&quote;')+'\',\''+txt.topicId+'\',\''+div+'\')">已关注该话题</a>');	
		}else if(txt.code=='11'){
			ui.error('已关注过此话题');
		}else{
			ui.error('关注失败');
		}
	});
}

function unfollow_topic(name,topicId,div){
	var $topic = $(div);
	$.post(U('weibo/operate/unfollowtopic'),{topicId:topicId},function(txt){
		if(txt=='01'){
			$topic.html('<a href="javascript:void(0)" onclick="follow_topic(\''+name.replace(/\'/g,'\\\'').replace(/\"/g,'&quote;')+'\',\''+div+'\')">关注该话题</a>');
		}else{
			ui.error('取消关注失败');
		}
	});	
}

/*名人堂*/	
function dofollow(div){
	if( $(div+" input[name='followuid[]']:checked").size() ==0){
		ui.error('请选择要关注的人');
		return '';
	}
	var options = {
			success: function(txt) {
			ui.success('关注成功');
			//setInterval("location.reload()",1000);
			} 
		};		
		$(div+' form').ajaxSubmit( options );
	}