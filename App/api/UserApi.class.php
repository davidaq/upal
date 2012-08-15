<?php
class UserApi extends Api{

	//按用户UID或昵称返回用户资料，同时也将返回用户的最新发布的微博
	function show(){
		$data = getUserInfo($this->user_id, urldecode($this->user_name),$this->mid,true);
		return $data;
	}

	public function notificationCount() {
		if(empty($this->user_id) && isset($this->mid)){
			return service('Notify')->getCount($this->mid);
		}else{
			return service('Notify')->getCount($this->user_id);
		}

	}
	
	public function unsetNotificationCount()
	{
		if(empty($this->user_id) && isset($this->mid)){
			switch ($this->data['type']) { // 暂仅允许message/weibo_commnet/atMe
				case 'message':
					return (int) model('Message')->setAllIsRead($this->mid);
				case 'weibo_comment':
					return (int) model('UserCount')->setZero($this->mid, 'comment');
				case 'atMe':
					return (int) model('UserCount')->setZero($this->mid, 'atme');
				default:
					return 0;
			}
		}else{
			switch ($this->data['type']) { // 暂仅允许message/weibo_commnet/atMe
				case 'message':
					return (int) model('Message')->setAllIsRead($this->user_id);
				case 'weibo_comment':
					return (int) model('UserCount')->setZero($this->user_id, 'comment');
				case 'atMe':
					return (int) model('UserCount')->setZero($this->user_id, 'atme');
				default:
					return 0;
			}
		}
	}
	
	public function getNotificationList(){
		$this->data['type'] 	= $this->data['type']	? $this->data['type'] : array(1,2);
		$this->data['order']	= $this->data['order'] == 'ASC'	? '`mb`.`list_id` ASC' : '`mb`.`list_id` DESC';
		if(empty($this->user_id) && isset($this->mid)){
			return service('Notify')->getNotifityCount($this->mid, $this->data['type'], $this->since_id, $this->max_id, $this->count, $this->page);
		}else{
			return service('Notify')->getNotifityCount($this->user_id, $this->data['type'], $this->since_id, $this->max_id, $this->count, $this->page);
		}		
	}
	
	public function setMessageIsRead(){
		if(empty($this->user_id) && isset($this->mid)){
			return (int)model('Message')->setMessageIsRead($this->id,$this->mid);
		}else{
			return (int)model('Message')->setMessageIsRead($this->id,$this->user_id);
		}
		
	}
}