var bulkFollow = {
	setting:{
		width:'380',
		skin:'skin01',
		uids:_MID_
	},
	autowidth: 1,
	HTML:'<iframe id="ThinkSNSBulkFollowFrame" src="" scrolling="no" frameborder="0" width="$WIDTH$" '
	     + 'onload="var frame=this;setTimeout(function(){ThinkSNSBulkFollowFrameAuto(frame);}, 1);"></iframe>'
		 + '<script type="text/javascript">'
		 + '(function(){'
		 + 'var url = "' + _HOST_ + U('home/Widget/bulkFollow') +  '$QUERY$";'
		 + 'var frame = document.getElementById("ThinkSNSBulkFollowFrame");'
		 + 'frame.src=url;'
		 + '})();'
		 + 'function ThinkSNSBulkFollowFrameAuto(frame){'
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
			var dUserIds = document.getElementById( "ui_fri_ids" );
			$width.val(bulkFollow.setting.width);
			bulkFollow._autowidth();
			bulkFollow.code();

			// 监听
			$width.blur(function(){
				if (this.value < 190 || this.value > 1024 || true == isNaN(this.value)) {
					this.value = isNaN(this.value) ? bulkFollow.setting.width : (this.value < 190 ? 190 : 1024);
					$('#wh_error').attr('class', 'warning');
				} else {
					$('#wh_error').removeAttr('class', 'warning');
				}
				if (this.value == bulkFollow.setting.width) {
					return ;
				}
				bulkFollow.setting.width = this.value;
				bulkFollow.code();
			});
			$autowidth.click(function(){
				bulkFollow.autowidth = true == this.checked ? 1 : 0;
				bulkFollow._autowidth();
				bulkFollow.code();
			});
			$template_skins.click(function(o){
				var $target = $(o.target);
				if (undefined != $target.attr('href') && '' != $target.attr('href')) {
					bulkFollow.setting.skin = $target.attr('class');
					$('li', $template_skins).removeClass('on');
					$target.parent().attr('class', 'on');
					bulkFollow.code();
				}
			});
			// 监听推荐用户
			var fGetUserIds = function( e ) {
				e = e || window.event;
				var target = e.target || e.srcElement;
				bulkFollow.setting.uids = target.value;
				bulkFollow.code();
			};
			if ( document.addEventListener ) {
				setInterval( (function() {
					var sValue = "";
					return function() {
						var dUserIds = document.getElementById( "ui_fri_ids" ),
							sCurrentValue = dUserIds.value;

						if (sCurrentValue != sValue) {
							sValue = sCurrentValue;
							bulkFollow.setting.uids = sValue;
							bulkFollow.code();
						}
					};
				})(), 200 );
			} else {
				dUserIds.attachEvent( "onpropertychange", fGetUserIds );
			}
			dUserIds = null;

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
bulkFollow.init();