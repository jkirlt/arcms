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
class Login extends Controller
{
    // 登陆
    public function login()
    {
        if ($this->getUserService()->isLogin()) {
            $this->redirect('/index');
        }
        $systemInfo = $this->getIndexService()->systemSetting();
        $this->assign(['sysInfo' => $systemInfo]);
        $this->display('/login/login');
    }

    // 退出
    public function loginout()
    {
        $this->getUserService()->loginout();
        $this->redirect('login');
    }

    // 安装页面
    public function setup()
    {
        $this->display('/login/setup');
    }
}
