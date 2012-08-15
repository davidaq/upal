<?php

class PublicApi extends Api {
	// 获取MD5加密
	public function getMd5Data() {
		$data = md5($this->data['md5_data']);
		return $data;
	}
	
	// 通过email获取uid
	public function emailGetUid(){
		$map['email']=$this->data['email'];
		$uid = M('user')->where($map)->getField('uid');
		return $uid;	
	}
}

?>