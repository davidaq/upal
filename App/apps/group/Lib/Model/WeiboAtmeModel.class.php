<?php 
class WeiboAtmeModel extends Model{
    var $tableName = 'group_weibo_atme';
    
    function addAtme($uids, $gid, $weibo_id){
		foreach ($uids as $k=>$v){
			$sqlArr[] = "($v,$gid,$weibo_id)";
		}
		if( $sqlArr ){
			$result = $this->query("INSERT INTO "
								   . C('DB_PREFIX') . $this->tableName
								   . ' (`uid`,`gid`,`weibo_id`) values '
								   . implode(',', $sqlArr));
		}
		return $result;
    }
}
?>