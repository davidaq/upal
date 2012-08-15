<?php
class TagService extends Service {

	//所有设置的值
	protected $scws	=	array();
	protected $text	=	'';
	protected $dict	=	'./addons/libs/scws/etc/dict.utf8.xdb';
	protected $rule	=	'./addons/libs/scws/etc/rules.utf8.ini';
	
	/**
     +----------------------------------------------------------
     * 架构函数
     +----------------------------------------------------------
     * @author melec制作
     * @access public
     +----------------------------------------------------------
     */
    public function __construct($text) {
		//1.判断是否安装、是否运行该服务，系统服务可以不做判断
		//2.服务初始化
		$this->init($text);
		$this->run();
    }

	//服务初始化
	public function init($text=''){
		//如果服务器没有启用scws扩展，则使用原生phpscws4库
		if(function_exists('scws_new')){
			$this->scws = scws_new('utf8');
		}else{
			require_cache('./addons/libs/scws/pscws4.class.php');
			$this->scws = new PSCWS4('utf8');
		}
		$this->scws->set_charset('utf8');
		$this->scws->set_dict($this->dict);
		$this->scws->set_rule($this->rule);
		$this->setText($text);
	}

	//运行服务
	public function run(){
	}

	/**
     +----------------------------------------------------------
     * 设置待分词文本
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $text 待分词文本
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
	public function setText($text='') {

		$this->text	=	$text;
		$this->scws->send_text($text);
		return $this->text;
	}

	/**
     +----------------------------------------------------------
     * 设置字典路径
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $dict 字典路径
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
	public function setDict($dict='') {

		if(file_exists($dict)) {

			$this->dict	=	$dict;
			return $this->dict;
		}else{

			return false;
		}
	}

	/**
     +----------------------------------------------------------
     * 设置规则路径
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $rule 规则路径
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
	public function setRule($rule='') {

		if(file_exists($rule)) {

			$this->rule	=	$rule;
			return $this->rule;
		}else{

			return false;
		}
	}

	/**
     +----------------------------------------------------------
     * 获取权重前几位的标签
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $text		待分词字符串
	 * @param int	 $top		获取条数
	 * @param string $attr		过滤规则（暂时取消）
	 * @param string $format	输出格式
     +----------------------------------------------------------
     * @return array
	 * a,ad,an,b,d,f,i,j,l,mq,n,nr,nz,nt,ns,nv,nnz,nrnr,ntnr,r,s,un,v,vd,vg,vn,y,z,zl
     +----------------------------------------------------------
     */
	public function getTop($top=10,$format='string',$attr='',$text=false){
		//设置带分词字符串
		if($text){
			$this->setText($text);
		}
		//获取前几位的标签
		$tops	=	$this->scws->get_tops($top);

		$tags	=	$this->_formatTags($tops,$format);
		
		return $tags;
	}

	/**
     +----------------------------------------------------------
     * 获取所有分词关键字
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
	 * @param string $format	输出格式
     * @param string $text		待分词字符串
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
	public function getAll($format='array',$text=false){
		//设置带分词字符串
		if($text){
			$this->setText($text);
		}
		//获取所有分词关键字
		while ($tmp = $this->scws->get_result()) {
			$words[]	=	$tmp;
		}

		//$tags	=	$this->_formatTags($words,$format);

		return $words;
	}

	/**
     +----------------------------------------------------------
     * 获取标签云
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
	 * @param string $data	输出数据，数组形式array(array('tag1',100),array('tag2',200));
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
	public function getCloud($data){

		return $words;
	}

	/**
     +----------------------------------------------------------
     * 格式化tag，输出以逗号分隔的词组或json数组
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $tags	 待格式化数组
	 * @param string $output 输出类型(string,json)
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
	protected function _formatTags($tags=array(),$type='string'){
		
		$output	=	'';

		foreach($tags as $k=>$v){
			$tagWords[]	=	$v['word'];
		}

		switch($type){
			case 'json'	 : $output	=	json_encode($tagWords);break;
			case 'string': $output	=	implode(',',$tagWords);break;
			case 'array' : $output	=	$tags;break;
		}
		return $output;
	}

	/* 后台管理相关方法 */

	//启动服务，未编码
	public function _start(){
		return true;
	}
	
	//停止服务，未编码
	public function _stop(){
		return true;
	}

	//卸载服务，未编码
	public function _install(){
		return true;
	}

	//卸载服务，未编码
	public function _uninstall(){
		return true;
	}

	//析构方法
	public function __destruct() {
		$this->scws->close();
	}
}
?>