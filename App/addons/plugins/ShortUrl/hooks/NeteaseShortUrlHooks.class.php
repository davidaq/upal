<?php
class NeteaseShortUrlHooks
{
	// 替换短网址
	public function getShortUrl($url,$apikey)
	{
		if(!$apikey) return $url;
		return $this->request($url,$apikey);
	}

	private function request($url,$apikey){
		if(!$url) return '';
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, 'http://126.am/api!shorten.action');   //goo.gl api url
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, 'key='.$apikey.'&longUrl='.urlencode($url));
		$short = curl_exec($curl);
		$short = json_decode($short);
		curl_close($curl);
		if(isset($short->status_code) && $short->status_code=='200'){
			return 'http://'.$short->url;
		}else{
			return $url;
		}
	}
}