<?php
class GroupAction extends BaseAction
{

     protected function _initialize()
     {
        parent::_initialize();
        $this->assign('current','group');  //头部导航切换
     }

    //群首页
    public function index()
    {
        //关闭群微博时，自动跳转到群帖子页面；如果群帖子也没开启，自动跳转到群成员页面
        if($this->groupinfo['openWeibo']==0 && $this->groupinfo['openBlog']==1){
            redirect(U('group/Topic/index', array('gid' => $this->gid)));
        //如果群帖子也没开启，自动跳转到群成员页面
        }elseif($this->groupinfo['openWeibo']==0 && $this->groupinfo['openBlog']==0){
            redirect(U('group/Member/index', array('gid' => $this->gid)));
        }
        $data['weibo_menu'] = array(
	        ''  => L('all'),
	        'original' => L('original'),
        );
        Addons::hook('home_index_weibo_tab', array(0 => & $data['weibo_menu'], 'menu' =>  & $data['weibo_menu'], 'position'=>'other'));

        //群组微博
        $strType = h($_GET['weibo_type']);
        $data['type'] = $strType;
        $data['list'] = D('WeiboOperate')->getHomeList($this->mid, $this->gid, $strType, '', 10);

		$_last_weibo = reset($data ['list'] ['data']);
		$data['lastId'] = $_last_weibo['id'];
		$_since_weibo = end($data ['list'] ['data']);
		$data['sinceId'] =  empty($_since_weibo['id']) ? 0 : $_since_weibo['id'];

        $this->assign($data);
		$this->setTitle($this->groupinfo['name'].' - '.$this->groupinfo['intro']);
        $this->display();
    }

    //查找微博话题
    public function search()
    {
        $data['search_key']    = $this->_getSearchKey('k','group_weibo_search');
        $data['type']          = t($_REQUEST['type']);
        $data['type'] = empty($data['type']) ? "" : $data['type'];
        $data['list']          = D('WeiboOperate', 'group')->doSearch( $data['search_key'], $this->gid, $data['type'] );
        //$data['hotTopic']        = D('WeiboTopic','weibo')->getHot();
        $data['search_key_id'] = D('WeiboTopic', 'group')->getTopicId($data['search_key'], $this->gid);
        //$data['followTopic']   = D('Follow','weibo')->getTopicList($this->mid);
        $this->assign($data);
		$this->setTitle('搜群组: '.$data['search_key']);
        $this->display('index');
    }

    //查看微博详细
    public function detail()
    {
        $data['mini']      = D('GroupWeibo', 'group')->getOneLocation($_GET['id'], $this->gid);
        if(!$data['mini']) {
            $this->assign('jumpUrl', U('group/Group/index', array('gid'=>$this->gid)));            
            $this->error('提交错误参数');
        }
        $data['comment']   =  D('WeiboComment','weibo')->getComment($_GET['id'], $this->gid);
        $data['privacy'] = D('UserPrivacy','home')->getPrivacy($this->mid,$data['mini']['uid']);

        $this->assign( $data );
        $this->display();
    }

    // 加入该群
    public function  joinGroup()
    {
        if (isset($_POST['addsubmit'])) {
            $level = 0;
            $incMemberCount = false;
            if ($this->is_invited) {
                M('group_invite_verify')->where("gid={$this->gid} AND uid={$this->mid} AND is_used=0")->save(array('is_used'=>1));
                if (0 === intval($_POST['accept'])) {
                    // 拒绝邀请
                    exit;
                } else {
                    // 接受邀请加入
                    $level = 3;
                    $incMemberCount = ture;
                }
            } else if ($this->groupinfo['need_invite'] == 0) {
                // 直接加入
                $level = 3;
                $incMemberCount = ture;
            } else if ($this->groupinfo['need_invite'] == 1) {
                // 需要审批，发送私信到管理员
                $level = 0;
                $incMemberCount = false;
                // 添加通知
                $toUserIds = D('Member')->field('uid')->where('gid='.$this->gid.' AND (level=1 or level=2)')->findAll();
                foreach ($toUserIds as $k=>$v) {
                    $toUserIds[$k] = $v['uid'];
                }

                $message_data['title']   = "申请加入群组 {$this->groupinfo['name']}";
                $message_data['content'] = "你好，请求你批准加入“{$this->groupinfo['name']}” 群组，点此"
                                         ."<a href='".U('group/Manage/membermanage', array('gid'=>$this->gid,'type'=>'apply'))."' target='_blank'>"
                                         . U('group/Manage/membermanage', array('gid'=>$this->gid,'type'=>'apply')) . '</a>进行操作。';
                $message_data['to']      = $toUserIds;
                $res = model('Message')->postMessage($message_data,  $this->mid);

            }

            $result = D('Group')->joinGroup($this->mid, $this->gid, $level, $incMemberCount, $_POST['reason']);   //加入
            S('Cache_MyGroup_'.$this->mid,null);
            exit;
        }

        parent::base();

        $this->assign('joinCount', D('Member')->where("uid={$this->mid} AND level>1")->count());
        $member_info = D('Member')->field('level')->where("gid={$this->gid} AND uid={$this->mid}")->find();
        $this->assign('isjoin', $member_info['level']);  // 是否加入过或加入情况
        $this->display();
    }

    //退出该群对话框
    function quitGroupDialog() {
        $this->assign('gid',$this->gid);
        $this->display();
    }

    //退出该群
    function quitGroup() {
        if(iscreater($this->mid,$this->gid) || !$this->ismember) { echo '0';exit;} //$this->error('你没有权限'); //群组不可以退出
        $res = D('Member')->where("uid={$this->mid} AND gid={$this->gid}")->delete();  //用户退出
        if($res){
            D('Group')->setDec('membercount', 'id=' . $this->gid);     //用户数量减少1
            // 积分操作
            X('Credit')->setUserCredit($this->mid, 'quit_group');
            S('Cache_MyGroup_'.$this->mid,null);
            echo '1';
            exit;
        }
    }


    //删除该群
    function delGroup() {
        if (md5(strtoupper($_POST['verify'])) != $_SESSION['verify']) {
            exit('验证码错误');
        }
        if(!iscreater($this->mid,$this->gid))  exit('你没有权限');
        D('Group')->remove($this->gid);
        S('Cache_MyGroup_'.$this->mid, NULL);
        exit('1');
    }


    //删除群组对话框
    function delGroupDialog() {

        $this->assign('gid',$this->gid);
        $this->display();
    }

    function addShare_check(){

        $result = 1;

        $aimId = intval($_REQUEST['aimId']);
        $this->assign('aimId',$aimId);

        $test = $this->api->share_isForbid($this->mid,8,$aimId);

        if($test==-1){
            $result = -2;
        }

        echo $result;
    }
    function addShare(){
        $aimId = intval($_REQUEST['aimId']);
        $this->assign('aimId',$aimId);
        $group = D('group')->where("id='$aimId'")->field('name')->find();

        $this->assign('name',$group['name']);
        $this->assign($group);
        $this->assign('mid',$this->mid);
        $this->display();
    }

    function doaddShare(){
        $type['typeId'] = 8;
        $type['typeName'] = '群组';
        $type['alias'] = 'group';

        $info = h($_REQUEST['info']);
        $aimId = intval($_REQUEST['aimId']);

        $field = 'uid,name,logo,cid0,membercount';
        $data = D('group')->where("id='$aimId'")->field($field)->find();
        $data['logo'] = get_photo_url($data['logo']);
        $data['catagory'] = D('Category')->where("id=".$data['cid0'])->getField('title');

        //$data['name'] = h($_REQUEST['name']);
        $fids = $_REQUEST['fids'];


        $result = $this->api->share_addShare($type,$aimId,$data,$info,0,$fids);
        echo $result;
    }


}