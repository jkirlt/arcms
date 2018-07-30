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
 * 用户控制器
 * 该控制器不受权限管理控制
 */
class User extends Controller
{

    // 添加用户页面
    public function userAdd()
    {
        $this->display('/user/userAdd');
    }

    // 分配用户组页面
    public function graupAdd()
    {
        $data = \ar\core\get();

        $uid = $data['uid'];

        $roleList = $this->getUserService()->getUserRoleList($uid);

        $this->assign(['roleList' => $roleList]);
        $this->display('/user/graupAdd');
    }

    // 分配权限页面
    public function roleAdd()
    {
        $data = \ar\core\get();

        $rid = $data['rid'];

        $roleList = $this->getUserService()->getRoleNavList($rid);

        $this->assign(['roleList' => $roleList]);
        $this->display('/user/roleAdd');
    }

    // 编辑用户组页面
    public function roleEdit()
    {
        $data = \ar\core\get();
        $rid = $data['rid'];

        $roleDetal = $this->getUserService()->getRoleRow($rid);

        $this->assign(['role' => $roleDetal]);
        $this->display('/user/roleEdit');
    }

    // 用户组列表
    public function userGrade()
    {
        $roleList = $this->getUserService()->getRoleList();

        $this->assign(['roleList' => $roleList]);
        $this->display('/user/userGrade');
    }

    // 分配角色
    public function changeRole()
    {
        $data = \ar\core\post();
        $type = $data['type'];

        $urDetail = [
            'uid' => $data['uid'],
            'role_id' => $data['role_id']
        ];

        if($type == 1){
            // 分配角色
            $result = $this->getUserService()->addUserRole($urDetail);
            if ($result) {
                $this->showJsonSuccess('分配成功');
            } else {
                $this->showJsonError('分配失败', '6002');
            }
        } else if($type == 0){
            // 取消分配角色
            $result = $this->getUserService()->delUserRole($urDetail);
            if ($result) {
                $this->showJsonSuccess('取消分配成功');
            } else {
                $this->showJsonError('取消分配失败', '6002');
            }
        }

    }

    // 分配权限
    public function changeRoleNav()
    {
        $data = \ar\core\post();
        $type = $data['type'];

        $rnDetail = [
            'nav_id' => $data['nav_id'],
            'role_id' => $data['role_id']
        ];

        if($type == 1){
            // 分配权限
            $result = $this->getUserService()->addRoleNav($rnDetail);
            if ($result) {
                $this->showJsonSuccess('分配成功');
            } else {
                $this->showJsonError('分配失败', '6002');
            }
        } else if($type == 0){
            // 取消分配权限
            $result = $this->getUserService()->delRoleNav($rnDetail);
            if ($result) {
                $this->showJsonSuccess('取消分配成功');
            } else {
                $this->showJsonError('取消分配失败', '6002');
            }
        }

    }

    public function userInfo()
    {
        $userDetal = $this->getUserService()->getLoginUser();
        $uid = $userDetal['id'];
        $userInfo = $this->getUserService()->getOneUser($uid);

        $this->assign(['userInfo' => $userInfo]);

        $this->display('/user/userInfo');
    }

    public function changePwd()
    {
        $userDetal = $this->getUserService()->getLoginUser();
        $uid = $userDetal['id'];
        $userInfo = $this->getUserService()->getOneUser($uid);

        $this->assign(['userInfo' => $userInfo]);
        
        $this->display('/user/changePwd');
    }
}
