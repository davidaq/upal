<?php
class UserModel extends Model
{
	function search($name){
    	return $this->where("name like '%$name%'")->field('id')->findAll();
    	
    }
    
}
?>