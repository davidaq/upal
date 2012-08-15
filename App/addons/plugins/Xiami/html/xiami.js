jQuery.extend(weibo.plugin, {
	xiami:function(element, options){
	   
	    
	}
});


jQuery.extend(weibo.plugin.xiami, {
	html:'<dl id="music_input" class="layer_send_music"><dt>歌曲名字：</dt><dd><input name="publish_type_data" type="hidden" /><input name="xima_key" type="text" style="width:320px" class="text  mr5"  value="" id="xiami_key" /><input type="button" class="btn_b" onclick="weibo.plugin.xiami.searchmusic(1)" value="搜索"></dd></dl><div style="display:none; padding:10px 10px 20px" id="music_add_complete">您将要分享的音乐：</div><div class="xiami_s_r"></div>',
	click:function(options){
	   weibo.publish_type_box(this.html,options);
	},
	
	searchmusic:function(page){
			$.post( U('home/widget/addonsRequest'),{addon:'Xiami',hook:'searchmusic',page:page,key:$("#xiami_key").val()},function(txt){
					$(".xiami_s_r").html(txt);
					});
		},
	add_music:function(sid,name,art){
			$("#content_publish").val( $("#content_publish").val( ) + ' #音乐分享# ' + name+ '--'+art);
			weibo.publish_type_val(20);
			$("input[name='publish_type_data']").val(sid);
			weibo.checkInputLength(_LENGTH_);
			
			$(".xiami_s_r").html('<span class="xiami"><embed src="http://www.xiami.com/widget/8230560_'+sid+'/singlePlayer.swf" type="application/x-shockwave-flash" width="257" height="33" wmode="transparent" /></span>');
			$(".layer_send_music").hide();
			$("#music_add_complete").show();
		}
});
