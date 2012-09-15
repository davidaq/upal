<?php
/**
 * 标准插件抽象。该插件具备插件的标准行为。
 * 获取信息，以及该插件拥有的管理操作
 * @author sampeng
 *
 */
abstract class NormalAddons extends AbstractAddons
{

    /**
     * getHooksList
     * 获取该插件的所有钩子列表
     * @access public
     * @return void
     */
    public function getHooksList($name)
    {
        $hooks = $this->getHooksInfo();
        $hooksBase = get_class_methods('Hooks');
        $list = array();
        //生成插件列表
        foreach($hooks['list'] as $value){
            $dirName = ADDON_PATH . '/plugins';
            require_cache($this->path . '/hooks/' . $value . '.class.php');
            $hook = array_diff(get_class_methods($value),$hooksBase);
            foreach($hook as $v){
                $list[$v][$name][] = $value;
            }
        }
        //排序
        foreach($hooks['sort'] as $key=>$value){
            if(isset($list[$name][$key])){
                $temp = array();
                foreach($value as $v){
                    $temp[] = $hooks['list'][$v];
                }
                $list[$name][$key] = $temp;
            }
        }
        return $list;
    }
}
