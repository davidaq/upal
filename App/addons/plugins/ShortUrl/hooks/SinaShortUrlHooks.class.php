<?php
class SinaShortUrlHooks
{
	// 替换短网址
	public function getShortUrl($url,$appkey)
	{
		if(!$appkey) return $url;
		return $this->request($url,$appkey);
	}

	private function request($url,$appkey){
		if(!$url) return '';
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, 'http://api.t.sina.com.cn/short_url/shorten.json');   //goo.gl api url
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, 'source='.$appkey.'&url_long='.urlencode($url));
		$short = curl_exec($curl);
		$short = json_decode($short);
		curl_close($curl);
		if(!isset($short->error_code)){
			return $short['0']->url_short;
		}else{
			return $url;
		}
	}
}