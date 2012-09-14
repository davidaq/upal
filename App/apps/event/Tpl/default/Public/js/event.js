function EventAction( id,allow,action ){
  $.post( U('event/Index/doAction'),{id:id,allow:allow,action:action},function( text ){
      if( text == 1 ){
        if( allow == 1 ){
        	ui.success( '申请成功，该活动需要发起人审核，请耐心等待...' );
        }else{
        	ui.success( '操作成功' );
        }
    	if( action == 'joinIn' ){
    		if( allow == 1 ){
    			$('.list_joinIn_'+id).html('<a href="javascript:EventDelAction( '+id+','+allow+',\'joinIn\' )">取消申请</a>');
    			$('.detail_joinIn_'+id).html(
    					'<span class="cGreen lh35">已提交申请,等待审核中,'+
    					'<button class="btn_w" style="margin-right:5px;" onclick="javascript:EventDelAction( '+id+','+allow+',\'joinIn\' )">取消申请</button>'
    			);
    		}else{
    			$('.list_joinIn_'+id).html('<a href="javascript:EventDelAction( '+id+',null,\'joinIn\' )">取消参加</a>');
    			$('.detail_joinIn_'+id).html('<button class="btn_w" style="margin-right:5px;" onclick="javascript:EventDelAction( '+id+',null,\'joinIn\' )">取消参加</button>');
    		}
    	}else if( action == 'attention' ){
    		$('.list_attention_'+id).html('<a href="javascript:EventDelAction( '+id+',null,\'attention\')">取消关注</a>');
    		$('.detail_attention_'+id).html('<button class="btn_w" style="margin-right:5px;" onclick="javascript:EventDelAction( '+id+',null,\'attention\')">取消关注</button>');
    	}
      }else if( text == -2 ){
        if( allow == 1 ){
            ui.error( '已经提交申请，请不要重复申请' );
        }else{
            ui.error( '操作已经执行，请不要重复操作' );
        }
      }else if( text == -1 ){
    	  ui.error( '这个活动已不存在，即将刷新本页面' );
          location.reload();
      }else if( text == 0 ){
    	  ui.error( '操作失败,请稍后再试' );
      }else{
    	  ui.error( '未知错误' );
      }
  });
}

function EventDelAction( id,allow,action ){
  $.post( U('event/Index/doDelAction'),{id:id,allow:allow,action:action},function( text ){
      if( text == 1 ){
        if( allow == 1 ){
        	ui.success( '撤销申请成功' );
        }else{
        	ui.success( '操作成功' );
        }
    	if( action == 'joinIn' ){
    		$('.list_joinIn_'+id).html(
    				'<a href="javascript:EventAction( '+id+','+allow+',\'joinIn\' )">我要参加</a>'+
    				'<span class="list_attention_'+id+'">'+
    				'<a href="javascript:EventAction( '+id+',null,\'attention\')">我要关注</a>'+
                    '</span>'
    		);
    		$('.detail_joinIn_'+id).html(
    				'<button class="btn_b" style="margin-right:5px;" onclick="javascript:EventAction( '+id+','+allow+',\'joinIn\' )">我要参加</button>'+
    				'<span class="detail_attention_'+id+'">'+
    				'<button class="btn_b" style="margin-right:5px;" onclick="javascript:EventAction( '+id+',null,\'attention\')">我要关注</button>'+
                    '</span>'
    		);
    	}else if( action == 'attention' ){
        	$('.list_attention_'+id).html('<a href="javascript:EventAction( '+id+',null,\'attention\')">我要关注</a>');
        	$('.detail_attention_'+id).html('<button class="btn_b" style="margin-right:5px;" onclick="javascript:EventAction( '+id+',null,\'attention\')">我要关注</button>');
    	}
      }else if( text == -2 ){
    	  ui.error( '您没有对本活动进行过操作' );
    	  location.reload();
      }else if( text == -1 ){
    	  ui.error( '这个活动已不存在，即将刷新本页面' );
    	  location.reload();
      }else if( text == 0 ){
    	  ui.error( '操作失败,请稍后再试' );
      }else{
    	  ui.error( '未知错误' );
      }
  });
}

function agree( id,eventId,uid ){
  $.post( U('event/Index/doAgreeAction'),{id:id,eventId:eventId,uid:uid},function( text ){
      if( text == 1 ){
    	  ui.success( '操作成功' );
        location.reload();
      }else if( text == -3 ){
    	  ui.error( '未知错误' );
      }else if( text == -2 ){
    	  ui.error( '您没有对本活动进行过操作' );
        location.reload();
      }else if( text == -1 ){
    	  ui.error( '这个活动已不存在，即将刷新本页面' );
        location.reload();
      }else if( text == 0 ){
    	  ui.error( '操作失败,请稍后再试' );
      }else{
    	  ui.error( '未知错误' );
      }
  });
}

function adminDelAction( id,uid,action,opts ){
  $.post( U('event/Index/doAdminAction'),{eventId:id,uid:uid,action:action,admin:'user',opts:opts},function( text ){
      if( text == 1 ){
    	  ui.success( '操作成功' );
        location.reload();
      }else if( text == -3 ){
    	  ui.error( '未知错误' );
      }else if( text == -2 ){
    	  ui.error( '您没有对本活动进行过操作' );
        location.reload();
      }else if( text == -1 ){
    	  ui.error( '这个活动已不存在，即将刷新本页面' );
        location.reload();
      }else if( text == 0 ){
    	  ui.error( '操作失败,请稍后再试' );
      }else{
    	  ui.error( '未知错误' );
      }
  });

}

function endEvent( id ){
	if(confirm('是否提前结束此活动?')){
		$.post( U('event/Index/doEndAction'),{id:id},function( text ){
            if( text == 1 ){
              ui.success('提前结束活动成功');
              $('#event_satus_' + id).html('活动结束');//活动列表
              $('#event_satus').html('此活动已经结束');//活动详细页
              $('#event_edit_button').html('');//活动详细页
            }else if( text == -1 ){
              ui.error( '非法访问' );
            }else if( text == 0 ){
              ui.error( '结束活动失败。请稍后再试' );
            }else{
              ui.error( '未知错误' );
            }
        });
	}
}

function delEvent(eventId,jump){
    var jump = jump==true?true:false;
	if(confirm('确认删除此活动?')){
		$.post( U('event/Index/doDeleteEvent'),{id:eventId},function( text ){
            if( text == 1 ){
              ui.success('删除活动成功');
              if(jump == true){
            	  location.href=U('event/Index/personal');
              }else{
            	  $('#event_'+eventId).remove();
              }
            }else if( text == 0 ){
              ui.error( '删除活动失败！' );
            }else{
              ui.error( '未知错误，请稍后再试' );
            }
        });
	}
}

function removeHTMLTag(str) {
    str = str.replace(/<\/?[^>]*>/g,'');
    str = str.replace(/[ | ]*\n/g,'\n');
    str=str.replace(/&nbsp;/ig,'');
    return str;
}

function check(){
  var title      = $( '#title' ).val();
  var type       = $( '#type' ).val();
  var address    = $( '#address' ).val();
  var limitCount = $( '#limitCount' ).val();
  var explain    = getEditorContent('explain');//$( '#explain' ).val();
  var stime      = $( '#sTime' ).val();
  var etime      = $( '#eTime' ).val();
  var deadline      = $( '#deadline' ).val();
  
  if(!title || getLength(title.replace(/\s+/g,"")) == 0){
	  ui.error("活动名称不能为空");
	  return false;
  }
  if( title.length<4 ) {
	  ui.error( '活动名称必须大于4个字符' );
	  return false;
  }
  if( address == 0 ) {
	  ui.error( '请填写活动地点' );
	  return false;
  }
  if( type == 0 ) {
	  ui.error( '请选择活动分类' );
	  return false;
  }
  //if ( limitCount.test( '/^d+$/' ) ){alert( '人数只允许数字类型' ) return false}
  explain = removeHTMLTag(explain);

  if(explain.length <10 ){
	  ui.error( '活动介绍不得小于10个字符' );
	  return false;
  }
  if( !stime ) {
	  ui.error( '请填写开始时间' );
	  return false;}
  if( !etime ) {
	  ui.error( '请填写结束时间' );
	  return false;
  }
  if( !deadline ) {
	  ui.error( '请填写截止报名时间' );
	  return false;
  }
  return true;
}