<?php
class AddonsAction extends AdministratorAction
{
	public function index()
	{
	    $model = model('Addons');
	    $admin = $model->getAddonsAdmin();
        $result = $model->getAddonAllList();
        foreach($result['valid']['data'] as $key=>$value){
            foreach($admin as $v){
                if($v[1] == $value['addonId']) $result['valid']['data'][$key]['admin'] = true;
            }
        }

		foreach($result['valid']['data'] as $key=>$value){
            foreach($admin as $v){
                if($v[1] == $value['addonId']) $result['valid']['data'][$key]['admin'] = true;
            }
        }

		$this->assign('list',$result);
		$this->display();
	}

	public function startAddon()
	{
		$result = model('Addons')->startAddons($_GET['name']);
		if (true === $result) {
			$this->success('启动成功');
		} else {
			$this->error('启动失败');
		}
	}

	public function stopAddon()
	{
		$result = model('Addons')->stopAddonsById($_GET['addonId']);
		if (true === $result) {
			$this->success('停用成功');
		} else {
			$this->error('停用失败');
		}
    }

    public function uninstallAddon()
    {
		$result = model('Addons')->uninstallAddons($_GET['name']);
		if (true === $result) {
			$this->success('卸载成功');
		} else {
			$this->error('卸载失败');
		}
    }

	public function admin()
	{


        $addon = model('Addons')->getAddonObj($_GET['pluginid']);
        $addonInfo = model('Addons')->getAddon($_GET['pluginid']);
        if(!$addon) $this->error('插件未启动或插件不存在');
        $info = $addon->getAddonInfo();
        $adminMenu = $addon->adminMenu();
        if(!$adminMenu){
            $this->assign('addonName',$info['pluginName']);
            $this->assign('menu',false);
            $this->display();
            return;
        }

        $this->assign('menu',$adminMenu);

        if(empty($_GET['page'])){
            $_GET['page'] = $page = array_shift(array_keys($adminMenu));
        }else{
            $page = t($_GET['page']);
        }
        $this->assign('page',$page);
        $this->assign('addonName',$addonInfo['pluginName']);
        $this->assign('name',$addonInfo['name']);
        $this->assign('isAjax',$this->isAjax());

        $this->display();
    }

    public function doAdmin() {
        $addonInfo = model('Addons')->getAddon($_GET['pluginid']);
        $result = array('status'=>true,'info'=>"");
        F('Cache_App',null);
        Addons::addonsHook($addonInfo['name'],$_GET['page'],array('result'=>&$result));
        if($result['status']){
            $this->success($result['info']);
        }else{
            $this->error($result['info']);
        }
    }
}
