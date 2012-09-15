jQuery.extend(weibo.plugin, {
	music:function(element, options){
	   
	    
	}
});


jQuery.extend(weibo.plugin.music, {
	html:'<dl id="music_input" class="layer_send_music"><dt>请输入歌曲链接地址：</dt><dd><input name="publish_type_data" type="text" style="width:320px" class="text  mr5"  value="" /><input type="button" class="btn_b" onclick="weibo.plugin.music.add_music()" value="添加"></dd></dl><div style="display:none" id="music_add_complete">添加完成</div>',
	click:function(options){
	   weibo.publish_type_box(this.html,options)
	},
	add_music:function(){
		var video_url = $.trim($("input[name='publish_type_data']").val());
		if(0 == video_url.length){
		    ui.error('请输入音乐地址');
		    return false;
		}
		var point = video_url.lastIndexOf(".");
		var type = video_url.substr(point);
		//thinksns2.5修正易对链接地址判断失误的问题
		if(!type.toLowerCase().indexOf("mp3")){
		//if(!type.toLowerCase().match(/.mp3\?.*/)){
			ui.error('只能发布mp3格式的音乐');
			return false;
		}
    $.post( U('home/widget/addonsRequest'),{addon:'WeiboType',hook:'_addMusic',url:video_url},function(txt){
			txt = eval('('+txt+')');
			if(txt.boolen){
				$('#music_input').hide();
				$('#music_add_complete').show();
				$("#content_publish").val( $("#content_publish").val( ) + ' ' + txt.short + ' ');
				weibo.checkInputLength(_LENGTH_);
				$('div .talkPop').hide();
                weibo.publish_type_val(txt.publish_type);
			}else{
				ui.error(txt.message);
			}
		})
	}
});
