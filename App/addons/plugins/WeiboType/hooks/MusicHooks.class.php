<?php
require_once('AbstractWeiboTypeHooks.class.php');
/**
 * ImageHooks 
 * 图片微博插件
 * 可以上传多张图片
 * @uses Hooks
 * @package 
 * @version $id$
 * @copyright 2001-2013 SamPeng 
 * @author SamPeng <penglingjun@zhishisoft.com> 
 * @license PHP Version 5.2 {@link www.sampeng.org}
 */
class MusicHooks extends AbstractWeiboTypeHooks
{
    public $typeCode = 4;

    /**
     * home_index_middle_publish_type 
     * 在发布微博底部可以自由添加类型,这里将添加一个音乐的效果
     * @access public
     * @return void
     */
    public function _addWeiboTypeHtml()
    {
        $html = sprintf("<a href='javascript:void(0)' onclick='weibo.plugin.music.click(this)' class='a52'><img class='icon_add_music_d' src='%s' />音乐</a>",$this->htmlPath."/html/images/zw_img.gif");
        echo $html;
    }


    public function _weiboTypePublish($type_data)
    {
	    $typedata['songurl']  = $type_data;
        return $typedata;

    }
    
    public function _weiboTypeShow($typeData,$rand)
    {
        $this->assign('data',$typeData);
        $this->assign('rand',$rand);
        $res = $this->fetch('music');
        return $res;
    }




    /**
     * uploadImage 
     * 上传图片接受处理
     * @access public
     * @return void
     */
    public function _addMusic()
    {
    	if(preg_match('/http\:\/\/.+(WAV|MP3|MIDI|MID|MMF|WMA|AMR|AAC)\??.*/i', $_POST['url'])){
            $return['boolen'] = 1;
            $return['data']   = $_POST['url'];
            $return['short']  = getShortUrl($_POST['url']);
            $return['publish_type'] = $this->typeCode;
        }else{
            $return['boolen'] = 0;
            $return['message'] = L('add_filed');
        }
        exit( json_encode($return) );
      
    }
}
