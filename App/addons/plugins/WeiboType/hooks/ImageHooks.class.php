<?php
require_once('AbstractWeiboTypeHooks.class.php');
/**
 * ImageHooks
 * 图片微博插件
 * 可以上传多张图片
 * @uses Hooks
 * @package
 * @version $id$
 * @copyright 2001-2013 SamPeng
 * @author SamPeng <penglingjun@zhishisoft.com>
 * @license PHP Version 5.2 {@link www.sampeng.org}
 */
class ImageHooks extends AbstractWeiboTypeHooks
{
    public $typeCode = 1;

    /**
     * home_index_middle_publish_type
     * 在发布微博底部可以自由添加类型,这里将添加一个图片的效果
     * @access public
     * @return void
     */
    public function _addWeiboTypeHtml()
    {
        $html = sprintf("<a href='javascript:void(0)' onclick='weibo.reset();weibo.plugin.image.click(this)' class='a52'><img class='icon_add_img_d' src='%s' />图片</a>",$this->htmlPath."/html/images/zw_img.gif");
        echo $html;
    }

    public function _weiboTypePublish($type_data)
    {
        //需要特殊处理只有一个的情况下,要兼容以前的显示效果
        if(is_array($type_data)&&count($type_data) == 1){
            $type_data = array_pop($type_data);
        }
        if(is_array($type_data)){
            $result = array();
            foreach($type_data as $data){
                $result[] = $this->_publishWeiboTypeData($data);
            }
            $res = array_filter($result);
            if(empty($res)) return false;
            return $res ;
        }else{
            $result = $this->_publishWeiboTypeData($type_data);
            return $result;
        }
    }

    public function weibo_type_publish($param)
    {
        if($param['type'] == $this->typeCode && $this->__checkHasInstallApp()){
            $type_data = unserialize($param['data']['type_data']);

            $weibo_id  = $param['weiboId'];
            $type      = $param['type'];
            $weibo_attach	=	D('WeiboAttach','weibo');
            $weiboAttach	=	D('WeiboAttach','weibo');
            if(isset($type_data['picurl'])){
                $attach = array($type_data['attach_id']);
            }else{
                $attach = array();
                foreach($type_data as $value){
                    if(!empty($value['attach_id'])){
                        $attach[] = $value['attach_id'];
                    }
                }
            }

            $weiboAttach->add($this->mid,$weibo_id,$type,$attach);
        }
    }
    public function cancelPublish(){
        if(!empty($_SESSION['weibo_img_attach'])){
            foreach($_SESSION['weibo_img_attach'] as $value){
                if(file_exists($value)){
                    unlink($value);
                }
            }
            Session::start();
            unset($_SESSION['weibo_img_attach']);
            Session::pause();
        }
    }

    public function _weiboTypeShow($typeData,$rand)
    {
        //如果数组中picurl索引不存在，则一定是多维数组，一定是多张图片
        $hasMore = false;
        if(!isset($typeData['picurl'])){
            $hasMore = true;
        }
        $this->assign('hasMore',$hasMore);
        $this->assign('data',$typeData);
        $this->assign('rand',$rand);
        $res = $this->fetch('image');
        return $res;
    }




    /**
     * uploadImage
     * 上传图片接受处理
     * @access public
     * @return void
     */
    public function uploadImage()
    {
        if( $_FILES['pic'] ){
            $config = model('AddonData')->lget('weibo_type');
            $size =  $config['image']['size'] ?  $config['image']['size']:999999999;
            if($_FILES['pic']['size'] > $size*1024 ){
                $result['boolen']    = 0;
                $result['message']   = "请上传小于".$config['image']['size']."KB大小的图片";
                exit( json_encode( $result ) );
            }

            $imageInfo = getimagesize($_FILES['pic']['tmp_name']);

            if(function_exists(image_type_to_extension($imageInfo[2],1))){
                $imageType = strtolower(substr(image_type_to_extension($imageInfo[2]),1));
            }else{
                $imageType = strtolower(substr($_FILES['pic']['name'],strrpos($_FILES['pic']['name'],'.')+1));
            }

            if($imageType == "jpeg") $imageType ='jpg';

            $config['image']['ext'] = empty($config['image']['ext'])?"jpg;png;jpeg;gif":$config['image']['ext'];
            $ext = explode(';', $config['image']['ext']);
            if( !in_array($imageType,$ext) ) {
                $result['boolen']    = 0;
                $result['message']   = L('photo_format_error');
                exit( json_encode( $result ) );
            }

            //执行上传操作
            $savePath = $this->_getSaveTempPath();
            $filename = md5( $_FILES['pic']['tmp_name'].$this->mid ).'.'.$imageType;
            $copyRes  = @copy($_FILES['pic']['tmp_name'], $savePath.'/'.$filename);
            $moveUploadRes = @move_uploaded_file($_FILES['pic']['tmp_name'], $savePath.'/'.$filename);
            if($copyRes || $moveUploadRes)
            {
                $result['boolen']    = 1;
                $result['type_data'] = 'temp/'.$filename;
                $result['file_name'] = $filename;
                $result['picurl']    = __UPLOAD__.'/temp/'.$filename;
                 Session::start();
                $_SESSION['weibo_img_attach'][]= $savePath.'/'.$filename;
                Session::pause();
            } else {
                $result['boolen']    = 0;
                $result['message']   = L('upload_filed');
            }
        }else{
            $result['boolen']    = 0;
            $result['message']   = L('upload_filed');
        }
        $result['publish_type'] = $this->typeCode;
        exit( json_encode( $result ) );
    }

    private function _publishWeiboTypeData($type_data){

		if(!file_exists($type_data)){
            $type_data = '/data/uploads/'.$type_data;
        }else{
            $type_data = preg_replace("/^\./",'',$type_data);
        }

        $info	=	X('Xattach')->addFile('weibo_image', SITE_PATH.$type_data);

		if($info['status']){
			//缩图规格
			$size['small']['x']	=	120;
			$size['small']['y']	=	120;
			$size['middle']['x']	=	465;
			$size['middle']['y']	=	-1; //不限制

			//缩图路径-文件名
			$bigpic		=	$info['info']['savepath'].$info['info']['savename'];
			$smallpic	=	$info['info']['savepath'].'small_'.$info['info']['savename'];
			$middlepic	=	$info['info']['savepath'].'middle_'.$info['info']['savename'];

			//缩图
			// if(extension_loaded("imagick")){
   //              $this->_imageickThumb( UPLOAD_PATH.'/'.$bigpic, UPLOAD_PATH.'/'.$smallpic, $size['small']['x'], $size['small']['y'], false);
   //              $this->_imageickThumb( UPLOAD_PATH.'/'.$bigpic, UPLOAD_PATH.'/'.$middlepic, $size['middle']['x'], $size['middle']['y'], false);
   //          }else{
                include_once SITE_PATH.'/addons/libs/Image.class.php';
                Image::thumb( UPLOAD_PATH.'/'.$bigpic , UPLOAD_PATH.'/'.$smallpic , '' , $size['small']['x'] , $size['small']['y'] );
                Image::thumb( UPLOAD_PATH.'/'.$bigpic , UPLOAD_PATH.'/'.$middlepic , '' , $size['middle']['x'] , ($size['middle']['y']==-1)?'auto':$size['middle']['y'] );
            // }
            $typedata['thumburl']		= $smallpic;
			$typedata['thumbmiddleurl'] = ($info['info']['extension']=='gif')?$bigpic:$middlepic;
			$typedata['picurl']			= $bigpic;
            $typedata['attach_id']      = $info['info']['id'];
			//为微博缩略图-小图不加水印，大图、中图加水印
			//if($fileext!='gif'){
            
            if($info['info']['extension']!='gif'){
				require_cache(SITE_PATH."/addons/libs/WaterMark/WaterMark.class.php");
				WaterMark::iswater(UPLOAD_PATH.'/'.$bigpic);
				WaterMark::iswater(UPLOAD_PATH.'/'.$middlepic);
			}

            return $typedata;
        }else{
            return false;
        }
    }
    private function _getSaveTempPath()
    {
        $savePath = SITE_PATH . '/data/uploads/temp';
        if (! file_exists ( $savePath ))
            mk_dir ( $savePath );
        return $savePath;
    }

    private function _imageickThumb($url,$type,$new_w=765,$new_h=1000,$self = false,$trim=false)
    {
        $srcFile = $url;
        if($self){
            $destFile = $url;
        }else{
            $destFile = $type;
        }
        if($new_w <= 0 || !file_exists($srcFile)) return false;
        $src = new Imagick($srcFile);
        $image_format = strtolower($src->getImageFormat());
        if($image_format != 'jpeg' && $image_format != 'gif' && $image_forumat != 'bmp' && $image_format != 'png' && $image_format != 'jpg') return false;

        $src_page = $src->getImagePage();
        $src_w = $src_page['width'];
        $rate_w  = $new_w / $src_w;

        if($rate_w >= 1) return false;

        if($new_h == -1){
            $new_h = $rate_w * $src_page['height'];
        }
        //如果是 jpg jpeg gif
        if($image_format != 'gif'){
            $dest = $src;
            if(!$trim){
                $dest->thumbnailImage($new_w, $new_h, true);
            }else{
                $dest->cropthumbnailImage($new_w, $new_h);
            }

            $dest->writeImage($destFile);
            $dest->clear();
            //gif需要以帧一帧的处理
        }else{
            $dest = new Imagick();
            $color_transparent = new ImagickPixel("transparent"); //透明色
            foreach($src as $img){
                $page = $img->getImagePage();

                if($new_h == -1){
                    $new_h = ($new_w/$page['width']) * $src_page['hight'];
                }

                $tmp = new Imagick();
                $tmp->newImage($page['width'], $page['height'], $color_transparent, 'gif');
                $tmp->compositeImage($img, Imagick::COMPOSITE_OVER, $page['x'], $page['y']);
                if(!$trim){
                    $tmp->thumbnailImage($new_w, $new_h, true);
                }else{
                    $tmp->cropthumbnailImage($new_w, $new_h);
                }
                $dest->addImage($tmp);
                $dest->setImagePage($tmp->getImageWidth(), $tmp->getImageHeight(), 0, 0);
                $dest->setImageDelay($img->getImageDelay());
                $dest->setImageDispose($img->getImageDispose());
            }
            $dest->coalesceImages();
            $dest->writeImages($destFile, true);
            $dest->clear();
        }
    }
}
