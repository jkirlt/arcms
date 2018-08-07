<?php
/**
 * Powerd by ArPHP.
 *
 * User service.
 *
 */
namespace arcms\ctl\main\service;
use arcms\lib\model\User as UserModel;
use arcms\lib\model\Role as RoleModel;
use arcms\lib\model\Nav as NavModel;
use arcms\lib\model\UserRole as UserRoleModel;
use arcms\lib\model\RoleNav as RoleNavModel;

/**
 * 用户服务组件
 */
class User
{
    // seesion 组件
    protected $_seesion = null;

    function __construct() {
        $this->_session = \ar\core\comp('lists.session');
    }

    // 登陆组件
    public function login($username, $pass, $code)
    {
        $errorMsg = '';
        // var_dump($code, $this->_session->get('code'));
        if ($code && $code === $this->_session->get('code')) {
            $userConditon = [
                'username' => $username,
                'password' => UserModel::pwd($pass),
            ];
            $userCount = UserModel::model()->getDb()->where($userConditon)->count();
            if ($userCount > 0) {
                $userDetal = UserModel::model()->getDb()->where($userConditon)->queryRow();
                // 登录用户id
                $uid = $userDetal['id'];
                // 判断是否开启单一登录
                $oneLogin = \arcms\lib\model\CoopSystemSetting::model()->getDb()->queryRow();
                // 未开启单一登录
                if($oneLogin['onelogin'] == 0){
                    $this->_session->set('login', true);
                    // 在session中设置用户详情
                    \ar\core\comp('lists.session')->set('userDetal', $userDetal);
                    // 修改登录信息
                    UserModel::model()->getDb()
                        ->where(['id' => $uid])
                        ->update([
                            'logintime' => time(),
                            'ip' => \ar\core\comp('tools.Util')->getClientIp()
                        ]);
                    $this->_session->set('code', null);
                    return true;
                // 开启单一登录
                } else if($oneLogin['onelogin'] == 1){
                    // 判断是否登录
                    $isLogin = UserModel::model()->getDb()->where(['id' => $uid])->queryRow();
                    if($isLogin['islogin'] == 0){
                        $this->_session->set('login', true);
                        // 在session中设置用户详情
                        \ar\core\comp('lists.session')->set('userDetal', $userDetal);
                        // 设置登录时间及登录状态
                        $loginStatus = [
                            'islogin' => 1,
                            'logintime' => time(),
                            'ip' => \ar\core\comp('tools.Util')->getClientIp()
                        ];
                        UserModel::model()->getDb()->where(['id' => $uid])->update($loginStatus);
                        $this->_session->set('code', null);
                        return true;
                    } else if($isLogin['islogin'] == 1){
                        // 判断上次登录时间距现在是否超过30分钟
                        $loginTime = UserModel::model()->getDb()->where(['id' => $uid])->queryRow();
                        $addTime = time()-$loginTime['logintime'];
                        // 如果非法退出超过30分钟在同一台机器上将登录状态设为未登录
                        if($addTime > 1800){
                            // 判断ip是否正确
                            $ipOld = UserModel::model()->getDb()->where(['id' => $uid])->queryRow();
                            $ipNew = \ar\core\comp('tools.Util')->getClientIp();
                            if($ipNew == $ipOld['ip']){
                                // 如果是同一台客户端，则将登录状态设为未登录
                                $loginStatus0 = ['islogin' => 0,'logintime' => time()];
                                // 更改登录状态
                                $changeLoginStatus0 = UserModel::model()->getDb()->where(['id' => $uid])->update($loginStatus0);
                                // 登录
                                if($changeLoginStatus0){
                                    $this->_session->set('login', true);
                                    // 在session中设置用户详情
                                    \ar\core\comp('lists.session')->set('userDetal', $userDetal);
                                    // 设置登录时间及登录状态
                                    $loginStatus = [
                                        'islogin' => 1,
                                        'logintime' => time(),
                                        \ar\core\comp('tools.Util')->getClientIp()
                                    ];
                                    UserModel::model()->getDb()->where(['id' => $uid])->update($loginStatus);
                                    $this->_session->set('code', null);
                                    return true;
                                } else {
                                    $errorMsg = '登录失败！';
                                }
                            } else {
                                $errorMsg = '此用户已在别的地方登录！';
                            }
                        } else if($addTime < 1800) {
                            $errorMsg = '此用户已在别的地方登录！';
                        }
                    }
                }
            } else {
                $errorMsg = '帐号或者密码错误';
            }
        } else {
            $errorMsg = '验证码错误';
        }
        return $errorMsg;
    }

    // 切换单一登录更改登录状态
    public function changeLoginStatus($id,$onelogin)
    {
        if($onelogin == 1){
            UserModel::model()->getDb()->where(['id' => $id])->update(['islogin' => 1]);
        } else if($onelogin == 0){
            UserModel::model()->getDb()->where(['id' => $id])->update(['islogin' => 0]);
        }

    }

    // 是否登陆
    public function isLogin()
    {
        return !!$this->_session->get('login');
    }

    // 获取当前登录用户详情
    public function getLoginUser()
    {
        $user = \ar\core\comp('lists.session')->get('userDetal');
        $userCondition = ['id' => $user['id']];

        $userDetal = UserModel::model()->getDb()->where($userCondition)->queryRow();

        // 默认不是超级管理员
        $userDetal['isadmin1'] = 0;
        $urs = UserRoleModel::model()->getDb()
            ->where(['uid' => $userDetal['id']])
            ->select('role_id')
            ->queryAll();

        foreach($urs as $ur) {
            // 判断是否为超级管理员
            if($ur['role_id']==1){
                $userDetal['isadmin1'] = 1;
            }
        }

        return $userDetal;
    }

    // 更改用户详情
    public function changeInfo($data)
    {
        $update = UserModel::model()->getDb()
            ->where(['id' => $data['id']])
            ->update($data, 1);

        return $update;
    }

    // 根据id获取单个用户
    public function getOneUser($id)
    {
        $user = UserModel::model()->getDb()
            ->where(['id' => $id])
            ->queryRow();
        $user['group'] = "";
        $urs = UserRoleModel::model()->getDb()
            ->where(['uid' => $user['id']])
            ->select('role_id')
            ->queryAll();

        foreach($urs as $key => $value) {
            $roleName = RoleModel::model()->getDb()->where(['id' => $value['role_id']])->queryRow();
            $urs[$key]['rname'] = $roleName['name'];
            $user['group'] = $user['group'] . " " . $urs[$key]['rname'];
        }

        return $user;
    }

    // 生成登陆验证码
    public function generateCode()
    {
        $_vc = new \arcms\lib\ext\ValidateCode();
        $_vc->doimg();
        $code = $_vc->getCode();// 验证码保存到SESSION中
        // $code = \ar\core\comp('tools.util')->randpw(4, 'NUMBER');
        $this->_session->set('code', $code);
    }

    // 退出
    public function loginout()
    {
        // 获取当前登录用户id
        $user = \ar\core\comp('lists.session')->get('userDetal');
        $loginStatus = ['islogin' => 0];
        // 更改登录状态
        UserModel::model()->getDb()->where(['id' => $user['id']])->update($loginStatus);

        $this->_session->set('login', false);
    }

    // 用户列表
    public function userlist($request)
    {
        // 分页数据
        // $cpage = $request['page'];
        // $cpage = !empty($request['page']) ? $request['page'] : 1;
        $limit = !empty($request['limit']) ? $request['limit'] : 10;

        $condition = [];
        // 搜索
        $key = !empty($request['key']) ? $request['key'] : '';
        if ($key) {
            $condition['nickname like '] = '%'. $key . '%';
        }
        $totalCount = UserModel::model()->getDb()->where($condition)->count();
        $page = new \arcms\lib\ext\Page($totalCount, $limit);

        $users = UserModel::model()->getDb()
            ->where($condition)
            ->limit($page->limit())
            ->order('id desc')
            ->queryAll();

        foreach($users as $user => $uv){
            $urs = UserRoleModel::model()->getDb()
                ->where(['uid' => $uv['id']])
                ->select('role_id')
                ->queryAll();

            $users[$user]['group'] = "";

            foreach($urs as $key => $value) {
                $roleName = RoleModel::model()->getDb()->where(['id' => $value['role_id']])->queryRow();
                $urs[$key]['rname'] = $roleName['name'];
                $users[$user]['group'] = $users[$user]['group'] . " " . $urs[$key]['rname'];
            }
        }



        return [
            'users' => $users,
            'count' => $totalCount
        ];
    }

    // 添加用户
    public function addUser($data)
    {
        if ($uid = $data['id']) {
            // 更新
            return UserModel::model()->getDb()
                ->where(['id' => $uid])
                ->update($data, 1);
        } else {
            $password = UserModel::model()->pwd('123456');
            $data['password'] = $password;
            return UserModel::model()->getDb()->insert($data, 1);
        }
    }

    // 所有用户组列表
    public function getRoleList()
    {
        $role = RoleModel::model()->getDb()
            ->order('id')
            ->queryAll();

        return $role;
    }

    // 添加用户组
    public function addRole($data)
    {
        return RoleModel::model()->getDb()->insert($data, 1);
    }

    // 查找单个用户组
    public function getRoleRow($rid)
    {
        return RoleModel::model()->getDb()->where(['id'=>$rid])->queryRow();
    }

    // 编辑用户组
    public function editRole($data)
    {
        return RoleModel::model()->getDb()->where(['id'=>$data['id']])->update(['name'=>$data['name']]);
    }

    // 删除用户组
    public function delRole($id)
    {
        $role = RoleModel::model()->getDb()->where(['id'=>$id])->queryRow();
        if($role['status']==1){
            UserRoleModel::model()->getDb()->where(['role_id'=>$id])->delete();
            RoleNavModel::model()->getDb()->where(['role_id'=>$id])->delete();
            $del = RoleModel::model()->getDb()->where(['id'=>$id])->delete();
            return $del;
        } else {
            return false;
        }

    }

    // 分配用户组列表
    public function getUserRoleList($uid)
    {
        $role = RoleModel::model()->getDb()
            ->order('id')
            ->queryAll();

        foreach($role as $key => $value) {
            $ur = UserRoleModel::model()->getDb()
                ->where(['role_id' => $role[$key]['id'], 'uid' => $uid])
                ->queryRow();
            if($ur){
                $role[$key]['check'] = 1;
            } else {
                $role[$key]['check'] = 0;
            }
            $role[$key]['uid'] = $uid;
        }

        return $role;
    }

    // 权限列表
    public function getRoleNavList($rid)
    {
        $navsList = \ar\core\service('Data')->navsList("");
        $navs = $navsList['navs'];
        foreach($navs as $key => $value){
            if($navs[$key]['cate']==1){
                $navs[$key]['check'] = 2;
            } else {
                $rn = RoleNavModel::model()->getDb()
                    ->where(['nav_id' => $navs[$key]['nav_id'], 'role_id' => $rid])
                    ->queryRow();
                if($rn){
                    $navs[$key]['check'] = 1;
                } else {
                    $navs[$key]['check'] = 0;
                }
            }

            $navs[$key]['rid'] = $rid;
        }

        return $navs;
    }

    // 分配用户角色
    public function addUserRole($urDetail)
    {
        return UserRoleModel::model()->getDb()->insert($urDetail, 1);
    }

    // 取消分配用户角色
    public function delUserRole($urDetail)
    {
        return UserRoleModel::model()->getDb()->where($urDetail)->delete();
    }

    // 分配角色权限
    public function addRoleNav($rnDetail)
    {
        return RoleNavModel::model()->getDb()->insert($rnDetail, 1);
    }

    // 取消分配角色权限
    public function delRoleNav($rnDetail)
    {
        return RoleNavModel::model()->getDb()->where($rnDetail)->delete();
    }

    // 根据用户id查找用户组id
    public function getRidByUid($uid)
    {
        $roleList = UserRoleModel::model()->getDb()
            ->where(['uid' => $uid])
            ->queryAll();
        return $roleList;

    }

    // 根据用户组id查找权限id
    public function getNidByRid($rid)
    {
        $navList = RoleNavModel::model()->getDb()
            ->where(['role_id' => $rid])
            ->queryAll();
        return $navList;

    }

    // 根据nav_id在Nav表查找链接
    public function getHrefByNid($nid)
    {
        $con = [
            'nav_id' => $nid,
        ];
        $nav = NavModel::model()->getDb()
            ->where($con)
            ->queryRow();
        return $nav['href'];
    }

    // 锁屏
    public function lockUserPsw($uid, $pwd)
    {
        $userConditon = [
            'id' => $uid,
            'password' => UserModel::pwd($pwd),
        ];

        $userCount = UserModel::model()->getDb()->where($userConditon)->queryRow();
        if ($userCount) {
            return true;
        } else {
            return false;
        }
    }

    // 修改密码
    public function checkPsw($oldPwd, $uid, $newPwd)
    {
        $userConditon = [
            'id' => $uid,
            'password' => UserModel::pwd($oldPwd),
        ];

        $userCount = UserModel::model()->getDb()->where($userConditon)->queryRow();
        if ($userCount) {
            return UserModel::model()->getDb()->where(['id' => $uid])->update(['password' => UserModel::pwd($newPwd)]);
        } else {
            return false;
        }
    }

}
