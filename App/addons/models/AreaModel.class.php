<?php
/**
 * 地区模型
 * 
 * @author daniel <desheng.young@gmail.com>
 */
class AreaModel extends Model {
	protected $tableName = 'area';
	
	private $__depth = 3;
	
	/**
     * 当指定pid时，仅查询该父地区的所有子地区；否则查询所有地区
     * 
	 * @param $pid 父地区ID
	 * @return array
	 */
	public function getAreaList($pid = -1) {
		$map = array();
		$pid != -1 && $map['pid'] = $pid;
		return $this->where($map)->order('`area_id` ASC')->findAll();
	}
	
	/**
	 * TODO:目前简单处理，仅取前两级地区的结构树
	 * 
     * @param $pid 父地区ID
     * @return array
	 */
	public function getAreaTree($pid) {
		$output	= array();
		$list	= $this->getAreaList();
		
		// 先获取省级
		foreach ($list as $k1 => $p) {
			if ($p['pid'] == 0) {
				// 获取当前省的市
				$city  = array();
				foreach ($list as $k2 => $c) {
					if($c['pid'] == $p['area_id']) {
						$city[] = array($c['area_id'] => $c['title']);
						unset($list[$k2]);
					}
				}
				$output['provinces'][] = array(
									       'id'		=> $p['area_id'],
											'name'	=> $p['title'],
											'citys'	=> $city,
									   	  );
				unset($list[$k1], $city);
			}
		}
		unset($list);
		return $output;
	}
}