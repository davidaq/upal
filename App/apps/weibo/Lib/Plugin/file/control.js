jQuery.extend(weibo.plugin, {
	file:function(element, options){
	   
	    
	}
});

var stopUploadPic = 0;
jQuery.extend(weibo.plugin.file, {
	html:'<div id="upload_select_file"><div class="btn_green" href="javascript:void(0);" >从电脑选择文件'+
	'<form action="'+U("weibo/plugins/before_publish")+'" enctype="multipart/form-data" method="post" id="upload_file">'+
	'<input type="hidden" name="plugin_id" value="5"><input type="file" hidefoucs="true" name="file" onchange="weibo.plugin.file.upload(this)">'+
	'</form></div><div>仅支持jpg,gif,png,jpeg,bmp,zip,rar,doc,xls,ppt,docx,xlsx,pptx,pdf文件，且文件小于10M</div></div>'
	+ '<div class="alC pt10 pb10 f14px" id="upload_loading" style="display:none">'
	+ '<img src="'+ _THEME_+'/images/icon_waiting.gif" width="20" class="alM"> 正在上传中...<br />'
	+ '<a class="btn_w mt10" href="javascript:void(0)" onclick="$(\'div .talkPop\').remove();weibo.plugin.file.stopAjax();">取消上传</a></div>',
	click:function(options){
		if (1 != $('div.talkPop').data('type')) {
			weibo.publish_type_box(5,this.html,options);
		}
	},
	upload:function(o){
		var allowext = ['jpg','gif','png','jpeg','bmp','zip','rar','doc','xls','ppt','docx','xlsx','pptx','pdf'];
		var ext = /\.[^\.]+$/.exec( $(o).val() );
		ext = ext.toString().replace('.','');
		if( jQuery.inArray( ext.toLowerCase() , allowext )==-1 ){
			alert('只允许上传' + allowext.join('、') + '格式的文件');
			return false;
		}
		weibo.textareaStatus('off');
		$('#upload_select_file').hide();
		$('#upload_loading').show();
		$('#weibo_close_handle').hide();
		var options = {
			    success: function(txt) {
				if(stopUploadPic==1){
					return false;
				}
			      txt = eval( '(' + txt + ')' );
			      if(txt.boolen==1){
			    	var html = "<img src='" + _THEME_ + "/images/file/" + txt.file_ext + ".gif' alt='" + txt.file_ext + "' />"
			    			 + "<a href='" + txt.file_url + "' target='_blank'>" + txt.file_name + "</a>"
			    			 + "<input type='hidden' name='publish_type_data' value='" + txt.file_id + "' />";	
					if($('#content_publish').val()==''){
						$('#content_publish').val('文件分享');
					}
					$("#publish_type_content").html( '&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" onclick="$(\'div .talkPop\').remove();">删除文件</a><br />'+html );
					$('div.talkPop').data('type', 5);
					$('#upload_loading').hide();
					$('#upload_select_file').show();
					weibo.checkInputLength('#content_publish',_LENGTH_);
				  }else{
					alert( txt.message );
					$('.talkPop').remove();
			      }
			    } 
			};
//			$('#publish_type_content').html('<div style="width:400px;text-align:center;height:150px;"><img src="__THEME__/images/icon_waiting.gif" width="30"></div>');
			var httpRespondHandle = $('#upload_file').ajaxSubmit( options );
		    return false;
	},
	stopAjax:function(){
		stopUploadPic=1;
	}

});