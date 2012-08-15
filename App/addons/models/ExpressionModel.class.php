<?php
/**
 * 表情模型
 *
 * @author daniel <desheng.young@gmail.com>
 */
class ExpressionModel extends Model {
	protected $tableName = 'expression';

	/**
	 * 获取表情
	 *
	 * @param array  $map   查询条件
	 * @param string $order 默认表情类型升序,表情ID升序排列
	 * @return array 返回中的filepath为表情图片的真实地址
	 */
	public function getExpressionByMap($map = array(), $order = 'type ASC,expression_id ASC') {
		$res = $this->where($map)->order($order)->findAll();
		foreach ($res as $k => $v) {
			$res[$k]['filepath'] = __THEME__ . '/images/expression/' . $v['type'] . '/' . $v['filename'];
		}
		return $res;
	}

	//获取当前表情. $flush=true时刷新缓存.
    public function getAllExpression ($flush=false) {
        $cache_id = '_model_expression';
        if (($res = F($cache_id)) === false || $flush===true) {
            global $ts;
            $pkg = $ts['site']['expression'];
            $filepath = SITE_PATH . '/public/themes/' . $ts['site']['site_theme'] .
             '/images/expression/' . $pkg;
            require_once ADDON_PATH . '/libs/Io/Dir.class.php';
            $expression = new Dir($filepath);
            $expression_pkg = $expression->toArray();
            $res = array();
            foreach ($expression_pkg as $value) {
				if(!is_utf8($value['filename'])){
					$value['filename'] = auto_charset($value['filename'],'GBK','UTF8');
				}
                list ($file) = explode(".", $value['filename']);
                $temp['title'] = $file;
                $temp['emotion'] = '[' . $file . ']';
                $temp['filename'] = $value['filename'];
                $temp['type'] = $pkg;
                $res[$temp['emotion']] = $temp;
            }
            F($cache_id, $res);
        }
        return $res;
    }

	public function getExpressionDetailByEmotion($emotion) {
		$cache_id = "_model_expression";
		if (($list = object_cache_get($cache_id)) === false) {
			$res = $this->getAllExpression();
			$list = $res;
			object_cache_set($cache_id, $list);
		}
		return $list[$emotion];
	}
}