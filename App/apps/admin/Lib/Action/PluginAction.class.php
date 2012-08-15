<?php
class PluginAction extends AdministratorAction {
	
	private function __getPluginInfo($path_name = '', $using_lowercase = true) {
		$filename = SITE_PATH . '/addons/plugins/' . $path_name . '/info.php';
		
		if ( is_file($filename) ) {
			$info = include_once $filename;
			return $using_lowercase ? array_change_key_case($info) : array_change_key_case($info,CASE_UPPER);
		}else {
			return null;
		}
	}

	public function shorturl(){
		$shorturl = model('Xdata')->lget('shorturl');
		$this->assign($shorturl);
		$this->display();
	}
	
	public function doshorturl(){
		$data['shorturl_type'] = $_POST['shorturl_type'];
		$data['customize_url'] = h($_POST['customize_url']);
		model('Xdata')->lput('shorturl', $data);
		$this->redirect( 'admin/Plugin/shorturl' );
	}
}