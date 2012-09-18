<?php
include_once 'ModelCommon.php';
class BuyCommentModel extends Model{
	public function makeComment($bid,$uid,$content,$vote){
		$m = M('buy');
		$r = $m->where(array('id'=>$bid))->field('vote')->select();
		if($r){
			$data['bid']=$bid;
			$data['uid']=$uid;
			$data['content']=htmlspecialchars($content);
			$data['vote']=$vote;
			$this->add($data);
			$vote=$r[0]['vote']*0.7+3*$vote;
			$r = $m->save(array('id'=>$bid,'vote'=>$vote));
		}
	}
	public function getComments($bid){
		// Todo: get a list of comments of the buy item with id $bid
		$P=C('DB_PREFIX');
		$r = $this->join("{$P}user as U ON U.uid={$P}buy_comment.uid")->where(array('bid'=>intval($bid)))->limit(20)->field('U.uid,U.uname,vote,content')->select();
		return $r;
	}
}
