function useCamera()
{
	var content = '<embed height="464" width="514" ';
	content +='flashvars="type=camera';
	content +='&postUrl='+cameraPostUrl+'&radom=1';
	content += '&saveUrl='+saveUrl+'&radom=1" ';
	content +='pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" ';
	content +='allowscriptaccess="always" quality="high" ';
	content +='src="'+editorFlaPath+'"/>';
	document.getElementById('avatar_editor').innerHTML = content;
}
function buildAvatarEditor(pic_id,pic_path,post_type)
{
	var content = '<embed height="464" width="514"'; 
	content+='flashvars="type='+post_type;
	content+='&photoUrl='+pic_path;
	content+='&photoId='+pic_id;
	content+='&postUrl='+cameraPostUrl+'&radom=1';
	content+='&saveUrl='+saveUrl+'&radom=1"';
	content+=' pluginspage="http://www.macromedia.com/go/getflashplayer"';
	content+=' type="application/x-shockwave-flash"';
	content+=' allowscriptaccess="always" quality="high" src="'+editorFlaPath+'"/>';
	document.getElementById('avatar_editor').innerHTML = content;
}
	/**
	  * 提供给FLASH的接口 ： 没有摄像头时的回调方法
	  */
	 function noCamera(){
		 alert("没找到摄像头硬件 ：）");
	 }
			
	/**
	 * 提供给FLASH的接口：编辑头像保存成功后的回调方法
	 */
	function avatarSaved(){
		alert('保存成功');
		//window.location.href = '/profile.do';
	}
	
	 /**
	  * 提供给FLASH的接口：编辑头像保存失败的回调方法, msg 是失败信息，可以不返回给用户, 仅作调试使用.
	  */
	 function avatarError(msg){
		 alert("上传失败");
			 }
 
			 function checkFile()
			 {
				 var path = document.getElementById('Filedata').value;
		 var ext = getExt(path);
		 var re = new RegExp("(^|\\s|,)" + ext + "($|\\s|,)", "ig");
		  if(extensions != '' && (re.exec(extensions) == null || ext == '')) {
		 alert('对不起，只能上传jpg, gif, png类型的图片');
				 return false;
				 }
				 showLoading();
				 return true;
			 }
 
	function getExt(path) {
		return path.lastIndexOf('.') == -1 ? '' : path.substr(path.lastIndexOf('.') + 1, path.length).toLowerCase();
	}
	function showLoading()
	{
	   document.getElementById('loading_gif').style.visibility = 'visible';
	}
	  function hideLoading()
	  {
		document.getElementById('loading_gif').style.visibility = 'hidden';
	  }