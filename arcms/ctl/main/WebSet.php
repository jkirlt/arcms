<?php
/**
 * 前端基于layuicms2.0 ，后端基于arphp 5.1.14
 *
 * @author assnr assnr@coopcoder.com
 *
 * 本项目仅供学习交流使用，如果用于商业请联系授权
 */
namespace arcms\ctl\main;

/**
 * 网站设置页面控制器
 * 该控制器接受权限访问限制
 */
class WebSet extends Base
{
    // 模板选择
    public function themes()
    {
        $this->display('/webSet/themes');
    }

    // 导航设置
    public function setMenu()
    {
        $this->display('/webSet/setMenu');
    }

    // 网站信息
    public function aboutWeb()
    {
        $this->display('/webSet/aboutWeb');
    }

    // Banner图
    public function banner()
    {
        $this->display('/webSet/banner');
    }

    // 友情链接
    public function hrefList()
    {

        $this->display('/webSet/hrefList');
    }

    // 添加导航菜单
    public function navAdd()
    {
        $topNavs = $this->getDataService()->findTopMenu();
        $secondNavs = $this->getDataService()->findSecondMenu();

        $this->assign(['topMenu' => $topNavs['top']]);
        $this->assign(['secondMenu' => $secondNavs['second']]);
        $this->display('/webSet/navAdd');
    }

}
