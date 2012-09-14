function fDoBulkFollow(div){
	// 未登录状态，弹出登陆框
	if ( ! ( _MID_ > 0 ) ) {
		ui.quicklogin();
		return false;
	}
	if( $(div+" input[name='followuid[]']:checked").size() ==0){
		ui.error('请选择要关注的人');
		return '';
	}
	var options = {
		success: function(txt) {
			ui.success('关注成功');
		} 
	};		
	$(div).ajaxSubmit( options );
}