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
 * 用户页面控制器
 * 该控制器接受权限访问限制
 */
class Users extends Base
{
    public function userList()
    {
        $this->display('/user/userList');
    }

    public function userGrade()
    {
        $roleList = $this->getUserService()->getRoleList();

        $this->assign(['roleList' => $roleList]);
        $this->display('/user/userGrade');
    }
}
