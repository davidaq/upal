<?php
/**
 * 皮肤风格模型
 * 
 * @author 陈伟川 <258396027@qq.com>
 */
class SpaceStyleModel extends Model
{
	protected $tableName = 'user_change_style';

	private $_error = null;

	public function getLastError()
	{
		return $this->_error;
	}

	public function getStyle($uid)
	{
		$uid = intval($uid);
		if (!$uid) {
			return false;
		}
        $map = array('uid' => $uid);
        $style_data = $this->where($map)->find();
        $style_data['background'] = unserialize($style_data['background']);
        return $style_data;
	}

	public function saveStyle($uid, $style_data)
	{
		$style_data = $this->_escapeStyleData($uid, $style_data);
		if (false === $style_data) {
			return false;
		}

        //判断重名
        $map = array('uid' => $style_data['uid']);
        $uid = $this->getField('uid', $map);
        if ($uid > 0) {
            $res = $this->save($style_data);
        } else {
            $res = $this->add($style_data);
        }

        if (false !== $res) {
			$this->_error = '设置成功';
            return true;
        } else {
			$this->_error = '设置失败';
            return false;
        }
	}

	/* ------- 私有方法 ------ */
	private function _escapeStyleData($uid, $style_data)
	{
		$_style_data['uid'] = intval($uid);
		$_style_data['classname'] = t($style_data['classname']);
		$_style_data['background'] = $this->_escapeBackgroundData($style_data['background']);
		if ($_style_data['uid'] > 0) {
			return $_style_data; 
		} else {
			$this->_error = '用户UID 不合法';
			return false;
		}
	}

	private function _escapeBackgroundData($background_data)
	{
		$_backgroup_data['color'] = t($background_data['color']);
		$_backgroup_data['image'] = t($background_data['image']);
		$_backgroup_data['repeat'] = t($background_data['repeat']);
		$_backgroup_data['attachment'] = t($background_data['attachment']);
		$_backgroup_data['position'] = t($background_data['position']);
		return serialize($_backgroup_data);
	}
}