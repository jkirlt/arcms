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

// 主页数据 接口
class WebData extends Controller
{
    // 导航列表
    public function navList()
    {
        $navlists = $this->getWebService()->navslist();
        $backJson = [
            'code' => 0,
            'msg' => '',
            'count' => $navlists['count'],
            'data' => $navlists['navs'],
        ];
        $this->showJson($backJson, array('data' => true));
        return;
    }

    // 添加菜单功能
    public function addMenu()
    {
        $data = \ar\core\post();

        if($data['cate']==1){
            $count = $this->getWebService()->findTopMenu();
            if($count['count'] < 6){
                $addSuccess = $this->getWebService()->addMenu($data);
                if ($addSuccess) {
                    $this->showJsonSuccess('添加菜单成功');
                } else {
                    $this->showJsonError('添加菜单失败', '6002');
                }
            } else {
                $this->showJsonError('一级导航栏目不能超过6个,添加失败', '6001');
            }
        } else if($data['cate']==2){
            $addSuccess = $this->getWebService()->addMenu($data);
            if ($addSuccess) {
                $this->showJsonSuccess('添加菜单成功');
            } else {
                $this->showJsonError('添加菜单失败', '6002');
            }
        }


    }

}
