<?php
class OperationAction extends Action{
	private $buy;
	protected $app_alias;
	
	function _initialize(){
		global $ts;
		$this->buy = D('Buy');
	}
	function edit(){
		if($_POST['id']==0){
			$id=$this->addItem();
		}else{
			$this->modifyItem();
			$id=intval($_POST['id']);
		}
		$this->redirect('buy/Index/showitem',array('id'=>$id));
	}
	function addItem() {
		$name = $_POST['name'];
		$owner = $this->mid;
		$description = $_POST['description'];
		$count = $_POST['count'];
		return $this->buy->createItem($name,$description,$owner,$count);
	}
	function modifyItem() {
		$id = $_POST['id'];
		$name = $_POST['name'];
		$description = $_POST['description'];
		$count = $_POST['count'];
		if($this->buy->getOwner($id)==$this->mid)
			$this->buy->modifyItem($id, $name, $description,$count);
	}
	function uploadThumb(){
		$f=$_FILES['upload'];
		$ext=explode('.',$f['name']);
		$ext=strtolower($ext[count($ext)-1]);
		$id=intval($_POST['id']);
		if($this->buy->getOwner($id)==$this->mid&&in_array($ext,array('png','jpg','gif','bmp'))){
			$url='data/uploads/buy_'.time().rand(10,99).'.'.$ext;
			if(move_uploaded_file($f['tmp_name'],$url)){
				$this->buy->addImage($id,$url);
			}
		}
		$this->redirect('buy/Index/showitem',array('id'=>$id));
	}
	function deleteThumb(){
		$id = intval($_GET['id']);
		if($this->buy->getOwner($id)==$this->mid)
			$this->buy->removeImage($id,intval($_GET['index']));
		$this->redirect('buy/Index/showitem',array('id'=>$id));
	}
	public function deleteItem() {
		$id = intval($_GET['id']);
		if($this->buy->getOwner($id)==$this->mid)
			$this->buy->removeItem($id);
		$this->redirect('buy/Index/myshop');
	}
}
