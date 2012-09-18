<?php
include 'ModelCommon.php';
class BuyCommentModel extends Model{
	public function makeComment($bid,$uid,$content,$vote){
		// Todo: add a record into the buy_comment table
		$m = M('Buy');
		$r = $m->where(array('id'=>$bid))->field('vote')->select();
		if($r){
			$data['bid']=$bid;
			$data['uid']=$uid;
			$data['content']=htmlspecialchars($content);
			$data['vote']=$vote;
			$this->add($data);
		}
		
		// Todo: refresh the vote of the buy item
	}
	public function getComments($bid){
		// Todo: get a list of comments of the buy item with id $bid
	}
}
