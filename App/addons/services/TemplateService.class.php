<?php
class TemplateService extends Service{
    // 模板页面中引入的标签库列表
    protected $tagLib          =  array();
    // 当前模板文件
    protected $templateFile  =  '';
    // 模板变量
    public $tVar                 = array();
    public $config  =  array();
    private   $literal = array();
    private static $nowTags = array();

    /**
       +----------------------------------------------------------
       * 渲染模板输出
       +----------------------------------------------------------
       * @access public
       +----------------------------------------------------------
       * @param string $templateFile 模板文件名
       * @param array $var 模板变量
       * @param string $charset 模板输出字符集
       * @param string $varPrefix 模板变量前缀
       +----------------------------------------------------------
       * @return void
       +----------------------------------------------------------
    */
    public function fetch($templateFile,$var,$charset) {
        if(!$this->checkCache($templateFile)) {
            // 编译并加载模板文件
            $this->load($templateFile,$var,$charset);
        }else{
            // 缓存有效 直接载入模板缓存
            // 模板阵列变量分解成为独立变量
            extract($var, EXTR_OVERWRITE);
            //载入模版缓存文件
            include C('CACHE_PATH').md5($templateFile).C('TMPL_CACHFILE_SUFFIX');
        }
    }

	//运行服务，系统服务自动运行
	public function run() {
	
	}
    /**
       +----------------------------------------------------------
       * 架构函数
       +----------------------------------------------------------
       * @access public
       +----------------------------------------------------------
       * @param array $config 模板引擎配置数组
       +----------------------------------------------------------
    */
    public function __construct(){
        $this->config['cache_path']        =  C('CACHE_PATH');
        $this->config['template_suffix']   =  C('TMPL_TEMPLATE_SUFFIX');
        $this->config['cache_suffix']       =  C('TMPL_CACHFILE_SUFFIX');
        $this->config['tmpl_cache']        =  C('TMPL_CACHE_ON');
        $this->config['cache_time']        =  C('TMPL_CACHE_TIME');
        $this->config['taglib_begin']        =  $this->stripPreg(C('TAGLIB_BEGIN'));
        $this->config['taglib_end']          =  $this->stripPreg(C('TAGLIB_END'));
        $this->config['tmpl_begin']         =  $this->stripPreg(C('TMPL_L_DELIM'));
        $this->config['tmpl_end']           =  $this->stripPreg(C('TMPL_R_DELIM'));
        $this->config['default_tmpl']       =  C('TMPL_FILE_NAME');
        $this->config['tag_level']            =  C('TAG_NESTED_LEVEL');
    }

    private function stripPreg($str) {
        $str = str_replace(array('{','}','(',')','|','[',']'),array('\{','\}','\(','\)','\|','\[','\]'),$str);
        return $str;
    }

    // 模板变量获取和设置
    public function get($name) {
        if(isset($this->tVar[$name]))
            return $this->tVar[$name];
        else
            return false;
    }

    public function set($name,$value) {
        $this->tVar[$name]= $value;
    }

    // 加载模板
    public function load($templateFile,$templateVar,$charset) {
        $this->tVar = $templateVar;
        $templateCacheFile  =  $this->loadTemplate($templateFile);
        // 模板阵列变量分解成为独立变量
        extract($templateVar, EXTR_OVERWRITE);
        //载入模版缓存文件
        include $templateCacheFile;
    }

    /**
       +----------------------------------------------------------
       * 加载主模板并缓存
       +----------------------------------------------------------
       * @access public
       +----------------------------------------------------------
       * @param string $tmplTemplateFile 模板文件
       * @param string $varPrefix  模板变量前缀
       +----------------------------------------------------------
       * @return string
       +----------------------------------------------------------
       * @throws ThinkExecption
       +----------------------------------------------------------
    */
    public function loadTemplate ($tmplTemplateFile='')
    {
        if(empty($tmplTemplateFile))    $tmplTemplateFile = $this->config['default_tmpl'];
        if(!is_file($tmplTemplateFile)){
            $tmplTemplateFile =  dirname($this->config['default_tmpl']).'/'.$tmplTemplateFile.$this->config['template_suffix'];
            if(!is_file($tmplTemplateFile))
                throw_exception(L('_TEMPLATE_NOT_EXIST_'));
        }
        $this->templateFile    =  $tmplTemplateFile;

        //根据模版文件名定位缓存文件
        $tmplCacheFile = $this->config['cache_path'].md5($tmplTemplateFile).$this->config['cache_suffix'];
        $tmplContent = '';
        // 检查Cache文件是否需要更新
        if (!$this->checkCache($tmplTemplateFile)) {
            // 需要更新模版 读出原模板内容
            $tmplContent = file_get_contents($tmplTemplateFile);
            //编译模板内容
            $tmplContent = $this->compiler($tmplContent);
            // 检测分组目录
            if(!is_dir($this->config['cache_path']))
                mk_dir($this->config['cache_path']);
            //重写Cache文件
            if( false === file_put_contents($tmplCacheFile,trim($tmplContent)))
                throw_exception(L('_CACHE_WRITE_ERROR_'));
        }
        return $tmplCacheFile;
    }

    /**
       +----------------------------------------------------------
       * 编译模板文件内容
       +----------------------------------------------------------
       * @access protected
       +----------------------------------------------------------
       * @param mixed $tmplContent 模板内容
       +----------------------------------------------------------
       * @return string
       +----------------------------------------------------------
    */
    protected function compiler( $tmplContent)
    {
        //模板解析
        $tmplContent = $this->parse($tmplContent);
        if(ini_get('short_open_tag'))
            // 开启短标签的情况要将<?标签用echo方式输出 否则无法正常输出xml标识
            $tmplContent = preg_replace('/(<\?(?!php|=|$))/i', '<?php echo \'\\1\'; ?>'."\n", $tmplContent );
        // 添加安全代码
        $tmplContent  =  '<?php if (!defined(\'THINK_PATH\')) exit();?>'.$tmplContent;
        if(C('TMPL_STRIP_SPACE')) {
            /* 去除html空格与换行 */
            $find     = array("~>\s+<~","~>(\s+\n|\r)~");
            $replace  = array("><",">");
            $tmplContent = preg_replace($find, $replace, $tmplContent);
        }
        return trim($tmplContent);
    }

    /**
       +----------------------------------------------------------
       * 检查缓存文件是否有效
       * 如果无效则需要重新更新
       +----------------------------------------------------------
       * @access public
       +----------------------------------------------------------
       * @param string $tmplTemplateFile  模板文件名
       +----------------------------------------------------------
       * @return boolen
       +----------------------------------------------------------
    */
    protected function checkCache($tmplTemplateFile)
    {
        if (!$this->config['tmpl_cache']) // 优先对配置检测
            return false;
        $tmplCacheFile = $this->config['cache_path'].md5($tmplTemplateFile).$this->config['cache_suffix'];
        if(!is_file($tmplCacheFile)){
            return false;
        }elseif (filemtime($tmplTemplateFile) > filemtime($tmplCacheFile)) {
            // 模板文件如果有更新则缓存需要更新
            return false;
        }elseif ($this->config['cache_time'] != -1 && time() > filemtime($tmplCacheFile)+$this->config['cache_time']) {
            // 缓存是否在有效期
            return false;
        }
        //缓存有效
        return true;
    }

    /**
       +----------------------------------------------------------
       * 模板解析入口
       * 支持普通标签和TagLib解析 支持自定义标签库
       +----------------------------------------------------------
       * @access public
       +----------------------------------------------------------
       * @param string $content 要解析的模板内容
       +----------------------------------------------------------
       * @return string
       +----------------------------------------------------------
    */
    public function parse($content)
    {
        // 内置标签库
        $tagLibs =  explode(',',C('TAGLIB_BUILD_IN'));
        foreach ($tagLibs as $tag){
            $this->parseTagLib($tag,$content,true);
        }
        //解析普通模板标签 {tagName:}
        $content = preg_replace('/('.$this->config['tmpl_begin'].')(\S.+?)('.$this->config['tmpl_end'].')/eis',"\$this->parseTag('\\2')",$content);
        return $content;
    }


    /**
       +----------------------------------------------------------
       * 搜索模板页面中包含的TagLib库
       * 并返回列表
       +----------------------------------------------------------
       * @access public
       +----------------------------------------------------------
       * @param string $content  模板内容
       +----------------------------------------------------------
       * @return string|false
       +----------------------------------------------------------
    */
    public function getIncludeTagLib(& $content)
    {
        //搜索是否有TagLib标签
        $find = preg_match('/'.$this->config['taglib_begin'].'taglib\s(.+?)(\s*?)\/'.$this->config['taglib_end'].'\W/is',$content,$matches);
        if($find) {
            //替换TagLib标签
            $content = str_replace($matches[0],'',$content);
            //解析TagLib标签
            $tagLibs = $matches[1];
            $xml =  '<tpl><tag '.$tagLibs.' /></tpl>';
            $xml = simplexml_load_string($xml);
            if(!$xml)
                throw_exception(L('_XML_TAG_ERROR_'));
            $xml = (array)($xml->tag->attributes());
            $array = array_change_key_case($xml['@attributes']);
            $this->tagLib = explode(',',$array['name']);
        }
        return;
    }

    /**
       +----------------------------------------------------------
       * TagLib库解析
       +----------------------------------------------------------
       * @access public
       +----------------------------------------------------------
       * @param string $tagLib 要解析的标签库
       * @param string $content 要解析的模板内容
       * @param boolen $hide 是否隐藏标签库前缀
       +----------------------------------------------------------
       * @return string
       +----------------------------------------------------------
    */
    public function parseTagLib($tagLib,&$content,$hide=false)
    {
        $begin = $this->config['taglib_begin'];
        $end   = $this->config['taglib_end'];
        $fileName = SITE_PATH.'/addons/plugins/Tags/TPTag.class.php';
  		require_cache($fileName);
        $tLib =  new TPTag($tagLib);
        if($tLib->valid()) {
            //如果标签库有效则取出支持标签列表
            $tagList =  $tLib->getTagList();
            //遍历标签列表进行模板标签解析
            foreach($tagList as $tag) {
            	self::$nowTags = $tag;
                // 实际要解析的标签名称
                $startTag = $tag['name'];
                // 检查可嵌套标签以及嵌套级别
                if($tag['nested'] && $this->config['tag_level']>1)
                    $level   =   $this->config['tag_level'];
                else 
                    $level   =   1;
                $endTag = $startTag;
                if(false !== stripos($content,C('TAGLIB_BEGIN').$startTag)) {
                	
                    if(empty($tag['attribute'])){
                        // 无属性标签
                        if($tag['content'] !='empty'){
                            for($i=0;$i<$level;$i++)
                                $content = preg_replace('/'.$begin.$startTag.'(\s*?)'.$end.'(.*?)'.$begin.'\/'.$endTag.'(\s*?)'.$end.'/eis',"\$this->parseXmlTag('".$tagLib."','".$tag['name']."','\\1','\\2')",$content);
                        }else{
                            $content = preg_replace('/'.$begin.$startTag.'(\s*?)\/(\s*?)'.$end.'/eis',"\$this->parseXmlTag('".$tagLib."','".$tag['name']."','\\1','')",$content);
                        }
                    }elseif($tag['content'] !='empty') {//闭合标签解析
                        for($i=0;$i<$level;$i++)
                            $content = preg_replace('/'.$begin.$startTag.'\s(.*?)'.$end.'(.+?)'.$begin.'\/'.$endTag.'(\s*?)'.$end.'/eis',"\$this->parseXmlTag('".$tagLib."','".$tag['name']."','\\1','\\2')",$content);
                    }else {//开放标签解析
                        // 开始标签必须有一个空格
                        $content = preg_replace('/'.$begin.$startTag.'\s(.*?)\/(\s*?)'.$end.'/eis',"\$this->parseXmlTag('".$tagLib."','".$tag['name']."','\\1','')",$content);
                    }
                }
                self::$nowTags = array();
            }
        }
    }

    /**
       +----------------------------------------------------------
       * 解析标签库的标签
       * 需要调用对应的标签库文件解析类
       +----------------------------------------------------------
       * @access public
       +----------------------------------------------------------
       * @param string $tagLib  标签库名称
       * @param string $tag  标签名
       * @param string $attr  标签属性
       * @param string $content  标签内容
       +----------------------------------------------------------
       * @return string|false
       +----------------------------------------------------------
    */
    public function parseXmlTag($tagLib,$tag,$attr,$content)
    {
        $attr = stripslashes($attr);
        $content = stripslashes($content);
        if(ini_get('magic_quotes_sybase'))
            $attr =  str_replace('\"','\'',$attr);
           if(!empty(self::$nowTags['alias'])){
           	           	        $tLib = get_instance_of('Tag'.ucwords(strtolower(self::$nowTags['alias'])));
           	           	        $tLib->setOther($tag);
           }else{
           	           	        $tLib = get_instance_of('Tag'.ucwords(strtolower($tag)));
           	
           }
        return $tLib->parse($attr,$content);
    }

    /**
       +----------------------------------------------------------
       * 模板标签解析
       * 格式： {TagName:args [|content] }
       +----------------------------------------------------------
       * @access public
       +----------------------------------------------------------
       * @param string $tagStr 标签内容
       +----------------------------------------------------------
       * @return string
       +----------------------------------------------------------
    */
    public function parseTag($tagStr){
        //if (MAGIC_QUOTES_GPC) {
            $tagStr = stripslashes($tagStr);
        //}
        //还原非模板标签
        if(preg_match('/^[\s|\d]/is',$tagStr))
            //过滤空格和数字打头的标签
            return C('TMPL_L_DELIM') . $tagStr .C('TMPL_R_DELIM');
        $flag =  substr($tagStr,0,1);
        $name   = substr($tagStr,1);
        if('$' == $flag){
            //解析模板变量 格式 {$varName}
            return $this->parseVar($name);
        }elseif(':' == $flag){
            // 输出某个函数的结果
            return  '<?php echo '.$name.';?>';
        }elseif('~' == $flag){
            // 执行某个函数
            return  '<?php '.$name.';?>';
        }elseif('&' == $flag){
            // 输出配置参数
            return '<?php echo C("'.$name.'");?>';
        }elseif('%' == $flag){
            // 输出语言变量
            return '<?php echo L("'.$name.'");?>';
		}elseif('@' == $flag){
			// 输出SESSION变量
            if(strpos($name,'.')) {
                $array   =  explode('.',$name);
	    		return '<?php echo $_SESSION["'.$array[0].'"]["'.$array[1].'"];?>';
            }else{
    			return '<?php echo $_SESSION["'.$name.'"];?>';
            }
		}elseif('#' == $flag){
			// 输出COOKIE变量
            if(strpos($name,'.')) {
                $array   =  explode('.',$name);
	    		return '<?php echo $_COOKIE["'.$array[0].'"]["'.$array[1].'"];?>';
            }else{
    			return '<?php echo $_COOKIE["'.$name.'"];?>';
            }
		}elseif('.' == $flag){
            // 输出GET变量
            return '<?php echo $_GET["'.$name.'"];?>';
        }elseif('^' == $flag){
            // 输出POST变量
            return '<?php echo $_POST["'.$name.'"];?>';
        }elseif('*' == $flag){
            // 输出常量
            return '<?php echo constant("'.$name.'");?>';
        }

        $tagStr = trim($tagStr);
        if(substr($tagStr,0,2)=='//' || (substr($tagStr,0,2)=='/*' && substr($tagStr,-2)=='*/'))
            //注释标签
            return '';

        //解析其它标签
        //统一标签格式 {TagName:args [|content]}
        $pos =  strpos($tagStr,':');
        $tag  =  substr($tagStr,0,$pos);
        $args = trim(substr($tagStr,$pos+1));

        //解析标签内容
        if(!empty($args)) {
            $tag  =  strtolower($tag);
            switch($tag){
                case 'include':
                    return $this->parseInclude($args);
                    break;
                case 'load':
                    return $this->parseLoad($args);
                    break;
                //这里扩展其它标签
                //…………
                default:
                    if(C('TAG_EXTEND_PARSE')) {
                        $method = C('TAG_EXTEND_PARSE');
                        if(array_key_exists($tag,$method))
                            return $method[$tag]($args);
                    }
            }
        }
        return C('TMPL_L_DELIM') . $tagStr .C('TMPL_R_DELIM');
    }

    /**
       +----------------------------------------------------------
       * 模板变量解析,支持使用函数
       * 格式： {$varname|function1|function2=arg1,arg2}
       +----------------------------------------------------------
       * @access public
       +----------------------------------------------------------
       * @param string $varStr 变量数据
       +----------------------------------------------------------
       * @return string
       +----------------------------------------------------------
    */
    public function parseVar($varStr){
        $varStr = trim($varStr);
        static $_varParseList = array();
        //如果已经解析过该变量字串，则直接返回变量值
        if(isset($_varParseList[$varStr])) return $_varParseList[$varStr];
        $parseStr ='';
        $varExists = true;
        if(!empty($varStr)){
            $varArray = explode('|',$varStr);
            //取得变量名称
            $var = array_shift($varArray);
            //非法变量过滤 不允许在变量里面使用 ->
            if(preg_match('/->/is',$var))
                return '';
            if( false !== strpos($var,'.')) {
                //支持 {$var.property}
                $vars = explode('.',$var);
                $var  =  array_shift($vars);
                $name = '$'.$var;
                foreach ($vars as $key=>$val)
                    $name .= '["'.$val.'"]';
            }elseif(false !== strpos($var,'[')) {
                //支持 {$var['key']} 方式输出数组
                $name = "$".$var;
                preg_match('/(.+?)\[(.+?)\]/is',$var,$match);
                $var = $match[1];
            }
            else {
                $name = "$$var";
            }
            
            //对变量使用函数
            if(count($varArray)>0)
                $name = $this->parseVarFunction($name,$varArray);
            $parseStr = '<?php echo ('.$name.'); ?>';
        }
        $_varParseList[$varStr] = $parseStr;
        return $parseStr;
    }

    /**
       +----------------------------------------------------------
       * 对模板变量使用函数
       * 格式 {$varname|function1|function2=arg1,arg2}
       +----------------------------------------------------------
       * @access public
       +----------------------------------------------------------
       * @param string $name 变量名
       * @param array $varArray  函数列表
       +----------------------------------------------------------
       * @return string
       +----------------------------------------------------------
    */
    public function parseVarFunction($name,$varArray){
        //对变量使用函数
        $length = count($varArray);
        //取得模板禁止使用函数列表
        $template_deny_funs = explode(',',C('TMPL_DENY_FUNC_LIST'));
        for($i=0;$i<$length ;$i++ ){
            if (0===stripos($varArray[$i],'default='))
                $args = explode('=',$varArray[$i],2);
            else
                $args = explode('=',$varArray[$i]);
            //模板函数过滤
            $args[0] = trim($args[0]);
            switch(strtolower($args[0])) {
                case 'default':  // 特殊模板函数
                    $name   = '('.$name.')?('.$name.'):'.$args[1];
                    break;
                default:  // 通用模板函数
                    if(!in_array($args[0],$template_deny_funs)){
                        if(isset($args[1])){
                            if(strstr($args[1],'###')){
                                $args[1] = str_replace('###',$name,$args[1]);
                                $name = "$args[0]($args[1])";
                            }else{
                                $name = "$args[0]($name,$args[1])";
                            }
                        }else if(!empty($args[0])){
                            $name = "$args[0]($name)";
                        }
                    }
            }
        }
        return $name;
    }

    /**
       +----------------------------------------------------------
       * 加载公共模板并缓存 和当前模板在同一路径，否则使用相对路径
       +----------------------------------------------------------
       * @access public
       +----------------------------------------------------------
       * @param string $tmplPublicName  公共模板文件名
       +----------------------------------------------------------
       * @return string
       +----------------------------------------------------------
    */
    public function parseInclude($tmplPublicName){
		//thinksns修改: 2009-5-28 可以导入风格模板目录下的模板文件，写的比较死，以后再优化
		if(substr($tmplPublicName,0,9)=='__THEME__'){
			$tmplTemplateFile	=	THEME_PATH.'/'.substr($tmplPublicName,10).'.html';
			$parseStr = file_get_contents($tmplTemplateFile);
			return $this->parse($parseStr);
		}
		//thinksns修改:结束

        if(substr($tmplPublicName,0,1)=='$')
            //支持加载变量文件名
            $tmplPublicName = $this->get(substr($tmplPublicName,1));

        if(is_file($tmplPublicName)) {
            // 直接包含文件
            $parseStr = file_get_contents($tmplPublicName);
        }else {
            $tmplPublicName = trim($tmplPublicName);
            if(strpos($tmplPublicName,'@')){
                /* // 引入其它模块的操作模板 */
                $tmplTemplateFile   =   dirname(dirname(dirname($this->templateFile))).'/'.str_replace(array('@',':'),'/',$tmplPublicName);
            }elseif(strpos($tmplPublicName,':')){
                // 引入其它模块的操作模板
                $tmplTemplateFile   =   dirname(dirname($this->templateFile)).'/'.str_replace(':','/',$tmplPublicName);
            }else{
                // 默认导入当前模块下面的模板
                $tmplTemplateFile = dirname($this->templateFile).'/'.$tmplPublicName;
            }
            $tmplTemplateFile .=  $this->config['template_suffix'];
            $parseStr = file_get_contents($tmplTemplateFile);
        }
        //再次对包含文件进行模板分析
        return $this->parse($parseStr);
    }
}
