<?php
/*
 * 自动缩略图 参数 url|w|h|type="cut/full"|mark="text/image|r"
 * thumb.php?url=/thinksns/data/userface/000/00/00/41_middle_face.jpg?1247718988&w=20&h=20
*/
error_reporting(0);
set_time_limit(30);
$biggest_memory_limit	=	256; //单位M，后缀不要加M
//全局定义文件
//require 'define.inc.php';

//临时目录
$tempDir	=	"./data/thumb_temp/";
checkDir($tempDir);

//分析URL
$url = urldecode($_GET['url']);
$url = preg_replace('/(.*)\?(.*)$/','$1',$url);

//XSS脚本攻击探测
//include THINK_PATH.'/Vendor/xss.php';
//DetectXSS($url);

//2009-10-7 修改 将本地图片修改成相对地址，避免file_get_contents不能读取远程文件时出错（可修改php.ini  设置 allow_fopen_url 为 true）
//$url =  str_ireplace(SITE_URL,'.',$url);
if(file_exists($url)){
	$url	=	$url;
}elseif($result	=	GrabImage($url,$tempDir)){
	$url	=	$result;
	$grab_temp_file	=	$result;
}else{
	$url	=	"./public/images/nopic.jpg";
}

//解析参数
$w = $_GET['w']?intval($_GET['w']):'100';	//宽度
$h = $_GET['h']?intval($_GET['h']):'100';	//高度
$t = $_GET['t']=='f'?'f':'c';		//是否切割
$r = $_GET['r']?1:0;			//是否覆盖

//目录名hash
$fileHash	=	md5($url.$w.$h);
$hashPath	=	substr($fileHash,0,2).'/'.substr($fileHash,2,2).'/';

//缩图目录
$thumbDir	=	"./data/thumb/".$hashPath;
checkDir($thumbDir);

if(!$sourceInfo['type']) 
    $sourceInfo['type'] == 'jpg';

$tempFile	=	$tempDir.$fileHash.'.'.$sourceInfo['type'];
$thumbFile	=	$thumbDir.$fileHash."_".$w."_".$h."_".$t.'.'.$sourceInfo['type'];

$img		=	new Image();
//判断是否替换，存在则跳转，不存在继续进行
if(!$r && file_exists($thumbFile)){
	//这里有2种方法，第一种多一次跳转，多一个http连接数，第二种，要进行一次php处理，多占用一部分内存。
	//header('location:'.$thumbFile);
	$img->showImg($thumbFile);
}

//不存在输出
if(copy($url,$tempFile)){

	//判断图片大小 如果图片宽和高都小于要缩放的比例 直接输出
	$info	=	getimagesize($tempFile);
	//判断处理图片大约需要的内存
	$need_memory	=	(($info[0]*$info[1])/100000);
	$memory_limit	=	ini_get('memory_limit');
	if( ($need_memory > $memory_limit) && ($need_memory <= $biggest_memory_limit) ){
		ini_set('memory_limit',$need_memory.'M');
	}

	if($info[0]<=$w && $info[1]<=$h){
		copy($tempFile,$thumbFile);
		$img->showImg($thumbFile,'',$info[0],$info[1]);
		unlink($tempFile);
		unlink($grab_temp_file);
		exit;
	}else{
		//生成缩图
		if($t=='c'){
			$thumb	=	$img->cutThumb($tempFile,$thumbFile,$w,$h);
		}elseif($t=='f'){
			$thumb	=	$img->thumb($tempFile,'',$thumbFile,$w,$h);
		}
		//输出缩图
		$img->showImg($thumb,'',$w,$h);
		unlink($tempFile);
		unlink($grab_temp_file);
		exit;
	}
}

//获取远程图片
function GrabImage($url,$thumbDir) {
	if($url=="")	return false;
	$filename	=	md5($url).strrchr($url,".");
	$img		=	file_get_contents($url);
	if(!$img)		return false;

	$filepath	=	$thumbDir.$filename;
	$result		=	file_put_contents($filepath,$img);
	if($result){
		return $filepath;
	}else{
		return false;
	}
}

//检查并创建多级目录
function checkDir($path){
	$pathArray = explode('/',$path);
	$nowPath = '';
	array_pop($pathArray);
	foreach ($pathArray as $key=>$value){
		if ( ''==$value ){
			unset($pathArray[$key]);
		}else{
			if ( $key == 0 )
				$nowPath .= $value;
			else
				$nowPath .= '/'.$value;
			if ( !is_dir($nowPath) ){
				if ( !mkdir($nowPath, 0777) ) return false;
			}
		}
	}
	return true;
}

function imagecreatefrombmp($fname) {

	$buf=@file_get_contents($fname);

	if(strlen($buf)<54) return false;

	$file_header=unpack("sbfType/LbfSize/sbfReserved1/sbfReserved2/LbfOffBits",substr($buf,0,14));

	if($file_header["bfType"]!=19778) return false;
	$info_header=unpack("LbiSize/lbiWidth/lbiHeight/sbiPlanes/sbiBitCountLbiCompression/LbiSizeImage/lbiXPelsPerMeter/lbiYPelsPerMeter/LbiClrUsed/LbiClrImportant",substr($buf,14,40));
	if($info_header["biBitCountLbiCompression"]==2) return false;
	$line_len=round($info_header["biWidth"]*$info_header["biBitCountLbiCompression"]/8);
	$x=$line_len%4;
	if($x>0) $line_len+=4-$x;

	$img=imagecreatetruecolor($info_header["biWidth"],$info_header["biHeight"]);
	switch($info_header["biBitCountLbiCompression"]){
	case 4:
	$colorset=unpack("L*",substr($buf,54,64));
	for($y=0;$y<$info_header["biHeight"];$y++){
	$colors=array();
	$y_pos=$y*$line_len+$file_header["bfOffBits"];
	for($x=0;$x<$info_header["biWidth"];$x++){
	if($x%2)
	$colors[]=$colorset[(ord($buf[$y_pos+($x+1)/2])&0xf)+1];
	else
	$colors[]=$colorset[((ord($buf[$y_pos+$x/2+1])>>4)&0xf)+1];
	}
	imagesetstyle($img,$colors);
	imageline($img,0,$info_header["biHeight"]-$y-1,$info_header["biWidth"],$info_header["biHeight"]-$y-1,IMG_COLOR_STYLED);
	}
	break;
	case 8:
	$colorset=unpack("L*",substr($buf,54,1024));
	for($y=0;$y<$info_header["biHeight"];$y++){
	$colors=array();
	$y_pos=$y*$line_len+$file_header["bfOffBits"];
	for($x=0;$x<$info_header["biWidth"];$x++){
	$colors[]=$colorset[ord($buf[$y_pos+$x])+1];
	}
	imagesetstyle($img,$colors);
	imageline($img,0,$info_header["biHeight"]-$y-1,$info_header["biWidth"],$info_header["biHeight"]-$y-1,IMG_COLOR_STYLED);
	}
	break;
	case 16:
	for($y=0;$y<$info_header["biHeight"];$y++){
	$colors=array();
	$y_pos=$y*$line_len+$file_header["bfOffBits"];
	for($x=0;$x<$info_header["biWidth"];$x++){
	$i=$x*2;
	$color=ord($buf[$y_pos+$i])|(ord($buf[$y_pos+$i+1])<<8);
	$colors[]=imagecolorallocate($img,(($color>>10)&0x1f)*0xff/0x1f,(($color>>5)&0x1f)*0xff/0x1f,($color&0x1f)*0xff/0x1f);
	}
	imagesetstyle($img,$colors);
	imageline($img,0,$info_header["biHeight"]-$y-1,$info_header["biWidth"],$info_header["biHeight"]-$y-1,IMG_COLOR_STYLED);
	}
	break;
	case 24:
	for($y=0;$y<$info_header["biHeight"];$y++){
	$colors=array();
	$y_pos=$y*$line_len+$file_header["bfOffBits"];
	for($x=0;$x<$info_header["biWidth"];$x++){
	$i=$x*3;
	$colors[]=imagecolorallocate($img,ord($buf[$y_pos+$i+2]),ord($buf[$y_pos+$i+1]),ord($buf[$y_pos+$i]));
	}
	imagesetstyle($img,$colors);
	imageline($img,0,$info_header["biHeight"]-$y-1,$info_header["biWidth"],$info_header["biHeight"]-$y-1,IMG_COLOR_STYLED);
	}
	break;
	default:
	return false;
	break;
	}
	return $img;
}
function imagebmp(&$im, $filename = '', $bit = 8, $compression = 0)
{
    if (!in_array($bit, array(1, 4, 8, 16, 24, 32)))
    {
        $bit = 8;

    }
    else if ($bit == 32) // todo:32 bit
    {
        $bit = 24;
    }

    $bits = pow(2, $bit);

    // 调整调色板
    imagetruecolortopalette($im, true, $bits);
    $width = imagesx($im);
    $height = imagesy($im);
    $colors_num = imagecolorstotal($im);

    if ($bit <= 8)
    {
        // 颜色索引
        $rgb_quad = '';
        for ($i = 0; $i < $colors_num; $i ++)
        {
            $colors = imagecolorsforindex($im, $i);
            $rgb_quad .= chr($colors['blue']) . chr($colors['green']) . chr($colors['red']) . "\0";         }

        // 位图数据
        $bmp_data = '';

        // 非压缩
        if ($compression == 0 || $bit < 8)
        {
            if (!in_array($bit, array(1, 4, 8)))
            {
                $bit = 8;
            }

            $compression = 0;

            // 每行字节数必须为4的倍数，补齐。


            $extra = '';
            $padding = 4 - ceil($width / (8 / $bit)) % 4;
            if ($padding % 4 != 0)
            {
                $extra = str_repeat("\0", $padding);
            }

            for ($j = $height - 1; $j >= 0; $j --)
            {
                $i = 0;
                while ($i < $width)
                {
                    $bin = 0;
                    $limit = $width - $i < 8 / $bit ? (8 / $bit - $width + $i) * $bit : 0;

                    for ($k = 8 - $bit; $k >= $limit; $k -= $bit)
                    {
                        $index = imagecolorat($im, $i, $j);
                        $bin |= $index << $k;
                        $i ++;
                    }

                    $bmp_data .= chr($bin);
                }

                $bmp_data .= $extra;
            }
        }
        // RLE8 压缩
        else if ($compression == 1 && $bit == 8)
        {
            for ($j = $height - 1; $j >= 0; $j --)
            {
                $last_index = "\0";
                $same_num   = 0;
                for ($i = 0; $i <= $width; $i ++)
                {
                    $index = imagecolorat($im, $i, $j);
                    if ($index !== $last_index || $same_num > 255)
                    {
                        if ($same_num != 0)
                        {
                            $bmp_data .= chr($same_num) . chr($last_index);
                        }

                        $last_index = $index;
                        $same_num = 1;
                    }
                    else
                    {
                        $same_num ++;
                    }
                }

                $bmp_data .= "\0\0";
            }

            $bmp_data .= "\0\1";
        }

        $size_quad = strlen($rgb_quad);
        $size_data = strlen($bmp_data);
    }
    else
    {
        // 每行字节数必须为4的倍数，补齐。
        $extra = '';
        $padding = 4 - ($width * ($bit / 8)) % 4;
        if ($padding % 4 != 0)
        {
            $extra = str_repeat("\0", $padding);
        }

        // 位图数据
        $bmp_data = '';

        for ($j = $height - 1; $j >= 0; $j --)
        {
            for ($i = 0; $i < $width; $i ++)
            {
                $index = imagecolorat($im, $i, $j);
                $colors = imagecolorsforindex($im, $index);

                if ($bit == 16)
                {
                    $bin = 0 << $bit;

                    $bin |= ($colors['red'] >> 3) << 10;
                    $bin |= ($colors['green'] >> 3) << 5;
                    $bin |= $colors['blue'] >> 3;

                    $bmp_data .= pack("v", $bin);
                }
                else
                {
                    $bmp_data .= pack("c*", $colors['blue'], $colors['green'], $colors['red']);
                }

                // todo: 32bit;
            }

            $bmp_data .= $extra;
        }

        $size_quad = 0;
        $size_data = strlen($bmp_data);
        $colors_num = 0;
    }

    // 位图文件头
    $file_header = "BM" . pack("V3", 54 + $size_quad + $size_data, 0, 54 + $size_quad);

    // 位图信息头
    $info_header = pack("V3v2V*", 0x28, $width, $height, 1, $bit, $compression, $size_data, 0, 0, $colors_num, 0);
    // 写入文件
    if ($filename != '')
    {
        $fp = fopen("test.bmp", "wb");

        fwrite($fp, $file_header);
        fwrite($fp, $info_header);
        fwrite($fp, $rgb_quad);
        fwrite($fp, $bmp_data);
        fclose($fp);

        return 1;
    }

    // 浏览器输出
    header("Content-Type: image/bmp");
    echo $file_header . $info_header;
    echo $rgb_quad;
    echo $bmp_data;

    return 1;
}
class Image
{//类定义开始

    /**
     +----------------------------------------------------------
     * 架构函数
     *
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    function __construct()
    {

    }

    /**
     +----------------------------------------------------------
     * 取得图像信息
     *
     +----------------------------------------------------------
     * @static
     * @access public
     +----------------------------------------------------------
     * @param string $image 图像文件名
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    function getImageInfo($img) {
        $imageInfo = getimagesize($img);
        if( $imageInfo!== false) {

			if(function_exists(image_type_to_extension)){
				$imageType = strtolower(substr(image_type_to_extension($imageInfo[2]),1));
			}else{
				$imageType = strtolower(substr($img,strrpos($img,'.')+1));
			}

            $imageSize = filesize($img);
            $info = array(
                "width"=>$imageInfo[0],
                "height"=>$imageInfo[1],
                "type"=>$imageType,
                "size"=>$imageSize,
                "mime"=>$imageInfo['mime']
            );
            return $info;
        }else {
            return false;
        }
    }

    /**
     +----------------------------------------------------------
     * 显示服务器图像文件
     * 支持URL方式
     +----------------------------------------------------------
     * @static
     * @access public
     +----------------------------------------------------------
     * @param string $imgFile 图像文件名
     * @param string $text 文字字符串
     * @param string $width 图像宽度
     * @param string $height 图像高度
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    function showImg($imgFile,$text='',$width=80,$height=30) {
        //获取图像文件信息
		//2007/6/26 增加图片水印输出，$text为图片的完整路径即可
		$info = Image::getImageInfo($imgFile);
        if($info !== false) {
            $createFun  =   str_replace('/','createfrom',$info['mime']);
            $im = $createFun($imgFile);
            if($im) {
                $ImageFun= str_replace('/','',$info['mime']);
				//水印开始
                if(!empty($text)) {
                    $tc  = imagecolorallocate($im, 0, 0, 0);
					if(is_file($text)&&file_exists($text)){
						// 取得水印信息
						$textInfo = Image::getImageInfo($text);
						$createFun2= str_replace('/','createfrom',$textInfo['mime']);
						$waterMark = $createFun2($text);
						$imgW	=	$info["width"];
						$imgH	=	$info["width"]*$textInfo["height"]/$textInfo["width"];
						$y	=	($info["height"]-$textInfo["height"])/2;
						if(function_exists("ImageCopyResampled"))
							ImageCopyResampled($im,$waterMark,0,$y,0,0, $imgW,$imgH, $textInfo["width"],$textInfo["height"]);
						else
							ImageCopyResized($im,$waterMark,0,$y,0,0,$imgW,$imgH,  $textInfo["width"],$textInfo["height"]);
					}else{
						imagestring($im, 3, 5, 5, $text, $tc);
					}
					//ImageDestroy($tc);
                }
				//水印结束
                if($info['type']=='png' || $info['type']=='gif') {
                imagealphablending($im, FALSE);//取消默认的混色模式
                imagesavealpha($im,TRUE);//设定保存完整的 alpha 通道信息
                }
                Header("Content-type: ".$info['mime']);
                $ImageFun($im);
                @ImageDestroy($im);
                return ;
            }
        }
        //获取或者创建图像文件失败则生成空白PNG图片
        $im  = imagecreatetruecolor($width, $height);
        $bgc = imagecolorallocate($im, 255, 255, 255);
        $tc  = imagecolorallocate($im, 0, 0, 0);
        imagefilledrectangle($im, 0, 0, 150, 30, $bgc);
        imagestring($im, 4, 5, 5, "no pic", $tc);
        Image::output($im);
        return ;
    }

	// 切割缩图cutThumb
	// 2007/6/15
	function cutThumb($image,$filename='',$maxWidth='200',$maxHeight='50',$warterMark='',$type='',$interlace=true,$suffix='_thumb')
	{
        // 获取原图信息
        $info  = Image::getImageInfo($image);
         if($info !== false) {
            $srcWidth  = $info['width'];
            $srcHeight = $info['height'];
            $pathinfo = pathinfo($image);
			$type =  $pathinfo['extension'];
            $type = empty($type)?$info['type']:$type;
			$type	=	strtolower($type);
            $interlace  =  $interlace? 1:0;
            unset($info);
            // 载入原图
            $createFun = 'ImageCreateFrom'.($type=='jpg'?'jpeg':$type);
            $srcImg     = $createFun($image);

            //创建缩略图
            if($type!='gif' && function_exists('imagecreatetruecolor'))
                $thumbImg = imagecreatetruecolor($maxWidth, $maxHeight);
            else
                $thumbImg = imagecreate($maxWidth, $maxHeight);

            // 新建PNG缩略图通道透明处理
            if('png'==$type) {
                imagealphablending($thumbImg, false);//取消默认的混色模式
                imagesavealpha($thumbImg,true);//设定保存完整的 alpha 通道信息
            }elseif('gif'==$type) {
            // 新建GIF缩略图预处理，保证透明效果不失效
	            $background_color  =  imagecolorallocate($thumbImg,  0,255,0);  //  指派一个绿色
	            imagecolortransparent($thumbImg,$background_color);  //  设置为透明色，若注释掉该行则输出绿色的图
            }

			// 计算缩放比例
			if(($maxWidth/$maxHeight)>=($srcWidth/$srcHeight)){
				//宽不变,截高，从中间截取 y=
				$width	=	$srcWidth;
				$height	=	$srcWidth*($maxHeight/$maxWidth);
				$x		=	0;
				$y		=	($srcHeight-$height)*0.5;
			}else{
				//高不变,截宽，从中间截取，x=
				$width	=	$srcHeight*($maxWidth/$maxHeight);
				$height	=	$srcHeight;
				$x		=	($srcWidth-$width)*0.5;
				$y		=	0;
			}
			// 复制图片
			if(function_exists("ImageCopyResampled")){
				ImageCopyResampled($thumbImg, $srcImg, 0, 0, $x, $y, $maxWidth, $maxHeight, $width,$height);
			}else{
				ImageCopyResized($thumbImg, $srcImg, 0, 0, $x, $y, $maxWidth, $maxHeight,  $width,$height);
			}
			ImageDestroy($srcImg);
			/*水印开始* /
			if($warterMark){
				//计算水印的位置,默认居中
				$textInfo = Image::getImageInfo($warterMark);
				$textW	=	$textInfo["width"];
				$textH	=	$textInfo["height"];
				unset($textInfo);
				$mark = imagecreatefrompng($warterMark);
				$imgW	=	$width;
				$imgH	=	$width*$textH/$textW;
				$y		=	($height-$textH)/2;
				if(function_exists("ImageCopyResampled")){
					ImageCopyResampled($thumbImg,$mark,0,$y,0,0, $imgW,$imgH, $textW,$textH);
				}else{
					ImageCopyResized($thumbImg,$mark,0,$y,0,0,$imgW,$imgH,  $textW,$textH);
				}
				ImageDestroy($mark);
			}
			/*水印结束*/
            /*if('gif'==$type || 'png'==$type) {
				//imagealphablending($thumbImg, FALSE);//取消默认的混色模式
                //imagesavealpha($thumbImg,TRUE);//设定保存完整的 alpha 通道信息
                $background_color  =  ImageColorAllocate($thumbImg,  0,255,0);
				//  指派一个绿色
				imagecolortransparent($thumbImg,$background_color);
				//  设置为透明色，若注释掉该行则输出绿色的图
            }*/

            // 对jpeg图形设置隔行扫描
            if('jpg'==$type || 'jpeg'==$type) 	imageinterlace($thumbImg,$interlace);

            // 生成图片
            //$imageFun = 'image'.($type=='jpg'?'jpeg':$type);
            $imageFun	=	'imagepng';
			$filename  = empty($filename)? substr($image,0,strrpos($image, '.')).$suffix.'.'.$type : $filename;

            $imageFun($thumbImg,$filename);
            ImageDestroy($thumbImg);
            return $filename;
         }
         return false;

	}
    /**
     +----------------------------------------------------------
     * 生成缩略图
     *
     +----------------------------------------------------------
     * @static
     * @access public
     +----------------------------------------------------------
     * @param string $image  原图
     * @param string $type 图像格式
     * @param string $filename 缩略图文件名
     * @param string $maxWidth  宽度
     * @param string $maxHeight  高度
     * @param string $position 缩略图保存目录
     * @param boolean $interlace 启用隔行扫描
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	 //2007/7/18 添加水印缩略图
    function thumb($image,$type='',$filename='',$maxWidth=200,$maxHeight=50,$warterMark='',$interlace=true,$suffix='_thumb')
    {
        // 获取原图信息
		$info  = Image::getImageInfo($image);
        if($info !== false) {
			$srcWidth  = $info['width'];
            $srcHeight = $info['height'];
            $pathinfo = pathinfo($image);
            $type =  $pathinfo['extension'];
            $type = empty($type)?$info['type']:$type;
			$type	=	strtolower($type);
			$interlace  =  $interlace? 1:0;
            unset($info);
            $scale = min($maxWidth/$srcWidth, $maxHeight/$srcHeight); // 计算缩放比例
            // 缩略图尺寸
            $width  = (int)($srcWidth*$scale);
            $height = (int)($srcHeight*$scale);
            // 载入原图
            $createFun = 'ImageCreateFrom'.($type=='jpg'?'jpeg':$type);
            $srcImg     = $createFun($image);
            //创建缩略图
            if($type!='gif' && function_exists('imagecreatetruecolor'))
                $thumbImg = imagecreatetruecolor($width, $height);
            else
                $thumbImg = imagecreate($width, $height);

            // 新建PNG缩略图通道透明处理
            if('png'==$type) {
                imagealphablending($thumbImg, false);//取消默认的混色模式
                imagesavealpha($thumbImg,true);//设定保存完整的 alpha 通道信息
            }elseif('gif'==$type) {
            // 新建GIF缩略图预处理，保证透明效果不失效
	            $background_color  =  imagecolorallocate($thumbImg,  0,255,0);  //  指派一个绿色
	            imagecolortransparent($thumbImg,$background_color);  //  设置为透明色，若注释掉该行则输出绿色的图
            }

            // 复制图片
            if(function_exists("ImageCopyResampled"))
                ImageCopyResampled($thumbImg, $srcImg, 0, 0, 0, 0, $width, $height, $srcWidth,$srcHeight);
            else
                ImageCopyResized($thumbImg, $srcImg, 0, 0, 0, 0, $width, $height,  $srcWidth,$srcHeight);
            ImageDestroy($srcImg);
			/*
			//水印开始
			//计算水印的位置,默认居中
			$textInfo = Image::getImageInfo($warterMark);
			$textW	=	$textInfo["width"];
			$textH	=	$textInfo["height"];
			unset($textInfo);
			$mark = imagecreatefrompng($warterMark);
			$imgW	=	$width;
			$imgH	=	$width*$textH/$textW;
			$y		=	($height-$textH)/2;
			if(function_exists("ImageCopyResampled")){
				ImageCopyResampled($thumbImg,$mark,0,$y,0,0, $imgW,$imgH, $textW,$textH);
			}else{
				ImageCopyResized($thumbImg,$mark,0,$y,0,0,$imgW,$imgH,  $textW,$textH);
			}
			ImageDestroy($mark);
			//水印结束
			*/
            /*if('gif'==$type || 'png'==$type) {
				imagealphablending($thumbImg, FALSE);//取消默认的混色模式
                imagesavealpha($thumbImg,TRUE);//设定保存完整的 alpha 通道信息
                $background_color  =  ImageColorAllocate($thumbImg,  0,255,0);//  指派一个绿色
				imagecolortransparent($thumbImg,$background_color);//  设置为透明色，若注释掉该行则输出绿色的图
            }*/
            if('jpg'==$type || 'jpeg'==$type) {
				imageinterlace($thumbImg,$interlace);// 对jpeg图形设置隔行扫描
			}
            // 生成图片
            // $imageFun = 'image'.($type=='jpg'?'jpeg':$type);
            $imageFun	=	'imagepng';
			$filename  = empty($filename)? substr($image,0,strrpos($image, '.')).$suffix.'.'.$type : $filename;
            $imageFun($thumbImg,$filename);
			ImageDestroy($thumbImg);
            return $filename;
         }
         return false;
    }

    function output($im,$type='png')
    {
        Header("Content-type: image/".$type);
        //$ImageFun='Image'.$type;
        $ImageFun='ImagePNG';
		$ImageFun($im);
        ImageDestroy($im);
    }


}//类定义结束
?>