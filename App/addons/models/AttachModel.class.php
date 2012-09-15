<?php
/**
 * 附件模型
 *
 * @author melec
 */
class AttachModel extends Model {
	protected $tableName = 'attach';

	/**
	 * 获取附件数据
	 *
	 * @param array|string $map   查询条件
	 * @param string       $field 默认*
	 * @param int          $limit 默认20
	 * @param string       $order 默认以附件ID降序排列
	 */
	public function getAttachByMap($map, $field = '*', $limit = 20, $order = 'id DESC') {
		return $this->where($map)->field($field)->order($order)->findPage($limit);
	}

	/**
	 * 获取所有相关附件数据
	 *
	 * @param array|string $map   查询条件
	 * @param string       $field 默认*
	 * @param string       $order 默认以附件ID降序排列
	 */
	public function getAllAttachByMap($map, $field = '*', $order = 'id DESC'){
        return $this->where($map)->field($field)->order($order)->findAll();
	}

	/**
	 * 获取指定id的附件地址
	 * 如果id不为数组，则该id必须为数字。并且只取该id的附件
	 * 如果id为数组,格式=》array(附件id)
	 * @param unknown_type $id
	 */
	public function getAllAttachById($id){
	    if(!is_array($id)){
	        $map['id'] = intval($id);
	        $data = $this->where($map)->field('id,savepath,savename')->find();
	        return UPLOAD_URL.'/'.$data['savepath'].$data['savename'];
	    }else{
	        $map['id'] = array('in',$id);
	        $data = $this->where($map)->field('id,savepath,savename')->findAll();
	        $result = array();
	        foreach($data as $value){
	            $result[$value['id']] = UPLOAD_URL.'/'.$value['savepath'].$value['savename'];
	        }
	        return $result;
	    }

	}

	/**
	 * 删除附件
	 *
	 * @param array   $ids       附件ID
	 * @param boolean $with_file 是否删除文件
	 * @return boolean
	 */
	public function deleteAttach($ids, $with_file = false, $with_thumb = false) {
		if( empty($ids) ) return false;

		$attaches  = array();
		$map['id'] = array('in', $ids);

		if ( $with_file ) {
			$attaches = $this->where($map)->findAll();
			if ( empty($ids) )
				return false;
		}

		// 删除表记录
		if ( !$this->where($map)->delete() )
			return false;

		// 删除文件
		if ($with_file) {
			foreach ($attaches as $v) {
				$path = SITE_PATH . '/data/uploads/' . $v['savepath'] . $v['savename'];
				if ( is_file($path) )
					unlink($path);
				if($with_thumb){
					$middle_path = SITE_PATH . '/data/uploads/' . $v['savepath'] . 'middle_' .$v['savename'];
					$small_path = SITE_PATH . '/data/uploads/' . $v['savepath'] . 'small_' .$v['savename'];
					if ( is_file($middle_path) )
						unlink($middle_path);
					if ( is_file($small_path) )
						unlink($small_path);
				}
			}
		}

		return true;
	}

	/**
	 * 获取已有所有附件的扩展名
	 *
	 * @return array
	 */
	public function enumerateExtension() {
		$sql = "SELECT `extension` FROM " . C('DB_PREFIX') . "attach GROUP BY `extension`";
		$res = $this->query($sql);
		return getSubByKey($res, 'extension');
	}
}