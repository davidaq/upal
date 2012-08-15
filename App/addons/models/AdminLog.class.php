<?php
class AdminLogModel extends Model {
	protected $tableName = 'admin_log';
	
	public function addLog( $_LOG ){
		$this->add( $_LOG );
	}
}

?>