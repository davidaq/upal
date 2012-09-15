<?php
class SquareAppShowHooks extends Hooks
{
	private $apps = array();

	// 向head中输出tab的css
	public function public_head()
	{
		echo '<style type="text/css">
		.ico_blog, .ico_blog_on, .ico_photo, .ico_photo_on, .ico_group, .ico_group_on
		{background: url("' . __THEME__ . '/images/ico_sidebar.png") no-repeat scroll 0 0 transparent; display: inline-block;}
		.ico_blog{background-position:-26px -95px}.ico_blog_on{background-position:-26px -95px}
		.ico_photo{background-position:-26px -114px}.ico_photo_on{background-position:-26px -114px}
		.ico_group{background-position:-26px -190px}.ico_group_on{background-position:-26px -190px}
		</style>';
	}

	public function home_square_tab($param)
	{
		$param['menu'] = $this->_squareTab();
	}

	public function header_square_tab($param)
	{
		$param['menu'] = $this->_squareTab();
	}

	private function _squareTab()
	{
		static $_menu = array();
		
		if (empty($_menu)) {
			$square_config = model('AddonData')->lget('square_app_show');
			$apps_list = model('App')->getAllApp('app_name,app_alias,status');
			
			foreach ($apps_list as $value) {
				if ($square_config[$value['app_name']] && $value['status']) {
					$_menu[] = array(
						'act' => $value['app_name'],
						'name' => $value['app_alias'],
						'param' => array(
							'addon' => 'SquareAppShow',
							'hook'	=> 'home_square_show',
						)
					);
					$this->apps[$value['app_name']] = $value['app_name'];
				}
			}
		}
		return $_menu;
	}

	public function home_square_show()
	{
		if (in_array(ACTION_NAME, $this->apps)) {
			$function_name = '_' . ACTION_NAME;
			if (method_exists($this, $function_name)) {
				$this->assign('app_show', ACTION_NAME);
				$this->$function_name();
			}
		}
	}

	private function _photo()
	{
		require_once SITE_PATH . '/apps/photo/Common/common.php';
		// 热门推荐相册
		$data['IsHotList'] = IsHotList();
		// 照片
		$order  = NULL;
        switch( $_GET['order'] ) {
        	case 'new':    //最新排行
       			$order = 'cTime DESC';
                break;
            default:      //默认热门排行
                $order = 'readCount DESC';
        }
		$map['privacy']	=	1; //所有人公开的图片

		//获取配置参数
		$config = getConfig();
		$data['photos'] = D('Photo', 'photo')->where($map)->order($order)->findPage(15);
		$data['photo_preview'] = $config['photo_preview'];
		$this->assign($data);
		$this->display('photo');
	}

	private function _blog()
	{
		// 推荐日志
		$map          = array();
		$map['isHot'] = 1;
        $map['status']= 1;
        $order        = 'rTime DESC';
		$data['relist'] = M('blog')->where( $map )->order( $order )->findAll();
		// 日志列表
		$field = '*';
        $map          = array();
        $map['private'] = array('neq',2);
        $map['status']= 1;
        switch( $_GET['order'] ) {
            case 'new':    //最新排行
                $order = 'cTime DESC';
                break;
            default:      //默认热门排行
                $order = 'hot DESC';
        }
		$data += M('blog')->field( $field )->where( $map )->order( $order )->findPage(20) ;
		$this->assign($data);
		$this->display('blog');
	}

	private function _group()
	{
		require_once SITE_PATH . '/apps/group/Common/common.php';
		// 热门群组排行
		$data['hot_group_list']  = D('Group', 'group')->getHotList();
		// 热门群标签
		$data['hot_tags_list']   = D('GroupTag', 'group')->getHotTags();
		// 群组热帖
		$data['hot_thread_list'] = D('Topic', 'group')
		                           ->field('topic.id,topic.gid,topic.title,topic.dist,post.content')
		                           ->table(C('DB_PREFIX').'group_topic as topic
                                            left join '.C('DB_PREFIX').'group_post as post
                                            on topic.id = post.tid')
                                   ->where('post.istopic = 1 AND topic.is_del=0 and post.is_del=0 AND topic.replytime>' . (time()-30*24*3600))
		                           ->order('topic.viewcount+topic.replycount DESC,topic.id DESC')
                                   ->limit(10)->findAll();
        // 群组分类 - 多级
        $data['category_tree']   = D('Category', 'group')->_makeTree(0);
        foreach ($data['category_tree'] as $k => $v) {
            $data['category_tree'][$k]['count'] = D('Group', 'group')->where("cid0={$v['a']} AND is_del=0")->count();
        }
        foreach($data['hot_thread_list'] as $key=>$value){
            $data['hot_thread_list'][$key]['content'] = html_entity_decode($value['content']);
        }

        //查询数据库得到统计数据
        $data['count']['member'] = array_pop(D('Member','group')->field('count(distinct uid)')->find()) ;
        $data['count']['group']  = D('Group','group')->where('is_del =0')->count();
        $data['count']['topic']  = D('Topic','group')->where('is_del =0')->count();

		// 群组分类 - 一级
		//$data['category_tree']   = D('Category', 'group')->where("pid=0")->findAll();

		// 热门标签推荐
        $data['reTags']  = D('GroupTag', 'group')->getHotTags('recommend');
		$this->assign($data);
		$this->display('group');
	}

	/* 插件后台配置项 */
	public function config()
	{
		
		$apps_show_list = array('photo', 'blog', 'group');
		
		$square_app_show = model('AddonData')->lget('square_app_show');
		
		$list= model('App')->getAllApp('app_name,app_alias,status');
		
		$this->assign('apps_list', $list);
		$this->assign('apps_show_list', $apps_show_list);
		$this->assign($square_app_show);
		$this->display('index');
	}

	public function saveConfig($param)
	{
		if ($_POST['__hash__'])
			unset($_POST['__hash__']);

		$apps_show_list = array('photo', 'blog', 'group');
		foreach ($apps_show_list as $v) {
			$post[$v] = intval($_POST[$v]);
		}
		$res = model('AddonData')->lput('square_app_show', $post);
		
		
		if ($res) {
			F('Cache_App',null);
			$this->assign('jumpUrl', Addons::adminPage('config'));
			
    		$this->success();
		} else {
    		$this->error();
		}
	}
}