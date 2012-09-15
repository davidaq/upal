<?php
class PublicTypeHooks extends Hooks
{
    protected static $validTypeAlias = array(
            '1'=>'图片',
            '3'=>'视频',
            '4'=>'音乐',
            '8'=>'博客',
            '7'=>'投票'
    );

    public function weibo_js_plugin()
    {
        // echo '<script type="text/javascript" src="'.Addons::createAddonUrl('WeiboType','loadJs').'"></script>';
        $weibo_type = array('1', '3', '4', '7', '8');

        //处理默认值
        $config = model('AddonData')->lget('weibo_type');
        $config = $this->_defaultConfig($config);
        if(!in_array(1,$config['open'])){
            $config['open'][] = 1;
        }
        if(!in_array(3,$config['open'])){
            $config['open'][] = 3;
        }
        if(!in_array(4,$config['open'])){
            $config['open'][] = 4;
        }
        foreach($weibo_type as $value){
            if(in_array($value,$config['open'])){
            echo '<script type="text/javascript" src="'.Addons::createAddonUrl('WeiboType','loadJs', array('type'=>$value)).'"></script>';
            }
        }
    }

    public function loadJs()
    {
//     	if(extension_loaded('zlib')){//检查服务器是否开启了zlib拓展
// 	    	ob_start('ob_gzhandler');
//         }
	  	header ("content-type: text/javascript; charset: UTF-8");//注意修改到你的编码
	  	header ("cache-control: must-revalidate");
        if(!defined('__PUBLIC__')){
            define('__PUBLIC__',SITE_PATH.'/public');
        }
	  	$offset = 60 * 60 * 24;//css文件的距离现在的过期时间，这里设置为一天
	  	$expire = "expires: " . gmdate ("D, d M Y H:i:s", time() + $offset) . " GMT";
	  	header ($expire);
	  	ob_start("compress");
	  	$config = model('AddonData')->lget('weibo_type');
	  	$js = array(
	  	        '1'=>array(__PUBLIC__.'/js/swf/swfupload.js',$this->path.'/html/image.js'),
	  	        '3'=>array($this->path.'/html/video.js'),
	  	        '4'=>array($this->path.'/html/music.js'),
	  	        // '5'=>array($this->path.'/html/file.js'),
	  	        '8'=>array($this->path.'/html/blog.js'),
	  	        '7'=>array($this->path.'/html/vote.js')
	  	        );

        //包含你的全部js文档
        $htmlPath = $this->htmlPath.'/html/images/';
        //处理默认值
        $config = $this->_defaultConfig($config);
        if(!in_array(1,$config['open'])){
            $config['open'][] = 1;
        }
        if(!in_array(3,$config['open'])){
            $config['open'][] = 3;
        }
        if(!in_array(4,$config['open'])){
            $config['open'][] = 4;
        }
        // foreach($js as $key=>$value){
        //     if(in_array($key,$config['open'])){
        //         foreach($value as $v){
        //             include $v;
        //         }
        //     }
        // }

        $type = intval($_GET['type']);
        if(in_array($type,$config['open'])){
            foreach($js[$type] as $v){
                include $v;
            }
        }

// 	  	if(extension_loaded('zlib')){
// 		    ob_end_flush();//输出buffer中的内容，即压缩后的css文件
// 	  	}
    }

    /**
     * 后台配置
     */
    public function config(){
        $config = model('AddonData')->lget('weibo_type');
        //博客和投票需要检测是否安装了该应用
        $mustCheck = array('blog'=>false,'vote'=>false);
        $mustCheckIndex = array('7'=>'vote','8'=>'blog');
        $installed = model('App')->getAllApp();
        foreach($installed as $value){
            if(isset($mustCheck[$value['app_name']])){
                $mustCheck[$value['app_name']] = $value['status']>0?true:false;
            }
        }
       $valid = self::$validTypeAlias;
       foreach($valid as $key=>$value){
           if(isset($mustCheck[$mustCheckIndex[$key]])){
               if($mustCheck[$mustCheckIndex[$key]]){
                   continue;
               }else{
                   unset($valid[$key]);
               }
           }
       }
       $this->assign('config',$config);
       $this->assign('validType',$valid);
       $this->assign('alias',self::$validTypeAlias);
       $this->display('config');
    }

    public function saveConfig(){
        if(empty($_POST)) return;
        $data = array();
        foreach($_POST['open'] as $key=>$value){
            $data['open'][] = $value;
        }
        if(empty($data['open'])) $data['open'] = array();


        foreach($_POST['image'] as $key=>$value){
            if($key == 'size' || $key == 'limit'){
                $data['image'][$key] = intval($value);
            }else{
                $data['image'][$key] = h($value);
            }
        }
        foreach($_POST['file'] as $key=>$value){
            if($key == 'size' ){
                $data['file'][$key] = intval($value);
            }else{
                $data['file'][$key] = h($value);
            }
        }

        $res = model('AddonData')->lput('weibo_type', $data);
        if ($res) {
            $this->assign('jumpUrl', Addons::adminPage('config'));
            $this->success();
        } else {
            $this->error();
        }
        exit;
    }

    private function _defaultConfig($config){
        $config['image']['limit'] = empty($config['image']['limit'])?9999999:$config['image']['limit'];
        $config['image']['size2'] = empty($config['image']['size'])?"无限制":$config['image']['size']."KB";
        $config['image']['size'] = empty($config['image']['size'])?9999999:$config['image']['size'];
        $config['image']['ext'] = empty($config['image']['ext'])?"jpg;png;jpeg;gif":$config['image']['ext'];

        $config['file']['ext'] = empty($config['file']['ext'])?"jpg;gif;png;jpeg;bmp;zip;rar;doc;xls;ppt;docx;xlsx;pptx;pdf":$config['file']['ext'];

        $ext = explode(';', $config['image']['ext']);
        $extRes = array();
        foreach($ext as $value){
            $extRes[] = '*.'.$value;
        }
        $ext= explode(';',$config['file']['ext']);
        $config['file']['ext']  = json_encode($ext);
        $config['image']['ext'] = implode(';',$extRes);
        $config['file']['ext2'] = implode(',',$ext);
        $config['file']['size2'] = empty($config['file']['size'])?"无限制":$config['file']['size']."KB";

        return $config;
    }
}
