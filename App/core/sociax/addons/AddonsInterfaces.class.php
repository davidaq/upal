<?php
/**
 * 这里是整个插件的抽象接口。
 * 系统需要知道
 * 插件使用了哪些hooks
 * 插件的基本信息
 * @author sampeng
 *
 */
interface AddonsInterface
{
	/**
	 * 告之系统，该插件使用了哪些hooks以及排序等信息
	 * @return array()
	 */
	public function getHooksInfo();
	/**
	 * 插件初始化时需要的数据信息。所以就不需要写类的构造函数
	 * Enter description here ...
	 */
	public function start();
	/**
	 * 该插件的基本信息
	 * 这个方法不需要用户实现，将在下一层抽象中实现。
	 * 用户需要填写几个基本信息作为该插件的属性即可
	 */
    public function getAddonInfo();

    /**
     * setUp
     * 启动插件时的接口
     * @access public
     * @return void
     */
    public function install();

    /**
     * setDown
     * 卸载插件时的接口;
     * @access public
     * @return void
     */
    public function uninstall();

	/**
	 * 显示不同的管理面板或表单等操作的处理受理接口。默认$page为false.也就是只显示第一个管理面板页面
	 */
    public function adminMenu();

}
