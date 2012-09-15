<?php
class MessageApi extends Api{
	private function __formatMessageList($message) {
		foreach ($message as $k => $v) {
			$message[$k] = $this->__formatMessageDetail($v);
		}
		return $message;
	}
	private function __formatMessageDetail($message) {
		unset($message['deleted_by']);
		$message['from_uname']	= getUserName($message['from_uid']);
		//$message['to_uname']	= getUserName($message['to_uid']);
		$message['from_face']	= getUserFace($message['from_uid']);
		//$message['to_face']		= getUserFace($message['to_uid']);
		$message['timestmap']	= $message['mtime'];
		$message['ctime']		= date('Y-m-d H:i', $message['mtime']);
		return $message;
	}
	// 返回用户的最新n条私信，并包含发送者和接受者的ID,姓名,头像
	/*public function inbox() {
		$this->data['type'] 	= $this->data['type']	? $this->data['type'] : 'all';
		$this->data['order']	= $this->data['order'] == 'ASC'	? 'message_id ASC' : 'message_id DESC';
		$message = model('Message')->getMessageListByUidFromApi($this->mid, $this->data['type'], $this->since_id, $this->max_id, $this->count, $this->page, $this->data['order']);
		$message = $this->__formatMessageList($message);
		return $message;
	}
	// 获取当前用户发送的最新私信列表
	public function outbox() {
		$this->data['order'] = $this->data['order'] == 'ASC'	? 'message_id ASC' : 'message_id DESC';
		$message = model('Message')->getOutboxByUidFromApi($this->mid, $this->since_id, $this->max_id, $this->count, $this->page, $this->data['order']);
		$message = $this->__formatMessageList($message);
		return $message;
	}*/
	public function box()
	{
		$this->data['type'] 	= $this->data['type']	? $this->data['type'] : array(1,2);
		$this->data['order']	= $this->data['order'] == 'ASC'	? '`mb`.`list_id` ASC' : '`mb`.`list_id` DESC';
		
		$message = model('Message')->getMessageListByUidForAPI($this->mid, $this->data['type'], $this->since_id, $this->max_id, $this->count, $this->page);
		$message = $this->__formatMessageList($message);
		// 设置私信为已读
		model('Message')->setMessageIsRead(null, $this->mid);
		return $message;
	}
	// 获取当前登录用户的私信详情
	public function show() {
		//$res = model('Message')->getDetailById($this->mid, $this->data['id'], $show_cascade);
		$res = model('Message')->getMessageByListId($this->data['id'], $this->mid, $this->since_id, $this->max_id, $this->count);
		return $this->__formatMessageList($res['data']);
	}
	// 发送私信
	public function create() {
		if ( empty($this->data['to_uid']) || empty($this->data['content']) ) {
			return 0;
		}
		$data['to'] 		= $this->data['to_uid'];
		$data['title']		= $this->data['title'];
		$data['content']	= $this->data['content'];
		return (int) model('Message')->postMessage($data, $this->mid);
	}
	// 回复私信
	public function reply() {
		if ( empty($this->data['id']) || empty($this->data['content']) ) {
			return 0;
		}
		return (int) model('Message')->replyMessage($this->data['id'], $this->data['content'], $this->mid);
	}
	// 删除私信
	public function destroy() {
		return (int) model('Message')->deleteMessageByListId($this->mid, t($this->data['message_id']));
	}
}
?>