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
 * 默认入口控制器
 */
class Index extends Controller
{
    // 后台首页
    public function index()
    {
        if (!file_exists(AR_PUBLIC_CONFIG_PATH . 'install.lock')) {
            $installPage = \ar\core\comp('url.route')->serverPath(AR_SERVER_PATH, true) . 'install/index.php';
            $this->redirect($installPage);
        }

        if (!$this->getUserService()->isLogin()) {
            $this->redirect('login/login');
        }
        // 顶级菜单
        $topid = $this->getUserTop();
        $topNavs = $this->getDataService()->findUserTopMenu($topid);
        // 系统信息
        $systemInfo = $this->getIndexService()->systemSetting();
        // 用户详情
        $userDetal = $this->getUserService()->getLoginUser();

        $this->assign(['topMenu' => $topNavs['top'], 'topCont' => $topNavs['cont']]);
        $this->assign(['firstId' => $topNavs['top'][0]]);
        $this->assign(['user' => $userDetal]);
        $this->assign(['systemInfo' => $systemInfo]);
        $this->display('/index');
    }

    // 获取当前登录用户能访问的顶级菜单id并存入数组
    public function getUserTop(){
        $userDetal = $this->getUserService()->getLoginUser();
        $uid = $userDetal['id'];
        $unav = [];
        $funav = [];
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

        // 根据用户能访问的子菜单查到对应的顶级菜单
        foreach($unav as $un){
            // 查找子菜单详情
            $cnav = $this->getDataService()->getNavOne($un);
            // 根据fid查找顶级菜单详情
            $fnav = $this->getDataService()->getTopNavOne($cnav['fid']);
            // 添加到数组
            array_push($funav,$fnav['nav_id']);
        }
        // 去重
        $funav = array_unique($funav);

        return $funav;
    }

    // 主页
    public function main()
    {
        $userDetal = $this->getUserService()->getLoginUser();
        $systemInfo = $this->getIndexService()->systemSetting();
        $this->assign(['user' => $userDetal]);
        $this->assign(['systemInfo' => $systemInfo]);
        $this->display('/main');
    }

    // 404
    public function p404()
    {
        $this->display('/404');
    }

    // 公告
    public function notice()
    {
        $systemInfo = $this->getIndexService()->systemSetting();
        $this->assign(['systemInfo' => $systemInfo]);
        $this->display('/notice');
    }

    // 安装
    public function install()
    {
        // $this->redirect(AR_SERVER_PATH . '/install.php');
    }
}
