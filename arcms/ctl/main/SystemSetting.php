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
class SystemSetting extends Controller
{
    public function basicParameter()
    {
        $this->display('/systemSetting/basicParameter');
    }

    public function logs()
    {
        $this->display('/systemSetting/logs');
    }

    public function linkList()
    {
        $this->display('/systemSetting/linkList');
    }

    public function linksAdd()
    {
        $this->display('/systemSetting/linksAdd');
    }

    public function icons()
    {
        $this->display('/systemSetting/icons');
    }

    public function menuAdd()
    {
        $topNavs = $this->getDataService()->findTopMenu();
        $secondNavs = $this->getDataService()->findSecondMenu();
        $isToModel = 0;
        // 模型增加菜单
        $mid = \ar\core\request('mid');
        if ($mid) {
            $model = $this->getDataService()->getModel($mid);
            $isToModel = 1;
            $this->assign(['model' => $model]);
        }

        $this->assign(['topMenu' => $topNavs['top']]);
        $this->assign(['secondMenu' => $secondNavs['second']]);
        $this->assign(['isToModel' => $isToModel]);
        $this->display('/systemSetting/menuAdd');
    }

    // 数据表字段
    public function showFields()
    {
        $this->display('/systemSetting/showFields');
    }

    // 管理数据表字段
    public function manageCols()
    {
        $this->display('/systemSetting/manageCols');

    }

    // 管理模型关联外键
    public function viewFK()
    {
        $this->display('/systemSetting/viewFK');
    }

    // 查看关联模型页面
    public function modelFkView()
    {
        $data = \ar\core\request();
        $mtable = $this->getDataService()->getFkModel($data);

        $this->assign(['fk' => $mtable]);
        $this->display('/systemSetting/modelFkView');
    }

    // 编辑外键模型页面
    public function modelFkEdit()
    {
        $data = \ar\core\request();
        $mtable = $this->getDataService()->getFkModel($data);
        $modelList = $this->getDataService()->getModelList();

        $this->assign(['fk' => $mtable]);
        $this->assign(['modellist' => $modelList['modelLists']]);
        $this->display('/systemSetting/modelFkEdit');

    }

    // 编辑外键模型字段页面
    public function modelColFkEdit()
    {
        $data = \ar\core\request();
        $id = $data['id'];
        $con = ['id' => $id];

        $fktable = $this->getDataService()->getFkModel($con);

        $tablename = $fktable['ftablename'];
        $colList = $this->getDataService()->getCol($tablename);

        $this->assign(['fk' => $fktable]);
        $this->assign(['collist' => $colList['colLists']]);
        $this->display('/systemSetting/modelColFkEdit');
    }

    // 模型菜单自定义功能列表
    public function coustomFunc()
    {
        $this->display('/systemSetting/coustomFunc');
    }

    // 添加模型说明页面
    public function addModelExplain()
    {
        $data = \ar\core\request();

        $model = $this->getDataService()->getModel($data['id']);

        $this->assign(['model' => $model]);
        $this->display('/systemSetting/addModelExplain');
    }


}
