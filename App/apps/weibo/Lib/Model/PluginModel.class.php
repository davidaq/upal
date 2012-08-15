<?php 
class PluginModel extends Model{
    protected $tableName = 'weibo_plugin';
    
    public function getPluginInfoById($id) {
    	$id = intval($id);
    	
    	static $_path = array();
    	if (isset($_path[$id]))
    		return $_path[$id];
    	
    	if (($_path = F('_weibo_plugin_model')) === false) {
    		$res = $this->findAll();
    		foreach ($res as $v)
    			$_path[$v['plugin_id']] = $v;
    			
    		F('_weibo_plugin_model', $_path);
    	}
    	
    	return $_path[$id];
    }
}
?>