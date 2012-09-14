<?php
class PhotoModel extends Model {
	var $tableName	=	'photo';

	public function getUidFeature($uid) {
		$map ['userId'] = $uid;
		$map ['feature'] = 1;
		$result = $this->where ( $map )->order ( "sorder ASC" )->findAll ();
		return $result;
	}
	public function setCount($appid, $count) {
		$map ['id'] = $appid;
		$map2 ['commentCount'] = $count;
		return $this->where ( $map )->save ( $map2 );
	}

	public function setFeature($pid,$uid){
		$condition['id'] = $pid;
		$condition['userId'] = $uid;
		$featureCount = $this->getFeatureCount($uid);
		if($featureCount == 3){
			$old_feature = $this->getUidFeature($uid);
			foreach($old_feature as $value){
				//取消最下面的特色展示
				if($value['sorder'] == 3){
					$rm['sorder'] = 0;
					$rm['feature'] = 0;
					$this->where('id='.$value['id'])->save($rm);
					continue;
				}
				$new_order = array();
				$new_order['sorder'] = $value['sorder'] + 1;
				$this->where('id='.$value['id'])->save($new_order);
			}
			//设置最新的这个图为第一个特色展示
			$map['sorder'] = 1;
		}else{
			$map['sorder'] = $featureCount+1;
		}
		$map['feature'] = 1;
		//设置为特色.并设置排序
		return $this->where($condition)->save($map)?1:0;
	}

	public function setDownOrder($pid,$uid){
	   $featureCount = $this->getFeatureCount($uid);
	   if($featureCount == 1) return -2; //只有一个特色的情况下，不需要进行调换
	   $old_order = $this->where('id='.$pid)->getField('sorder');
	   if($featureCount ==3 && $old_order == 3) return -1;//最后一个无法向下调换
	   $feature   = $this->getUidFeature($uid);
	   $now_feature = $this->getOrderFeature($feature);

       $map_current['sorder'] = $old_order+1;
       $map_current_condition['id']     = $pid;
       //当前的排序增加一个
       $this->where($map_current_condition)->save($map_current);

       //下一个图片的排序换成当前的
       $map_next['sorder'] = $old_order;
       $map_next_condition['id'] = $this->getNextFeature($now_feature,$old_order,true);
       $this->where($map_next_condition)->save($map_next);
		return 1;
	}

	private function getNextFeature($featureOrder,$order,$add = true){
		foreach($featureOrder as $key=>$value){
			if($add){
			    if($value == $order+1){
                    return $key;
                }
			}else{
			    if($value == $order-1){
                    return $key;
                }
			}

		}
	}

	private function getOrderFeature($feature){
		$result = array();
		foreach($feature as $value){
			$result[$value['id']] = $value['sorder'];
		}
		return $result;
	}
	public function setUpOrder($pid,$uid){
	   $featureCount = $this->getFeatureCount($uid);
       if($featureCount == 1) return -2; //只有一个特色的情况下，不需要进行调换
       $old_order = $this->where('id='.$pid)->getField('sorder');
       if($featureCount ==1 && $old_order == 3) return -1;//第一个无法向上调换
       $feature   = $this->getUidFeature($uid);
       $now_feature = $this->getOrderFeature($feature);

       //当前的排序减少一个
       $map_current['sorder']           = $old_order-1;
       $map_current_condition['id']     = $pid;
       $this->where($map_current_condition)->save($map_current);

       //上一个图片的排序换成当前的
       $map_next['sorder'] = $old_order;
       $map_next_condition['id'] = $this->getNextFeature($now_feature,$old_order,false);
       $this->where($map_next_condition)->save($map_next);
        return 1;
	}
	public function getFeatureCount($uid){
		$map['userId'] = $uid;
		$map['feature'] = 1;
	    $count = $this->field('count(1) as count')->where($map)->find();
	    return $count['count'];
	}
}
?>