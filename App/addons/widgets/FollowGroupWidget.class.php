<?php
/**
 * 选择好友Widget
 */
class FollowGroupWidget extends Widget{

	/**
	 * 选择好友Widget
	 * 
	 * $data的参数:
	 * array(
	 * 	'name'(可选)	=> '表单的name', // 默认为"fri_ids"
	 * )
	 * 
	 * @see Widget::render()
	 */
	public function render($data){

		$follow_group_status = D('FollowGroup','weibo')->getGroupStatus($data['uid'],$data['fid']);

		foreach($follow_group_status as $k => $v){
			$v['title']      = (strlen($v['title'])+mb_strlen($v['title'],'UTF8'))/2>6?getShort($v['title'],3).'...':$v['title'];
			$data['status'] .= $v['title'].',';
			if(!empty($follow_group_status[$k+1]) && (strlen($data['status'])+mb_strlen($data['status'],'UTF8'))/2>=13){
				$data['status'] .= '···,';
				break;
			}
		}
        $data['status'] = substr($data['status'],0,-1);

        $content = $this->renderFile(ADDON_PATH . '/widgets/FollowGroup.html',$data);

        return $content;

    }
}
?>