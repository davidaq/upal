var webpageComment = {
	setting:{
		width:'380',
		skin:'skin01',
		uid:_MID_
	},
	autowidth: 1,
	HTML:'<iframe id="ThinkSNSCommentFrame" src="" scrolling="no" frameborder="0" width="$WIDTH$" '
	     + 'onload="var frame=this;setInterval(function(){ThinkSNSCommentFrameAuto(frame);}, 300);"></iframe>'
		 + '<script type="text/javascript">'
		 + '(function(){'
		 + 'var url = "' + _HOST_ + U('home/Widget/webpageComment') +  '$QUERY$&url=auto";'
		 + 'var frame = document.getElementById("ThinkSNSCommentFrame");'
		 + 'url = url.replace("url=auto", "url=" + encodeURIComponent(document.URL));'
		 + 'frame.src=url;'
		 + '})();'
		 + 'function ThinkSNSCommentFrameAuto(frame){'
		 + 'frame.height=(frame.Document?frame.Document.body.scrollHeight:frame.contentDocument.body.offsetHeight);'
		 + '}'
		 + '</script>',
	code:function(){
		var $input_copyhtml = $('#input_copyhtml');
		var query = '';

		this.setting.width = 1 == this.autowidth ? '0' : this.setting.width;
		$.each(this.setting, function(i, n){
			query += '&' + i + '=' + n;
		});

		var html = this.HTML.replace('$WIDTH$', 1 == this.autowidth ? '100%' : this.setting.width)
					.replace('$QUERY$', query);

		$input_copyhtml.val(html);
		$('#weiboshow').html(html);
	},
	init:function(){
		$(document).ready(function(){
			var $width = $('#width');
			var $autowidth = $('#autowidth');
			var $template_skins = $('#template_skins');
			$width.val(webpageComment.setting.width);
			webpageComment._autowidth();
			webpageComment.code();

			// 监听
			$width.blur(function(){
				if (this.value < 190 || this.value > 1024 || true == isNaN(this.value)) {
					this.value = isNaN(this.value) ? webpageComment.setting.width : (this.value < 190 ? 190 : 1024);
					$('#wh_error').attr('class', 'warning');
				} else {
					$('#wh_error').removeAttr('class', 'warning');
				}
				if (this.value == webpageComment.setting.width) {
					return ;
				}
				webpageComment.setting.width = this.value;
				webpageComment.code();
			});
			$autowidth.click(function(){
				webpageComment.autowidth = true == this.checked ? 1 : 0;
				webpageComment._autowidth();
				webpageComment.code();
			});
			$template_skins.click(function(o){
				var $target = $(o.target);
				if (undefined != $target.attr('href') && '' != $target.attr('href')) {
					webpageComment.setting.skin = $target.attr('class');
					$('li', $template_skins).removeClass('on');
					$target.parent().attr('class', 'on');
					webpageComment.code();
				}
			});
			$('#input_copyhtml').click(function(){
				$(this).select();
			});
			$('#copy').click(function(){
				if (false == copy_clip(document.getElementById('input_copyhtml').value)) {
					$('#input_copyhtml').select();
				}
				return false;
			});
		});
	},
	_autowidth:function()
	{
		var $autowidth = $('#autowidth');
		var $width = $('#width');
		if (1 == this.autowidth) {
			$autowidth.attr('checked','checked');
			$width.attr('disabled', 'disabled');
		} else {
			$autowidth.removeAttr('checked');
			$width.removeAttr('disabled');
			this.setting.width = $width.val();
		}
	}
};
webpageComment.init();