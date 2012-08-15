jQuery.extend(weibo.plugin, {
	blog:function(element, options){
	   
	    
	}
});

jQuery.extend(weibo.plugin.blog, {
	use:0,
	html:'<div id="blog_div"><img class="loadimg" src="'+_THEME_+'/images/icon_waiting.gif" style="border: 0px none ; position: absolute; top: 50px; left: 50px; background-color: transparent; width: 16px; height: 16px; z-index: 1001;"></div>',
	click:function(options){
	   weibo.publish_type_box(this.html,options)
		 $.post(U('Blog/index/addAjaxBlog'),{
			 used:weibo.plugin.blog.use
		 },function(result){
		        weibo.publish_type_val(0);
                $('#blog_publish_data').remove();
				$('.loadimg').hide();
				$('#blog_div').html(result);
				weibo.plugin.blog.use = 1;
		 });
	},
	onload:function(id){
		var data = $("#"+id).contents().find("body").html();
		var result;
		if(data.length != 0 && data != undefined){
			result = eval("("+data+")");
			if(result.status){
				$('div .talkPop').remove();
				$('#content_publish').val(result.data.html);
				weibo.publish_type_val(8);

			    if(result.data.image){
			        var newInput = document.createElement('input');
			        newInput.id  = "blog_publish_data";
			        newInput.type="hidden";
			        newInput.name="publish_type_data[]";
			        newInput.value=result.data.image;
			        $('#miniblog_publish').append(newInput);
			    }
				weibo.do_publish();

			    weibo.checkInputLength(_LENGTH_);
			    weibo.setLastIdByWeiboListDiv();
			}else{
				ui.error(result.info);
			}

		}
	}
});
