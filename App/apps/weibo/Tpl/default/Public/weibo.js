function callback(fun,argum){
	fun(argum);
}

// 删除类型框
function delTypeBox(){
	$('input[name="publish_type"]').val( 0 );
	$('.talkPop').remove();
  if(weibo.obj != null){
    weibo.obj.destroy();
    weibo.obj = null;
  }
  weibo.hasBox = false;
}

$(document).ready(function(){
	  // 评论切换
	  $("a[rel='comment']").live('click',function(){
	      var id = $(this).attr('minid');
	      var $comment_list = $("#comment_list_"+id);
		  if( $comment_list.html() == '' ){
			  $comment_list.html('<div class="feed_quote feed_wb" style="text-align:center"><img src="'+ _THEME_+'/images/icon_waiting.gif" width="15"></div>');
			var url,param;
			if(weibo.gid){
			    url = U("group/WeiboIndex/loadcomment");
			    param = {id:id,gid:weibo.gid}
			}else{
			    url = U("weibo/Index/loadcomment");
			    param = {id:id};
			}
			$.post( url,param,function(txt){
				$comment_list.html( txt ) ;
			});
		  }else{
			  $comment_list.html('');
		  }
	  });


	// 发布评论
	$("form[rel='miniblog_comment']").live("submit", function(){
		var _this = $(this);
		var callbackfun = _this.attr('callback');
		var _comment_content = _this.find("textarea[name='comment_content']");
		if( _comment_content.val()=='' ){
			ui.error("{L('内容不能为空')}");
			return false;
		}
		_this.find("input[type='submit']").val( "{L(:('评论中...'))}").attr('disabled','true') ;
		var options = {
		    success: function(txt) {
				txt = eval('('+txt+')');
				_this.find("input[type='submit']").val( "{:L('确定')}");
			       _this.find("input[type='submit']").removeAttr('disabled') ;
				   _comment_content.val('');
				if(callbackfun){
					callback(eval(callbackfun),txt);
				}else{
					_comment_content.css('height','');
			       $("#comment_list_before_"+txt.data['weibo_id']).after( txt.html );

				   $("#replyid_" + txt.data['weibo_id'] ).val('');
				   //更新评论数
				   $("a[rel='comment'][minid='"+txt.data['weibo_id']+"']").html("{:L('评论')}("+txt.data['comment']+")");
				 //  _this.find("textarea[name='comment_content']").focus();
				   
				}
		    }
		};
		_this.ajaxSubmit( options );
	    return false;
	});
});
var CallBack = function(){
	return{
		Vote:{}
	}
}
CallBack.Vote = {
		addSuccess:function(data){
			
		}
}

weibo = function(){
	
}

weibo.prototype = {
  obj:null,
  gid:0,
  setGid:function(gid){
      weibo.gid = gid;
  },
  hasBox:false,
	//初始化微博发布
	init:function(option){
		var __THEME__ = "<?php echo __THEME__;?>";
		var Interval;
		$("#publish_type_content_before").prepend(
			"<a href=\"javascript:void(0)\" target_set=\"content_publish\" onclick=\"ui.emotions(this)\" class=\"a52\">"
			+ "<img class=\"icon_add_face_d\" src=\""+__THEME__+"/images/zw_img.gif\" />{:L('表情')}</a> "
			+ "<a href=\"javascript:void(0)\" onclick=\"addtheme()\" class=\"a52\">"
			+ "<img class=\"icon_add_topic_d\" src=\""+__THEME__+"/images/zw_img.gif\" />{:('话题')}</a> "
		);

		$("#content_publish").keypress(function(event){
			var key = event.keyCode?event.keyCode:event.which?event.which:event.charCode;
	        if (key == 27) {
	        	clearInterval(Interval);
	        }
			weibo.checkInputLength(this, _LENGTH_);
		}).blur(function(){
			clearInterval(Interval);
			weibo.checkInputLength(this, _LENGTH_);
		}).focus(function(){
			//微博字数监控
			clearInterval(Interval);
		    Interval = setInterval(function(){
		    	weibo.checkInputLength('#content_publish', _LENGTH_);
			},300);
		});
		weibo.checkInputLength('#content_publish', _LENGTH_);
		shortcut('ctrl+return',	function(){weibo.do_publish();clearInterval(Interval);},{'target':'miniblog_publish'});
	},
	//发布前的检测
	before_publish:function(){
		
		if( $.trim( $('#content_publish').val() ) == '' ){
            ui.error("{:L('内容不能为空')}");		
			return false;
		}
		return true;
	},
	//发布操作
	do_publish:function(){
		if( weibo.before_publish() ){
			weibo.textareaStatus('sending');
			var options = {
			    success: function(txt) {
			      if(txt){
			    	   weibo.after_publish(txt);
			      }else{
	                  alert( "{:L('发布失败')}");
			      }
				}
			};		
			$('#miniblog_publish').ajaxSubmit( options );
		    return false;
		}
	},
	//发布后的处理
	after_publish:function(txt){
		if(txt==0) {
			ui.success("{:L('您发布的微博含有敏感词，请等待审核！')}");
		}else {
			delTypeBox();
		    $("#feed_list").prepend( txt ).slideDown('slow');
		    var sina_sync = $('#sina_sync').attr('checked');
		    $('#miniblog_publish').clearForm();
		    if (sina_sync) {
		    	$('#sina_sync').attr('checked', true);
		    }
		    weibo.upCount('weibo');
		    ui.success("{:L('微博发布成功')}");
		    weibo.checkInputLength('#content_publish', _LENGTH_);
		}
	},
	//发布按钮状态
	textareaStatus:function(type){
		var obj = $('#publish_handle');
		if(type=='on'){
			obj.removeAttr('disabled').attr('class','btn_big_disable hand');
		//}else if( type=='sending'){
		//	obj.attr('disabled','true').attr('class','btn_big_disable hand');
		}else{
			obj.attr('disabled','true').attr('class','btn_big_disable hand');
		}
	},
	//删除一条微博
	deleted:function(weibo_id){
	    if(weibo.gid){
	        url = U("group/WeiboOperate/delete");
	        param = {gid:weibo.gid,id:weibo_id};
	    }else{
	        url = U("weibo/Operate/delete");
	        param = {id:weibo_id};
	    }
	    
		$.post(url,param,function(txt){
			if( txt ){
				$("#list_li_"+weibo_id).slideUp('fast');
				weibo.downCount('weibo');
			}else{
				alert("{:L('删除失败')}");
			}
		});
	},
	//收藏
	favorite:function(id,o){
		$.post( U("weibo/Operate/stow") ,{id:id},function(txt){
			if( txt ){
				$(o).wrap('<span id=content_'+id+'></span>');
				$('#content_'+id).html("{:L('已收藏')}");
			}else{
				alert("{:L('收藏失败')}");
			}
		});
	},
	//取消收藏
	unFavorite:function(id,o){
		$.post( U("weibo/Operate/unstow") ,{id:id},function(txt){
			if( txt ){
				$('#list_li_'+id).slideUp('slow');
			}else{
				alert("{:L('取消失败')}");
			}
		});
	},
	// 分享微博
    share:function(id, upcontent){
        upcontent = ( upcontent== undefined ) ? 1 : 0;
        ui.box.load( U("group/WeiboOperate/shareWeibo",["id="+id,"gid="+weibo.gid,"upcontent="+upcontent] ),{title:"{:L('分享到我的微博')}",closeable:true});
    },
	//转发
	transpond:function(id,upcontent){
		upcontent = ( upcontent == undefined ) ? 1 : 0;
		if(weibo.gid){
	       ui.box.load( U("group/WeiboOperate/transpond",["id="+id,"gid="+weibo.gid,"upcontent="+upcontent] ),{title:"{:L('转发到群聊')}",closeable:true});
		}else{
		   ui.box.load( U("weibo/operate/transpond",["id="+id,"upcontent="+upcontent] ),{title:"{:L('转发')}",closeable:true});
		}
	},
	//关注话题
	followTopic:function(name){
		$.post(U('weibo/operate/followtopic'),{name:name},function(txt){
			txt = eval( '(' + txt + ')' );
			if(txt.code==12){
				$('#followTopic').html('<a href="javascript:void(0)" onclick="weibo.unfollowTopic(\''+txt.topicId+'\',\''+name+'\')">{:L("已关注该话题")}</a>');
			}
		});
	},
	unfollowTopic:function(id,name){
		$.post(U('weibo/operate/unfollowtopic'),{topicId:id},function(txt){
			if(txt=='01'){
				$('#followTopic').html('<a href="javascript:void(0)" onclick="weibo.followTopic(\''+name+'\')">{:L("关注该话题")}</a>');
			}
		});	
	},
	quickpublish:function(text){
		$.post(U('weibo/operate/quickpublish'),{text:text},function(txt){
			ui.box.show(txt,{title:"{:L('说几句')}",closeable:true});
		});
	},
	//更新计数器
	upCount:function(type){
		if(type=='weibo'){
			$("#miniblog_count").html( parseInt($('#miniblog_count').html())+1 );
		}
	},
	downCount:function(type){
		if(type=='weibo'){
			$("#miniblog_count").html( parseInt($('#miniblog_count').html())-1 );
		}
	},
	//检查字数输入
	checkInputLength:function(obj,num){
		var len = getLength($(obj).val(), true);
		var wordNumObj = $('.wordNum');
		
		if(len==0){
			wordNumObj.css('color','').html('{:L("你还可以输入")}<strong id="strconunt">'+ (num-len) + '</strong>{:L("字")}');
			weibo.textareaStatus('off');
		}else if( len > num ){
			wordNumObj.css('color','red').html('{:L("已超出")}<strong id="strconunt">'+ (len-num) +'</strong>{:L("字")}');
			weibo.textareaStatus('off');
		}else if( len <= num ){
			wordNumObj.css('color','').html('{:L("你还可以输入")}<strong id="strconunt">'+ (num-len) + '</strong>{:L("字")}');
			weibo.textareaStatus('on');
		}
	},
	publish_type_box:function(content,obj){
    var obj_left = $(obj).offset().left;
    var mg_left = obj_left - $('#publish_type_content_before').offset().left+($(obj).width()/2);
    //if(this.hasBox) return;
		var __THEME__ = "<?php echo __THEME__;?>";
		var html = '<div class="talkPop"><div  style="position: relative; height: 7px; line-height: 3px;">'
		     + '<img class="talkPop_arrow" style="margin-left:'+ mg_left +'px;position:absolute;" src="'+__THEME__+'/images/zw_img.gif" /></div>'
             + '<div class="talkPop_box">'
			 + '<div class="pop_tit close" id="weibo_close_handle"><a href="javascript:void(0)" class="del" onclick=" delTypeBox()" > </a></div>'
			 + '<div id="publish_type_content">'+content+'</div>'
			 + '</div></div>';
		$('div.talkPop').remove();
		$("#publish_type_content_before").after( html );
    //this.hasBox = true;
	},
  publish_type_val:function(publish_type){
     $('input[name="publish_type"]').val( publish_type );
  }
}
weibo = new weibo();

weibo.plugin = {};

function addtheme(){
	var text = '{:L("#请在这里输入自定义话题#")}';
	var   patt   =   new   RegExp(text,"g");  
	var content_publish = $('#content_publish');
	var result;
				
	if( content_publish.val().search(patt) == '-1' ){
		content_publish.val( content_publish.val() + text);
	}
	
	var textArea = document.getElementById('content_publish');
	
	result = patt.exec( content_publish.val() );
	
	var end = patt.lastIndex-1 ;
	var start = patt.lastIndex - text.length +1;
	
	if (document.selection) { //IE
		 var rng = textArea.createTextRange();
		 rng.collapse(true);
		 rng.moveEnd("character",end)
		 rng.moveStart("character",start)
		 rng.select();
	}else if (textArea.selectionStart || (textArea.selectionStart == '0')) { // Mozilla/Netscape…
        textArea.selectionStart = start;
        textArea.selectionEnd = end;
    }
    textArea.focus();
	weibo.checkInputLength('#content_publish', _LENGTH_);
	return ;
}
