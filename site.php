<?php
/**
 * lionfish_comshop模块微站定义
 *
 * @author 超级
 * @url
 */
defined('IN_IA') or exit('Access Denied');

require_once IA_ROOT . '/addons/lionfish_comshop/framework/common/config.path.php';
require_once SNAILFISH_COMMON . 'functions.php';

class Lionfish_comshopModuleSite extends WeModuleSite
{


    public function doWebAdmin()
    {
        global $_W;

        load_class('dispatchweb')->doaction();

    }

    public function doMobileBv()
    {
        //这个操作被定义用来呈现 功能封面
    }

    public function doWebGo()
    {
        //这个操作被定义用来呈现 规则列表
    }

    public function doWebNo()
    {
        //这个操作被定义用来呈现 管理中心导航菜单
    }

    public function doMobileOl()
    {
        //这个操作被定义用来呈现 微站首页导航图标
    }

    public function doMobileGgr()
    {
        //这个操作被定义用来呈现 微站个人中心导航
    }

    public function doMobileEr()
    {
        //这个操作被定义用来呈现 微站快捷功能导航
    }

    public function doWebEr()
    {
        //这个操作被定义用来呈现 微站独立功能
    }


}

?>