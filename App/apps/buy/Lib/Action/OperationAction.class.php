<?php
class OperationAction extends Action{
	private $buy;
	protected $app_alias;
	
	/**
	 * 初始化函数
	 *
	 */	
	function _initialize(){
		global $ts;
		$this->wiki = D('Buy');
	}
	function addItem() {
		$name = $_POST['item_name'];
		$owner = $_POST['uid'];
		$description = $_POST['item_des'];
		/**
		ImagPath set Here!
		*/
		$buy->createItem($name,$description,$owner, $imgpath);
	}
	function modifyItem() {
		$id = $_POST['item_id'];
		$name = $_POST['item_name'];
		$owner = $_POST['uid'];
		$description = $_POST['item_des'];
		/**
		ImagPath set Here!
		*/
		$buy->modifyItem($id, $name, $description, $owner, $imgpath);
	}
	function deleteItem() {
		$id = $_POST['item_id'];
		$buy->removeItem($id);
	}
}
