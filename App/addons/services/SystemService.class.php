<?php
class SystemService extends Service
{
	public function getSystemVersion()
	{
		static $versions = null;
		if (isset($versions))
			return $versions;

		$xdata_dao = model('Xdata');

		// 核心版本
		$core_version = intval($xdata_dao->get('siteopt:site_system_version_number'));
		$versions = array('core' => $core_version);

		// 应用版本
		$apps = model('App')->getAllApp('app_name');
		$apps = getSubByKey($apps, 'app_name');
		foreach ($apps as $app)
			$versions[$app] = intval($xdata_dao->get($app.':version_number'));

		return $versions;
	}

	public function checkUpdate()
	{
		$cache_id = '_service_system_update';
		if (($result = S($cache_id)) === false) {

			//升级服务器地址
			$url = 'http://t.thinksns.com/version.php';
			$versions = $this->getSystemVersion();

			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($versions) . '&output_format=&site='.SITE_URL);
			//curl_setopt($curl, CURLOPT_NOBODY, 0);
			$result = curl_exec($curl);
			curl_close($curl);

			$result = unserialize($result);
			if ($result === false) {
				$result = array('error' => '1', 'error_message' => '获取版本信息失败');
			} else {
				$result['error']		 = '0';
				$result['error_message'] = '';
			}

			S($cache_id, $result, 60);
		}
		return $result;
	}

	public function unsetUpdateCache()
	{
		$cache_id = '_service_system_update';
		S($cache_id, null);
	}

	public function run()
	{}
}