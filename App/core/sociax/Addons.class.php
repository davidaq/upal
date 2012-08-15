<?php
/**
 * 插件调度类。由该对象调度插件的运行逻辑
 * @author sampeng
 *
 */
class Addons
{
    private static $validHooks = array();
    private static $addonsObj  = array();
    private static $hooksObj   = array();

	static public function getValidHooks()
	{
		return self::$validHooks;
	}

	/**
	 * 调用hook并予以执行
	 * 注意。run是高于html的执行的
	 * @param string $name
	 * @param int $mod  self::ADDONS_HTML|self::ADDONS_RUN
	 * @param array $param
	 */
	static public function hook($name, $param = array())
    {
        $hasValid = self::requireHooks($name);
        if(!$hasValid) return false;
        $list = self::$validHooks[$name];
        $dirName = ADDON_PATH . '/plugins';
        $urlDir  = SITE_URL . '/addons/plugins';
        foreach ($list as $key => $value) {
            //检查插件类型
            if(isset(self::$addonsObj[$key])){
                $obj = self::$addonsObj[$key];
            }else{
                $addonPath = $dirName.'/'.$key;
                $addonUrl  = $urlDir.'/'.$key;
                $filename = $addonPath.'/'.$key."Addons.class.php";
                require_once($filename);
                $className = $key.'Addons';
                $obj = new $className();
                $obj->setPath($addonPath);
                $obj->setUrl($addonUrl);
                self::$addonsObj[$key] = $obj;
            }
            $simple = $obj instanceof SimpleAddons;
            foreach($value as $hook){
                if($simple){
                    $obj->$hook($param);
                }else{
                    if(isset(self::$hooksObj[$hook])){
                        self::$hooksObj[$hook]->$name($param) ;
                    }else{
                        $filename = $dirName.'/'.$key.'/'."hooks".'/'.$hook.".class.php";
                        require_cache($filename);
                        $tempObj = new $hook();
                        self::$hooksObj[$hook] = $tempObj;
                        $tempObj->setPath($obj->getPath());
                        $tempObj->setPath($obj->getUrl(),true);
                        $tempObj->$name($param);
                    }

                }
            }
		}
    }

    static public function addonsHook($addonsName,$name,$param=array(),$admin = false){

		if(!$addonsName) throw new ThinkException("您加载的插件不存在！");
        //$addonsName = ucfirst($addonsName)."Hooks";
        //检查插件类型
        $dirName = ADDON_PATH.'/plugins';
        $urlDir  = SITE_URL . '/addons/plugins';
        $path = $dirName.'/'.$addonsName;
        $addonUrl = $urlDir.'/'.$addonsName;
		if(!preg_match('/^[a-zA-Z0-9_]+$/i',$addonsName)){
			throw new ThinkException("您加载的插件不存在！");
		}
        $adminHooks = array();
        if(isset(self::$addonsObj[$addonsName])){
            $obj = self::$addonsObj[$addonsName];
        }else{
            $filename = $path.'/'.$addonsName."Addons.class.php";
            require_once($filename);
            $className = $addonsName.'Addons';
            $obj = new $className();
            $obj->setPath($path);
            $obj->setUrl($addonUrl);
            self::$addonsObj[$addonsName] = $addonsName;
        }
        $simple = $obj instanceof SimpleAddons;
        $adminHooks = $obj->adminMenu();
        if(!$admin && isset($adminHooks[$name])) throw new ThinkException("非法操作，该操作只允许管理员操作");

        if($simple){
            $obj->$name($param);
        }else{
            $list = self::$validHooks[$name];
            foreach($list[$addonsName] as $hooks){
                if(isset(self::$hooksObj[$hooks])){
                    self::$hooksObj[$hooks]->$name($param) ;
                }else{
                    $filename = $dirName.'/'.$addonsName.'/'."hooks".'/'.$hooks.".class.php";
                    require_once($filename);
                    $tempObj = new $hooks();
                    self::$hooksObj[$hooks] = $tempObj;
                    $tempObj->setPath($path);
                    $tempObj->setPath($obj->getUrl(),true);
                    $tempObj->$name($param);
                }
            }
        }
    }


	/**
	 * 加载所有有效的插件
	 */
	static public function loadAllValidAddons()
	{
        //加载所有有效的插件
        self::$validHooks = F('thinksns_addon_list');
        if(false === self::$validHooks){
            self::$validHooks = model('Addons')->resetAddonCache();
        }
	}

	static public function requireHooks($hookname,$addon = null)
    {
        if(empty($addon)){
           return isset(self::$validHooks[$hookname]);
        }else{

        }
    }

	/**
	 * 用于生成插件后台管理页面的URL
	 * @param string $page 管理页面或操作
	 * @param array $param 其他参数
	 */
	static public function adminPage($page, $param = null)
	{
		return U('admin/Addons/admin', array('pluginid'=>intval($_GET['pluginid']), 'page'=>$page) + (array)$param);
	}

	/**
	 * 用于生成插件后台管理的处理URL
	 * @param string $page 管理页面或操作
	 * @param array $param 其他参数
	 */
	static public function adminUrl($page, $param = null)
	{
		return U('admin/Addons/doAdmin', array('pluginid'=>intval($_GET['pluginid']), 'page'=>$page) + (array)$param);
	}
	/**
	 * 实现异步加载的钩子的URL
	 * @param array $param 参数
	 */
	static public function createAddonUrl($name,$hooks,$param = null)
    {
        $param['addon'] = $name;
        $param['hook'] = $hooks;
		return U('home/Widget/addonsRequest', $param);
    }

    /**
     * createAddonShow
     * 为插件的展示页快速创建一个链接
     * @param mixed $name
     * @param mixed $hooks
     * @param mixed $param
     * @static
     * @access public
     * @return void
     */
    static public function createAddonShow($name,$hooks,$param=null)
    {
        $param['addon'] = $name;
        $param['hook'] = $hooks;
		return U('home/public/displayAddons', $param);
    }
}
