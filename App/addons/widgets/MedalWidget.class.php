<?php
/**
 * 勋章Widget
 * 
 * @author daniel <desheng.young@gmail.com>
 */
class MedalWidget extends Widget {
	
	/**
	 * 勋章Widget
	 * 
	 * $data接受的参数:
	 * arrary(
	 * 	'uid'(可选)			=> $uid, // 用户ID(默认当前用户)
	 * 	'show_alert'(可选)	=> 1,    // 1:显示提示(默认) 0:不显示提示
	 * )
	 * 
	 * @see Widget::render()
	 */
	public function render($data) {
		if(!isset($data['async']) || $data['async'] != 1){
			$data['uid']		= intval($data['uid']);
			$data['uid']		= $data['uid'] > 0 ? $data['uid'] : $_SESSION['mid'];
			$data['show_alert']	= isset($data['show_alert']) ? $data['show_alert'] : '1';
			
			$medal_data = model('Medal')->getMedalWidgetData($data['uid']);
			$data = array_merge($data, $medal_data);
			unset($medal_data);
		}else{
			unset($data['async']);
			$data['param'] = urlencode(serialize($data));
			$data['async'] = 1;
		}

		$content = $this->renderFile(ADDON_PATH . '/widgets/Medal.html', $data);
		return $content;
	}

	private function __changeArrayKey($input, $key = 'medal_id') {
		$output = array();
		foreach ($input as $v) {
			$output[$v[$key]] = $v;
		}
		return $output;
	}
}