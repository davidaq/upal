<?php
/**
 * hook的抽象类
 * @author sampeng
 *
 */
abstract class Hooks
{
	protected $mid;
	protected $model;
	protected $view;
    protected $path;
    protected $htmlPath;

	public function __construct()
	{
		$this->mid   = $_SESSION ['mid'];
		$this->model = model ( "AddonData" );
		$this->view  = Think::instance ( 'View' );
	}

	/**
	 * 子类不能重写，设置该插件的位置在哪里
	 * @param unknown_type $path
	 */
	final public function setPath($path,$html=false)
    {
        if($html){
		    $this->htmlPath = $path;
        }else{
		    $this->path = $path;
        }
	}

	/**
	 * 同Action的assign方法
	 */
	public function assign($name, $value = '')
	{
		$this->view->assign($name, $value);
	}

	/**
	 * 同Action的Display方法。唯一区别，一定要给文件名。只要给文件名（除了后缀，如：index.html=>index）;
	 */
	protected function fetch($templateFile,$charset='',$contentType='text/html')
	{
		$templateFile = realpath($this->path.DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR.$templateFile.C('TMPL_TEMPLATE_SUFFIX'));
		return $this->view->fetch($templateFile,$charset,$contentType,false);
	}

	protected function display($templateFile, $charset = '', $contentType = 'text/html')
    {
        echo $this->fetch($templateFile,$charset,$contentType);
	}

    /**
     +----------------------------------------------------------
     * 操作错误跳转的快捷方法
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $message 错误信息
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function error($message)
    {
        $this->_dispatch_jump($message ? $message : '操作失败', 0);
    }

    /**
     +----------------------------------------------------------
     * 操作成功跳转的快捷方法
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $message 提示信息
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function success($message)
    {
        $this->_dispatch_jump($message ? $message : '操作成功', 1);
    }

    /**
     +----------------------------------------------------------
     * 默认跳转操作 支持错误导向和正确跳转
     * 调用模板显示 默认为public目录下面的success页面
     * 提示页面为可配置 支持模板标签
     +----------------------------------------------------------
     * @param string $message 提示信息
     * @param Boolean $status 状态
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    private function _dispatch_jump($message,$status=1)
    {
		// 跳转时不展示广告
		global $ts;
		unset($ts['ad']);

        // 提示标题
        $this->assign('msgTitle',$status? L('_OPERATION_SUCCESS_') : L('_OPERATION_FAIL_'));
        $this->assign('status',$status);   // 状态
        $this->assign('message',$message);// 提示信息
        //保证输出不受静态缓存影响
        C('HTML_CACHE_ON',false);
        if($status) { //发送成功信息
            // 成功操作后默认停留1秒
            if(!$this->view->get('waitSecond')) $this->assign('waitSecond',"1");
            // 默认操作成功自动返回操作前页面
            if(!$this->view->get('jumpUrl')) $this->assign("jumpUrl",$_SERVER["HTTP_REFERER"]);
			echo $this->view->fetch(THEME_PATH.'&success');
		}else{
            //发生错误时候默认停留3秒
            if(!$this->view->get('waitSecond')) $this->assign('waitSecond',"5");
            // 默认发生错误的话自动返回上页
            if(!$this->view->get('jumpUrl')) $this->assign('jumpUrl',"javascript:history.back(-1);");

			echo $this->view->fetch(THEME_PATH.'&success');
        }
        if(C('LOG_RECORD')) Log::save();
        // 中止执行  避免出错后继续执行
        exit ;
    }

	/**
	 * 获取该插件目录下面的model模型文件。同D（）函数的作用;
	 */
	protected function model($name, $class = "Model")
	{
		$className = ucfirst($name) . $class;
		require_cache($this->path . DIRECTORY_SEPARATOR . $className . '.class.php');
		return new $className();
	}
}
