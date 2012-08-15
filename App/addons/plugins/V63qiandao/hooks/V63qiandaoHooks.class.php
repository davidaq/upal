<?php
class V63qiandaoHooks extends Hooks
{
	public function init()
	{
	}

    
    public function home_index_right_top(){
        
        $isq = '0';
        $pM = M('v63qiandao');
        $where[uid] = $this->mid;
        $this->assign('num',$pM->where($where)->count());
        $year = date("Y");
        $month = date("m");
        $day = date("d");
        
        $dayBegin = mktime(0,0,0,$month,$day,$year);//当天开始时间戳
        $dayEnd = mktime(23,59,59,$month,$day,$year);//当天结束时间戳
        
        $map['time'] = array(array('gt',$dayBegin),array('lt',$dayEnd));
        $map['uid'] = $this->mid;
        
        if(is_array($pM->where($map)->select())){
            $isq = 1;
        }
        
        $this->assign("day",$day);
        $this->assign("isq",$isq);
        $this->display("qd");
    }
    
    public function qd(){
        $jarr = service("Credit")->getCreditType();
        $addata = model('AddonData')->lget('v63qiandao');
        foreach($jarr as $key=>$value){
            if($value[name] == $addata[type][jttype]){
                $jfname[name] = $value[alias];
            }
        }
        
        $jfname[shu] = $addata[type][jfsl];
        
        $pM = M('v63qiandao');
        $where[uid] = $this->mid;
        $this->assign('num',$pM->where($where)->count());
        $this->assign('j',$jfname);
        $this->display("qdd");
    }
    public function qddo(){
        global $ts;
        $uid = $ts[user][uid];
        $username = $ts[user][uname];
        $say = strip_tags($_POST['say']);
        $xq = strip_tags($_POST['xq']);
        
        
        $year = date("Y");
        $month = date("m");
        $day = date("d");
        
        $dayBegin = mktime(0,0,0,$month,$day,$year);//当天开始时间戳
        $dayEnd = mktime(23,59,59,$month,$day,$year);//当天结束时间戳
        
        $map['time'] = array(array('gt',$dayBegin),array('lt',$dayEnd));
        $map['uid'] = $uid;
        $pM = M('v63qiandao');
        if(is_array($pM->where($map)->select())){exit("您已经签到了");}
        
        $data[uid] = $uid;
        $data[num] = $pM->where($data)->count();
        $data[time] = time();
        $data[username] = $username;
        $data[xq] = $xq;
        $data[say] = $say;
        
        $wd[ctime] = time();
        $wd[uid] = $uid;
        $wd['content'] =  "#每日签到# <img width='25' height='25' src='addons/plugins/V63qiandao/html/face/".$xq.".gif'/> ".$say;
        M('weibo')->add($wd);
        
        if($pM->add($data)){
            $addata = model('AddonData')->lget('v63qiandao');
            service("Credit")->setUserCredit($this->mid,array($addata[type][jttype]=>$addata[type][jfsl]));
            echo '签到成功';
        }else{
            echo '签到失败';
        }
        
    }
    
	/* 插件后台管理项 */
    public function set(){
        
        if($_POST){
            $data[type][jttype] =$_POST[jttype];
            $data[type][jfsl] =$_POST[jfsl];
            model('AddonData')->lput('v63qiandao',$data,TRUE)?true:false;
            $this->success();
        }
        global $ts;
        //$moneyType = service("Credit")->getCreditType(); 
        $this->assign('set',model('AddonData')->lget('v63qiandao'));
        $this->display("set");
        
    }
    
    public function log(){
        import("ORG.Util.Page");
        $this->display("list");
    }
}