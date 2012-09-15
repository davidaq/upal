<?php
/**
 * 举报模型
 *
 * @author JunStar <wangjuncheng@zhishisoft.com>
 */
class DenounceModel extends Model {
	/**
	 * [后台]获取相应类型的举报列表
	 */
	public function getFromList($map){
		return $this->where( $map )->order('ID DESC')->findpage();
	}


	/**
	 * [后台]进入回收站
	 */
	public function deleteDenounce( $ids ){
        $weiboIds = $this->_getWeiboIdsByDenounce($ids);
        $weibo_map['weibo_id'] = array('in',$weiboIds);
        $weibo_set=D('Weibo','weibo')->where ( $weibo_map )->save ( array('isdel'=>4) );
		return $weibo_set && $this->where($this->_paramMaps($ids))->save(array('state'=>'1'));
	}
	/**
	 * [后台]通过审核
	 */
	public function reviewDenounce( $ids ){
        $weiboIds = $this->_getWeiboIdsByDenounce($ids);
		$weibo_map['weibo_id'] = array('in',$weiboIds);
        $weibo_set = M('Weibo')->where ( $weibo_map )->save ( array('isdel'=>3) );
		return $weibo_set && $this->where($this->_paramMaps($ids))->save(array('state'=>'2'));
	}

	public function autoDenounce($id,$uid,$content,$type='weibo'){
	    $map['from'] = 'weibo';
	    $map['aid'] = $id;
	    $map['uid'] = '0';
	    $map['fuid'] = $uid;
	    $map['content'] = $content;
	    $map['reason'] = L('content_need_filter');
	    $map['ctime'] = time();
	    $map['state'] = '1';
	    $weibo_map['weibo_id'] = $id;
	    D('Weibo','weibo')->where ( $weibo_map )->save ( array('isdel'=>1) );
	    return M( 'Denounce' )->add( $map );
	}

	/**
	 * [各应用]获取已经被举报并且管理员将其进入回收站的各应用的id值
	 * $type参数为空则返回一个数组
	 * $type参数不为空则返回一个以逗号隔开的id字符串
	 */
	public function getIdsDenounce( $from,$type='' ){
		$map['from'] = $from;
		$map['state'] = '1';
		$ids = getSubByKey( $this->where( $map )->field('aid')->findall(),'aid' );
		if( $type ){
			$ids = implode(',', $ids);
		}
		return $ids;
	}

	private function _getWeiboIdsByDenounce($ids){
	    $data = $this->where($this->_paramMaps($ids))->field('aid')->findAll();
	    $weibo_id = getSubByKey($data,'aid');
	    return $weibo_id;
	}

	private function _paramMaps($ids){
	    $ids = is_array ( $ids ) ? $ids : explode ( ',', $ids );
	    if (empty ( $ids ))
	        return false;
	    $map ['id'] = array ('in', $ids );
	    return $map;
	}
}