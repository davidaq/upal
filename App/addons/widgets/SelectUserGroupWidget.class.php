<?php
/**
 * 选择用户组Widget
 * 
 * @author daniel <desheng.young@gmail.com>
 */
class SelectUserGroupWidget extends Widget {
	
	/**
	 * 选择用户组Widget
	 * 
	 * $data接受的参数:
	 * arrary(
	 * 	'user_group_id'(可选) => $user_group_id, // 已选中的用户组ID(默认为空)
	 * 	'uid'(可选)			 => $uid,			// 用户ID(默认为空), 该用户所在的用户组将被选中. 当user_group_id无效时有效.
	 * )
	 * 
	 * @see Widget::render()
	 */
	public function render($data) {
		$data['user_groups'] = model('UserGroup')->getUserGroupByMap('', 'user_group_id,title');
		$data['selected']	= array();

		//指定已选中的用户组ID
		if ( !empty($data['user_group_id']) ) {
			$data['selected'] = is_array($data['user_group_id']) ? $data['user_group_id'] : explode(',', $data['user_group_id']);
		}
		
		//根据用户ID获取已选中的用户组
		if ( !empty($data['uid']) && empty($data['selected']) ) {
			$data['selected'] = model('UserGroup')->getUserGroupByUid( intval($data['uid']) );
			$data['selected'] = getSubByKey($data['selected'], 'user_group_id');
		}

		$content = $this->renderFile(ADDON_PATH . '/widgets/SelectUserGroup.html', $data);
		return $content;
	}
}