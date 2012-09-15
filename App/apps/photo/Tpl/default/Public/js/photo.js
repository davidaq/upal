//删除单张图片
function delphoto(){
	if(confirm('你确定要删除这张图片么？')){
		$.post(U('photo/Manage/delete_photo'),{id:photo_id,albumId:album_id},function(data){
			if(data==1){
				if(nextid==photo_id||nextid==''){
					location.href=U('photo/Index/album')+'&id='+album_id+'&uid='+_UID_;
				}else{
					location.href=U('photo/Index/photo')+'&id='+nextid+'&aid='+album_id+'&uid='+_UID_;
				}
				return;
			}else{
				ui.error('删除失败！');
			}
		});
	}
}

//将我的一张图片设置为该专辑}的封面
function setcover(){
	if(confirm('你要将这张图片设置为封面么？')){
		$.post(U('photo/Manage/set_cover'),{photoId:photo_id,albumId:album_id},function(data){
			if(data==1){
				ui.success('封面设置成功！');
			}else if(data==-1){
				ui.error('该图片不存在！');
			}else{
				ui.error('当前封面已是该图片，或设置失败！');
			}
		});
	}
}

//编辑图片
function editphotoTab(){
	ui.box.load(U('photo/Manage/edit_photo_tab')+'&aid='+album_id+'&pid='+photo_id,{title:'编辑图片'});
}
function do_update_photo(){
	var id		=	$('#photoId').val();
	var name	=	$('#name').val();
	var albumId	=	$('#albumId').val();
	if(!name || getLength(name.replace(/\s+/g,"")) == 0){
		alert('图片名字不能为空！');
        return false;
	}
	$.post(U('photo/Manage/do_update_photo'),{id:id,name:name,albumId:albumId},function(data){
	    if(data.result==1){
			if(albumId!=albumIdold){
				if(nextid==id||nextid==''){
					location.href=U('photo/Index/album')+'&id='+album_id+'&uid='+_UID_;
				}else{
					location.href=U('photo/Index/photo')+'&id='+nextid+'&aid='+album_id+'&uid='+_UID_;
				}
				return;
			}else{
				// $('.photoName').html(name);
				$('.photoName').html(data.message);
			}
			ui.box.close();
			ui.success('修改成功！');
		}else{
			ui.box.close();
			ui.error('图片信息无变化！');
		}
	}, 'json');
}