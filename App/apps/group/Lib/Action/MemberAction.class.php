<?php

class MemberAction extends BaseAction
{
	var $member;

	public function _initialize(){
		parent::_initialize();
		$this->member = D('Member');
		$this->assign('current','member');
        $this->setTitle("成员 - " . $this->groupinfo['name']);
	}

	//所有成员
	public function index() {
		if($_GET['order'] == 'new') {
			$order = 'ctime DESC';
			$this->assign('order', $_GET['order']);
		}elseif($_GET['order'] == 'visit'){
			$order = 'mtime DESC';
			$this->assign('order', $_GET['order']);
		}else{
			$order = 'level ASC';
			$this->assign('order', 'all');
		}

		$search_key = $this->_getSearchKey();
		if ($search_key) {
			
		} else {
			$memberInfo = $this->member->order($order)->where('gid=' . $this->gid . " AND status=1 AND level>0")->findPage(20);
		}

		foreach ($memberInfo['data'] as &$member) {
			$member['weibo'] = D('GroupWeibo')->field('weibo_id,gid,content')
			        					 ->where("uid={$member['uid']} AND gid={$member['gid']} AND isdel=0")
			        					 ->order('ctime DESC')
			        					 ->find();
			$member['followState'] = getFollowState( $this->mid, $member['uid']);
		}

		$this->assign('memberInfo',$memberInfo);
		$this->display();
	}
}