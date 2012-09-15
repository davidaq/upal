<?php
class AddonsModel extends Model
{
	protected $tableName = 'addons';
	private $valid = array();
	private $invalid = array();
	private $fileAddons = array();
	protected	$fields		=	array (0 => 'addonId',1 => 'name',2 => 'pluginName',3 => 'author',4 => 'info',5 => 'version',6 => 'status',7 => 'lastupdate',8 => 'site',9 => 'tsVersion','_autoinc' => true,'_pk' => 'addonId');


	public function getAddonAllList()
	{
		$this->_getFileAddons();
		//获取数据库中的所有插件
        $databaseAddons = $this->findAll();
		$this->_validAddons($databaseAddons);

		$this->_invalidAddons();
		$result['valid']['data']   = $this->valid;
		$result['valid']['name']   = "已安装插件";
		$result['invalid']['data'] = $this->invalid;
		$result['invalid']['name'] = "待安装插件";
		return $result;
	}

	public function resetAddonCache(){
		
		if(empty($this->fileAddons)) $this->_getFileAddons();
		
		$addonList = $this->getAddonsValid();
	
		$addonCache = array();
		foreach($addonList as $key => $value){
			if(isset($this->fileAddons[$value['name']])){
				$addonCache = $this->_createAddonsCacheData($value['name'],$addonCache);
			}
		}
		$res = F('thinksns_addon_list',$addonCache);
		return $addonCache;
	}

	public function getAddonsValid()
	{
			
		$map['status'] = '1';
		return $this->where($map)->findAll();
	}

	public function getAddonsInvalid()
	{
	}

	public function stopAddonsById($id)
    {
        if(empty($id)) return false;
        //将数据库中标示该插件停止
        $result = $this->_stopAddons('addonId',intval($id));
		return $result?true:false;
	}
	public function stopAddonsByName($name)
    {
        if(empty($name)) return false;
        $result = $this->_stopAddons('name',$name);
		return $result?true:false;
	}

	public function getAddonObj($id){
		$data = $this->getAddon($id);
		if($data){
			$this->_getFileAddons();
			return $this->fileAddons[$data['name']];
		}
		return false;
	}

	private function _stopAddons($field,$value)
    {
        //将数据库中标示该插件停止
		$map[$field] = $value;
		if($filed != 'name'){
			$addon = $this->where($map)->find();
			$name = $addon['name'];
		}else{
			$name = $value;
		}
		$save['status'] = '0';
		$result = $this->where($map)->save($save);
		if($result){
			$addonCacheList = $this->resetAddonCache();
			F('thinksns_addon_list',$addonCacheList);
		}
		return $result?true:false;
	}
	public function startAddons($name)
	{
		//先查看该插件是否安装
		$map['name'] = t($name);
		$addon = $this->where($map)->find();
		//装载缓存列表
		$this->_getFileAddons();
		if(!isset($this->fileAddons[$name])) throw new ThinkException("插件".$name."的目录不存在");


        //如果安装后启用的，设置插件启动
		if ($addon && $addon['status'] == 0) {
			$save['status'] = '1';
			$result = $this->where($map)->save($save) ? true : false;
		} else if($addon && $addon['status'] == 1){
			$result = false;
		} else {
            $addonObject = $this->fileAddons[$name];
            $add = $addonObject->getAddonInfo();
			$add['name'] = $name;
			$add['status'] = '1';
			if($this->add($add) && $addonObject->install()){
				$result = true;
			}else{
				$result = false;
			}
        }

		if($result){
			$addonCacheList = $this->resetAddonCache();
			F('thinksns_addon_list',$addonCacheList);
        }
		return $result;
    }

    public function uninstallAddons ($name)
    {
        if(empty($name)) return false;
        $this->_getFileAddons();
        if(!isset($this->fileAddons[$name])) throw new ThinkException("插件".$name."不存在");
        $addonObject = $this->fileAddons[$name];
        $addonObject->uninstall();

		$map['name'] = $name;
		$result = $this->where($map)->delete()?true:false;
		if($result){
			$addonCacheList = $this->resetAddonCache();
			F('thinksns_addon_list',$addonCacheList);
		}
		return $result;
    }

	public function getAddon($id,$status=1)
	{
		$map['addonId'] = intval($id);
		$status = intval($status);
		$map['status']  = "$status";
		return $this->where($map)->find();
	}

	public function getAddonsAdmin()
	{
		$valid = $this->getAddonsValid();
		$this->_getFileAddons();
		$data = array();
		foreach($valid as $value){
			$obj = $this->fileAddons[$value['name']];
			//$type = $obj instanceof AddonsAdminInterface;
			//$class = new ReflectionClass($obj);
			//$methods = $class->getMethods();
			//$methods = getSubByKey($methods,'name');
			if($obj && $obj->adminMenu()){
				$data[] = array($value['pluginName'], $value['addonId']);
				//if(!$obj->adminMenu()){
				//	throw new ThinkException($value['pluginName'].' 必须具备管理面板,但是我没看到adminMenu');
				//}
			}
		}
		return $data;
	}

	private function _createAddonsCacheData($name,$addonList)
    {
        $list = $this->fileAddons[$name]->getHooksList($name);
		//合并钩子缓存列表
		if(empty($addonList)){
			$addonList = $list;
        }else{
            $result = array();
            $addonListKey = array_keys($addonList);
            $listKey = array_keys($list);
            $addonList = array_merge_recursive($addonList,$list);
        }
		return $addonList;
	}

	private function _validAddons($databaseAddons)
    {
        if(empty($databaseAddons)) return;
		foreach($databaseAddons as $value){
			if($value['status'] == 1){
				$this->valid[] = $value;
			}else{
				$this->invalid[] = $value;
			}
			if(isset($this->fileAddons[$value['name']]))
				unset($this->fileAddons[$value['name']]);
		}
	}

	private function _invalidAddons()
	{
		//得到未启用的插件
		foreach($this->fileAddons as $key=>$value){
            $data = $value->getAddonInfo();
            $data['status'] = 0;
            $data['name'] = $key;
			$this->invalid[] = $data;
		}
	}

	private function _getFileAddons()
	{
		if(!empty($this->fileAddons)) return $this->fileAddons;
		//获取文件夹下面的所有插件
		$dirName = ADDON_PATH . '/plugins/';
		$dir = dir($dirName);
		$fileAddons = array();
		while (false !== $entry = $dir->read()) {
			if ($entry == '.' || $entry == '..' || $entry==".svn") {
				continue;
			}
			$path = $dirName . DIRECTORY_SEPARATOR . $entry;
			$addonsFile = $path.DIRECTORY_SEPARATOR . $entry . 'Addons.class.php';

			if (is_file($addonsFile)) {
				require_once $addonsFile;
				$class = $entry . 'Addons';
				$fileAddons[$entry] = new $class();
				$fileAddons[$entry]->setPath($path);
			}
		}
		$this->fileAddons = $fileAddons;
	}
}
