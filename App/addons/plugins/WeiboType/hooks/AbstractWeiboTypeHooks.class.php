<?php
/**
 * AbstractWeiboTypeHooks
 * 微博类型的抽象对象，完成公共抽象功能
 * @uses Hooks
 * @package
 * @version $id$
 * @copyright 2001-2013 SamPeng
 * @author SamPeng <penglingjun@zhishisoft.com>
 * @license PHP Version 5.2 {@link www.sampeng.org}
 */
abstract class AbstractWeiboTypeHooks extends Hooks
{
    const EMPTY_TYPE = 0;
    protected static $type = array(
                '1' => '图片',
                '3' => '视频',
                '4' => '音乐'
				);

    private static $hasValid = array();

    public function __construct(){
        Session::pause();
        parent::__construct();
    }

    /**
     * _addWeiboTypeHtml
     * 添加微博类型到微博发布框底下
     * @abstract
     * @access public
     * @return void
     */
    abstract public function _addWeiboTypeHtml();

    /**
     * _weiboTypePublish
     * 微博发布时的处理
     * @param mixed $typeData
     * @abstract
     * @access public
     * @return false|array
     */
    abstract public function _weiboTypePublish($typeData);

    /**
     * _weiboTypeShow
     * 微博不同类型的模板渲染，每个类型自己用模板进行渲染处理
     * @param mixed $typeData
     * @param int $rand
     * @abstract
     * @access public
     * @return void
     */
    abstract public function _weiboTypeShow($typeData,$rand);

    /**
     * 默认为false。可以返回字符串或者数组。返回false意味着该组件永远可用
     */
    protected function getRequireApp(){
        return false;
    }
    /**
     * 如果需要的应用为空或者为false时，则该组件可用
     */
    protected function hasInstallThisApp()
    {
        $config = model('AddonData')->lget('weibo_type');
        if(!in_array($this->typeCode,$config['open'])) return false;

        $requireApp = $this->getRequireApp();
        if(!$requireApp) return true;
        global $ts;
        $install_app = $ts['install_apps'];
        foreach($install_app as $value){
            if( (!is_array($requireApp) && strtolower($value['app_name']) == $requireApp) || (is_array($requireApp) && in_array($value['app_name'],$requireApp))){
                 return true;
            }
        }
        return false;
    }

    public function home_index_weibo_tab($param)
    {
        $tab = &$param[0];
        $position = $param['position'];
        $array = array(1,3,4);
        foreach(self::$type as $key=>$value){
        	if(!$position || ($position=='other' && in_array($key, $array))){
            	$tab[$key]=$value;
        	}
        }
    }

    public function home_index_middle_publish_type($param)
    {
        $position = $param['position'];
        $array = array(1,3,4);
        if($position == 'index' || ($position=='other' && in_array($this->typeCode,$array))){
            if($this->__checkHasInstallApp()){
                self::$hasValid[$this->typeCode] = true;
                $this->_addWeiboTypeHtml();
            }else{
                self::$hasValid[$this->typeCode] = false;
            }
        }
    }

    public function weibo_type($param)
    {
        if($param['typeId'] == $this->typeCode && $this->__checkHasInstallApp()){
            $res = &$param['result'];
            $typeData = $param['typeData'];
            $data = $this->_weiboTypePublish($typeData);
            if($data){
                $res['type'] = $this->typeCode;
                $res['type_data'] = serialize($data);
            }else{
                $res['type'] = self::EMPTY_TYPE;
            }
        }
    }

    public function weibo_type_parse_tpl($param)
    {
        $type     = $param['typeId'];
        $typeData = $param['typeData'];
        $rand     = $param['rand'];
        if($type == $this->typeCode){
            $res = &$param['result'];
            $data = $this->_weiboTypeShow($typeData,$rand);
            if($data){
                $res = $data;
            }else{
                $res = '';
            }
        }
    }

    protected function __checkHasInstallApp(){
        return isset(self::$hasValid[$this->typeCode]) ?self::$hasValid[$this->typeCode]:$this->hasInstallThisApp();
    }
}
