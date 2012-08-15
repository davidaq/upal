<?php
class BaseAction extends Action {
	
	public function _initialize() {
		$this->assign('isAdmin', 1);
	}
	
	protected function _getSearchMap($fields) {
		// 为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
		if ( !empty($_POST) ) {
			$_SESSION['admin_search_attach'] = serialize($_POST);
		}else if ( isset($_GET[C('VAR_PAGE')]) ) {
			$_POST = unserialize($_SESSION['admin_search_attach']);
		}else {
			unset($_SESSION['admin_search_attach']);
		}
		
		// 组装查询条件
		$map	= array();
		foreach ($fields as $k => $v) {
			foreach ($v as $field) {
				if ( isset($_POST[$field]) && $_POST[$field] != '' ) {
					if($k == 'in') {
						$map[$field] = array($k, explode(',', $_POST[$field]));
					}else {
						$map[$field] = array($k, $_POST[$field]);					
					}
				}
			}
		}
		
		return $map;
	}
}