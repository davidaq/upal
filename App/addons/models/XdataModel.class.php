<?php
// +----------------------------------------------------------------------
// | OpenSociax [ open your team ! ]
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.sociax.com.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: genixsoft.net <智士软件>
// +----------------------------------------------------------------------
// $Id$

/**
 +------------------------------------------------------------------------------
 * Key-value存储引擎，用MySQL模拟memcache等key-value数据库写法
 * 以后可以切换到其它成熟数据库 或 amazon云计算平台
 +------------------------------------------------------------------------------
 * @category	core
 * @package		core
 * @author		liuxiaoqing <liuxiaoqing@thinksns.com>
 * @version		$0.1$ 完成基本数据结构
				$0.2$ 增加":查询语法"  list:key
				$0.3$ todo：增加多条查询 list1:key1,list2:key2,
				$0.4$ todo：增加模糊查询 list3:*,*:key,list1:ke3*;
 +------------------------------------------------------------------------------
 */
class XdataModel extends Model {

	protected	$tableName	=	'system_data';	// 数据库表名
	protected	$list_name	=	'global';	// 默认列表名
	protected	$fields		=	array (0 => 'id',1 => 'uid',2 => 'list',3 => 'key',4 => 'value',5 => 'mtime','_autoinc' => true,'_pk' => 'id');

	/**
	 * 写入参数列表
	 *
	 * @param string $listName 参数列表list
	 * @param array $listData array(key=>value)
	 * @return boolean
	 */
	public function lput($listName='',$listData=array()) {
		//初始化list_name
		$listName	=	$this->_strip_key($listName);
		$result = false;

		//格式化数据
		if(is_array($listData)){

			$insert_sql	.=	"REPLACE INTO __TABLE__ (`list`,`key`,`value`,`mtime`) VALUES ";

			foreach($listData as $key=>$data){
				$insert_sql	.=	" ('$listName','$key','".serialize($data)."','".date('Y-m-d H:i:s')."') ,";
			}
			
			$insert_sql	=	rtrim($insert_sql,',');

			//插入数据列表
			$result	=	$this->execute($insert_sql);
			
		}
		
		$cache_id = '_xdata_lget_' . $listName;
		F($cache_id, null);

		return $result;
	}

	/**
	 * 读取参数列表
	 *
	 * @param string $listName 参数列表list
	 * @return array
	 */
	public function lget($list_name='') {
		$list_name = $this->_strip_key($list_name);

		static $_res = array();
		if (isset($_res[$list_name]))
			return $_res[$list_name];
		
		$cache_id = '_xdata_lget_' . $list_name;
		
		if (($data = F($cache_id)) === false) {
			
			$data = array();
			$map['`list`'] = $list_name;
			$result	= $this->order('id ASC')->where($map)->findAll();	
			
			if ($result)
				foreach($result as $v)
					$data[$v['key']] = unserialize($v['value']);

			F($cache_id, $data);
		}
		
					//dump($data);
		$_res[$list_name] = $data;
		return $_res[$list_name];
	}

	/**
	 * 写入单个数据
	 *
	 * @param string $key     要存储的参数list:key
	 * @param string $value   要存储的参数的值
	 * @param boolean $replace false:插入新参数，ture:更新已有参数
	 * @return boolean
	 */
	public function put($key,$value='',$replace=false) {

		$key	=	$this->_strip_key($key);
		$keys	=	explode(':',$key);
		$data	=	serialize($value);
		if($replace){
			$insert_sql	=	"REPLACE INTO __TABLE__ ";
		}else{
			$insert_sql	=	"INSERT INTO __TABLE__ ";
		}

		$insert_sql	.=	"(`list`,`key`,`value`) VALUES ('$keys[0]','$keys[1]','$data') ";
		$result		=	$this->execute($insert_sql);

		$cache_id = '_xdata_lget_' . $keys[0];
		F($cache_id, null);

		return $result;
	}

	/**
	 * 读取数据list:key
	 *
	 * @param string $key 要获取的某个参数list:key
	 * @return string
	 */
	public function get($key) {
		$key	=	$this->_strip_key($key);
		$keys	=	explode(':',$key);

		static $_res = array();
		if (isset($_res[$key]))
			return $_res[$key];

		$list = $this->lget($keys[0]);
		return $list[$keys[1]];

		/*
		$map['`list`']		=	$keys[0];
		$map['`key`']		=	$keys[1];

		$result	=	$this->where($map)->find();
		if(!$result){
			return false;
		}else{
			return unserialize($result['value']);
		}
		*/
	}

	/**
	 * 存储单个数据
	 *
	 * @param string $key     要存储的参数list:key
	 * @param string $value   要存储的参数的值
	 * @return boolean
	 */
	public function save($key,$value='') {
		$result		=	$this->put($key,$value,true);
		return $result;
	}

	/**
	 * 批量读取数据 非必要
	 *
	 * @param string			  $listName 参数列表list
	 * @param array|Object|string $keys     参数键key
	 * @return array
	 */
	public function getAll($listName,$keys) {
		if($key){  //用于获取list下所有数据 Nonant
			$keysArray	=	$this->_parse_keys($keys);
			$map['`key`']		=	array('in',$keysArray);
		}

		$map['`list`']		=	$listName;


		$result	=	$this->where($map)->findAll();

		if(!$result){
			return false;
		}else{
			foreach($result as $v){
				$datas[$v['list']][$v['key']]	=	unserialize($v['value']);
			}
		}
		return $datas;
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
	 * @param string  $key 只允许格式 数字字母下划线，list:key 不允许出现html代码 和这些符号 ' " & * % ^ $ ? ->
	 * @return string
	 */
	protected function _strip_key($key=''){
		if($key==''){

			return $this->list_name;
		}else{

			$key	=	strip_tags($key);
			$key	=	str_replace(array('\'','"','&','*','%','^','$','?','->'),'',$key);

			return $key;
		}
	}
}
?>