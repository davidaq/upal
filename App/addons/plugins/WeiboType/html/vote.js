jQuery.extend(weibo.plugin, {
	vote:function(element, options){
	   
	    
	}
});

jQuery.extend(weibo.plugin.vote, {
	html:'<div id="vote_div"><img class="loadimg" src="'+_THEME_+'/images/icon_waiting.gif" style="border: 0px none ; position: absolute; top: 50px; left: 50px; background-color: transparent; width: 16px; height: 16px; z-index: 1001;"></div>',
	click:function(options){
	   weibo.publish_type_box(this.html,options)
		 $.post(U('home/widget/addonsRequest'),{
			 addon:'WeiboType',
			 hook:'_getVoteForm'
		 },function(result){
		       weibo.publish_type_val(0);
		       $('#vote_publish_data').remove();
			   $('.loadimg').hide();
			   $('#vote_div').html(result);
		 });
	}
});
CallBack.Vote.exit = function(){
    weibo.publish_type_val(0);
    $('#vote_publish_data').remove();
    delTypeBox();
}
CallBack.Vote.addSuccess=function(data,json){
	var html = "";
	html += "我发起了一个投票：";
	html += "（"+data.title+"）";
	html += " "+data.url;
	if($('#content_publish').val()==''){
		$('#content_publish').val(html);
	}
	 weibo.publish_type_val(7);
	 var newInput = document.createElement('input');
     newInput.id  = "vote_publish_data";
     newInput.type="hidden";
     newInput.name="publish_type_data[]";
     newInput.value=json;
     $('#miniblog_publish').append(newInput);
	weibo.checkInputLength(_LENGTH_);
	weibo.do_publish();
	weibo.setLastIdByWeiboListDiv();
}
