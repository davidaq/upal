<?php
/**
 * watermark
 * 添加图片水印类
 * @author 浪子不归 <fbcha@163.com>
 * @license ThinkSNS Version 2.5
 */
class WaterMark extends Think
{
	//判断是否开启水印功能
	static public function iswater($filename,$savename=null){
		//读取默认配置
		$default['attach_watermark_closed'] = 1; // 是否开启水印功能，默认关闭
		$default['attach_watermark_minwidth'] = 100; // 添加水印最小宽度，默认300px
		$default['attach_watermark_minheight'] = 100; // 添加水印最小高度，默认300px
		$default['attach_watermark_type'] = 'txt'; // 水印类型，默认图片水印
		$default['attach_watermark_txt'] = "@{uname}\n{space}"; // 文本水印内容
		$default['attach_watermark_img'] =  realpath(dirname(__FILE__).'/watermark.png'); // 图片水印文件
		$default['attach_watermark_font'] =  realpath(dirname(__FILE__).'/msyh.ttf'); // 文字水印字体文件
		$default['attach_watermark_fontsize'] = 10; // 字体大小
		$default['attach_watermark_pct'] = 50; // 水印透明度，0-100默认80
		$default['attach_watermark_quality'] = 90; // 水印质量，0-100默认90
		$default['attach_watermark_pos'] = 7; // 水印位置，默认左下角

		//读取系统后台配置
		$system_options = model('Xdata')->lget('attach');
		//$system_options['attach_watermark_img']  = realpath(dirname(__FILE__).'/'.$system_options['attach_watermark_img']);
		//$system_options['attach_watermark_font'] = realpath(dirname(__FILE__).'/'.$system_options['attach_watermark_font']);
		//$options = array_merge($default,$system_options);
		$default['attach_watermark_closed'] = intval($system_options['attach_watermark_closed']);

		if(isset($system_options['attach_watermark_minwidth']) && !empty($system_options['attach_watermark_minwidth']))
			$default['attach_watermark_minwidth'] = intval($system_options['attach_watermark_minwidth']);

		if(isset($system_options['attach_watermark_minheight']) && !empty($system_options['attach_watermark_minheight']))
			$default['attach_watermark_minheight'] = intval($system_options['attach_watermark_minheight']);

		if(isset($system_options['attach_watermark_txt']) && !empty($system_options['attach_watermark_txt']))
			$default['attach_watermark_txt'] = $system_options['attach_watermark_txt'];

		if(isset($system_options['attach_watermark_fontsize']) && !empty($system_options['attach_watermark_fontsize']))
			$default['attach_watermark_fontsize'] = intval($system_options['attach_watermark_fontsize']);

		$options = $default;
		//解析 attach_watermark_txt 中的变量
		$user	=	getUserInfo(intval($_SESSION['mid']));
		if($user){
			$key	=	array('{uid}','{uname}','{space}');
			$value	=	array($user['uid'],$user['uname'],$user['space']);
			$options['attach_watermark_txt']	=	str_ireplace($key, $value, $options['attach_watermark_txt']);
		}

		//水印处理
		if($options['attach_watermark_closed']==1){
			self::water($filename,$options,$savename);
		}
	}
	/**
     * 取得图像信息
     *
     * @static
     * @access public
     * @param string $image 图像文件名
     * @return mixed
     */
	static function getImageInfo($img) {
		$imageInfo = getimagesize($img);
		if ($imageInfo !== false) {
			$imageType = strtolower(substr(image_type_to_extension($imageInfo[2]), 1));
			$imageSize = filesize($img);
			$info = array(
			"width" => $imageInfo[0],
			"height" => $imageInfo[1],
			"type" => $imageType,
			"size" => $imageSize,
			"mime" => $imageInfo['mime']
			);
			return $info;
		} else {
			return false;
		}
	}
	/**
     * 为图片添加水印
     * @static public
     * @param string $source 原文件名
     * @param string $water  水印图片
     * @param string $$savename  添加水印后的图片名
     * @param string $alpha  水印的透明度
     * @return string
     * @throws ThinkExecption
     */
	static public function water($source, $conf, $savename=null) {
		//检查文件是否存在
		//if (!file_exists($source) || !file_exists($conf['attach_watermark_img']))
		if (!file_exists($source))
			return false;

		//图片信息
		$sInfo = self::getImageInfo($source);

		//如果图片小于预定图片尺寸，不生成图片
		if ($sInfo["width"] < $conf['attach_watermark_minwidth'] || $sInfo['height'] < $conf['attach_watermark_minheight'])
		return false;

		//图像位置,默认为右下角右对齐
		switch($conf['attach_watermark_pos'])
		{
			case 1:
				$posX = +5;
				$posY = +5;
				break;
			case 2:
				$posX = ($sInfo["width"] - $wInfo["width"]) / 2;
				$posY = +5;
				break;
			case 3:
				$posX = $sInfo["width"] - $wInfo["width"] - 5;
				$posY = +15;
				break;
			case 4:
				$posX = +5;
				$posY = ($sInfo["height"] - $wInfo["height"]) / 2;
				break;
			case 5:
				$posX = ($sInfo["width"] - $wInfo["width"]) / 2;
				$posY = ($sInfo["height"] - $wInfo["height"]) / 2;
				break;
			case 6:
				$posX = $sInfo["width"] - $wInfo["width"] - 5;
				$posY = ($sInfo["height"] - $wInfo["height"]) / 2;
				break;
			case 7:
				$posX = +5;
				$posY = $sInfo["height"] - $wInfo["height"] - 5;
				break;
			case 8:
				$posX = ($sInfo["width"] - $wInfo["width"]) / 2;
				$posY = $sInfo["height"] - $wInfo["height"] - 5;
				break;
			case 9:
				$posX = $sInfo["width"] - $wInfo["width"] - 5;
				$posY = $sInfo["height"] - $wInfo["height"] -5;
				break;
			default:
				$posX = $sInfo["width"] - $wInfo["width"] - 5;
				$posY = $sInfo["height"] - $wInfo["height"] -5;
				exit;
		}

		//建立图像
		$sCreateFun = "imagecreatefrom" . $sInfo['type'];
		$sImage = $sCreateFun($source);

		//图片水印类型
		if($conf['attach_watermark_type'] == 'img' && file_exists($conf['attach_watermark_img']))
		{
			$wInfo = self::getImageInfo($conf['attach_watermark_img']);
			$wCreateFun = "imagecreatefrom" . $wInfo['type'];
			$wImage = $wCreateFun($conf['attach_watermark_img']);

			//设定图像的混色模式
			imagealphablending($wImage, true);

			//生成混合图像
			self::imagecopymerge_alpha($sImage, $wImage, $posX, $posY, 0, 0, $wInfo['width'], $wInfo['height'], $conf['attach_watermark_pct']);
		}

		//文本水印类型
		if($conf['attach_watermark_type'] == 'txt' && file_exists($conf['attach_watermark_font']))
		{
			$fontSize = $conf['attach_watermark_fontsize'];
			$fontType = $conf['attach_watermark_font'];
			$txt = $conf['attach_watermark_txt'];
			$box = imagettfbbox($fontSize, 0, $fontType,$conf['attach_watermark_txt']);
			$wInfo["width"] = max($box[2], $box[4]) - min($box[0], $box[6]);
			$wInfo["height"] = max($box[1], $box[3]) - min($box[5], $box[7]);
			$posY  = $posY - $wInfo["height"]+15;
			//imagestring ( $sImage, $fontSize, $posX, $posY - 30, $conf['attach_watermark_txt'], imagecolorallocate($sImage, $R, $G, $B));

			//可以加两层就是加一个阴影效果
			imagettftext ( $sImage, $fontSize, 0, $posX , $posY, imagecolorallocate($sImage, 0, 0, 0), $fontType, $txt);
			imagettftext ( $sImage, $fontSize, 0, $posX -1 , $posY -1, imagecolorallocate($sImage, 255, 255, 255), $fontType, $txt);
		}

		//输出图像
		$ImageFun = 'Image' . $sInfo['type'];
		//如果没有给出保存文件名，默认为原图像名
		if (!$savename) {
			$savename = $source;
			//@unlink($source);
		}

		//保存图像
		($sInfo['type'] == 'jpeg') ? $ImageFun($sImage, $savename, $conf['attach_watermark_quality']) : $ImageFun($sImage, $savename);
		imagedestroy($sImage);
		return true;
	}

	static function imagecopymerge_alpha($sImage, $wImage, $posX, $posY, $wX, $wY, $wWidth, $wHeight, $pct)
	{
		$opacity = $pct;
		$cut = imagecreatetruecolor($wWidth, $wHeight);
		imagecopy($cut, $sImage, 0, 0, $posX, $posY, $wWidth, $wHeight);
		imagecopy($cut, $wImage, 0, 0, $wX, $wY, $wWidth, $wHeight);
		imagecopymerge($sImage, $cut, $posX, $posY, $wX, $wY, $wWidth, $wHeight, $opacity);
	}
}
?>