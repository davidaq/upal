<?php
/**
 * 可能认识的人Widget
 *
 * @author daniel <desheng.young@gmail.com>
 */
class RelatedUserWidget extends Widget
{
	/**
	 * 可能认识的人 = 不为好友的“好友的好友” || 不为好友的“IP相近用户”
	 *
	 * $data接受的参数:
	 * array(
	 * 	'uid'(可选)		=> $uid,	// 用户ID(默认当前用户)
	 * 	'limit'(可选)	=> $limit,	// 展示的数量(默认为3x3=9个), 用户点击"关注"后自动补全
	 * 	'max'(可选)		=> $max,	// 一次搜索获取的最大数结果数(默认100)
	 * 	'title'(可选)	=> $title,	// 标题(默认"可能感兴趣的人")
	 * )
	 *
	 * @see Widget::render()
	 */
	public function render($data)
	{
		if (!isset($data['async']) || $data['async'] != 1) {
			global $ts;
			$data['uid']	= isset($data['uid'])	? intval($data['uid'])	 : $ts['user']['uid'];
			$data['limit']	= isset($data['limit']) ? intval($data['limit']) : 3;
			$data['max']	= isset($data['max'])	? intval($data['max'])	 : 100;
			$data['title']	= isset($data['title']) ? $data['title'] 		 : L('may_interest');
			$data['user'] 	= model('Friend')->getRelatedUser($data['uid'], $data['max']);
			if (empty($data['user']))
				exit;
		} else {
			unset($data['async']);
			$data['param'] = urlencode(serialize($data));
			$data['async'] = 1;
		}
		$data['oldUser'] = $data['user'];

		$content = $this->renderFile(ADDON_PATH . '/widgets/RelatedUser.html', $data);

		return $content;
	}
}