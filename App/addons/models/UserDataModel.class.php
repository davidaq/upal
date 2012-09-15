<?php
/**
 +------------------------------------------------------------------------------
 * 用户数据字典表，提供给插件功能使用
 * 存储/缓存用户数据 - 只能通过key查询数据
 +------------------------------------------------------------------------------
 * @category	core
 * @package		core
 * @author		liuxiaoqing <liuxiaoqing@thinksns.com>
 * @version		$0.1$ 完成基本数据结构
				$0.2$ 完成用户数据缓存
 +------------------------------------------------------------------------------
 */
class UserDataModel extends Model {

	protected	$tableName	=	'user_data';	// 数据库表名
	protected	$fields		=	array (0 => 'id',1 => 'uid',2 => 'key',3 => 'value',4 => 'mtime','_autoinc' => true,'_pk' => 'id');

	/**
	 * 写入该用户的多条数据
	 *
	 * @param intval $uid
	 * @param array $listData array(key=>value)
	 * @return boolean
	 */
	public function lput($uid,$listData=array()) {
		//初始化uid
		$uid	=	intval($uid);
		if($uid==0) return false;

		$result = false;

		//格式化数据
		if(is_array($listData)){

			$insert_sql	.=	"REPLACE INTO __TABLE__ (`uid`,`key`,`value`) VALUES ";

			foreach($listData as $key=>$data){
				$insert_sql	.=	" ($uid,'$key','".serialize($data)."') ,";
			}

			$insert_sql	=	rtrim($insert_sql,',');

			//插入数据列表
			$result	=	$this->execute($insert_sql);

			//更新缓存
			$this->_flush($uid);
		}
		return $result;
	}

	/**
	 * 读取该用户的所有数据
	 *
	 * @param string $listName 参数列表list
	 * @return array
	 */
	public function lget($uid,$flush=false) {
		//初始化uid
		$uid	=	intval($uid);
		if($uid==0) return false;

		static $_res = array();
		if (isset($_res[$uid]))
			return $_res[$uid];

		$cache_id = '_xuserdata_' . $uid;
		if (($data = F($cache_id)) === false || $flush===true) {
			$this->_flush($uid);
		}

		$_res[$uid] = $data;
		return $_res[$uid];
	}

	/**
	 * 写入单个数据
	 *
	 * @param string $key     要存储的参数list:key
	 * @param string $value   要存储的参数的值
	 * @param boolean $replace false:插入新参数，ture:更新已有参数
	 * @return boolean
	 */
	public function put($uid,$key,$value='',$replace=false) {

		//初始化uid
		$uid	=	intval($uid);
		if($uid==0) return false;

		$key	=	$this->_strip_key($key);
		$data	=	serialize($value);
		if($replace){
			$insert_sql	=	"REPLACE INTO __TABLE__ ";
		}else{
			$insert_sql	=	"INSERT INTO __TABLE__ ";
		}

		$insert_sql	.=	"(`uid`,`key`,`value`) VALUES ($uid,'$key','$data') ";
		$result		=	$this->execute($insert_sql);

		//更新缓存
		$this->_flush($uid);

		return $result;
	}

	/**
	 * 读取用户数据
	 * @param intval $uid
	 * @param string $key
	 * @return string
	 */
	public function get($uid,$key=null) {

		//初始化uid
		$uid	=	intval($uid);
		if($uid==0) return false;

		if(!$key){
			return $this->lget($uid);
		}

		$key	=	$this->_strip_key($key);

		static $_res = array();
		if (isset($_res[$key]))
			return $_res[$key];

		$list = $this->lget($uid);
		return $list[$key];
	}

	/**
	 * 存储单个数据
	 *
	 * @param string $key     要存储的参数list:key
	 * @param string $value   要存储的参数的值
	 * @return boolean
	 */
	public function save($uid,$key,$value='') {
		$result		=	$this->put($uid,$key,$value,true);
		return $result;
	}

	/**
	 * 解析过滤输入
	 *
	 * @param array|Object|string $input
	 * @return array
	 */
	protected function _parse_keys($input=''){

		$output	=	'';

		if(is_array($input) || is_object($input)){

			foreach($input as $v){
				$output[]	=	$this->_strip_key($v);
			}
		}elseif(is_string($input)){

			$output[]	=	$this->_strip_key($input);
		}else{
			//异常处理
		}

		return $output;
	}

	/**
	 * 过滤key
	 *
	 * @param string  $key 允许格式 数字字母下划线冒号，如：list:key01
	 * @return string
	 */
	protected function _strip_key($key=''){

		//$key	=	strip_tags($key);
		//$key	=	str_replace(array('\'','"','&','*','%','^','$','?','->'),'',$key);
		$matches = array();
		preg_match('/^[a-zA-Z0-9_:]+$/',$key,$matches);
		if($matches[0] && is_array($matches)){
			return $matches[0];
		}else{
			return '';
		}
	}

	/**
	 * 重建缓存
	 *
	 * @param intval  $uid
	 */
	protected function _flush($uid){
		$cache_id = '_xuserdata_' . $uid;
		$data = array();
		$map['uid'] = $uid;
		$result	= $this->order('`key` ASC')->where($map)->findAll();
		if ($result)
			foreach($result as $v)
				$data[$v['key']] = unserialize($v['value']);

		F($cache_id, $data);
	}
}
?>