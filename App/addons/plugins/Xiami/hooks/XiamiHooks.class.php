<?php
class XiamiHooks extends Hooks
{
    protected static $validTypeAlias = array(
            '20'=>'虾米'
    );

    public function weibo_js_plugin()
    {
        echo '<script type="text/javascript" src="'.__ROOT__.'/addons/plugins/Xiami/html/xiami.js'.'"></script>';
        echo '<style>.xiami_s_r{padding:0px 10px 10px 10px;margin-top:-10px;}
.xiami_s_r ul li a{ display:block; height:25px; line-height:25px; width:400px; overflow:hidden}
.xiami_s_r ul li a:hover{ background:#E6F2FF; text-decoration:none}</style>';
    }

    public function home_index_middle_publish_type()
    {
        $html = sprintf("<a href='javascript:void(0)' onclick='weibo.plugin.xiami.click(this)' class='a52'><img class='icon_add_music_d' src='%s' />虾米</a>",$this->htmlPath."/html/zw_img.gif");
		echo $html;
    }


    public function searchmusic(){
        $page = $_POST['page'];
        if($page ==''){$page = '1';}
        $key = urlencode($_POST['key']);
        $content = file_get_contents("http://www.xiami.com/app/nineteen/search/key/".$key."/page/".$page."/size/1?random=1123132112132");
        $arr = json_decode($content,true);
        if($arr[total] == '0'){exit('没找到相关信息');}
        $numpage = ceil($arr[total]/8);
        if($numpage > $page){
            $pageshow = '<a href="javascript:" onclick="weibo.plugin.xiami.searchmusic('.($page+1).')">下一页</a> ';
        }
        if($page > 1){
            $pageshow =$pageshow.' <a href="javascript:" onclick="weibo.plugin.xiami.searchmusic('.($page-1).')">上一页</a>';
        }
        echo '<span>共找到'.$arr[total].'首歌曲。共 '.$numpage.'页。当前 ：'.$page.'页。'.$pageshow.'</span><ul>';
        $arr = $arr[results];
        foreach($arr as $key =>$song){
            echo '<li><a href="javascript:" onclick="weibo.plugin.xiami.add_music(\''.$song[song_id].'\',\''.urldecode($song[song_name]).'\',\''.urldecode($song[artist_name]).'\')">'.urldecode($song[song_name]).'--'.urldecode($song[artist_name]).'</a></li>';
        }
        echo '</ul>';


       // print_r($arr);
       // exit($content);
    }

    public function weibo_type($param)
    {
        if($param[typeId] =='20'){
            $res = &$param['result'];
            $typeData = $param['typeData'];
            $data[songid] = $typeData;

            if($data){
                $res['type'] = $param[typeId];
                $res['type_data'] =serialize($data);
            }else{
                $res['type'] ='';
            }
            
            }

    }

    public function weibo_type_parse_tpl($param)
    {
        $type     = $param['typeId'];
        $typeData = $param['typeData'];
        $rand     = $param['rand'];
        $res = &$param['result'];
        if($type =='20'){
            $res = '<span class="xiami"><embed src="http://www.xiami.com/widget/8230560_'.$typeData['songid'].'/singlePlayer.swf" type="application/x-shockwave-flash" width="257" height="33" wmode="transparent" /></span>';
        }else{
            
        }
    }
}
