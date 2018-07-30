<?php
/**
 * 前端基于layuicms2.0 ，后端基于arphp 5.1.14
 *
 * @author assnr assnr@coopcoder.com
 *
 * 本项目仅供学习交流使用，如果用于商业请联系授权
 */
namespace arcms\ctl\main;
use \ar\core\Controller as Controller;

/**
 * 控制器
 */
class Systems extends Base
{

    // 菜单列表
    public function menuList()
    {
        $this->display('/systemSetting/menuList');
    }

    // 数据库表
    public function tableList()
    {
        $this->display('/systemSetting/tableList');
    }

    // 模型表
    public function modelList()
    {
        $this->display('/systemSetting/modelList');
        
    }

    // 系统参数设置
    public function setSystem()
    {
        $systemInfo = $this->getIndexService()->systemSetting();

        $this->assign(['sysInfo' => $systemInfo]);
        $this->display('/systemSetting/setSystem');
    }
   
}
