<?php
class SpaceStyleHooks extends Hooks
{
    public static $defaultStyle = array(0=>"default",1=>"black",2=>"green",3=>"yellow",4=>"pink");

    public function public_head($param)
    {
		// 载入基本样式
		echo '<link href="' . $this->htmlPath . '/html/base.css" rel="stylesheet" type="text/css" />';

		//载入用户个性配置
		$param['uid'] = !$param['uid'] ? $this->mid : $param['uid'];
		$style_data = $this->model('SpaceStyle')->getStyle($param['uid']);

		if (!$style_data) {
			return;
		}

		$classname	= $style_data['classname'];
		$background	= $style_data['background'];

		//载入基本风格
		if ('' !== $classname) {
      $class_url = $this->htmlPath . '/html/' . $classname . '.css';
		}
    echo '<link href="' . $class_url . '" rel="stylesheet" type="text/css" id="change_skin" />';

		//载入自定义背景
		$background['image'] && $background['image'] = "url('{$background['image']}')";
		$background_CSS = array();
		foreach ($background as $key => $value) {
			$value && $background_CSS[$key] = "background-{$key}:{$value};";
		}
		if (!empty($background_CSS)) {
			echo '<style id="change_background">.page_home{' . implode('', $background_CSS) . '}</style>';
		}
	}

    public function home_index_right_top()
    {
        $this->display('changeStyleBtn');
    }

    public function changeStyleBox()
    {
		$style_data = $this->model('SpaceStyle')->getStyle($this->mid);
		$this->assign($style_data);
        $this->assign('defaultStyle', self::$defaultStyle);
        $this->display('changeStyleBox');
    }

    public function saveStyle()
    {
    	$change_style_model = $this->model('SpaceStyle');
    	$res = $change_style_model->saveStyle($this->mid, $_POST);

    	$ajax_return = array(
    		'data' => '',
    		'info' => $change_style_model->getLastError(),
    		'status' => false !== $res
    	);
    	echo json_encode($ajax_return);
    }

    /* XXXXXX 待重构 XXXXXX */
    public function config_changestyle(){
        return false;
    }
	//删除临时图片
	public function delImage(){
	  $imagePath=SITE_PATH .'/'.$_POST['imagePath'];
	  if(unlink($imagePath)){
	  echo 1;
	  }
	}
   //保存临时图片
	public function saveImageTemp(){
              $imageInfo = getimagesize($_FILES['pic']['tmp_name']);
              $filesize=abs(filesize($_FILES['pic']['tmp_name']));
            if($filesize>1024*1024*2){
                 echo '';
              }else{
                     if(function_exists(image_type_to_extension($imageInfo[2],1))){
                      $imageType = strtolower(substr(image_type_to_extension($imageInfo[2]),1));
                    }else{
                      $imageType = strtolower(substr($_FILES['pic']['name'],strrpos($_FILES['pic']['name'],'.')+1));
                   }
        if($imageType == "jpeg") $imageType ='jpg';
        $dir_path = 'data/uploads/background' . convertUidToPath($this->mid);
		    $savePath = SITE_PATH . '/' . $dir_path;
        if (!file_exists($savePath)) {
            mkdir($savePath, 0777, true);
        }
        $filename = md5($_FILES['pic']['tmp_name'].$this->mid ).'.'.$imageType;

        $moveUploadRes = @move_uploaded_file($_FILES['pic']['tmp_name'], $savePath.'/'.$filename);
		echo $dir_path . '/'.$filename;
          }

	}
}