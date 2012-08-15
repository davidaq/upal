<?php

class My{

	function parseRequest() {
		global $_SITE_CONFIG;
		
		$request	= $_GET ? $_GET : $_POST;
		$module		= $request['module'];
		$method		= $request['method'];
		
		$log = $request;
		$log['paramsArray'] = $this->myAddslashes(unserialize($request['params']));
		doLog($log, empty($_POST) ? '$_GET: ' : '$_POST: ');
		
		$errCode = 0;
		$errMessage = '';
		if ($_SITE_CONFIG['site_close']) {
			$errCode = 2;
			$errMessage = 'Site Closed';
		} elseif (!$_SITE_CONFIG['my_status']) {
			$errCode = 2;
			$errMessage = 'Manyou Service Disabled';
		} elseif (!$_SITE_CONFIG['site_key']) {
			$errCode = 11;
			$errMessage = 'Client SiteKey NOT Exists';
		} elseif (!$_SITE_CONFIG['my_site_key']) {
			$errCode = 12;
			$errMessage = 'My SiteKey NOT Exists';
		} elseif (empty($module) || empty($method)) {
			$errCode = '3';
			$errMessage = 'Invalid Method: ' . $moudle . '.' . $method;
		}

		if (get_magic_quotes_gpc()) {
			$request['params'] = sstripslashes($request['params']);
		}
		$mySign = $module . '|' . $method . '|' . $request['params'] . '|' . $_SITE_CONFIG['my_site_key'];
		$mySign = md5($mySign);
		if ($mySign != $request['sign']) {
			$errCode = '10';
			$errMessage = 'Error Sign';
		}

		if ($errCode) {
			return new APIErrorResponse($errCode, $errMessage);
		}

		$params = unserialize($request['params']);

		$params = $this->myAddslashes($params);
		
		if ($module == 'Batch' && $method == 'run') {
			$response = array();
			foreach($params as $param) {
				$response[] = $this->callback($param['module'], $param['method'], $param['params']);
			}
			return new APIResponse($response, 'Batch');
		}
		return $this->callback($module, $method, $params);
	}

	function callback($module, $method, $params) {
		global $_SITE_CONFIG;
		if (isset($params['uId'])) {
			$space = getspace($params['uId']);
			if ($this->_needCheckUserId($module, $method)) {
				if (!$space['uid']) {
					$errCode = 1;
					$errMessage = "User({$params['uId']}) Not Exists";
					return new APIErrorResponse($errCode, $errMessage);
				}
			}
		}
		
		$_SITE_CONFIG['uid']	= $space['uid'];
		$_SITE_CONFIG['uname'] 	= $space['uname'];

		@include_once API_ROOT . '/class/' . $module . '.class.php';
		if (!class_exists($module)) {
			$errCode 	= 3;
			$errMessage = "Class($module) Not Exists";
			return new APIErrorResponse($errCode, $errMessage);
		}

		$class 	  = new $module();
		$response = @call_user_func_array(array(&$class, $method), $params);

		return $response;
	}

	//格式化返回结果
	function formatResponse($data) {
		global $_SITE_CONFIG, $_SC;
		//返回结果要参加一些统一的返回信息
		$res = array(
			'timezone'		=> $_SITE_CONFIG['timeoffset'],
			'version'   	=> X_VER,
			'my_version'	=> MY_VER,
			'charset'		=> $_SC['charset'],
			'language'		=> $_SC['language'] ? $_SC['language'] : 'zh_CN',
		);
		
		if (strtolower(get_class($data)) == 'apiresponse' ) {
			if (is_array($data->result) && $data->getMode() == 'Batch') {
				foreach($data->result as $result) {
					if (strtolower(get_class($result)) == 'apiresponse') {
						$res['result'][] = $result->getResult();
					} else {
						$res['result'][] = array('errCode' => $result->getErrCode(),
												 'errMessage' =>  $result->getErrMessage()
												);
					}
				}
			} else {
				$res['result']  = $data->getResult();
			}
		} else {
			$res['errCode'] = $data->getErrCode();
			$res['errMessage'] = $data->getErrMessage();
		}
		return serialize($res);
	}

	function _needCheckUserId($module, $method) {
		$myMethod = $module . '.' . $method;
		switch($myMethod) {
			case 'Notifications.send':
			case 'Request.send':
				$res = false;
				break;
			default:
				$res = true;
		}
		return $res;
	}

	function myAddslashes($string) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = $this->myAddslashes($val);
			}
		} else {
			$string = ($string === null) ? null : addslashes($string);
		}
		return $string;
	}

}