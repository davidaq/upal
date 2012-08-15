<?php
/**
 * 模版模型
 *
 * @author daniel <desheng.young@gmail.com>
 */
class TemplateModel extends Model {
	protected $tableName = 'template';

	/**
	 * 获取模板列表
	 *
	 * @param array|string $map   查询条件
	 * @param string       $order 排序   默认'name ASC,tpl_id ASC'
	 * @param int          $limit 一次查询条数  默认30
	 * @return array
	 */
	public function getTemplate($map = array(), $order = 'tpl_id DESC', $limit = 30) {
		return $this->where($map)->order($order)->findPage($limit);
	}

	/**
	 * 按照模板名称查找模板
	 *
	 * @param string $name 模板名称
	 * @return string
	 */
	public function getTemplateByName($name) {
		$map['name'] = $name;
		return $this->where($name)->find();
	}

	/**
	 * 添加模版
	 *
	 * @param array $data
	 * @return int|boolean 添加成功时返回在数据库的ID, 否则返回false
	 */
	public function addTemplate($data) {
		$data['ctime'] = empty($data['ctime']) ? time() : $data['ctime'];
		return $this->add($data);
	}

	/**
	 * 删除模板
	 *
	 * @param int|string $where  可以是模板ID template_ids或模板名称 names 多个ID或名称是数组形式，也可用“,”分隔
	 * @return boolean
	 */
	public function deleteTemplate($where) {
		if ( empty($where) ) return false;

		$where = is_array($where) ? $where : explode(',', $where);
		if ( is_numeric($where[0]) ) {
			$map['tpl_id']		= array('in', $where);
		}else if ( is_string($where[0]) ) {
			$map['name']	= array('in', $where);
		}
		if ( empty($map) ) return false;

		return $this->where($map)->delete();
	}

	/**
	 * 解析模板（将模板中变量替换成数据）
	 * TODO: 多语
	 *
	 * @param string  $tpl_name    模板名称
	 * @param array   $data        模板中的变量和数据
	 * @param boolean $auto_record 是否添加模板记录
	 * @return boolean|string 模板解析的结果
	 */
	public function parseTemplate($tpl_name, $data, $auto_record = null) {
		$map['name'] = $tpl_name;
		$template	 = $this->where($map)->find();
		if (!$template) return false;

		$auto_record	= isset($auto_record) ? $auto_record : $template['is_cache'];
		$title			= '';
		$body			= '';

		//标题模板
		if ( !empty($template['title']) ) {
			$keys	= array_keys($data['title']);
			$values	= array_values($data['title']);
			foreach($keys as $k => $v) $keys[$k] = '{'.$v.'}';
			$template['title'] = str_replace($keys, $values, $template['title']);
			unset($keys, $values);
		}
		//内容模板
		if ( !empty($template['body']) ) {
			$keys	= array_keys($data['body']);
			$values	= array_values($data['body']);
			foreach($keys as $k => $v) $keys[$k] = '{'.$v.'}';
			$template['body'] = str_replace($keys, $values, $template['body']);
			unset($keys, $values);
		}

		//自动添加模板记录
		if ($auto_record) {
			$record_data['uid']			= isset($data['uid']) ? $data['uid'] : $_SESSION['mid'];
			$record_data['tpl_name']	= $template['name'];
			$record_data['tpl_alias']	= $template['alias'];
			$record_data['type']		= $template['type'];
			$record_data['type2']		= $template['type2'];
			$record_data['ctime']		= time();
			unset($template['tpl_id']);
			$record_data['data']		= serialize($template);
			return $this->addTemplateRecord($record_data);
		}else {
			return $template;
		}
	}

	/**
	 * 添加模板记录
	 *
	 * @param array $data 模板的各种参数
	 * @return boolean
	 */
	public function addTemplateRecord($data) {
		return M('template_record')->add($data);
	}

	/**
	 * 查询模板记录
	 *
	 * @param string|array $map 查询条件
	 * @param string       $order 结果排序  默认'tpl_record_id DESC'
	 * @param int          $limit 查询条数  默认30
	 * @return array
	 */
	public function getTemplateRecordByMap($map = array(), $order = 'tpl_record_id DESC', $limit = 30) {
		$res = M('template_record')->where($map)->order($order)->findPage($limit);
		foreach($res['data'] as $k => $v) {
			$res['data'][$k]['data'] = unserialize($v['data']);
		}
		return $res;
	}
}