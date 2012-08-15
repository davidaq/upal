<?php
class BaiduShortUrlHooks
{
	// 替换短网址
	public function getShortUrl($url)
	{
		return $this->request($url);
	}

	private function request($url){
		if(!$url) return '';
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, 'http://dwz.cn/create.php');   //goo.gl api url
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, 'url='.urlencode($url));
		$short = curl_exec($curl);
		$short = json_decode($short);
		curl_close($curl);
		if(isset($short->tinyurl) && $short->status==0){
			return $short->tinyurl;
		}else{
			return $url;
		}
	}
}