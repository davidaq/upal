<?php
    /**
     * GiftAction
     * 礼物控制层
     *
     * @uses
     * @package
     * @version
     * @copyright 2009-2011 SamPeng
     * @author SamPeng <sampeng87@gmail.com>
     * @license PHP Version 5.2 {@link www.sampeng.cn}
     */
class IndexAction extends Action{
	    private $icopath="";
	    protected $app_alias;

	    public function _initialize(){
	    	//参数转义
	        $this->icopath = '../Public/images/ico/';
	        $this->assign('icopath',$this->icopath);

	        global $ts;
	        $this->app_alias = $ts['app']['app_alias'];
	    }

	    public function index(){
	    	$uid=array();
	        if($_GET['order']=='following'){
	        	 $following = M('weibo_follow')->field('fid')->where("uid={$this->mid} AND type=0")->findAll();
                 foreach($following as $v) {
                     $uid[] = $v['fid'];
                 }
                 $this->setTitle("我关注的人的{$this->app_alias}");
	        }else {
	        	$this->setTitle("最新{$this->app_alias}");
	        }
	   	    $this->__setAssign($uid);
	        $this->display();
	    }

        public function personal(){
            $this->__setAssign($this->uid);
            $this->assign('uid',$this->uid);
            $this->assign('name',getUserName($this->uid));
            $this->setTitle("我的{$this->app_alias}");
            $this->display();
        }

	    public function addPosterSort(){
	    	$posterTypeDao = D('PosterType');
	    	$posterType = $posterTypeDao->getType();
	    	$this->assign('posterType',$posterType);
	    	$this->setTitle("发布{$this->app_alias}");
	    	$this->display();
	    }

	    public function posterDetail(){
	    	$posterTypeDao = D('PosterType');
	    	$poster = D('Poster');
	    	$id = intval($_GET['id']);

	    	if($id == 0){
	    		$this->error("错误的信息地址.请检查后再访问");
                exit;
	    	}

	    	$posterData     = $poster->getPoster($id,$this->mid);
	        if(!$posterData){
	        	$this->assign('jumpUrl', U('poster/Index/index'));
                $this->error("这个信息被删除或者不允许查看");
                exit;
            }

	    	$posterType = $posterTypeDao->getType($posterData['pid']);

	    	$posterTypeExtraField = $posterTypeDao->getExtraField($posterType['extraField']);
	    	unset($posterType['extraField']);

	    	if($posterData['uid'] == $this->mid){
	    		$posterData['name'] = '我';
	    		$this->assign('admin',1);
	    	}else{
	    		$posterData['name'] = getUserName($posterData['uid']);
	    	}

	    	$this->assign('poster',$posterData);
	    	$this->assign('extraField',$posterTypeExtraField);
	    	$this->assign('type',$posterType);
	    	$this->display();
	    }

	    public function doDeletePoster(){
	    	$id = intval($_POST['id']);
	    	if(0 == $id){
	    		echo -3;
	    	}else{
	    		$poster = D('Poster');
	    		if($res = $poster->deletePoster($id,$this->mid)){
                    //积分
                    X('Credit')->setUserCredit($this->mid,'delete_poster');
	    		}
	    		echo $res;
	    	}
	    }
	    private function __setAssign($uid = null){
	       $poster = D('Poster');
	       $pid = intval($_GET['pid'])?intval($_GET['pid']):null;
	       $stid = intval($_GET['stid'])?intval($_GET['stid']):null;
           $posterData = $poster->getPosterList($pid,$stid,$uid);
           $this->getPosterType ($poster);
           $this->assign($posterData);
	    }

        private function getPosterType($poster){
            $posterTypeDao = D('PosterType');
            $posterSmallTypeDao = D('PosterSmallType');
	        $posterType = $posterTypeDao->getType();
	        foreach($posterType as $value){
	   	       $id = $value['id'];
	           if(isset($value['type']) && $id == intval($_GET['pid'])){
	        	  $posterSmallType = $posterSmallTypeDao->getPosterSmallType($value['type']);
	           }
	        }

           $posterSmallType = $this->getPosterCount($poster,$posterSmallType);
	       $this->assign('posterType',$posterType);
	       $this->assign('type',$posterSmallType);
        }

        private function getPosterCount($poster,$posterSmallType){
            $tableName = $poster->getTableName();
            //$otherWhere = $this->private;
            if(!empty($posterSmallType)){
                for($i=0;$i<count($posterSmallType);$i++){
                	//if(isset($otherWhere)){
                	//	$where = "type = {$posterSmallType[$i]['id']} AND ".$otherWhere;
                	//}else{
                		$where = "type = {$posterSmallType[$i]['id']}";
                	//}
                    $sql[] = "select '{$posterSmallType[$i]['id']}' as `id`,count(1) as count from  {$tableName} where {$where}";
                }
            }
            $sql = implode( ' union all ',$sql );
            $count = $poster->query($sql);
            $temp_array = array();
            foreach($count as $value){
            	$temp_array[$value['id']] = $value['count'];
            }
            $result = $posterSmallType;
            foreach ($result as &$value){
            	$value['count'] = $temp_array[$value['id']] ;
            }
            return $result;
        }


	   public function addPoster(){
	   	   $typeId = intval($_GET['typeId']);
	   	   if(empty($typeId))
	   	       $this->error('参数有误');

	       $posterTypeDao = D('PosterType');
	       $poster = $posterTypeDao->getType($typeId);
	       if(empty($poster)){
	           $this->error('参数有误');
	       }
	       $posterSmallTypeDao = D('PosterSmallType');
	       $posterSmallType = $posterSmallTypeDao->getPosterSmallType($poster['type']);
           $this->assign($poster);
           $this->assign('smallType',$posterSmallType);
           //初始化截止日期
           $this->assign('deadline',date("Y-m-d H:i:s",time()+90*24*3600));

           $this->setTitle("发布{$this->app_alias}");
	       $this->display();
	   }

	   public function editPoster(){
           $posterDao  = D('Poster');
	   	   $posterData = $posterDao->getPoster($_GET['id'],$this->mid);

	   	   $posterTypeDao = D('PosterType');
           $poster = $posterTypeDao->getType(intval($_GET['typeId']), intval($_GET['id']));
           if(empty($poster)){
               $this->error('参数有误');
           }
           $posterSmallTypeDao = D('PosterSmallType');
           $userInfo['areaid'] = $posterData['address_province'].','.$posterData['address_city'];
           $posterData['deadline'] && $posterData['deadline'] = date("Y-m-d H:i:s",$posterData['deadline']);
           $posterSmallType = $posterSmallTypeDao->getPosterSmallType($poster['type']);
           $this->assign('smallType',$posterSmallType);
           $this->assign('userInfo',$userInfo);
           $this->assign('poster',$posterData);
           $this->assign($poster);
           $this->display();
	   }

	   public function doEditPoster(){
            $dao = D('Poster');
            $condition['id']=intval($_POST['id']);

		    $map['title']      = t($_POST['title']);
	        $map['type']       = intval($_POST['type']);
	        $map['content']    = h($_POST['explain']);
	        $map['contact']    = t($_POST['contact']);

	        $address = explode(',',$_POST['areaid']);
	        $map['address_province'] = $address[0];
	        $map['address_city'] = $address[1];
	        if($_POST['deadline']){
                $map['deadline'] = $deadline = $this->_paramDate( $_POST['deadline'] );
                $sendPosterTime =$dao->where('id='.intval($_POST['id']))->getField('cTime');
	        	$deadline < $sendPosterTime && $this->error( "结束时间不得小于发布时间" );
	        }else{
	        	$map['deadline'] = NULL;
	        }

	        // 检查详细介绍
	        if (get_str_length($map['content']) <= 0) {
	        	$this->error('详细介绍不能为空');
	        }

	        $map = $this->_extraField($map,$_POST);
	        //得到上传的图片
	        $option = array();
	        if($_FILES['cover']['size']>0){
	        	$options['userId']   = $this->mid;
	        	$options['max_size'] = 2*1024*1024;//2MB
	        	$options['allow_exts'] = 'jpg,gif,png,jpeg,bmp';
                $cover  =   X('Xattach')->upload('poster_cover',$options);
                if($cover['status']){
                	$map['cover'] = $cover['info'][0]['savepath'].$cover['info'][0]['savename'];
                }else{
                	$this->error($cover['info']);
                }
	        }

	        //$map['private'] = isset($_POST['friend'])?$_POST['friend']:0;

	        $rs = $dao->where($condition)->save($map);
	        if(false !== $rs){
	        	$this->assign('jumpUrl',U('//posterDetail',array('id'=>$condition['id'])));
	        	$this->success("编辑成功");
	        	exit;
	        }else{
	        	$this->error('编辑失败');
	        }
	   }

	    private function _paramDate( $date ) {
	        $date_list = explode( ' ',$date );
	        list( $year,$month,$day ) = explode( '-',$date_list[0] );
	        list( $hour,$minute,$second ) = explode( ':',$date_list[1] );
	        return mktime( $hour,$minute,$second,$month,$day,$year );
	    }
	   public function doAddPoster(){
	   	$map['title']      = t(h($_POST['title']));
        $map['type']       = intval($_POST['type']);
        $map['pid']        = intval($_POST['pid']);
        $map['content']    = h($_POST['explain']);
        $map['contact']    = t($_POST['contact']);
        $map['uid']        = $this->mid;
        $map['cTime']      = time();
	    if($_POST['deadline']){
            $map['deadline'] = $deadline = $this->_paramDate( $_POST['deadline'] );
            $deadline < time() && $this->error( "结束时间不得小于发布时间" );
        }else{
            $map['deadline'] = NULL;
        }

        // 检查详细介绍
        if (get_str_length($map['content']) <= 0) {
        	$this->error('详细介绍不能为空');
        }

        $address = explode(',',$_POST['areaid']);
        $map['address_province'] = $address[0];
        $map['address_city'] = $address[1];

        $map = $this->_extraField($map,$_POST);
        //得到上传的图片
        $option = array();
        if($_FILES['cover']['size'] > 0) {
	        $options['userId'] = $this->mid;
	        $options['max_size'] = 2*1024*1024;//2MB
	        $options['allow_exts'] = 'jpg,gif,png,jpeg,bmp';
	        $cover  =   X('Xattach')->upload('poster_cover',$options);
            if($cover['status']){
            	$map['cover'] = $cover['info'][0]['savepath'].$cover['info'][0]['savename'];
            }else{
            	$this->error($cover['info']);
            }
        }
        //$map['private'] = isset($_POST['friend'])?$_POST['friend']:0;
        $dao = D('Poster');
        $rs = $dao->add($map);
        if($rs){
            //发微薄
            $_SESSION['new_poster']=1;
            //积分
            X('Credit')->setUserCredit($this->mid,'add_poster');
            $this->assign('jumpUrl',U('//posterDetail',array('id'=>$rs)));
            $this->success("发布成功,即将跳转到内容页");
        }else{
            $this->error("发布失败");
        }
	   }

	   private function _extraField($map,$post){
	   	for($i=1;$i<6;$i++){
	   		if(isset($post['extra'.$i]) && !empty($post['extra'.$i])){
	   			if(is_array($post['extra'.$i])){
	   				$map['extra'.$i] =implode(',',$post['extra'.$i]);
	   			}else{
                    $map['extra'.$i] = $post['extra'.$i];
	   			}

	   		}
	   	}
	   	return $map;
	   }
}
