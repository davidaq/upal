<?php
import('TagLib');
trace('haha');
class TagLibScript_preprocess extends TagLib{
	public function _script($attr,$content){
		trace($attr);
		trace($content);
		$tag = $this->parseXmlAttr($attr,'include');
	}
}
?>
