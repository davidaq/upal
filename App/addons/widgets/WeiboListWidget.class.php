<?php
class WeiboListWidget extends Widget
{
	public function render($data)
	{
		//参数获取
		$data['type'] = $data['type'] ? $data['type'] : 'normal';
		$data['insert'] = 1 == $data['insert'] ? $data['insert'] : 0;
		switch($data['simple']){
		    case 0:
		        $template = "WeiboList.html";
		        break;
		    case 1:
		        $template = "WeiboNoLinkList.html";
		        break;
		    case 2:
		        $template = "GroupWeiboList.html";
		        break;
		}
		
		//开启举报按钮s
		$data['denounce'] = 1;
		
		//widget模版路径
		$templateFile = ADDON_PATH . '/widgets/'.$template;

		//通过插件.处理微博列表数据
		Addons::hook('weibo_weibolist_data', array(&$data));

		//通过插件.改变widget模版路径
		Addons::hook('weibo_weibolist_template', array(&$templateFile));

		//渲染widget数据
		return $this->renderFile($templateFile, $data);
	}
}