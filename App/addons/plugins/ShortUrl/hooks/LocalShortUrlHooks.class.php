<?php
class LocalShortUrlHooks
{
	var $offset	=	10000;
	var $string	=	'HwpGAejoUOPr6DbKBlvRILmsq4z7X3TCtky8NVd5iWE0ga2MchSZxfn1Y9JQuF';

	// 替换短网址
	public function getShortUrl($url,$localUrl)
	{
		if(!$url) return $url;
		return $this->getEncodeUrl($url,$localUrl);
	}

	//通过短地址获取源地址
	public function getOrignalUrl($shorten){

		$id	=	$this->getDecodeNum($shorten);
		$url = M('Url')->find($id);
		if($url['status']==1){
			return $url['url'];
		}else{
			return false;
		}
	}

	//获取编码后的Url
	protected function getEncodeUrl($url,$localUrl=''){

		//判断URL是否存在
		$hash	=	md5($url);

		//查询数据库中的ID
		if($result = M('Url')->where(array('hash'=>$hash))->find()){
			$url_id	=	intval($result['id']);
		}else{
			//插入新的url
			$map['url']	=	$url;
			$map['hash']=	$hash;
			$map['status']	=	1;
			$url_id	=	M('Url')->add($map);
		}

		if(!$url_id) return $url;
		//url前缀
		if(!$localUrl){
			$prefix = SITE_URL;
		}else{
			$prefix = $localUrl;
		}

		//输出缩短后的地址
		if(C('URL_ROUTER_ON')){
			$shorturl		=	$prefix.'/url/'.$this->getEncodeNum($url_id);
		}else{
			$shorturl		=	$prefix.'/shorturl.php?url='.$this->getEncodeNum($url_id);
		}

		return $shorturl;
	}

	/*
	 * 本地化 URL编码方法 为了将数字ID转换成字母
	 * 算法来自 http://www.alixixi.com/program/a/2011110775953.shtml
	 * 如果不想和其他ts站点一致,请在使用前就修改一下$this->string的字母顺序、和$this->offset的偏移量
	 */
	protected function getEncodeNum($num){

		$index = $this->string;
		//增加偏移量，其实只是为了好看，要不然初始的url就一个字母很别扭
		$num	=	intval($num);
		$num	+= $this->offset;
		$out	= "";
		for ($t = floor(log10($num) / log10(62 )); $t >= 0; $t-- ) {
			$a = floor( $num / pow( 62, $t ) );
			$out = $out . substr( $index, $a, 1 );
			$num = $num - ( $a * pow( 62, $t ) );
		}
		return $out;
	}

	/*
	 * 本地化 URL解码方法 用于shorturl.php
	 */
	protected function getDncodeNum($num){
		//编码符号集一定要与加密的相同
		$index  = $this->string;
		$out	= 0;
		$len	= strlen($num) - 1;
		for ($t = 0; $t <= $len; $t++) {
			$out = $out + strpos($index, substr($num, $t, 1 )) * pow(62, $len - $t);
		}
		//去除偏移量
		$out    -= $this->offset;
		return intval($out);
	}
}