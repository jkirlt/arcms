<?php
/**
 * 前端基于layuicms2.0 ，后端基于arphp 5.1.14
 *
 * @author assnr assnr@coopcoder.com
 *
 * 本项目仅供学习交流使用，如果用于商业请联系授权
 */
namespace arcms\ctl\main;

use \ar\core\ApiController as Controller;

// json 接口
class Json extends Controller
{
    // 登陆接口
    public function login($userName, $password, $code)
    {
        $errorMsg = $this->getUserService()->login($userName, $password, $code);
        if (is_string($errorMsg)) {
            $this->showJsonError($errorMsg, '6001');
        } else {
            $this->showJsonSuccess('登陆成功', '1000');
        }
    }

    // 更改用户信息
    public function changeUser()
    {
        $data = \ar\core\post();
        $changeSuccess = $this->getUserService()->changeInfo($data);

        if ($changeSuccess) {
            $this->showJsonSuccess('修改成功');
        } else {
            $this->showJsonError('修改失败', '6002');
        }
    }

    // 生成验证码图片
    public function code()
    {
        return $this->getUserService()->generateCode();
    }

    /**需要做验证的接口**/

    // 添加菜单
    public function addMenu()
    {
        $data = \ar\core\post();

        $addSuccess = $this->getDataService()->addMenu($data);
        if ($addSuccess) {
            $this->showJsonSuccess('添加菜单成功');
        } else {
            $this->showJsonError('添加菜单失败', '6002');
        }
    }

    // 删除菜单
    public function delMenu($nav_id)
    {
        $delResult = $this->getDataService()->delMenu($nav_id);
        if ($delResult) {
            $this->showJsonSuccess('删除菜单成功');
        } else {
            $this->showJsonError('删除失败', '6003');
        }
    }

    // 删除系统用户
    public function delAdmin($id)
    {
        // 获取当前登录用户id
        $user = \ar\core\comp('lists.session')->get('userDetal');
        $nowid = $user['id'];

        // 判断是否为超级管理员
        $isadmin1 = 0;
        $urs = \arcms\lib\model\UserRole::model()->getDb()
            ->where(['uid' => $id])
            ->select('role_id')
            ->queryAll();
        foreach($urs as $ur) {
            if($ur['role_id']==1){
                $isadmin1 = 1;
            }
        }

        if($nowid == $id){
            $this->showJsonError('删除失败,不能删除当前登录用户', '6002');
        } else if($isadmin1 == 1){
            $this->showJsonError('删除失败,不能删除超级管理员', '6001');
        } else {
            $delResult = $this->getDataService()->delAdmin($id);
            if ($delResult) {
                $this->showJsonSuccess('删除成功');
            } else {
                $this->showJsonError('删除失败', '6003');
            }
        }

    }

    // 添加自定义功能
    public function funcEdit()
    {
        $data = \ar\core\post();

        $data['updatetime'] = time();

        $addSuccess = $this->getDataService()->addFunc($data);
        if ($addSuccess) {
            $this->showJsonSuccess('添加功能成功');
        } else {
            $this->showJsonError('添加功能失败', '6002');
        }
    }

    // 删除自定义功能
    public function funcDel()
    {
        $id = \ar\core\post('id');

        $delResult = $this->getDataService()->delFunc($id);
        if ($delResult) {
            $this->showJsonSuccess('删除功能成功');
        } else {
            $this->showJsonError('删除失败', '6003');
        }

    }

    // 添加用户
    public function addUser()
    {
        $data = \ar\core\post();

        $addSuccess = $this->getUserService()->addUser($data);
        if ($addSuccess) {
            $this->showJsonSuccess('提交成功，请分配角色');
        } else {
            $this->showJsonError('添加用户失败', '6002');
        }
    }

    public function userList()
    {
        $userlists = $this->getUserService()->userlist($this->request);
        $backJson = [
            'code' => 0,
            'msg' => '',
            'count' => $userlists['count'],
            'data' => $userlists['users'],
        ];
        $this->showJson($backJson, array('data' => true));
        return;
    }

    // 数据表
    public function tableList()
    {
        $tableLists = $this->getSystemSettingService()->tableLists($this->request);
        $backJson = [
            'code' => 0,
            'msg' => '',
            'count' => $tableLists['count'],
            'data' => $tableLists['tableLists'],
        ];
        $this->showJson($backJson, array('data' => true));
        return;
    }

    // 模型表
    public function modelList()
    {
        $modelLists = $this->getSystemSettingService()->modelLists($this->request);
        $backJson = [
            'code' => 0,
            'msg' => '',
            'count' => $modelLists['count'],
            'data' => $modelLists['modelLists'],
        ];
        $this->showJson($backJson, array('data' => true));
        return;
    }

    // 数据表字段
    public function tableCols()
    {
      $data = \ar\core\request();
      $tname = $data['tname'];

        $tableCols = $this->getSystemSettingService()->tableCols($tname);
        $backJson = [
            'code' => 0,
            'msg' => '',
            'data' => $tableCols['tableCols'],
        ];
        $this->showJson($backJson, array('data' => true));
        return;
    }

    // 数据表外键字段
    public function tableFkCols()
    {
        $data = \ar\core\request();
        $tname = $data['tname'];

        $tableCols = $this->getSystemSettingService()->tableFkCols($tname);

        $backJson = [
            'code' => 0,
            'msg' => '',
            'data' => $tableCols['tableCols'],
        ];
        $this->showJson($backJson, array('data' => true));
        return;
    }

    // 添加外键模型
    public function addFkModel()
    {
        $data = \ar\core\post();

        $data['updatetime'] = time();

        $addSuccess = $this->getDataService()->addFk($data);
        if ($addSuccess) {
            $this->showJsonSuccess('添加成功');
        } else {
            $this->showJsonError('添加失败', '6002');
        }
    }

    // 添加外键关联模型名称
    public function manageFkModel()
    {
        $data = \ar\core\post();
        $fid = $data['fid'];
        $mid = $data['id'];
        $addSuccess = $this->getDataService()->updateFkModel($fid,$mid);
        if ($addSuccess) {
            $this->showJsonSuccess('添加成功');
        } else {
            $this->showJsonError('添加失败', '6002');
        }
    }

    // 添加外键关联字段名称
    public function manageFkCol()
    {
        $data = \ar\core\post();
        $did = $data['modelDetialId'];
        $mid = $data['id'];
        $unikey = $data['unikey'];
        $addSuccess = $this->getDataService()->updateFkCol($did,$mid,$unikey);
        if ($addSuccess) {
            $this->showJsonSuccess('添加成功');
        } else {
            $this->showJsonError('添加失败', '6002');
        }
    }

    ###############

    // 生成模型
    public function changeModel()
    {
        $data = \ar\core\post();

        $modelName = $this->convertUnderline($data['modelname']);

        $modelDetail = [
            'modelname' => $modelName,
            'tablename' => $data['modelname']
        ];

        $result = $this->getSystemSettingService()->addModel($modelDetail);
        if ($result) {
            $this->showJsonSuccess('生成模型成功');
        } else {
            $this->showJsonError('生成模型失败', '6002');
        }

    }

    // 生成菜单
    public function changeMenu()
    {
        $data = \ar\core\post();

        $modelDetail = [
            'modelname' => $data['modelname'],
            'tablename' => $data['tablename']
        ];

        $result = $this->getSystemSettingService()->addModelMenu($modelDetail);
        if ($result) {
            $this->showJsonSuccess('生成菜单成功');
        } else {
            $this->showJsonError('生成菜单失败', '6002');
        }
    }

    //将下划线命名转换为驼峰式命名
    function convertUnderline( $str , $ucfirst = true)
    {
        while(($pos = strpos($str , '_'))!==false)
            $str = substr($str , 0 , $pos).ucfirst(substr($str , $pos+1));

        return $ucfirst ? ucfirst($str) : $str;
    }

    // 查看数据表字段
    public function viewField()
    {
        $data = \ar\core\post();
        $modelName = $this->convertUnderline($data['tableName']);
        $tableName = $data['tableName'];

        $tableDetal = [
            'modelname' => $modelName,
            'tablename' => $tableName
        ];

        $result = $this->getSystemSettingService()->viewField($tableDetal);
        $backJson = [
            'code' => 0,
            'msg' => '',
            'data' => $result['fileds'],
        ];
        $this->showJson($backJson, array('data' => true));
        return;

    }

    // 编辑数据表字段  yxf
    public function manageCols()
    {
      $data = \ar\core\post();
      // 在coopadmin_model_detail表中，用数据表名查询已有的字段信息，后续用来判断是编辑或新增字段操作
      $colsInfo = $this->getSystemSettingService()->colsInfo($data['tablename']);

      $modelName = $this->convertUnderline($data['tablename']);  // 模型名称
        if($data['type']==3){
            // 根据模型名查看其它字段是否存在type==3
            $con = ['tablename' => $data['tablename'],'type' => 3];
            $res = $this->getDataService()->getModelDetail($con);
            if($res){
                $this->showJsonError('只允许一个字段为文章', '6005');
            } else {
                $manageSuccess = $this->getSystemSettingService()->manageCols($data, $colsInfo, $modelName);
                if ($manageSuccess) {
                    $this->showJsonSuccess('编辑字段成功');
                } else {
                    $this->showJsonError('编辑字段失败', '6005');
                }
            }
        } else if($data['type']==4){
          // 根据模型名查看其它字段是否存在type==4
          $con = ['tablename' => $data['tablename'],'type' => 4];
          $res = $this->getDataService()->getModelDetail($con);
          if($res){
              $this->showJsonError('只允许一个字段为图片', '6005');
          } else {
              $manageSuccess = $this->getSystemSettingService()->manageCols($data, $colsInfo, $modelName);
              if ($manageSuccess) {
                  $this->showJsonSuccess('编辑字段成功');
              } else {
                  $this->showJsonError('编辑字段失败', '6005');
              }
          }
        } else {
          $manageSuccess = $this->getSystemSettingService()->manageCols($data, $colsInfo, $modelName);
          if ($manageSuccess) {
              $this->showJsonSuccess('编辑字段成功');
          } else {
              $this->showJsonError('编辑字段失败', '6005');
          }
        }

    }


    // 获取一级菜单
    public function topNavs()
    {
        $topNavs = $this->getDataService()->findTopMenu();
        $backJson = [
            'code' => 0,
            'msg' => '',
            'data' => $topNavs['top'],
        ];
        $this->showJson($backJson, array('data' => true));
        return;
    }

    // 获取三级子菜单
    public function getNavs($nav_id)
    {

        // 获取用户能访问的菜单id
        $navsid = $this->getUserRole();

        $res = $this->getDataService()->findChildMenu($nav_id,$navsid)['menu'];
        $this->showJson($res);
    }

    // 获取当前登录用户能访问的菜单id并存入数组
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

    public function navsList()
    {
        $navlists = $this->getDataService()->navslist();
        $backJson = [
            'code' => 0,
            'msg' => '',
            'count' => $navlists['count'],
            'data' => $navlists['navs'],
        ];
        $this->showJson($backJson, array('data' => true));
        return;
    }

    // 添加用户组
    public function addRole()
    {
        $data = \ar\core\post();
        if($data['name']!=""){
            $addSuccess = $this->getUserService()->addRole($data);
            if ($addSuccess) {
                $this->showJsonSuccess('添加成功');
            } else {
                $this->showJsonError('添加失败', '6002');
            }
        } else {
            $this->showJsonError('不能为空', '6002');
        }
    }

    // 编辑用户组
    public function editRoleDo()
    {
        $data = \ar\core\post();

        $result = $this->getUserService()->editRole($data);

        if ($result) {
            $this->showJsonSuccess('编辑成功');
        } else {
            $this->showJsonError('编辑失败', '6002');
        }

    }

    // 删除用户组
    public function delRole($id)
    {
        $delResult = $this->getUserService()->delRole($id);
        if ($delResult) {
            $this->showJsonSuccess('删除成功');
        } else {
            $this->showJsonError('删除失败', '6003');
        }
    }

    // 获取系统基本信息
    public function systemInfo()
    {
      $systemInfo = $this->getSystemSettingService()->systemSetting();
      $backJson = [
            'code' => 0,
            'msg' => '',
            'data' => $systemInfo['systemInfo'],
        ];
        $this->showJson($backJson, array('data' => true));
        return;
    }

    // 用户头像
    public function userFace()
    {
        // 获取当前登录用户id及旧的头像名称
        $userDetal = $this->getUserService()->getLoginUser();
        $uid = $userDetal['id'];
        $userInfo = $this->getUserService()->getOneUser($uid);
        $old = $userInfo['userFace'];
        // $oldImg = substr($old,55);

        // 查找头像文件夹下的所有文件
        $path = AR_ROOT_PATH . 'arcms/themes/main/def/images/userFace';
        $resultFile = array();
        if($handle = opendir($path)){
            while($file=readdir($handle)){
                if($file!='.' && $file!='..'){
                    array_push($resultFile, $file);
                }
            }
        }

        $oldImg = substr($old,strlen($path)-5);

        foreach($resultFile as $r){
            if($r == $oldImg){
                // 删除旧图片
                unlink($path. "/" .$r);
            }
        }


        // 上传图片
        $dstDir = AR_ROOT_PATH . 'arcms/themes/main/def/images/userFace';
        $picName = \ar\core\comp('ext.upload')->upload('file',$dstDir,'img');

        $path = \ar\core\cfg('PATH.PUBLIC') . 'images/userFace/' . $picName['filename'];

        $backJson = [
            'code' => 0,
            'msg' => '',
            'data' => ['src' => $path]
        ];

        if($path){
            $this->showJson($backJson);
            return;
        }

    }

    // 锁屏密码
    public function lockPwd()
    {
        $userDetal = $this->getUserService()->getLoginUser();
        $uid = $userDetal['id'];
        $pwd = \ar\core\post()['password'];

        $unlockSuccess = $this->getUserService()->lockUserPsw($uid, $pwd);

        if ($unlockSuccess) {
            $this->showJsonSuccess('密码正确，立即解锁！', '1000');
        } else {
            $this->showJsonError('密码错误，请重新输入！', '6001');
        }

    }

    // 修改密码
    public function changeUserPwd()
    {
        $data = \ar\core\post();
        if(strlen($data['newPwd']) < 6){
            $this->showJsonError('密码长度不能小于6位', '6001');
        } else if($data['newPwd'] != $data['new2Pwd']){
            $this->showJsonError('两次输入密码不一致，请重新输入！', '6001');
        } else {
            $changePwdSuccess = $this->getUserService()->checkPsw($data['oldPwd'], $data['uid'], $data['newPwd']);
            if($changePwdSuccess){
                $this->showJsonSuccess('修改密码成功！', '1000');
            } else {
                $this->showJsonError('旧密码不正确，修改密码失败！', '6001');
            }
        }
    }

    // 系统参数设置
    public function setSys()
    {
        $data = \ar\core\post();
        $changeSuccess = $this->getIndexService()->changeInfo($data);

        if ($changeSuccess) {
            $this->showJsonSuccess('修改成功');
        } else {
            $this->showJsonError('修改失败', '6002');
        }
    }

    // 上传logo
    public function logoImg()
    {
        // 获旧的logo名称
        $sysInfo = $this->getIndexService()->systemSetting();
        $old = $sysInfo['logo'];
        // $oldImg = substr($old,51);

        // 查找头像文件夹下的所有文件
//        $path = AR_ROOT_PATH . 'arcms/themes/main/def/images/logo';
//        $resultFile = array();
//        if($handle = opendir($path)){
//            while($file=readdir($handle)){
//                if($file!='.' && $file!='..'){
//                    array_push($resultFile, $file);
//                }
//            }
//        }
//
//        $oldImg = substr($old,strlen($path)-5);
//
//        foreach($resultFile as $r){
//            if($r == $oldImg){
//                // 删除旧图片
//                unlink($path. "/" .$r);
//            }
//        }


        // 上传图片
        $dstDir = AR_ROOT_PATH . 'arcms/themes/main/def/images/logo';
        $picName = \ar\core\comp('ext.upload')->upload('file',$dstDir,'img');

        $path = 'https://' . $_SERVER['HTTP_HOST'] . \ar\core\cfg('PATH.PUBLIC') . 'images/logo/' . $picName['filename'];

        $backJson = [
            'code' => 0,
            'msg' => '',
            'data' => ['src' => $path]
        ];

        if($path){
            $this->showJson($backJson);
            return;
        }

    }

    // 上传登录背景图
    public function loginBg()
    {
        // 获旧的背景图名称
        $sysInfo = $this->getIndexService()->systemSetting();
        $old = $sysInfo['loginbg'];
        // $oldImg = substr($old,54);

        // 查找背景图文件夹下的所有文件
//        $path = AR_ROOT_PATH . 'arcms/themes/main/def/images/loginbg';
//        $resultFile = array();
//        if($handle = opendir($path)){
//            while($file=readdir($handle)){
//                if($file!='.' && $file!='..'){
//                    array_push($resultFile, $file);
//                }
//            }
//        }
//
//        $oldImg = substr($old,strlen($path)-5);
//
//        foreach($resultFile as $r){
//            if($r == $oldImg){
//                // 删除旧图片
//                unlink($path. "/" .$r);
//            }
//        }


        // 上传图片
        $dstDir = AR_ROOT_PATH . 'arcms/themes/main/def/images/loginbg';
        $picName = \ar\core\comp('ext.upload')->upload('file',$dstDir,'img');

        $path = 'https://' . $_SERVER['HTTP_HOST'] . \ar\core\cfg('PATH.PUBLIC') . 'images/loginbg/' . $picName['filename'];

        $backJson = [
            'code' => 0,
            'msg' => '',
            'data' => ['src' => $path]
        ];

        if($path){
            $this->showJson($backJson);
            return;
        }
    }

    // 通用上传图片
    public function uploadImg()
    {
        $request = \ar\core\request();
        // 字段名
        $colname = $request['colname'];
        // 字段值
        $colvalue = $request['colvalue'];
        // 模型id
        $mid = $request['mid'];

        // 删除旧图片
        // $oldImg = substr($colvalue,41);
        // 查找图片上传文件夹下的所有文件
//        $path = AR_ROOT_PATH . 'arcms/assets/adminUpload/img';
//        $resultFile = array();
//        if($handle = opendir($path)){
//            while($file=readdir($handle)){
//                if($file!='.' && $file!='..'){
//                    array_push($resultFile, $file);
//                }
//            }
//        }
//
//        $oldImg = substr($colvalue,strlen($path)+11);
//
//        foreach($resultFile as $r){
//            if($r == $oldImg){
//                // 删除旧图片
//                unlink($path. "/" .$r);
//            }
//        }

        // 上传图片
        $dstDir = AR_ROOT_PATH . 'arcms/assets/adminUpload/img';
        $picName = \ar\core\comp('ext.upload')->upload('file',$dstDir,'img');

        $path = 'https://' . $_SERVER['HTTP_HOST'] . \ar\core\cfg('PATH.GPUBLIC') . 'adminUpload/img/' . $picName['filename'];

        $backJson = [
            'code' => 0,
            'msg' => '',
            'data' => ['src' => $path,'colname' => $colname]
        ];

        if($path){
            $this->showJson($backJson);
            return;
        }
    }

    // 富文本编辑器中上传图片
    public function uploadImgByArtice()
    {
        // 上传图片
        $dstDir = AR_ROOT_PATH . 'arcms/assets/adminUpload/articeimg';
        $picName = \ar\core\comp('ext.upload')->upload('file',$dstDir,'img');

        $path = 'https://' . $_SERVER['HTTP_HOST'] . \ar\core\cfg('PATH.GPUBLIC') . 'adminUpload/articeimg/' . $picName['filename'];

        $backJson = [
            'code' => 0,
            'msg' => '',
            'data' => ['src' => $path]
        ];

        if($path){
            $this->showJson($backJson,['data'=>true]);
            return;
        }
    }

    public function modelDataList($mid, $unikey, $keyword)
    {
        $datalists = $this->getDataService()->modelDataList($mid, $unikey, $keyword);
        $backJson = [
            'code' => 0,
            'msg' => '',
            'count' => $datalists['count'],
            'data' => $datalists['data'],
        ];
        $this->showJson($backJson, array('data' => true));
    }

    public function newsList()
    {
        $newslists = $this->getDataService()->newslist($this->request);
        $backJson = [
            'code' => 0,
            'msg' => '',
            'count' => $newslists['count'],
            'data' => $newslists['news'],
        ];
        $this->showJson($backJson, array('data' => true));
    }

    // 添加新闻
    public function addNews()
    {
        $data = \ar\core\post();
        $addSuccess = $this->getDataService()->addNews($data);
        if ($addSuccess) {
             $this->showJsonSuccess('添加新闻成功');
        } else {
             $this->showJsonError('添加新闻失败', '6002');
        }
    }

    // 删除文章
    public function delModelData($mid, $id)
    {
       $delResult = $this->getDataService()->delModelData($mid, $id);
       if ($delResult) {
           $this->showJsonSuccess('删除成功');
       } else {
           $this->showJsonError('删除失败，数据不存在', '6003');
       }
    }


    // 设置置顶
    public function setNewsTop($id, $value)
    {
         $setResult = $this->getDataService()->setNewsTop($id, $value);
         if ($setResult) {
             $this->showJsonSuccess('操作成功');
         } else {
             $this->showJsonError('操作失败，请稍后重试', '6004');
         }
    }

    // 菜单设置可编辑删除
    public function setMenu()
    {
        $request = $this->request;

        $colname = $request['colname'];
        $id = $request['id'];
        $value = $request['value'];

        $setResult = $this->getDataService()->setMenuNow($colname, $id, $value);
        if ($setResult) {
            $this->showJsonSuccess('操作成功');
        } else {
            $this->showJsonError('操作失败，请稍后重试', '6004');
        }

    }

    // 开关自定义功能
    public function setCostomFunc()
    {
        $request = $this->request;

        $colname = $request['colname'];
        $id = $request['id'];
        $value = $request['value'];

        $setResult = $this->getDataService()->setFuncNow($colname, $id, $value);
        if ($setResult) {
            $this->showJsonSuccess('操作成功');
        } else {
            $this->showJsonError('操作失败，请稍后重试', '6004');
        }

    }

    // 开关状态值
    public function setSwitch()
    {
        $request = $this->request;
        $mid = $request['mid'];
        $colname = $request['colname'];
        $id = $request['id'];
        $value = $request['value'];
        $setResult = $this->getDataService()->setSwitchNow($mid, $colname, $id, $value);
        if ($setResult) {
            $this->showJsonSuccess('操作成功');
        } else {
            $this->showJsonError('操作失败，请稍后重试', '6004');
        }

    }

    // 模型数据修改
    public function modelDataEdit($mid)
    {
        $request = $this->request;
        $errMsg = $this->getDataService()->modelDataEdit($mid, $request);
        if (!$errMsg) {
             $this->showJsonSuccess('操作成功');
        } else {
             $this->showJsonError($errMsg);
        }
    }

    // 功能设定
    public function setItem()
    {
        $set = \ar\core\post();

        $data = [
            'id' => 1,
            'onelogin' => $set['onelogin'],
            'settime' => time()
        ];

        $change = $this->getIndexService()->changeInfo($data);
        if($change){
            $this->getUserService()->changeLoginStatus($set['uid'],$set['onelogin']);

            $this->showJsonSuccess('修改成功！', '1000');
        } else {
            $this->showJsonError('修改失败！', '6001');
        }
    }

    // 模型自定义功能列表
    public function coustomFuncData()
    {
        $res = \ar\core\request();
        $tname = $res['tname'];

        $list = $this->getSystemSettingService()->getFuncList($tname);
        $backJson = [
            'code' => 0,
            'msg' => '',
            'data' => $list,
        ];
        $this->showJson($backJson, array('data' => true));
        return;

    }

    // 添加外键模型
    public function addTableExplain()
    {
        $data = \ar\core\post();

        $addSuccess = $this->getDataService()->addTableExplain($data);
        if ($addSuccess) {
            $this->showJsonSuccess('添加成功');
        } else {
            $this->showJsonError('添加失败', '6002');
        }
    }
}
