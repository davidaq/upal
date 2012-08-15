<?php
/**
 * AbstractAddons
 * 抽象接口类
 * @uses AddonsInterface
 * @abstract
 * @package
 * @version $id$
 * @copyright 2001-2013 SamPeng
 * @author SamPeng <penglingjun@zhishisoft.com>
 * @license PHP Version 5.2 {@link www.sampeng.org}
 */
abstract class AbstractAddons implements AddonsInterface{
    protected $version;         //版本号
    protected $author;          //作者
    protected $site;            //网站
    protected $info;            //描述信息
    protected $pluginName;      //插件的中文/英文名字
    protected $path;
    protected $url;


    protected $mid;
    protected $model;
    protected $view;

    public function getUrl(){
        return $this->url;
    }

    public function setUrl($url){
        $this->url = $url;
    }
    /**
     * @return the $path
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * @param field_type $path
     */
    public function setPath($path) {
        $this->path = $path;
    }

    public function __construct(){
        $this->mid = $_SESSION['mid'];
        $this->model = model("AddonData");
        $this->view = Think::instance('View');
        $this->start();
    }

    abstract function getHooksList($name);

    /**
     * 子类不需要进行特殊处理。如果实在是要特殊处理。请在返回数据时候保持父类这些已有的索引
     * @see AddonsInterface::getAddonInfo()
     */
    public function getAddonInfo(){
        $data['version'] = $this->version;
        $data['author']  = $this->author;
        $data['site']    = $this->site;
        $data['info']    = $this->info;
        $data['pluginName']    = $this->pluginName;
        $data['tsVersion']    = $this->tsVersion;
        return $data;
    }


    protected function assign($name,$value='')
    {
        $this->view->assign($name,$value);
    }

    public function fetch($templateFile='',$charset='',$contentType='text/html')
    {
        $templateFile = realpath($this->path.DIRECTORY_SEPARATOR."html".DIRECTORY_SEPARATOR.$templateFile.C('TMPL_TEMPLATE_SUFFIX'));
        return $this->view->fetch($templateFile,$charset,$contentType,false);
    }

    public function display($templateFile='',$charset='',$contentType='text/html')
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
		unset($GLOBALS['ts']['ad']);

        // 提示标题
        $this->assign('msgTitle',$status? L('_OPERATION_SUCCESS_') : L('_OPERATION_FAIL_'));
        $this->assign('status',$status);   // 状态
        $this->assign('message',$message);// 提示信息
        //保证输出不受静态缓存影响
        C('HTML_CACHE_ON',false);
        if($status) { //发送成功信息
            // 成功操作后默认停留1秒
            $this->assign('waitSecond',"1");
            // 默认操作成功自动返回操作前页面
            $this->assign("jumpUrl",$_SERVER["HTTP_REFERER"]);
			echo $this->view->fetch(THEME_PATH.'&success');
		}else{
            //发生错误时候默认停留3秒
            $this->assign('waitSecond',"5");
            // 默认发生错误的话自动返回上页
            $this->assign('jumpUrl',"javascript:history.back(-1);");

			echo $this->view->fetch(THEME_PATH.'&success');
        }
        if(C('LOG_RECORD')) Log::save();
        // 中止执行  避免出错后继续执行
        exit ;
    }
}
