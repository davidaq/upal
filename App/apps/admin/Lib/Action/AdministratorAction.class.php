<?php
class AdministratorAction extends Action {
	
	public function _initialize()
	{
		// $this->success(); 和 $this->error();通过isAdmin变量决定是否加载头部
		$this->assign('isAdmin', 1);
		
		// 检查用户是否登录管理后台, 有效期为$_SESSION的有效期
		if (!service('Passport')->isLoggedAdmin())
			redirect( U('home/Public/adminlogin') );
		
		// 如果是应用的后台，检查用户是否具有节点权限
		if (APP_NAME != 'admin' && ! service('SystemPopedom')->hasPopedom($this->mid, 'admin/Apps/*', false)) {
			$this->assign('jumpUrl', U('home/Public/adminlogin'));
			$this->error('您无权限查看');
		}
	}
	
	protected function _getSearchMap($fields)
	{
		// 为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
		if (!empty($_POST)) {
			$_SESSION['admin_search_attach'] = serialize($_POST);
		} else if (isset($_GET[C('VAR_PAGE')])) {
			$_POST = unserialize($_SESSION['admin_search_attach']);
		} else {
			unset($_SESSION['admin_search_attach']);
		}
		
		// 组装查询条件
		$map = array();
		foreach ($fields as $k => $v) {
			foreach ($v as $field) {
				if (isset($_POST[$field]) && $_POST[$field] != '') {
					if($k == 'in')
						$map[$field] = array($k, explode(',', $_POST[$field]));
					else
						$map[$field] = array($k, $_POST[$field]);					
				}
			}
		}
		
		return $map;
	}
}