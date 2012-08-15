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
class BlogHooks extends AbstractWeiboTypeHooks
{
    public $typeCode = 8;

    /**
     * home_index_middle_publish_type
     * 在发布微博底部可以自由添加类型,这里将添加一个图片的效果
     * @access public
     * @return void
     */
    public function _addWeiboTypeHtml()
    {
        $html = sprintf("<a href='javascript:void(0)' onclick='weibo.plugin.blog.click(this)' class='a52'><img class='icon_add_blog_d' src='%s' />博客</a>",$this->htmlPath."/html/images/zw_img.gif");
        echo $html;
    }


    protected function getRequireApp(){
        return "blog";
    }

    public function _weiboTypePublish($type_data)
    {
        if(is_array($type_data)&&count($type_data) == 1){
            $type_data = array_pop($type_data);
        }
        if(is_array($type_data)){
            $res = array_filter($type_data);
            if(empty($res)) return false;
            return $res ;
        }else{
            return $type_data;
        }
    }



    public function _weiboTypeShow($typeData,$rand)
    {
        //如果数组中picurl索引不存在，则一定是多维数组，一定是多张图片
        $this->assign('data',$typeData);
        $this->assign('rand',$rand);
        $res = $this->fetch('blog');
        return $res;
    }
}
