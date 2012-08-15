<?php
require_cache(ADDON_PATH . '/models/XdataModel.class.php');
class AddonDataModel extends XdataModel
{
	protected $list_name = 'addons';
	const PREFIX = "addons_"; 

	public function lput($addonName, $data = array())
	{
		return parent::lput(self::PREFIX . $addonName, $data);
	}

	public function lget($addonName){
		return parent::lget(self::PREFIX . $addonName);
	}

	public function put($key, $value = '', $replace=false)
	{
		return parent::put(self::PREFIX . $key, $value, $replace);
	}

	public function get($key){
		return parent::get(self::PREFIX . $key);
	}

	public function getAll($listName, $keys)
	{
		return parent::getAll(self::PREFIX . $listName, $keys);
	}
}