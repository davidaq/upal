<?php
class FollowGroupAction extends Action{
	// 分组选择
	public function selector($type = 'box'){
		$fid = intval($_REQUEST['fid']);
		$followGroupDao = D('FollowGroup');
		$group_list = $followGroupDao->getGroupList($this->mid);
		$f_group_status = $followGroupDao->getGroupStatus($this->mid,$fid);
		if($type == 'list'){
			foreach($group_list as &$v){
				$v['title'] = (strlen($v['title'])+mb_strlen($v['title'],'UTF8'))/2>10?getShort($v['title'],4).'...':$v['title'];
			}
		}
		
		
		$this->assign('fid',$fid);
		$this->assign('group_list',$group_list);
		$this->assign('f_group_status',$f_group_status);
	}
	// 下拉式-分组选择
	public function selectorList(){
		$this->selector('list');
		$this->display();
	}
	// 弹窗式-分组选择
	public function selectorBox(){
		$this->selector();
		$this->display();
	}
	// 设置某一个好友的分组状态
	public function setFollowGroup(){
		$gid = intval($_REQUEST['gid']);
		$fid = intval($_REQUEST['fid']);
		$followGroupDao = D('FollowGroup');
		$followGroupDao->setGroupStatus($this->mid,$fid,$gid);
		$follow_group_status = $followGroupDao->getGroupStatus($this->mid,$fid);
		foreach($follow_group_status as $k => $v){
			$v['title']      = (strlen($v['title'])+mb_strlen($v['title'],'UTF8'))/2>6?getShort($v['title'],3).'...':$v['title'];
			$_follow_group_status .= $v['title'].',';
			if(!empty($follow_group_status[$k+1]) && (strlen($_follow_group_status)+mb_strlen($_follow_group_status,'UTF8'))/2>=13){
				$_follow_group_status .= '···,';
				break;
			}
		}
        $_follow_group_status = substr($_follow_group_status,0,-1);
        F("weibo_followlist_".$this->mid,null);
        exit($_follow_group_status);
	}
	// 为分组添加好友
	/*public function addFollows(){
		$fids = explode(',',$_REQUEST['fri_ids']);
		$gid = $_REQUEST['gid'];
		$groupDao = D('FollowGroup');
		foreach($fids as $fid){
			$groupDao->setGroupStatus($this->mid,$fid,$gid,'add');
		}
	}*/
	public function setGroupTab(){
		if(is_numeric($_REQUEST['gid'])){
			$title = D('FollowGroup')->getField('title',"follow_group_id={$_REQUEST['gid']}");
			$this->assign('gid',$_REQUEST['gid']);
			$this->assign('title',$title);
		}
		$this->display();
	}
	// 操作分组
	public function setGroup(){
		$title = trim(text($_REQUEST['title']));
		if(empty($title)){
		    $this->error("标题不能为空");
		}
		if(!$_REQUEST['gid']){
			$res = D('FollowGroup')->setGroup($this->mid,$title);
		}else{
			$gid   = $_REQUEST['gid'];
			$res = D('FollowGroup')->setGroup($this->mid,$title,$gid);
		}
		
		F("weibo_followlist_".$this->mid,null);
		if($res){
		    $this->success($res);
		}else{
		    $this->error("操作分组失败");
		}
	}
	// 删除某个关注分组
	public function deleteGroup($uid,$gid){
		$gid = $_REQUEST['gid'];
		$res = D('FollowGroup')->deleteGroup($this->mid,$gid);
		if(!$_SERVER['HTTP_REFERER']){
			$this->redirect('home/space/follow',array('uid'=>$this->mid,'type'=>'following'));
		}elseif($res){
			header('Location:'.preg_replace('/&gid=[0-9]*/i','',$_SERVER['HTTP_REFERER']));
		}else{
			header('Location:'.$_SERVER['HTTP_REFERER']);
		}
	}
}
?>