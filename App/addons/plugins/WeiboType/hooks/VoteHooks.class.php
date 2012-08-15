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
class VoteHooks extends AbstractWeiboTypeHooks
{
    public $typeCode = 7;

    /**
     * home_index_middle_publish_type
     * 在发布微博底部可以自由添加类型,这里将添加一个图片的效果
     * @access public
     * @return void
     */
    public function _addWeiboTypeHtml()
    {
        $html = sprintf("<a href='javascript:void(0)' onclick='weibo.plugin.vote.click(this)' class='a52'><img class='icon_add_vote_d' src='%s' />投票</a>",$this->htmlPath."/html/images/zw_img.gif");
        echo $html;
    }

    protected function getRequireApp(){
        return "vote";
    }

    public function _weiboTypePublish($type_data)
    {
        $type_data = json_decode($type_data[0]);
        return json_encode($type_data->data);
    }



    public function _weiboTypeShow($typeData,$rand)
    {
        $this->assign(json_decode($typeData));
        $this->assign('htmlPath',$this->htmlPath."/html/images");
        $this->assign($rand);
        return $this->fetch("vote");
    }


    public function _getVoteForm(){
        echo W('VoteAdd',array('inner'=>false,'exit'=>false));
    }
}
