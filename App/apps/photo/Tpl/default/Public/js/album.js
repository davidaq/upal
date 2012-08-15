//弹出创建专辑窗口
function create_album_tab(uid){
	ui.box.load(U('photo/Manage/create_album_tab')+'&uid='+uid,{title:'创建'+APP_NAME});
}
//执行创建专辑操作
function do_create_album(){
	var name		=	$('#name').val().replace(/\s+/g,"");
	var privacy		=	$('#privacy').val();
	var password	=	$('#textfield3').val();

	if(!name)	{ 
		alert('名称不能为空！');
		return false;
	}else if(name.length > 12)	{ 
		alert('名称不能超过12个字！');
		return false;
	}
	$.post(U('photo/Manage/do_create_album'),{name:name,privacy:privacy,privacy_data:password},function(data){
		if(data == -1){
			ui.error('该相册名已存在！');
		}else if(data){
			parent.setAlbumOption(data)
			ui.box.close();
			ui.success('创建成功！');
		}else{
			ui.box.close();
			ui.error('创建失败！');
		}
	});
}
//添加专辑下拉菜单
function setAlbumOption(data){
	var obj	=	eval('(' + data + ')');
	$('#albumlist').append('<option value="'+ obj.albumId +'" selected="selected" style="background-color:yellow">'+ obj.albumName +'</option>');
}