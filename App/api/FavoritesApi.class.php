<?php
//微博收藏
class FavoritesApi extends Api{
	//获取当前用户的收藏列表
	function index(){
		return D('Favorite','weibo')->getList($this->mid , $this->since_id , $this->max_id , $this->count , $this->page);
	}
	// 添加收藏
	function create(){
        if(empty($this->id) && empty($this->mid)){
            return 0;
        }else{
		    return (int) D('Favorite','weibo')->favWeibo($this->id,$this->mid);
        }
	}
	// 删除一个收藏信息
	function destroy(){
        if(empty($this->id) && empty($this->mid)){
            return 0;
        }else{
		    return (int) D('Favorite','weibo')->dodelete($this->id,$this->mid);
        }
	}
	// 当前用户是否收藏了给定微博
	function isFavorite() {
		return (int) D('Favorite','weibo')->isFavorited($this->id,$this->mid);
	}
}
?>