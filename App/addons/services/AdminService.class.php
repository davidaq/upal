<?php
class AdminService extends Service {
	private $popedom;
	private $adminRule;
	private $popupAdminRule=array(
		"allow_commend"=>array(
								"name"=>"热门推荐",
								"alert"=>true,
								"popup"=>false,
								"url"=>"commend",
								),
		"allow_changeCate"=>array(
								"name"=>"转移版块",
								"alert"=>false,
								"popup"=>true,
								"url"=>"changeCate",
								),
		"allow_all_tip"=>array(
								"name"=>"全局置顶",
								"popup"=>false,
								"alert"=>true,
								"url"=>"tip2",
								),
//		"allow_top5"=>array(
//								"name"=>"TOP5",
//								"popup"=>false,
//								"alert"=>true,
//								"url"=>"top",
//								),
		"allow_highlight"=>array(
								"name"=>"高亮",
								"popup"=>true,
								"alert"=>false,
								"url"=>"highlight",
								),
		"allow_banzhu"=>array(
								"name"=>"版主已有正式回复",
								"popup"=>false,
								"alert"=>true,
								"url"=>"banzhu",
								),
	);
	
	
    public function __construct($data) {
		$this->init($data);
		$this->run();
    }
    
    public function getAllAdmin(){
	
    	foreach($this->adminRule as $key=>$value){
    		if($this->popedom->check($key)){
    			$adminRule[$key] = $value;
    		}
    	}
    	return $adminRule;
    }
    
    public function getCheckBox(){
    	foreach($this->popupAdminRule as $key=>$value){
    		if($this->popedom->check($key)){
    			$adminRule[$key] = $value;
    		}
    	}
    	return $adminRule;
    }
    
    public function getTopicAdmin(){
    	$topicAdmin = array_merge($this->popupAdminRule,$this->adminRule);
    	$result = array();
    	foreach ($topicAdmin as $key=>$value) {
    		if($this->popedom->check($key)){
    			$result[$key] = $value;
    		}
    	}
    	
    	return $result;
    }

	//服务初始化
	public function init($data=''){
		$this->popedom = $data[0];
		$this->adminRule = array(
			"allow_close"=>array(
								"name"=>"锁帖",
								"popup"=>false,
								"alert"=>true,
	    						"url"=>"close",
								"class"=>"suo",
								),
			"allow_tip"=>array(
								"name"=>"置顶",
								"popup"=>false,
								"alert"=>true,
								"url"=>"tip1",
								"class"=>"tip1",
								),
			"allow_elite"=>array(
								"name"=>"精华",
								"alert"=>true,
								"url"=>"dist",
								"class"=>"elite",
								),
//			"allow_hide"=>array(
//								"name"=>"仅内部可见",
//								"alert"=>true,
//								"url"=>"hide",
//								"class"=>"hide",
//								),
	);
		
	}

	//运行服务，系统服务自动运行
	public function run(){
	}

	/* 后台管理相关方法 */

	//启动服务，未编码
	public function _start(){
		return true;
	}
	
	//停止服务，未编码
	public function _stop(){
		return true;
	}

	//卸载服务，未编码
	public function _install(){
		return true;
	}

	//卸载服务，未编码
	public function _uninstall(){
		return true;
	}
}
?>