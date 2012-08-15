<?php
class InviteRecordModel extends Model
{
	protected $tableName = 'invite_record';
	
	public function addRecord($uid, $fid)
	{
		if (($uid = intval($uid)) <= 0 || ($fid = intval($fid)) <= 0)
			return false;

		$time = time();
		$sql = "REPLACE INTO __TABLE__ (`uid`, `fid`, `ctime`, `valid`) VALUES ('{$uid}', '{$fid}', '{$time}', 0)";
		return $this->execute($sql);
	}
	
	public function setRecordValid($uid){
		$uid = intval($uid);
		$db_prefix = C('DB_PREFIX');
		$sql = "UPDATE {$db_prefix}invite_record SET valid=1 WHERE fid={$uid};";
		$result = $this->execute($sql);
		return $result;
	}

	public function getStatistics($uid = 0, $order = 'count DESC')
	{
		$table_name = $this->trueTableName;
		if (empty($uid) || (is_numeric($uid) && ($uid = intval($uid)) <= 0)) {
			$sql = "SELECT `uid`, count(`fid`) AS `count` FROM {$table_name} WHERE valid=1 GROUP BY `uid` ORDER BY {$order}";
		} else {
			if (is_array($uid))
				$uid = t(implode(',', $uid));
			$sql = "SELECT `uid`, count(`fid`) AS `count` FROM {$table_name} WHERE valid=1 AND `uid` IN ( {$uid} ) GROUP BY `uid` ORDER BY {$order}";
		}
		return $this->findPageBySql($sql);
	}
	
	public function getInvitedUser($uid)
	{
		if (($uid = intval($_GET['uid'])) <= 0)
			return false;
			
		$map['uid'] = $uid;
		$map['valid'] = 1;
		return $this->where($map)->order('invite_record_id DESC')->findPage();
	}

	public function getInviter($uid){
		$uid = intval($uid);
		$db_prefix = C('DB_PREFIX');
		$sql = "SELECT a.* FROM {$db_prefix}user as a,{$db_prefix}invite_record as b WHERE a.uid=b.uid AND b.fid=$uid";
		$result = $this->query($sql);
		return $result[0];
	}
}