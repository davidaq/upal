<?php
class CategoryService extends Service {
	private $model;
	private $tableName;
	private static $cache = array();
	public function __construct() {
		$this->model = M ();
		$this->tableName = C ( 'DB_PREFIX' ) . "forum";
	}
	public function setParam($tableName) {
		$this->tableName = $tableName;
	}
	/**
	 * getCategoryList 
	 * 获取所有分类
	 * @Access Public
	 * @Return Void
	 */
	function getCategoryList($level = false) {
		$list = $this->model->query ( "select a.fid,a.left_value,a.right_value,a.name,a.forum_manager
							from $this->tableName a
							where a.left_value > 1
							order by left_value
							" );
		$list = $this->treeFormat ( $list, $level );
		return $list;
	}
	
	function getRoot() {
		$data = $this->model->query ( "select a.fid
							from $this->tableName a
							where a.left_value = 1
							" );
		return $data [0] ['fid'];
	}
	/**
	 * isLeafCategory 
	 * 检查指定分类是否为叶子节点，是则返回true,否则返回false
	 * @Access Public         
	 * @param mixed $id    
	 * @Return Void
	 */
	
	public function isLeafCategory($id) {
		$list = $this->getOneCategoryInfo ( $id );
		if ($list [0] [right_value] - $list [0] [left_value] > 1) {
			return false;
		} else {
			return true;
		}
	}
	/**
	 * getSubCategoryList 
	 * 获取指定分类的子集
	 * @Access Public
	 * @param mixed $id    
	 * @Return Void
	 */
	
	public function getSubCategoryList($id) {
		if ($this->isLeafCategory ( $id )) {
			return false;
		}
		$list = $this->getOneCategoryInfo ( $id );
		$lft = $list [0] ["left_value"];
		$rgt = $list [0] ["right_value"];
		
		$list = $this->model->query ( "select a.*
							from $this->tableName a
							where  a.left_value > $lft
							and a.right_value < $rgt
							order by left_value" );
		$list = $this->treeFormat ( $list );
		return $list;
	}
	/**
	 * getCategoryPath 
	 * 获取指定分类的Path
	 * @Access Public
	 * @param mixed $id    
	 * @Return Void
	 */
	
	function getCategoryPath($id, $path = true) {
		$list = $this->getOneCategoryInfo ( $id );
		
		$lft = $list [0] ["left_value"];
		$rgt = $list [0] ["right_value"];
		$list = $this->model->query ( "select a.left_value,a.right_value,a.fid,a.name
						from $this->tableName a
						where  a.left_value <= $lft
						and a.right_value >= $rgt
						and a.left_value != 1
						order by left_value" );
		$result = array ();
		$str = "";
		foreach ( $list as $key => $val ) {
			$result [$key] ["fid"] = $val ["fid"];
			$result [$key] ["name"] = $val ["name"];
			if ($val ['left_value'] != 1) {
				$str .= $val ["name"] . ">";
			}
		}
		if ($path === true) {
			$result ['path'] = substr ( $str, 0, $str . length - 1 );
		}
		//dump($result);
		return $result;
	
	}
	/**
	 * getBrotherCategoryList 
	 * 获取指定分类的兄弟分类
	 * @Access Public
	 * @param mixed $id    
	 * @Return Void
	 */
	
	function getBrotherCategoryList($id) {
		$pid = $this->getParentCategoryId ( $id );
		if ($pid) {
			$list = $this->getSubCategoryList ( $pid );
		} else {
			$list = $this->getCategoryList ();
		}
		$result = array ();
		foreach ( $list as $key => $val ) {
			$result [$key] ["fid"] = $val ["fid"];
			$result [$key] ["name"] = $val ["name"];
		}
		return $result;
	}
	function getDepthList($depth, $fomart = true) {
		$list = M ()->query ( 'SELECT T1.*, T1.fid, COUNT(T1.fid) AS depth FROM 
                 ' . $this->tableName . ' AS T1, ' . $this->tableName . ' as T2 WHERE T1.left_value 
                 BETWEEN T2.left_value AND T2.right_value AND T2.left_value != 1 GROUP BY T1.fid
                  HAVING depth<' . $depth . ' ORDER BY T1.left_value' );
		if ($fomart) {
			$list = $this->treeFormat ( $list );
		}
		return $list;
	}
	
	/**
	 * moveCategory 
	 * 移动指定分类到$pid下，包括其子级
	 * @Access Public
	 * @param mixed $pid
	 * @param mixed $id    
	 * @Return Void
	 */
	function moveCategory($id, $pid) {
		$tempId = $this->getParentCategoryId ( $id );
		if ($pid == $tempId) {
			return 1;
		}
		if ($pid == 0) {
			//设为一级分类
			$info = $this->getOneCategoryInfo ( $id );
			$lft = $info [0] ["left_value"];
			$rgt = $info [0] ["right_value"];
			
			$max = $this->model->query ( "select max(right_value)  from $this->tableName a" );
			$max = $max [0] ['max(right_value)'];
			$step = $rgt - $lft + 1;
			$temp = $max - $rgt;
			
			$move_list = $this->getSubCategoryID ( $info [0] ['fid'] );
			$this->model->execute ( "update $this->tableName
						set left_value=left_value-$step
						where left_value>$lft and right_value>$rgt" );
			$this->model->execute ( "update $this->tableName
						set right_value=right_value-$step
						where right_value>$rgt" );
			
			foreach ( $move_list as $val ) {
				$this->model->execute ( "update $this->tableName 
						set left_value=left_value+$temp, right_value=right_value+$temp
						where fid = $val" );
			}
			$this->freeCategoryCach ();
			return 1;
		}
		
		$info = $this->getOneCategoryInfo ( $id );
		$pinfo = $this->getOneCategoryInfo ( $pid );
		if (! ($info && $pinfo)) {
			return false;
		}
		$lft = $info [0] ["left_value"];
		$rgt = $info [0] ["right_value"];
		$plft = $pinfo [0] ["left_value"];
		$prgt = $pinfo [0] ["right_value"];
		//$pid 是 $id 的子级，跳出
		if ($plft > $lft && $prgt < $rgt) {
			return "父级分类不能移动到子级分类下";
		}
		if ($prgt > $lft) {
			//右移
			$step = $rgt - $lft + 1;
			$tmpValue = $prgt - $rgt - 1;
			$move_list = $this->getSubCategoryID ( $info [0] ['fid'] );
			
			//$id已是$pid 的子集，还更新中间部分，以右值来找，并更新
			if ($plft < $lft && $prgt > $rgt) {
				//处理中间部分
				$this->model->execute ( "update $this->tableName 
						set left_value=left_value-$step
						where right_value>$rgt
						and right_value<$prgt
						and left_value >$lft
						" );
				$this->model->execute ( "update $this->tableName 
							set right_value=right_value-$step
							where right_value>$rgt
							and right_value<$prgt" );
			} else {
				//处理中间部分
				$this->model->execute ( "update $this->tableName 
						set left_value=left_value-$step
						where left_value>$rgt
						and left_value<=$prgt" );
				$this->model->execute ( "update $this->tableName 
							set right_value=right_value-$step
							where right_value>$rgt
							and right_value<$prgt" );
			}
			foreach ( $move_list as $val ) {
				$this->model->execute ( "update $this->tableName
						set left_value=left_value+$tmpValue,right_value=right_value+$tmpValue
						where fid = $val" );
			}
		} else {
			//左移
			$step = $rgt - $lft + 1;
			$tmpValue = $lft - $prgt;
			$move_list = $this->getSubCategoryID ( $info [0] ['fid'] );
			//处理中间部分
			$this->model->execute ( "update $this->tableName 
						set left_value=left_value+$step
						where left_value>$prgt
						and left_value<$lft" );
			$this->model->execute ( "update $this->tableName 
						set right_value=right_value+$step
						where right_value>=$prgt
						and right_value<$lft" );
			//处理移动部分 以ID来update
			

			foreach ( $move_list as $val ) {
				$this->model->execute ( "update $this->tableName 
						set left_value=left_value-$tmpValue, right_value=right_value-$tmpValue
						where fid = $val" );
			}
		}
		$err = $this->model->getDbError ();
		if (empty ( $err )) {
			return 1;
		} else {
			return false;
		}
	}
	
	/**
	 * deleteCategory 
	 * 删除指定分类
	 * @Access Public
	 * @param mixed $id    
	 * @Return Void
	 */
	function deleteCategory($id) {
		$list = $this->getOneCategoryInfo ( $id );
		$lft = $list [0] ["left_value"];
		$rgt = $list [0] ["right_value"];
		$name = $list [0] ["name"];
		$result = $this->model->execute ( "delete from $this->tableName where left_value>=$lft and right_value <=$rgt" );
		$this->model->execute ( "update $this->tableName 
					set left_value=left_value-($rgt-$lft+1)
					where left_value>$lft" );
		$this->model->execute ( "update $this->tableName 
					set right_value=right_value-($rgt-$lft+1) 
					where right_value>$rgt" );
		return $result;
	}
	
	/**
	 * addCategory 
	 * 添加分类,每次添加都是在第一个位置
	 * @Access Public
	 * @param mixed $pid    
	 * @param mixed $id    
	 * @Return Void
	 */
	function addCategory($pid, $name) {
		//todo:考虑同名分类拉通
		if ($pid != 0) {
			$list = $this->getOneCategoryInfo ( $pid );
			$lft = $list [0] ["left_value"];
			$rgt = $list [0] ["right_value"];
			
			//更新左边
			$this->model->execute ( "update $this->tableName a set left_value=left_value+2 where left_value>{$lft} and right_value > {$rgt}" );
			//更新右边
			$this->model->execute ( "update $this->tableName a set right_value=right_value+2 where right_value>={$rgt}" );
			
			$this->model->execute ( "insert into $this->tableName 
							(name,left_value,right_value) values
							('$name',$rgt,$rgt+1)" );
		} else {
			//添加一级分类
			

			$max = $this->model->query ( "select max(right_value)  from $this->tableName a" );
			$lft = $max [0] ['max(right_value)'] + 1;
			$this->model->execute ( "insert into $this->tableName 
						(name,left_value,right_value) values
						('$name',$lft,$lft+1)" );
		
		}
		return $this->model->getLastInsID ();
	}
	/**
	 * editCategory 
	 * 编辑分类
	 * @Access Public
	 * @param mixed $id    
	 * @Return Void
	 */
	function editCategory($id, $name) {
		$list = $this->getOneCategoryInfo ( $id );
		if ($list) {
			$this->model->execute ( "update $this->tableName 
									set name='$name'
									where fid=$id" );
			$err = $this->model->getDbError ();
			if (empty ( $err )) {
				$this->freeCategoryCach ();
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	/**
	 * getParentCategoryId 
	 * 取得父类的ID
	 * @Access Public
	 * @param mixed $id    
	 * @Return Void
	 */
	private function getParentCategoryId($id) {
		$list = $this->getOneCategoryInfo ( $id );
		$lft = $list [0] ["left_value"];
		$rgt = $list [0] ["right_value"];
		$list = $this->model->query ( "select a.*
						from $this->tableName a
						where a.left_value < $lft
						and a.right_value > $rgt
						order by left_value desc" );
		if (isset ( $list )) {
			return $list [0] ['fid'];
		} else {
			return false;
		}
	}
	/**
	 * getOneCategoryInfo 
	 * 读取 个分类的信息
	 * @Access Public
	 * @param mixed $id    
	 * @Return Void
	 */
	function getOneCategoryInfo($id) {
		$this->checkParmIsId ( $id );
		if(isset(self::$cache[$id])){
			return self::$cache[$id];
		}
		
		$list = $this->model->query ( "select fid,name,left_value,right_value,forum_logo,timeSetting,forum_icon,forum_intro,forum_manager,view_count,topic_count,post_count,most_online_user,lastpost_uid,lastpost_time,lastpost_post_tid,lastpost_post_pid,today_thread_count,today_post_count,today_view_count
						from $this->tableName
						where fid=$id" );
		if (empty ( $list )) {
			return false;
		}
		self::$cache[$id] = $list;
		return $list;
	}
	
	function checkNotSecond($id) {
		$path = $this->getCategoryPath ( $id );
		$pathData = $path ['path'];
		unset ( $path ['path'] );
		return count ( $path ) > 2 ? $pathData : false;
	}
	
	/**
	 * getSubCategoryID 
	 * 获取指定分类的子集的id,包括自身id
	 * @Access Public
	 * @param mixed $id    
	 * @Return Void
	 */
	
	function getSubCategoryID($id) {
		$list = $this->getOneCategoryInfo ( $id );
		if(!$list) return false;
		$lft = $list [0] ["left_value"];
		$rgt = $list [0] ["right_value"];
		$list = $this->model->query ( "select a.*
							from $this->tableName a
							where  a.left_value >= $lft
							and a.right_value <= $rgt
							order by left_value" );
		$res = array ();
		foreach ( $list as $key => $val ) {
			$res [$key] = $list [$key] ['fid'];
		}
		return $res;
	}
	
	private function checkParmIsId($id) {
		if (! intval ( $id )) {
			return false;
		} else if ($id == 0) {
			return false;
		} else {
			return true;
		}
	}
	/**
	 * treeFormat 
	 * 转换为树形结构
	 * @Access Public
	 * @param mixed $data    
	 * @Return Void
	 */
	public function treeFormat($data) {
		
		foreach ( $data as $key1 => $value ) {
			$list [] = $value;
			foreach ( $list as $key2 => $v ) {
				if ($value ['left_value'] > $v ['left_value'] && $value ['right_value'] < $v ['right_value']) {
					$list [$key2] ['children'] [] = $value;
					array_pop ( $list );
				}
			}
		}
		foreach ( $list as &$value ) {
			if (isset ( $value ['children'] )) {
				$value ['children'] = $this->treeFormat ( $value ['children'], $level, $inputLevel );
			}
		}
		return $list;
	}
	/**
	 * freeCategoryCach 
	 * 清除分类的缓存
	 * @Access Public
	 * @Return Void
	 */
	private function freeCategoryCach() {
		F ( $this->tableName , NULL );
		$list = $this->model->query ( "select fid from $this->tableName" );
		foreach ( $list as $key => $val ) {
			F ( $val ['fid'] . $this->tableName, NULL );
		}
	}
	/**
	 * savetree 
	 * 存储树形为左右值记录
	 * @Access Public
	 * @param mixed $data,分类ID的树形数据    
	 * @param mixed $i     
	 * @Return Void
	 */
	function saveTree($data, &$i = 1) {
		foreach ( $data as $key => $val ) {
			$id = $val ['fid'];
			$this->model->execute ( "update $this->tableName  set left_value=$i where fid=$id " );
			$i ++;
			if (isset ( $val ['children'] )) {
				$this->savetree ( $val ['children'], $i );
			}
			$this->model->execute ( "update $this->tableName  set right_value=$i where fid=$id " );
			$i ++;
		}
		
		$this->freeCategoryCach ();
		$err = $this->model->getDbError ();
		if (empty ( $err )) {
			return true;
		} else {
			return false;
		}
	}
	
	function makeTreeHtml($list) {
		$str = '<span id="spans-divs" class="page-list">';
		foreach ( $list as $key => $val ) {
			$str = $str . '<div id=' . $val ["id"] . ' class="clear-element page-item3 sort-handle left_al"><div>';
			$str = $str . '<a class="R" href="javascript:void(0)" onclick="del(' . $this->gid . ',' . $val ["id"] . ');">' . L ( common_delete ) . '</a>
			<a class="R" href="javascript:void(0)" onclick="edit(' . $this->gid . ',' . $val ["id"] . ');">' . L ( common_edit ) . '</a> 
			<a class="R" href="javascript:void(0)" onclick="add(' . $this->gid . ',' . $val ["id"] . ');">' . L ( common_add ) . '</a><span id="title_' . $val ["id"] . '">' . $val ["name"] . '</span>';
			$str = $str . '</div>';
			if (isset ( $val ["children"] )) {
				$temp = $this->makeTreeHtml ( $val ["children"] );
				$str = $str . $temp;
			}
			$str = $str . '</div>';
		}
		$str = $str . '</span>';
		return $str;
	}
	
	function makeTreeHtmlForUser($list, $i = false) {
		$html = $i ? "<ul class='treemenu'>" : "<ul >";
		foreach ( $list as $key => $val ) {
			$html .= "<li class='btm' id='li_" . $val ["id"] . "'>";
			$html .= "<a id='drop_" . $val ["id"] . "' class='drop' href='javascript:void(0)' onclick='getFileHtml(" . $val ["fid"] . ")'>";
			$html .= $val ['name'] . "</a>";
			
			if (isset ( $val ["children"] )) {
				$html .= $this->makeTreeHtmlForUser ( $val ['children'], false ) . "</li>";
			}
		}
		return $html . "</ul>";
	}
	function _format($list) {
		foreach ( $list as $key => $val ) {
			$res [$key] ['a'] = $val ['fid'];
			$res [$key] ['t'] = $val ['name'];
			if (isset ( $val ['children'] )) {
				$res [$key] ['d'] = $this->_format ( $val ['children'] );
			}
		}
		return $res;
	}
	
	/**
	 * moveCategoryPlumb
	 * 同级分类上下移动 (plumb  垂直)
	 */
	function moveCategoryPlumb($id, $type) {
		$move = $this->getOneCategoryInfo ( $id );
		$move_lft = $move [0] ['left_value'];
		$move_rgt = $move [0] ['right_value'];
		if ($type == "up") {
			//上移
			//找出与之交换的目标ID(tag)
			$map ['right_value'] = $move_lft - 1;
			$target = $this->model->table( $this->tableName )->where ( $map )->find ();
			if (! $target)
				return "已是本层级最上";
			$target_lft = $target ['left_value'];
			$target_rgt = $target ['right_value'];
			
			$move_step   = $target_rgt - $target_lft + 1;
			$target_step = $move_rgt - $move_lft + 1;
			
			//找出子集  
			$move_list = $this->getSubCategoryID ( $move [0] ['fid'] );
			$target_list = $this->getSubCategoryID ( $target ['fid'] );
			
			//处理$move
			foreach ( $move_list as $val ) {
				$aa = $this->model->query ( "UPDATE $this->tableName SET  left_value = left_value-$move_step , right_value = right_value-$move_step
							WHERE  (fid=$val )" );
			}
			//处理$target
			foreach ( $target_list as $val ) {
				$bb = $this->model->query ( "UPDATE $this->tableName SET  left_value = left_value+$target_step , right_value = right_value+$target_step
							WHERE  (fid=$val )" );
			}
		
		} else {
			//下移
			//找出与之交换的目标ID(tag)
			$map ['left_value'] = $move_rgt + 1;
			
			$target = $this->model->table( $this->tableName )->where ( $map )->find ();
			if (! $target)
				return "已是本层级最下";
			$target_lft = $target ['left_value'];
			$target_rgt = $target ['right_value'];
			
			$move_step = $target_rgt - $target_lft + 1;
			$target_step = $move_rgt - $move_lft + 1;
			
			//找出子集  
			$move_list = $this->getSubCategoryID ( $move [0] ['fid'] );
			$target_list = $this->getSubCategoryID ( $target ['fid'] );
			
			//处理$move
			foreach ( $move_list as $val ) {
				$aa = $this->model->query ( "UPDATE $this->tableName SET  left_value = left_value+$move_step , right_value = right_value+$move_step
							WHERE  (fid=$val)" );
			}
			//处理$target
			foreach ( $target_list as $val ) {
				$bb = $this->model->query ( "UPDATE $this->tableName SET  left_value = left_value-$target_step , right_value = right_value-$target_step
							WHERE  (fid=$val )" );
			}
		
		}
		$this->freeCategoryCach ();
		return 1;
	
	}
	public function _start() {
		return true;
	}
	public function run() {
		return true;
	}
	
	public function _stop() {
		return true;
	}
	
	public function _install() {
		return true;
	}
	
	public function _uninstall() {
		return true;
	}

}
?>