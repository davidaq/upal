jQuery.extend(weibo.plugin, {
	image:function(element, options){
	   
	    
	}
});

var stopUploadPic = 0;
jQuery.extend(weibo.plugin.image, {
    fileSize:"<?php echo $config['image']['size']; ?> KB",
    fileTypes:"<?php echo $config['image']['ext'] ?>",
	html:'<div class="layer_send_pic clearfix" ><ul class="list_send_pic" id="upload_list">'+'<li id="upload_selectpic" class="userPic" style="border:1px solid #CCCCCC;width:112px;height:112px;background:url(<?php echo $htmlPath."/img_default.png";?>) no-repeat;">'+
	'<span id="spanButtonPlaceholder"></span>'+
	'</li></ul><span style="padding:0px 10px;display:block;font-size:12px;line-height:23px">仅支持<?php echo $config['image']['ext'] ?>图片文件，文件大小<?php echo $config['image']['size2']; ?></span><div class="mt10"></div></div>',

	html_ie6:'<dl class="layer_upload_file">'+
	'<div id="upload_selectpic"><dd class="btn_green" href="javascript:void(0);" >从电脑选择图片'+
	'<form action="'+U("home/widget/addonsRequest")+'&addon=WeiboType&hook=uploadImage&PHPSESSID=<?php echo session_id(); ?>" enctype="multipart/form-data" method="post" id="uploadpic">'+
	'<input type="hidden" name="plugin_id" value="1"><input type="file" hidefoucs="true" name="pic" onchange="weibo.plugin.image.upload(this)">'+
	'</form></dd><dd>IE6下仅支持单个<?php echo $config['image']['ext'] ?>图片文件，且文件小于<?php echo $config['image']['size2']; ?></dd></div><div class="alC pt10 pb10 f14px" id="upload_loading" style="display:none"><img src="'+ _THEME_+'/images/icon_waiting.gif" width="20" class="alM"> 正在上传中...<br /><a class="btn_w mt10" href="javascript:void(0)" onclick="$(\'div .talkPop\').remove();weibo.plugin.image.stopAjax();">取消上传</a></div>'+
	'</dl>',
	click:function(options){
		if ($.browser.msie && ($.browser.version == "6.0") && !$.support.style) {
			this.html = this.html_ie6;
			weibo.publish_type_box(this.html,options);
		}else{
			weibo.publish_type_box(this.html,options);
			weibo.closeCallback(function(){
			   $.post( U('home/widget/addonsRequest'),{'addon':'WeiboType','hook':'cancelPublish'});
			})
			if(weibo.obj == null){
				this.createFlashUpload();
			}
		}
	},
	upload:function(o){
		var allowext = ['jpg','jpeg','gif','png'];
		var ext = /\.[^\.]+$/.exec( $(o).val() );
		ext = ext.toString().replace('.','');
		if( jQuery.inArray( ext.toLowerCase() , allowext )==-1 ){
			alert('只允许上传jpg、jpeg、gif、png格式的图片');
			return false;
		}
		$('#upload_selectpic').hide();
		$('#upload_loading').show();
		$('#weibo_close_handle').hide();
		var options = {
			    success: function(txt) {
				if(stopUploadPic==1){
					return false;
				}
			      txt = eval( '(' + txt + ')' );
			      if(txt.boolen==1){
						var img = new Image;
						img.src = txt.picurl;
						img.onload = function(){
							if( this.width>100 || this.height>100 ){
								var style;
								if( this.height >  this.width ){
									style = "height:100px;width:"+this.width*(100/this.height)+"px";
								}else{
									style = "width:100px;height:"+this.height*(100/this.width)+"px";
								}
								
								var html = "<img src='"+txt.picurl+"' style='"+style+"'><input name='publish_type_data' type='hidden' style='width:86%' value="+txt.type_data+" />";
							}else{
								var html = "<img src='"+txt.picurl+"'><input name='publish_type_data' type='hidden' style='width:86%' value="+txt.type_data+" />";					}
							if($('#content_publish').val()==''){
								$('#content_publish').val('图片分享 ');
							}
				            $("#publish_type_content").html('<div style="padding:10px;">'+txt.file_name+'&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" onclick="weibo.plugin.image.removeImage();">删除图片</a><BR>'+html+'</div>');
				            $('div.talkPop').data('type', 1);
				            weibo.publish_type_val(txt.publish_type);
							$('#upload_loading').hide();
							$('#upload_selectpic').show();
							weibo.checkInputLength(_LENGTH_);
						};
								
				  }else{
					alert( txt.message );
					$('.talkPop').remove();
			      }
			    } 
			};
//			$('#publish_type_content').html('<div style="width:400px;text-align:center;height:150px;"><img src="__THEME__/images/icon_waiting.gif" width="30"></div>');
			var httpRespondHandle = $('#uploadpic').ajaxSubmit( options );
		    return false;
	},
  createFlashUpload:function(){
			weibo.obj = new SWFUpload({
				// Backend Settings
        upload_url: U('home/widget/addonsRequest'),
				post_params: {'addon':'WeiboType','hook':'uploadImage',"PHPSESSID": "<?php echo session_id(); ?>"},

				// File Upload Settings
				file_size_limit : this.fileSize,
				file_types : this.fileType,
				file_types_description : "JPEG Images;GIF Images;JPG Images;PNG Image",
				file_upload_limit : <?php echo $config['image']['limit']; ?>,
				file_queue_limit : <?php echo $config['image']['limit']; ?>,
        file_post_name: 'pic',

				// Event Handler Settings - these functions as defined in Handlers.js
				//  The handlers are not part of SWFUpload but are part of my website and control how
				//  my website reacts to the SWFUpload events.
				swfupload_preload_handler : this.preLoad,
				swfupload_load_failed_handler : this.loadFailed,
				file_queue_error_handler : this.fileQueueError,
				file_dialog_complete_handler : this.fileDialogComplete,
	            file_queued_handler:this.fileQueued,
				upload_progress_handler : this.uploadProgress,
				upload_error_handler : this.uploadError,
				upload_success_handler : this.uploadSuccess,
				upload_complete_handler : this.uploadComplete,
				file_types:this.fileTypes,
				

				// Button Settings
				//button_image_url : "images/SmallSpyGlassWithTransperancy_17x18.png",
				button_placeholder_id : "spanButtonPlaceholder",
				button_width: 112,
				button_height: 112,
				button_text : '<span class="button">选择1张或多张图</span>',
        button_text_style : '.button {font:12px Arial,Helvetica,sans-serif,Simsun;}',
				button_text_top_padding: 59,
				button_text_left_padding: 15,
				button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
				button_cursor: SWFUpload.CURSOR.HAND,
				
				// Flash Settings
                flash_url : "<?php echo SITE_URL.'/public/js/swf'; ?>/swfupload.swf",
				flash9_url : "<?php echo SITE_URL.'/public/js/swf'; ?>/swfupload_fp9.swf",

				custom_settings : {
					progressTarget: "upload_selectpic"
				},
				// Debug Settings
				debug: false
  });
  },
  fileQueued:function(file) {
    try {
      var progress = new FileProgress(file, this.customSettings.progressTarget);
      progress.setStatus("Pending...");
      progress.toggleCancel(true, this);

    } catch (ex) {
      ui.error(ex);
    }
  },

  removeImage:function(){
    $('div .talkPop').remove();
    weibo.publish_type_val(0);
    weibo.hasBox = false;
  },
	stopAjax:function(){
		stopUploadPic=1;
	},
  preLoad:function(){
    if (!this.support.loading) {
      alert("You need the Flash Player to use SWFUpload.");
      return false;
    } else if (!this.support.imageResize) {
      alert("You need Flash Player 10 to upload resized images.");
    return false;
    }
  },
  loadFailed:function(){
    ui.error('抱歉，加载上传组件出错,请刷新再试或者联系管理员');
  },
  fileQueueError:function(file,errorCode,message){
    try {
        switch (errorCode) {
        case SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED:
            ui.error("同时上传文件数超过限制,还能上传"+message+"个");
            return;
        case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
            ui.error('文件上传过大，请上传小于'+weibo.plugin.image.fileSize);
            return;
        case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
            ui.error('不允许上传空字节文件');
            return;
        case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
            ui.error('上传文件属于无效类型');
            return;
        default:
            ui.error('上传过程出现失败，请联系管理员');
            return;
        }
    } catch (e) {
        ui.error(e);
    }
     
  },
  fileDialogComplete:function(numFilesSelected,numFilesQueued){
    try {
      if(numFilesQueued){
        this.startUpload();
      }
    } catch (ex) {
      ui.error(ex);
    }
  },
  uploadProgress:function(file,bytesLoaded){
   	try {
		  var percent = Math.ceil((bytesLoaded / file.size) * 100);
		  var progress = new FileProgress(file,  this.customSettings.upload_target);
		  progress.setProgress(percent);
		  progress.setStatus("Uploading...");
		  progress.toggleCancel(true, this);
	  } catch (ex) {
		  ui.error(ex);
	  } 
  },
  uploadError:function(file,errorCode,message){
	try {
		switch (errorCode) {
		case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
			ui.error("Error Code: HTTP Error, File name: " + file.name + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
			ui.error("Error Code: Upload Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.IO_ERROR:
			ui.error("Error Code: IO Error, File name: " + file.name + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
			ui.error("Error Code: Security Error, File name: " + file.name + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
			ui.error('抱歉，超过上传次数限制'+this.settings.file_upload_limit+'次');
			break;
		case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
			ui.error("Error Code: File Validation Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
			if (this.getStats().files_queued === 0) {
				document.getElementById(this.customSettings.cancelButtonId).disabled = true;
			}
			progress.setStatus("Cancelled");
			progress.setCancelled(this);
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
			progress.setStatus("Stopped");
			break;
		default:
			ui.error("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		}
	} catch (ex) {
        ui.error(ex);
    }
  },
  uploadSuccess:function(file,serverData){
		txt = eval( '(' + serverData + ')' );
    var progress = new FileProgress(file,  this.customSettings.upload_target);
    if(txt.boolen == 1){
      progress.setComplete(txt);
      progress.toggleCancel(true, this);
      weibo.publish_type_val(txt.publish_type);
      if($('#content_publish').val()==''){
        $('#content_publish').val('图片分享 ');
        weibo.checkInputLength(_LENGTH_);
      }
    }else{
      ui.error(txt.message);
      progress.remove(this);
    }
//    this.requeueUpload()
  },
  uploadComplete:function(file){
    try {
      /*  I want the next upload to continue automatically so I'll call startUpload here */
      if (this.getStats().files_queued > 0) {
        this.startUpload(this.getFile(0).ID);
      }
    } catch (ex) {
      ui.error(ex);
    }
  }
});


/**
* switchPicMore 
* 多个图片时展开
* @param id $id 
* @param type $type 
* @param picurl $picurl 
* @access public
* @return void
*/
function switchPicMore(id,type)
{
if(type=='close'){
  $('#pic_show_more_'+id).hide();
  $('#pic_mini_more_show_'+id).show();
}else{
  $.each($('.mini_item_'+id),function(key,value){
    var loading = $('.middel_loading_item_'+id).get(key);
    var img = new Image;
    var middelObj = $('.middel_item_'+id).get(key);
    if($(middelObj).attr('src')==''){
      loading.style.display="block";
      img.src = $(value).attr('middel')+'?time='+new Date();
      img.onload = function(){
        if(this.width>450){
          $(middelObj).css('width','450px');
        }
        $(middelObj).css('height','');
        $(middelObj).attr('src',this.src);
        loading.style.display="none";
      };
    }
    $('#pic_show_more_'+id).show();
    $('#pic_mini_more_show_'+id).hide();
    
  });
}
}
//切换图片
function switchPic(id,type,picurl){
	if( type=='close' ){
		$("#pic_show_"+id).hide();
		$("#pic_mini_show_"+id).show();
	}else{
		
		if( $("#pic_show_"+id).find('.imgSmall').attr('src')==''){
			$("#pic_mini_show_"+id).find('.loadimg').show();
			var img = new Image;
			img.src = picurl+'?time='+new Date();
			img.onload = function(){
				if( this.width>450 ){
					$("#pic_show_"+id).find('.imgSmall').css('width','450px');
				}
				$("#pic_show_"+id).find('.imgSmall').attr('src',this.src);
				$("#pic_mini_show_"+id).find('.loadimg').hide();
				$("#pic_show_"+id).show();
				$("#pic_mini_show_"+id).hide();	
			};
		}else{
			$("#pic_show_"+id).show();
			$("#pic_mini_show_"+id).hide();	
		}
	}
}

//旋转图片
function revolving(id,type){
	var img = $("#pic_show_"+id).find('.imgSmall');
	img.rotate(type);
}


$.fn.rotate = function(p){

	var img = $(this)[0],
		n = img.getAttribute('step');
	// 保存图片大小数据
	if (!this.data('width') && !$(this).data('height')) {
		this.data('width', img.width);
		this.data('height', img.height);
	};
	this.data('maxWidth',img.getAttribute('maxWidth'))

	if(n == null) n = 0;
	if(p == 'left'){
		(n == 0)? n = 3 : n--;
	}else if(p == 'right'){
		(n == 3) ? n = 0 : n++;
	};
	img.setAttribute('step', n);

	// IE浏览器使用滤镜旋转
	if(document.all) {
		if(this.data('height')>this.data('maxWidth') && (n==1 || n==3) ){
			if(!this.data('zoomheight')){
				this.data('zoomwidth',this.data('maxWidth'));
				this.data('zoomheight',(this.data('maxWidth')/this.data('height'))*this.data('width'));
			}
			img.height = this.data('zoomwidth');
			img.width  = this.data('zoomheight');
			
		}else{
			img.height = this.data('height');
			img.width  = this.data('width');
		}
		
		img.style.filter = 'progid:DXImageTransform.Microsoft.BasicImage(rotation='+ n +')';
		// IE8高度设置
		if ($.browser.version == 8) {
			switch(n){
				case 0:
					this.parent().height('');
					//this.height(this.data('height'));
					break;
				case 1:
					this.parent().height(this.data('width') + 10);
					//this.height(this.data('width'));
					break;
				case 2:
					this.parent().height('');
					//this.height(this.data('height'));
					break;
				case 3:
					this.parent().height(this.data('width') + 10);
					//this.height(this.data('width'));
					break;
			};
		};
	// 对现代浏览器写入HTML5的元素进行旋转： canvas
	}else{
		var c = this.next('canvas')[0];
		if(this.next('canvas').length == 0){
			this.css({'visibility': 'hidden', 'position': 'absolute'});
			c = document.createElement('canvas');
			c.setAttribute('class', 'maxImg canvas');
			img.parentNode.appendChild(c);
		}
		var canvasContext = c.getContext('2d');
		switch(n) {
			default :
			case 0 :
				img.setAttribute('height',this.data('height'));
				img.setAttribute('width',this.data('width'));
				c.setAttribute('width', img.width);
				c.setAttribute('height', img.height);
				canvasContext.rotate(0 * Math.PI / 180);
				canvasContext.drawImage(img, 0, 0);
				break;
			case 1 :
				if(img.height>this.data('maxWidth') ){
					h = this.data('maxWidth');
					w = (this.data('maxWidth')/img.height)*img.width;
				}else{
					h = this.data('height');
					w = this.data('width');
				}
				c.setAttribute('width', h);
				c.setAttribute('height', w);
				canvasContext.rotate(90 * Math.PI / 180);
				canvasContext.drawImage(img, 0, -h, w ,h );
				break;
			case 2 :
				img.setAttribute('height',this.data('height'));
				img.setAttribute('width',this.data('width'));
				c.setAttribute('width', img.width);
				c.setAttribute('height', img.height);
				canvasContext.rotate(180 * Math.PI / 180);
				canvasContext.drawImage(img, -img.width, -img.height);
				break;
			case 3 :
				if(img.height>this.data('maxWidth') ){
					h = this.data('maxWidth');
					w = (this.data('maxWidth')/img.height)*img.width;
				}else{
					h = this.data('height');
					w = this.data('width');
				}
				c.setAttribute('width', h);
				c.setAttribute('height', w);
				canvasContext.rotate(270 * Math.PI / 180);
				canvasContext.drawImage(img, -w, 0,w,h);
				break;
		};
	};
};

function FileProgress(file, targetID) {
	this.fileProgressID = file.id;
this.complete = false;

	this.opacity = 100;
	this.height = 0;
	

	this.fileProgressWrapper = document.getElementById(this.fileProgressID);
	if (!this.fileProgressWrapper) {
	  //图片容器
		this.fileProgressWrapper = document.createElement("li");
		this.fileProgressWrapper.className = "userPic";
		this.fileProgressWrapper.id = this.fileProgressID;

  //真正的图片
		this.fileProgressElement = document.createElement("img");
		this.fileProgressElement.src='<?php echo $htmlPath."img_default.jpg";?>';
  //取消上传的按钮
		var progressCancel = document.createElement("a");
		progressCancel.className = "del hover";
    progressCancel.href = "javascript:void(0)";
		progressCancel.appendChild(document.createTextNode(" "));
  //进度条
		var progressBar = document.createElement("div");
		progressBar.className = "uploadbar";
		progressBar.visible = 'hidden';
		var progressStatus = document.createElement("span");
		progressStatus.appendChild(document.createTextNode(" "));
		progressStatus.style.width="0%";
  progressBar.appendChild(progressStatus);


		this.fileProgressWrapper.appendChild(this.fileProgressElement);
		this.fileProgressWrapper.appendChild(progressCancel);
		this.fileProgressWrapper.appendChild(progressBar);
  document.getElementById('upload_list').insertBefore(this.fileProgressWrapper,document.getElementById(targetID));
		//document.getElementById(targetID).appendChild(this.fileProgressWrapper);
	} else {
		this.fileProgressElement = this.fileProgressWrapper.firstChild;
		//this.reset();
	}

	this.height = this.fileProgressWrapper.offsetHeight;
this.fileProgressWrapper.title = file.name;
	this.setTimer(null);
}

FileProgress.prototype.setTimer = function (timer) {
	this.fileProgressElement["FP_TIMER"] = timer;
};
FileProgress.prototype.getTimer = function (timer) {
	return this.fileProgressElement["FP_TIMER"] || null;
};

FileProgress.prototype.reset = function () {
	this.fileProgressElement.childNodes[2].firstChild.style.width = "0%";
	this.appear();	
};

FileProgress.prototype.setProgress = function (percentage) {
	this.fileProgressWrapper.childNodes[2].firstChild.style.width = percentage + "%";
	this.appear();	
};
FileProgress.prototype.setComplete = function (data) {
this.fileProgressElement.src = data.picurl;
this.fileProgressWrapper.childNodes[2].style.display='none';
var input = document.createElement('input');
input.name = 'publish_type_data[]';
input.value = data.type_data;
input.type='hidden';
this.fileProgressWrapper.appendChild(input);
this.complete = true;
};
FileProgress.prototype.setError = function () {
	this.fileProgressElement.className = "progressContainer red";
	this.fileProgressElement.childNodes[3].className = "progressBarError";
	this.fileProgressElement.childNodes[3].style.width = "";

	var oSelf = this;
	this.setTimer(setTimeout(function () {
		oSelf.disappear();
	}, 5000));
};
FileProgress.prototype.setCancelled = function (obj) {
this.remove(obj);
};
FileProgress.prototype.setStatus = function (status) {
this.fileProgressWrapper.childNodes[2].title=status;
};
FileProgress.prototype.remove = function(swfUploadInstance){
	this.fileProgressWrapper.childNodes[1].style.display = "none";
oSelf = this;
	var fileID = this.fileProgressID;
	//alert();
  if(swfUploadInstance){
      swfUploadInstance.setFileUploadLimit(swfUploadInstance.settings.file_upload_limit+1);
	  swfUploadInstance.cancelUpload(fileID);
}
	this.setTimer(setTimeout(function () {
		oSelf.disappear();
	}, 100));
}

//Show/Hide the cancel button
FileProgress.prototype.toggleCancel = function (show, swfUploadInstance) {
	if (swfUploadInstance) {
		var complete = this.complete;
  var self = this;
		this.fileProgressWrapper.childNodes[1].onclick = function () {
    self.remove(swfUploadInstance);
			return false;
		};
	}
};


FileProgress.prototype.appear = function () {
	if (this.getTimer() !== null) {
		clearTimeout(this.getTimer());
		this.setTimer(null);
	}
	
	if (this.fileProgressWrapper.filters) {
		try {
			this.fileProgressWrapper.filters.item("DXImageTransform.Microsoft.Alpha").opacity = 100;
		} catch (e) {
			// If it is not set initially, the browser will throw an error.  This will set it if it is not set yet.
			this.fileProgressWrapper.style.filter = "progid:DXImageTransform.Microsoft.Alpha(opacity=100)";
		}
	} else {
		this.fileProgressWrapper.style.opacity = 1;
	}
		
	this.fileProgressWrapper.style.height = "";
	
	this.height = this.fileProgressWrapper.offsetHeight;
	this.opacity = 100;
	this.fileProgressWrapper.style.display = "";
	
};

//Fades out and clips away the FileProgress box.
FileProgress.prototype.disappear = function () {
	var reduceOpacityBy = 15;
	var reduceHeightBy = 4;
	var rate = 30;	// 15 fps

	if (this.opacity > 0) {
		this.opacity -= reduceOpacityBy;
		if (this.opacity < 0) {
			this.opacity = 0;
		}

		if (this.fileProgressWrapper.filters) {
			try {
				this.fileProgressWrapper.filters.item("DXImageTransform.Microsoft.Alpha").opacity = this.opacity;
			} catch (e) {
				// If it is not set initially, the browser will throw an error.  This will set it if it is not set yet.
				this.fileProgressWrapper.style.filter = "progid:DXImageTransform.Microsoft.Alpha(opacity=" + this.opacity + ")";
			}
		} else {
			this.fileProgressWrapper.style.opacity = this.opacity / 100;
		}
	}

	if (this.height > 0) {
		this.height -= reduceHeightBy;
		if (this.height < 0) {
			this.height = 0;
		}

		this.fileProgressWrapper.style.height = this.height + "px";
	}

	if (this.height > 0 || this.opacity > 0) {
		var oSelf = this;
		this.setTimer(setTimeout(function () {
			oSelf.disappear();
		}, rate));
	} else {
  this.fileProgressWrapper.parentNode.removeChild(this.fileProgressWrapper);
		this.setTimer(null);
	}
};
