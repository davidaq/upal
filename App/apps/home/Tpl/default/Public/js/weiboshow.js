var weiboshow = {
	setting:{
		width:'380',
		height:'550',
		skin:'skin01',
		uid:_MID_
	},
	autowidth: 1,
	HTML:'<iframe width="$WIDTH$" height="$HEIGHT$" class="weibo_show"  frameborder="0" scrolling="no" '
		 + 'src="' + _HOST_ + U('home/Widget/weiboShow') +  '$QUERY$"></iframe>',
	code:function(){
		var $input_copyhtml = $('#input_copyhtml');
		var query = '';

		this.setting.width = 1 == this.autowidth ? '0' : this.setting.width;
		$.each(this.setting, function(i, n){
			query += '&' + i + '=' + n;
		});

		var html = this.HTML.replace('$WIDTH$', 1 == this.autowidth ? '100%' : this.setting.width)
					.replace('$HEIGHT$', this.setting.height).replace('$QUERY$', query);

		$input_copyhtml.val(html);
		$('#weiboshow').html(html);
	},
	init:function(){
		$(document).ready(function(){
			var $width = $('#width');
			var $height = $('#height');
			var $autowidth = $('#autowidth');
			var $template_skins = $('#template_skins');
			$width.val(weiboshow.setting.width);
			$height.val(weiboshow.setting.height);
			weiboshow._autowidth();
			weiboshow.code();

			// 监听
			$width.blur(function(){
				if (this.value < 190 || this.value > 1024 || true == isNaN(this.value)) {
					this.value = isNaN(this.value) ? weiboshow.setting.width : (this.value < 190 ? 190 : 1024);
					$('#wh_error').attr('class', 'warning');
				} else {
					$('#wh_error').removeAttr('class', 'warning');
				}
				if (this.value == weiboshow.setting.width) {
					return ;
				}
				weiboshow.setting.width = this.value;
				weiboshow.code();
			});
			$height.blur(function(){
				if (this.value < 75 || this.value > 800 || true == isNaN(this.value)) {
					this.value = isNaN(this.value) ? weiboshow.setting.height : (this.value < 75 ? 75 : 800);
					$('#wh_error').attr('class', 'warning');
				} else {
					$('#wh_error').removeAttr('class', 'warning');
				}
				if (this.value == weiboshow.setting.height) {
					return ;
				}
				weiboshow.setting.height = this.value;
				weiboshow.code();
			});
			$autowidth.click(function(){
				weiboshow.autowidth = true == this.checked ? 1 : 0;
				weiboshow._autowidth();
				weiboshow.code();
			});
			$template_skins.click(function(o){
				var $target = $(o.target);
				if (undefined != $target.attr('href') && '' != $target.attr('href')) {
					weiboshow.setting.skin = $target.attr('class');
					$('li', $template_skins).removeClass('on');
					$target.parent().attr('class', 'on');
					weiboshow.code();
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
weiboshow.init();