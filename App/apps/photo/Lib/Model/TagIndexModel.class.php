<?php
class TagIndexModel extends Model
{
	var $tableName	=	'tag_index';

	public function setTagIndex($tagId,$contentId,$module) {
		$map['tagId']		=	$tagId;
		$map['contentId']	=	$contentId;
		$map['module']	=	$module;
		$map['cTime']     =  time();
		$result	=	$this->add($map);
		if($result){
			return true;
		}else{
			return false;
		}
	}

	public function setTagIndexs($tagIds,$contentId,$module) {
		foreach($tagIds as $v){
			$result[]	=	$this->setTagIndex($v,$contentId,$module);
		}
		return $result;
	}

	public function getTagContents($tagId) {
		$map['tagId'] = $tagId;
		$map['module'] = "artifact";
		$result	=	$this->where($map)->getFields('contentId');
		if($result){
			return $result;
		}else{
			return '';
		}
	}
	
	public function getContentTage($contengId){
	    $map['contentId'] = $contengId;
        $result =   $this->where($map)->getFields('tagId');
        if($result){
            return $result;
        }else{
            return '';
        }
	}
}
?>