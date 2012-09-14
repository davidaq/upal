<?php
class ToolAction extends AdministratorAction {
	
	public function index() {
		echo '<h3>正在开发中...</h3>';
	}
	
	/** 工具 - 地区管理 **/
	
	public function area() {
		$_GET['pid'] = intval($_GET['pid']);
		$area = model('Area')->getAreaList($_GET['pid']);
    	$this->assign('area', $area);
    	
    	if ( $_GET['pid'] == 0 ) {
    		$this->assign('back_id', '-1');
    	}else {
	    	$back_id = model('Area')->where('area_id='.$_GET['pid'])->getField('pid');
	    	$this->assign('back_id', $back_id);
    	}
    	
    	$this->assign('pid', $_GET['pid']);
    	$this->display();
	}
	
	public function addArea() {
		$this->assign('pid', intval($_GET['pid']));
		$this->display('editArea');
	}
	
	public function editArea() {
		$_GET['area_id'] = intval($_GET['area_id']);
		$area = M('area')->where('area_id='.$_GET['area_id'])->find();
		$area['area_id'] = $_GET['area_id'];
		$this->assign('area', $area);
		$this->display();
	}
	
	public function doAddArea() {
		$_POST['title']	= t($_POST['title']);
		$_POST['pid']	= intval($_POST['pid']);
		if (empty($_POST['title'])) {
			echo 0;
			return ;
		}
		
		$_LOG['uid'] = $this->mid;
		$_LOG['type'] = '1';
		$data[] = '扩展 - 工具 - 地区管理';
		$data[] = $_POST;
		$_LOG['data'] = serialize($data);
		$_LOG['ctime'] = time();
		M('AdminLog')->add($_LOG);
		
		F('Cache_PostArea',null);
		echo ($res = M('area')->add($_POST)) ? $res : '0';
	}
	
	public function doEditArea() {
		$_POST['title']		= t($_POST['title']);
		$_POST['area_id']	= intval($_POST['area_id']);
		if (empty($_POST['title'])) {
			echo 0;
			return ;
		}
		
		$_LOG['uid'] = $this->mid;
		$_LOG['type'] = '3';
		$data[] = '扩展 - 工具 - 地区管理';
		$data[] =  M('area')->where( array( 'area_id'=>intval($_POST['area_id']) ) )->find();
		$data[] = $_POST;
		$data['2']['pid'] = $data['1']['pid'];
		$_LOG['data'] = serialize($data);
		$_LOG['ctime'] = time();
		M('AdminLog')->add($_LOG);
		
		F('Cache_PostArea',null);
		
		echo M('area')->where('`area_id`='.$_POST['area_id'])->setField('title', $_POST['title']) ? '1' : '0';
	}
	
	public function doDeleteArea() {
		$_POST['ids']	= explode(',', t($_POST['ids']));
		if (empty($_POST['ids'])) {
			echo 0;
			return ;
		}
		$map['area_id']	= array('in', $_POST['ids']);
		
		$_LOG['uid'] = $this->mid;
		$_LOG['type'] = '2';
		$data[] = '扩展 - 工具 - 地区管理';
		$data[] =  M('area')->where( $map )->findall();
		$_LOG['data'] = serialize($data);
		$_LOG['ctime'] = time();
		M('AdminLog')->add($_LOG);
		
		F('Cache_PostArea',null);
		
		echo M('area')->where($map)->delete() ? '1' : '0';
	}
	
	/*
	 * 工具 - 数据备份
	 */
	
	public function backup() {
		$dir = './data/database/';
		if(is_dir($dir)){
			if($dh = opendir($dir)){
				while (($filename = readdir($dh)) !== false) {
					if($filename != '.' && $filename != '..'){
            			if(substr($filename,strrpos($filename,'.')) == '.sql' || substr($filename,strrpos($filename,'.')) == '.php'){
            				$file = $dir.$filename;
            				$filemtime = date('Y-m-d H:i:s',filemtime($file));
            				$addtime[] = $filemtime;
            				$log[] = array(
            					'filename'	=> $filename,
            					'filesize'	=> formatsize(filesize($file)),
            					'addtime'	=> $filemtime,
            					'filepath'	=> C('SITE_URL').$file,
            				);
            			}
					}
				}
			}
		}else{
			@mk_dir($dir,0777);
		}
		
		array_multisort($addtime,SORT_ASC,$log);
		$this->assign('log',$log);
		
		$this->assign('table', D('Database')->getTableList());
		$this->display();
	}
	
	public function doBackUp() {
		if( empty($_REQUEST['backup_type']) )
			$this->error('参数错误');
		
		$tables		= array();
		// 当前卷号
		$volume		= isset($_GET['volume']) ? (intval($_GET['volume']) + 1) : 1;
		// 备份文件的文件名
		$filename	= date('ymd').'_'.substr(md5(uniqid(rand())),0,10);
		
		$_REQUEST['backup_type'] = t($_REQUEST['backup_type']) == 'custom' ? 'custom' : 'all';
		
		if ( $_REQUEST['backup_type'] == 'all' ) {
			$tables = D('Database')->getTableList();
			$tables = getSubByKey($tables, 'Name');
			
		}else if( $_REQUEST['backup_type'] == 'custom' ) {
			if ($_POST['backup_table']) {
				$tables	= $_POST['backup_table'];
				$_SESSION['backup_custom_table'] = $tables;
			}else {
				$tables = $_SESSION['backup_custom_table'];
			}
		}
		
		$filename	= trim($_REQUEST['filename']) ? trim($_REQUEST['filename']) : $filename;
		$startfrom	= intval($_REQUEST['startform']);
		$tableid	= intval($_REQUEST['tableid']);
		$sizelimit	= intval($_REQUEST['sizelimit']) ? intval($_REQUEST['sizelimit']) : 1000;
		$tablenum	= count($tables);
		$filesize	= $sizelimit*1000;
		$complete	= true;
		$tabledump	= '';
		
		if($tablenum == 0)
			$this->error('请选择备份的表');
			
		for(; $complete && ($tableid < $tablenum) && strlen($tabledump)+500 < $filesize; $tableid++ ){
			
			$sqlDump = D('Database')->getTableSql($tables[$tableid], $startfrom, $filesize,strlen($tabledump),$complete);
			
			$tabledump .= $sqlDump['tabledump']; 
			$complete	= $sqlDump['complete'];
			$startfrom	= intval($sqlDump['startform']);
			if($complete)
				$startfrom = 0;
		}
		
		!$complete && $tableid--;

		if ( trim($tabledump) ) {
			// $filepath = './data/database/'.$filename."_$volume".'.sql';
			$filepath = './data/database/'.$filename."_$volume".'.php';
			$fp = @fopen($filepath,'ab');
			fwrite($fp, "#<?php exit;?>\n\n");
			fwrite($fp,$tabledump);
			if ( ! fwrite($fp,$tabledump) ) {
				$this->error('文件目录写入失败, 请检查data目录是否可写');
			}else {
				$url_param = array(
					'filename'		=> $filename,
					'backup_type'	=> $_REQUEST['backup_type'],
					'sizelimit'		=> $sizelimit,
					'tableid'		=> $tableid,
					'startform'		=> $startfrom,
					'volume'		=> $volume,
				);
				
				$url = U('admin/Tool/doBackUp', $url_param);
				$this->assign('jumpUrl',$url);
				
				if( $_POST['backup_type'] =='custom' ){
					$_LOG['uid'] = $this->mid;
					$_LOG['type'] = '1';
					$data[] = '扩展 - 工具 - 数据备份';
					if( $_POST['__hash__'] )unset( $_POST['__hash__'] );
					$data[] =  $_POST;
					$_LOG['data'] = serialize($data);
					$_LOG['ctime'] = time();
					M('AdminLog')->add($_LOG);
				}
				if( $_POST['backup_type'] =='all' ){
					$_LOG['uid'] = $this->mid;
					$_LOG['type'] = '1';
					$data[] = '扩展 - 工具 - 数据备份';
					$data[] =  array('全部数据表都备份成功');
					$_LOG['data'] = serialize($data);
					$_LOG['ctime'] = time();
					M('AdminLog')->add($_LOG);
				}
				
				
				$this->success("备份第{$volume}卷成功");
			}
		}else {
			$this->assign('jumpUrl', U('admin/Tool/backup'));
			
			$this->success("备份成功");
		}
	}
	
	public function doDeleteBackUp() {
		$_POST['selected'] = explode(',', t($_POST['selected']));
		
		foreach($_POST['selected'] as $file){
			// $file = './data/database/'.$file.'.sql';
			$file = './data/database/'.$file.'.php';
			file_exists($file) && @unlink($file);
		}
		
		$_LOG['uid'] = $this->mid;
		$_LOG['type'] = '2';
		$data[] = '扩展 - 工具 - 数据备份';
		$data[] =  $_POST['selected'];
		$_LOG['data'] = serialize($data);
		$_LOG['ctime'] = time();
		M('AdminLog')->add($_LOG);
		
		echo 1;
	}
	
	//导入备份
	function import(){
		$filename = $_GET['filename'];
		$sqldump = '';
		$file = './data/database/'.$filename;
		if(file_exists($file)){
			
			$fp = @fopen($file,'rb');
			$sqldump = fread($fp,filesize($file));
			
			fclose($fp);
		}
		
		$ret = D('Database')->import($sqldump);
		if($ret) {
			$this->success('导入成功');
		}else{
			$this->error('导入失败');
		}
	}
	
	public function dbconvert() {
		$this->display();
	}
	
	public function doDbconvert() {
		if ($_POST['doDbconvert'] != 1) {
			$this->error('参数错误');
		}
		
		// 转换ts_comment的数据
		$dao = M('comment');
		$db_prefix = C('DB_PREFIX');
		
		// 转换to_uid
		$sql = "UPDATE {$db_prefix}comment c1 SET `to_uid` = ( SELECT c2.uid FROM ( SELECT temp.* FROM {$db_prefix}comment temp ) c2 WHERE c2.id = c1.toId ) WHERE toId <> 0";
		if ( false === $dao->execute($sql) ) {
			$this->error('转换ts_comment的to_uid字段出现错误');
		}
		
		// 转换data
		$apps = array('blog', 'vote');
		foreach ($apps as $k => $app) {
			$appids = $dao->query("SELECT `appid` FROM {$db_prefix}comment WHERE `type` = '{$app}'");
			$appids = getSubByKey($appids, 'appid');
			
			unset($map);
			$map['id']	= array('in', $appids);
			$app_titles = M($app)->where($map)->field('id,title,uid')->findAll();
			
			$sql = array();
			foreach ($app_titles as $app_detail) {
				unset($data);
				$data['title']	= $app_detail['title'];
				$data['url']	= ($app == 'blog') ? U('blog/Index/show',array('id'=>$app_detail['id'],'mid'=>$app_detail['uid'])) 
												   : U('vote/Index/pollDetail', array('id'=>$app_detail['id']));
				$data['table']	= $app;
				$data['id_field']			 = 'id';
				$data['comment_count_field'] = 'commentCount';
				
				if ( ! $dao->where("`type`='{$app}' AND `appid`={$app_detail['id']}")->setField('data', serialize($data)) ) {
					echo '转换程序出现错误, SQL: ' . $dao->getLastSql();
					exit;
				}
			} // END foreach - app_info
		} // END foreach - apps
		
		$this->assign('jumpUlr', U('admin/Tool/dbconvert'));
		$this->success('转换成功');
	}
	
	/*
	 * 邀请统计
	 */
	public function inviteRecord()
	{	
		$records = model('InviteRecord')->getStatistics($_POST['uid']);
		$this->assign($records);
		$this->display();
	}
	
	public function invitedUser()
	{
		$users = model('InviteRecord')->getInvitedUser($_GET['uid']);
		$uids = array_merge(array($_GET['uid']), getSubByKey($users['data'], 'fid'));
		D('User', 'home')->setUserObjectCache($uids);
		$this->assign($users);
		$this->assign('uid', $_GET['uid']);
		$this->display();
	}
	
	/*
	 * CNZZ统计
	 */
	public function cnzz()
	{
		$this->assign(model('Xdata')->lget('cnzz'));
		$this->display();
	}
	
	public function doSetCnzz()
	{
		// 申请CNZZ统计
		$domain = SITE_URL;
		$base	= 'ThinkSNS';
		$key	= md5($domain . 'KslDiq5H');
		$url	= "http://intf.cnzz.com/user/companion/thinksns.php?domain={$domain}&key={$key}&cms={$base}";
		$result	= file_get_contents($url);
		// 检查返回值
		switch (intval($result)) {
			case -1:
				; // No break!
			case -2:
				; // No break!
			case -3:
				$this->error('Key错误');
				break; 
			case -4:
				$this->error('申请失败');
				break;
			case -5:
				$this->error('申请过于频繁');
				break;
			default:
				if (strpos($result, '@') === false)
					$this->error('申请失败');
		}
		// 申请成功
		$result = explode('@', $result);
		$data['cnzz_id']		= $result[0];
		$data['cnzz_password']	= $result[1];
		model('Xdata')->lput('cnzz', $data);
		$this->assign('jumpUrl', U('admin/Tool/cnzz'));
		$this->success('安装成功');
	}
	
	public function deleteCnzz()
	{
		$data['cnzz_id']		= '';
		$data['cnzz_password']	= '';
		model('Xdata')->lput('cnzz', $data);
		$this->assign('jumpUrl', U('admin/Tool/cnzz'));
		$this->success('删除成功');
	}

	public function doDownload(){
		$filename = $_REQUEST['filename'];
		//下载函数
		require_cache('./addons/libs/Http.class.php');
		$file_path = SITE_PATH.'/data'.'/database' . '/' .$filename;
		// $file_path = UPLOAD_PATH . '/' .$attach['savepath'] . $attach['savename'];
		if(file_exists($file_path)) {
			$filename = iconv("utf-8",'gb2312',$filename);
			Http::download($file_path, $filename);
		}else{
			$this->error("数据不存在！");
		}

	}
}