<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$

/**
 +------------------------------------------------------------------------------
 * ThinkPHP Action控制器基类 抽象类
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Core
 * @author   liu21st <liu21st@gmail.com>
 * @version  $Id$
 +------------------------------------------------------------------------------
 */
abstract class Action extends Think
{//类定义开始

    // 视图实例对象
    protected	$view   =  null;

    // 当前Action名称
    private		$name =  '';

	protected	$site;
	protected	$user;
	protected	$app;
	protected	$mid;
	protected	$uid;
	protected $ajaxData;

   /**
     +----------------------------------------------------------
     * 架构函数 取得模板对象实例
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __construct()
    {
    	unset($_GET['app']);
    	unset($_REQUEST['app']);
        //实例化视图类
        $this->view	= Think::instance('View');

		$this->initSite();
		$this->initApp();
		$this->initUser();
		$this->initUserApp();
		$this->initAd();
		$this->initFooterDocument();
		//$this->initUcenter();

		//控制器初始化
        if(method_exists($this,'_initialize'))
            $this->_initialize();
    }

   /**
     +----------------------------------------------------------
     * 初始化站点配置信息
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     */
	protected function initSite() {
		$this->site	= $GLOBALS['ts']['site'];

		// cnzz
		getCnzz();
	}

	protected function api($name) {
	     return api($name);
	}

   /**
     +----------------------------------------------------------
     * 初始化当前应用信息
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     */
	protected function initApp()
	{
		if (!in_array(APP_NAME, C('DEFAULT_APPS')))
			$this->app = model('App')->getAppDetailByName(APP_NAME);
        else
			$this->app = array('appid'=>0, 'appname'=>APP_NAME);

		global $ts;
		$ts['app'] = $this->app;
		$this->assign('app', $this->app);
	}

   /**
     +----------------------------------------------------------
     * 初始化当前登录用户信息
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     */
	protected function initUser()
	{
		$this->mid = intval($_SESSION['mid']);
		$this->uid = intval($_REQUEST['uid']);

		if ($this->uid == 0)
			$this->uid = $this->mid;

		// 获取userInfo数据
		if ($this->mid > 0 &&
            (empty($_SESSION['userInfo']) || !$_SESSION['userInfo']['is_init'] || !$_SESSION['userInfo']['is_active'])) {
			$_SESSION['userInfo'] = D('User', 'home')->getUserByIdentifier($this->mid);
		} else {
			if(empty($_SESSION['userInfo'])){
				return true;
			}
			D('User', 'home')->setUserObjectCache(array($_SESSION['userInfo']));
		}

		//缓存登录用户个人资料
		$userInfo = D('User', 'home')->getUserInfoCache($this->mid);
		$this->assign('userInfoCache', $userInfo);
		//消息
		$this->assign('userCount', X('Notify')->getCount($this->mid));
		// 赋值当前用户
		$this->user	= $_SESSION['userInfo'];
		global $ts;
		$ts['user']	= $this->user;
		$this->assign('mid',$this->mid);
		$this->assign('uid',$this->uid);
		$this->assign('user',$this->user);

		//add By lenghaoran 2011-01-16 判断用户是否为管理员
		$isSystemAdmin = service('SystemPopedom')->hasPopedom($this->mid, 'admin/*/*', false);
		if($isSystemAdmin)
			$ts['isSystemAdmin'] = true;
		$this->assign('isSystemAdmin' , $isSystemAdmin );

		// 检查是否用户初始化
		if (!$ts['user']['is_init'] && !canAccess()) {
			$this->assign('jumpUrl', U('home/Public/userinfo'));
			$this->error('请先完善个人资料');
			exit;
		}
	}

	protected function initUserApp() {
        if ($this->mid <= 0 ) {
            return ;
        }
		global $ts;

		// 用户的应用 = 默认应用 + 用户安装的可选应用
		$default_app = model('App')->getSiteDefaultApp();
		$installed   = model('App')->getUserInstalledApp($this->mid);
		$res = array_merge($default_app, $installed);

		//SamPeng增加，得到我的团队列表
        $ts['my_group_list'] = $this->__getMyHasGroupWeibo($res);

        $this->assign('my_group_list',$ts['my_group_list']);
		$has_installed = false;
		$user_app	   = array();
		foreach ($res as $k => &$v) {
			if ($v['app_name'] == APP_NAME)
				$has_installed = true;

			$v['app_entry']	    = U($v['app_name'].'/'.$v['app_entry']);
			$v['admin_entry']   = $v['admin_entry']   ? U($v['app_name'].'/'.$v['admin_entry'])   : '';
			$v['sidebar_entry'] = $v['sidebar_entry'] ? U($v['app_name'].'/'.$v['sidebar_entry']) : '';
            $v['icon_url']       = getAppIconUrl($v['icon_url'],$v['app_name']);
            $v['icon_large_url'] = getAppIconUrl($v['icon_large_url'],$v['app_name']);
			if ($v['status']==1) { // 站点默认应用
				$user_app['local_default_app'][] = $v;
			}else { // 用户安装的应用
				$v['display_order'] = $v['user_display_order']; // 顺序号: 用户自定义的顺序号
				unset($v['user_display_order']);
				$user_app['local_app'][] = $v;
			}
		}
		$ts['install_apps'] = $res;


		// 检查是否为默认应用
		if (in_array(APP_NAME, C('DEFAULT_APPS')))
			$has_installed = true;
		// 检查是否为管理后台
		if (!$has_installed && model('App')->isAppAdmin(APP_NAME, $ts['_mod']))
			$has_installed = true;
		if (!$has_installed) {
			$this->assign('jumpUrl', U('home/Index/install', array('app_name' => APP_NAME, 'app_id' => 0)));
			$this->error('您尚未添加应用，请先添加');
		}

		// 漫游应用
		if ($ts['site']['my_status']) {
			$default_app = model('Myop')->getAllDefaultApp();
			$myopapp     = model('Myop')->getAllInstalledByUser($this->mid);

			$ids = array();
			foreach ($myopapp as $v) {
				if (in_array($v['appid'], $ids))
					continue ;
				$ids[] = $v['appid'];
				$user_app['myop_app'][] = array('app_id'		=> $v['appid'],
												'app_alias'		=> $v['appname'],
												'display_order' => $v['displayorder']);
			}
			foreach ($default_app as $v) {
				if (in_array($v['appid'], $ids))
					continue ;
				$ids[] = $v['appid'];
				$user_app['myop_default_app'][] = array('app_id'		=> $v['appid'],
														'app_alias'		=> $v['appname'],
														'display_order' => $v['displayorder']);
			}
		}
		$ts['user_app'] = $user_app;
	}


	protected function __hasApps($apps,$target)
	{
	    foreach($apps as $value){
	        if($value['app_name'] == $target){
	            return true;
	        }
	    }
	    return false;
	}

	protected function __getMyHasGroupWeibo($apps)
	{
	    if($this->__hasApps($apps, 'group')){
			//if(!$cache = S('Cache_MyGroup_'.$this->mid)){
				$cache = D('Group','group')->getAllMyGroup($this->mid,0,array(),6);
			//	S('Cache_MyGroup_'.$this->mid,$cache,120);
			//}
	        return $cache;
	    }
	    return false;
	}

	protected function initAd() {
		// 用户未登录时不展示广告
		if ($this->mid <= 0)
            return ;

		global $ts;
		if (($ts['ad'] = F('_action_ad')) === false) {
			$place_array = array('middle','header','left','right','footer','right_top');
			$sql = 'SELECT `content`,`place` FROM ' . C('DB_PREFIX') . 'ad WHERE `is_active` = "1" AND `content` <> "" ORDER BY `display_order` ASC,`ad_id` ASC';
			$ads = M('')->query($sql);
			foreach($ads as $k => $v) {
                $content = unserialize($v['content']);
                if (is_array($content)) {
                    $this->assign('switch_ad_id', 'ad_' . $k);
                    $this->assign('switch_ad_content', $content);
                    $v['content'] = $this->fetch(THEME_PATH.'&switchAd');
                } else {
                    $v['content'] = htmlspecialchars_decode($v['content']);
                }
				$ts['ad'][$place_array[$v['place']]][] = $v;
			}
			F('_action_ad', $ts['ad'] ? $ts['ad'] : array());
		}
	}

	protected function initFooterDocument() {
		global $ts;
		if (($ts['footer_document'] = F('_action_footer_document')) === false) {
			$sql = 'SELECT `document_id`,`title`,`content` FROM ' . C('DB_PREFIX') . 'document WHERE `is_active` = "1" AND `is_on_footer` = "1" ORDER BY `display_order` ASC,`document_id` ASC';
			$ts['footer_document'] = M('')->query($sql);
			foreach($ts['footer_document'] as $k => $v) {
				if ( mb_substr($v['content'],0,6,'UTF8') == 'ftp://' ||
					 mb_substr($v['content'],0,7,'UTF8') == 'http://' ||
					 mb_substr($v['content'],0,8,'UTF8') == 'https://' ||
					 mb_substr($v['content'],0,9,'UTF8') == 'mailto://' ) {
					$ts['footer_document'][$k]['url'] = $v['content'];
				}

				unset($ts['footer_document'][$k]['content']);
			}
			F('_action_footer_document', $ts['footer_document'] ? $ts['footer_document'] : array());
		}
	}

	protected function initUcenter()
	{

		// 获取UCenter的应用列表

		$filename = SITE_PATH . '/api/uc_client/uc_sync.php';

		if (file_exists($filename)) {

			require_once $filename;
			if (UC_SYNC) {
				unset($_ENV['app']);
				global $ts;
				$ts['ucenter']['app'] 			= uc_app_ls();
				$ts['ucenter']['current_appid'] = UC_APPID;
			}
		}
	}

   /**
     +----------------------------------------------------------
     * 获取当前Action名称
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     */
    protected function getActionName() {
        if(empty($this->name)) {
            // 获取Action名称
            $this->name     =   substr(get_class($this),0,-6);
        }
        return $this->name;
    }

    /**
     +----------------------------------------------------------
     * 是否AJAX请求
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @return bool
     +----------------------------------------------------------
     */
    protected function isAjax() {
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) ) {
            if('xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH']))
                return true;
        }
        if(!empty($_POST[C('VAR_AJAX_SUBMIT')]) || !empty($_GET[C('VAR_AJAX_SUBMIT')]))
            // 判断Ajax方式提交
            return true;
        return false;
    }

    /**
     +----------------------------------------------------------
     * 模板Title，keywords等赋值
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $input 要赋值的变量
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    public function setTitle($title='',$keywords='',$description='')
    {
    	global $ts;
    	if($title)
            $ts['site']['page_title'] = $title;
        if($keywords)
            $ts['site']['site_header_keywords'] = $keywords;
        if($description)
            $ts['site']['site_header_description'] = $description;
	}

    /**
     +----------------------------------------------------------
     * 模板显示
     * 调用内置的模板引擎显示方法，
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $templateFile 指定要调用的模板文件
     * 默认为空 由系统自动定位模板文件
     * @param string $charset 输出编码
     * @param string $contentType 输出类型
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function display($templateFile='',$charset='',$contentType='text/html')
    {
        $this->view->display($templateFile,$charset,$contentType);
    }

    /**
     +----------------------------------------------------------
     *  获取输出页面内容
     * 调用内置的模板引擎fetch方法，
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $templateFile 指定要调用的模板文件
     * 默认为空 由系统自动定位模板文件
     * @param string $charset 输出编码
     * @param string $contentType 输出类型
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function fetch($templateFile='',$charset='',$contentType='text/html')
    {
        return $this->view->fetch($templateFile,$charset,$contentType);
    }

    /**
     +----------------------------------------------------------
     *  创建静态页面
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @htmlfile 生成的静态文件名称
     * @htmlpath 生成的静态文件路径
     * @param string $templateFile 指定要调用的模板文件
     * 默认为空 由系统自动定位模板文件
     * @param string $charset 输出编码
     * @param string $contentType 输出类型
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function buildHtml($htmlfile='',$htmlpath='',$templateFile='',$charset='',$contentType='text/html') {
        return $this->view->buildHtml($htmlfile,$htmlpath,$templateFile,$charset,$contentType);
    }

    /**
     +----------------------------------------------------------
     * 模板变量赋值
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $name 要显示的模板变量
     * @param mixed $value 变量的值
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    public function assign($name,$value='')
    {
        $this->view->assign($name,$value);
    }

    public function __set($name,$value) {
        $this->view->assign($name,$value);
    }

    /**
     +----------------------------------------------------------
     * 取得模板显示变量的值
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $name 模板显示变量
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    protected function get($name)
    {
        return $this->view->get($name);
    }

    /**
     +----------------------------------------------------------
     * Trace变量赋值
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $name 要显示的模板变量
     * @param mixed $value 变量的值
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function trace($name,$value='')
    {
        $this->view->trace($name,$value);
    }

    /**
     +----------------------------------------------------------
     * 魔术方法 有不存在的操作的时候执行
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $method 方法名
     * @param array $parms 参数
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function __call($method,$parms) {
        if( 0 === strcasecmp($method,ACTION_NAME)) {
            // 检查扩展操作方法
            $_action = C('_actions_');
            if($_action) {
                // 'module:action'=>'callback'
                if(isset($_action[MODULE_NAME.':'.ACTION_NAME])) {
                    $action  =  $_action[MODULE_NAME.':'.ACTION_NAME];
                }elseif(isset($_action[ACTION_NAME])){
                    // 'action'=>'callback'
                    $action  =  $_action[ACTION_NAME];
                }
                if(!empty($action)) {
                    call_user_func($action);
                    return ;
                }
            }
            // 如果定义了_empty操作 则调用
            if(method_exists($this,'_empty')) {
                $this->_empty($method,$parms);
            }else {
                // 检查是否存在默认模版 如果有直接输出模版
                if(file_exists_case(C('TMPL_FILE_NAME')))
                    $this->display();
                else
                    // 抛出异常
                    throw_exception(L('_ERROR_ACTION_').ACTION_NAME);
            }
        }elseif(in_array(strtolower($method),array('ispost','isget','ishead','isdelete','isput'))){
            return strtolower($_SERVER['REQUEST_METHOD']) == strtolower(substr($method,2));
        }else{
            throw_exception(__CLASS__.':'.$method.L('_METHOD_NOT_EXIST_'));
        }
    }

    /**
     +----------------------------------------------------------
     * 操作错误跳转的快捷方法
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $message 错误信息
     * @param Boolean $ajax 是否为Ajax方式
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function error($message,$ajax=false)
    {
        $this->_dispatch_jump($message,0,$ajax);
    }

    /**
     +----------------------------------------------------------
     * 操作成功跳转的快捷方法
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $message 提示信息
     * @param Boolean $ajax 是否为Ajax方式
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function success($message,$ajax=false)
    {
        $this->_dispatch_jump($message,1,$ajax);
    }

    /**
     +----------------------------------------------------------
     * Ajax方式返回数据到客户端
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $data 要返回的数据
     * @param String $info 提示信息
     * @param boolean $status 返回状态
     * @param String $status ajax返回类型 JSON XML
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function ajaxReturn($data,$info='',$status=1,$type='')
    {

    	if(empty($data) && !empty($this->ajaxData)) $data= $this->ajaxData;
        // 保证AJAX返回后也能保存日志
        if(C('LOG_RECORD')) Log::save();
        $result  =  array();
        $result['status']  =  $status;
        $result['info'] =  $info;
        $result['data'] = $data;
        if(empty($type)) $type  =   C('DEFAULT_AJAX_RETURN');
        if(strtoupper($type)=='JSON') {
            // 返回JSON数据格式到客户端 包含状态信息
            header("Content-Type:text/html; charset=utf-8");
            exit(json_encode($result));
        }elseif(strtoupper($type)=='XML'){
            // 返回xml格式数据
            header("Content-Type:text/xml; charset=utf-8");
            exit(xml_encode($result));
        }elseif(strtoupper($type)=='EVAL'){
            // 返回可执行的js脚本
            header("Content-Type:text/html; charset=utf-8");
            exit($data);
        }else{
            // TODO 增加其它格式
        }
    }

    /**
     +----------------------------------------------------------
     * Action跳转(URL重定向） 支持指定模块和延时跳转
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $url 跳转的URL表达式
     * @param array $params 其它URL参数
     * @param integer $delay 延时跳转的时间 单位为秒
     * @param string $msg 跳转提示信息
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function redirect($url,$params=array(),$delay=0,$msg='') {
        if(C('LOG_RECORD')) Log::save();
        $url    =   U($url,$params);
        redirect($url,$delay,$msg);
    }

    /**
     +----------------------------------------------------------
     * 默认跳转操作 支持错误导向和正确跳转
     * 调用模板显示 默认为public目录下面的success页面
     * 提示页面为可配置 支持模板标签
     +----------------------------------------------------------
     * @param string $message 提示信息
     * @param Boolean $status 状态
     * @param Boolean $ajax 是否为Ajax方式
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    private function _dispatch_jump($message,$status=1,$ajax=false)
    {
		// 跳转时不展示广告
		global $ts;
		unset($ts['ad']);

        // 判断是否为AJAX返回
        if($ajax || $this->isAjax()) $this->ajaxReturn('',$message,$status);
        // 提示标题
        $this->assign('msgTitle',$status? L('_OPERATION_SUCCESS_') : L('_OPERATION_FAIL_'));
        //如果设置了关闭窗口，则提示完毕后自动关闭窗口
        if($this->get('closeWin'))    $this->assign('jumpUrl','javascript:window.close();');
        $this->assign('status',$status);   // 状态
        $this->assign('message',$message);// 提示信息
        //保证输出不受静态缓存影响
        C('HTML_CACHE_ON',false);
        if($status) { //发送成功信息
            // 成功操作后默认停留1秒
            if(!$this->get('waitSecond'))    $this->assign('waitSecond',"1");
            // 默认操作成功自动返回操作前页面
            if(!$this->get('jumpUrl')) $this->assign("jumpUrl",$_SERVER["HTTP_REFERER"]);
            //sociax:2010-1-21
			//$this->display(C('TMPL_ACTION_SUCCESS'));
			$this->display(THEME_PATH.'&success');
		}else{
            //发生错误时候默认停留3秒
            if(!$this->get('waitSecond'))    $this->assign('waitSecond',"5");
            // 默认发生错误的话自动返回上页
            if(!$this->get('jumpUrl')) $this->assign('jumpUrl',"javascript:history.back(-1);");
			//sociax:2010-1-21
            //$this->display(C('TMPL_ACTION_ERROR'));

			$this->display(THEME_PATH.'&success');
        }
        if(C('LOG_RECORD')) Log::save();
        // 中止执行  避免出错后继续执行
        exit ;
    }

}//类定义结束
?>