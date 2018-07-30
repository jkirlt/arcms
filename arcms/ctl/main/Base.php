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
 * 基类控制器
 */
class Base extends Controller
{
    // 公用方法
    public function init()
    {
        // 取出当前用户的所有权限id
        $navsid = $this->getUserRole();
        // 根据id在权限表中取出所有链接
        $addrs = $this->getAddr($navsid);
        // 获取当前访问页面地址
        $addr = \ar\core\cfg('requestRoute')['a_c'] . "/" . \ar\core\cfg('requestRoute')['a_a'];
        // 如果没有权限则跳转到错误页面
        if(!in_array($addr,$addrs)){
            // $this->redirect('index/p404');
        }

    }

    // 获取当前登录用户能访问的权限id并存入数组
    public function getUserRole(){
        $userDetal = $this->getUserService()->getLoginUser();
        $uid = $userDetal['id'];
        $unav = [];
        // 根据uid获取用户所属的用户组
        $role = $this->getUserService()->getRidByUid($uid);
        foreach($role as $key => $value){
            $role_id = $role[$key]['role_id'];
            // 根据role_id获取用户组所属的权限
            $nav = $this->getUserService()->getNidByRid($role_id);
            foreach($nav as $k => $v){
                // 将当前用户的权限id添加到数组
                array_push($unav,$v['nav_id']);
            }
        }
        // 去掉重复数据
        $unav = array_unique($unav);

        return $unav;
    }

    // 在权限表中取出所有的链接
    public function getAddr($navsid)
    {
        $addrs = [];
        foreach($navsid as $n){
            // 根据nav_id查找链接
            $navs = $this->getUserService()->getHrefByNid($n);

            $href = $navs;
            array_push($addrs,$href);
        }

        return $addrs;
    }

}
