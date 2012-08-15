<?php
/**
 * 来访的人
 * 
 * @author daniel <desheng.young@gmail.com>
 */
class VisitorWidget extends Widget
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
		if (1 != model('Xdata')->get('siteopt:site_user_visited')) {
            return '';
		}
		$data['title']  = $data['title'] ? $data['title'] : L('last_visitor');
		$data['uid']	= $data['id'] ? intval($data['id']) : $GLOBALS['ts']['user']['uid'];
		$data['list'] = M('user_visited')->field('uid')
						->where("fid={$data['uid']} AND ctime>0")
						->order('ctime DESC')->limit(6)->findAll();

		$content = $this->renderFile(ADDON_PATH . '/widgets/Visitor.html', $data);

		return $content;
	}
}