<?php
/**
 * Powerd by ArPHP.
 *
 * User service.
 *
 */
namespace arcms\ctl\main\service;

use arcms\lib\model\WebNav as WebNav;
/**
 * 数据服务组件
 */
class Web
{
    public function init()
    {

    }

    // 菜单列表
    public function navslist()
    {
        $totalCount = WebNav::model()->getDb()->count();

        // 准备装一级菜单
        $cate1 = [];
        // 准备装二级菜单
        $cate2 = [];
        // 准备装三级菜单
        $cate3 = [];

        $navs = WebNav::model()->getDb()
            ->queryAll();
        foreach ($navs as $key => $value) {
            if($value['cate'] == 1){
            } else if($value['cate'] == 2) {
                $navs[$key]['name'] = '&nbsp;&nbsp;' . $value['name'];
            } else if($value['cate'] == 3) {
                $navs[$key]['name'] = '&nbsp;&nbsp;&nbsp;&nbsp;' . $value['name'];
            }
            if($value['fid'] > 0) {
                $row = WebNav::model()->getDb()->where(['id' => $value['fid']])->queryRow();
                $navs[$key]['fmenu'] = $row['name'];
            } else if($value['fid'] == 0) {
                $navs[$key]['fmenu'] = '顶级菜单';
            }

        }

        // 往三个数组里面装
        foreach ($navs as $key => $value) {
            if($value['cate'] == 1){
                $cate1[] = $value;
            } else if($value['cate'] == 2) {
                $cate2[] = $value;
            } else if($value['cate'] == 3) {
                $cate3[] = $value;
            }

        }

        // 排序装入新数组
        $bigNavs = [];
        foreach ($cate1 as $k1 => $v1) {
            // 放入一级菜单
            $bigNavs[] = $v1;
            if($v1['children_code']==1) {
                foreach ($cate2 as $k2 => $v2) {
                    if($v2['fid'] == $v1['id']) {
                        // 放入二级菜单
                        $bigNavs[] = $v2;
                        if($v2['children_code']==1) {
                            foreach ($cate3 as $k3 => $v3) {
                                if($v3['fid'] == $v2['id']) {
                                    // 放入三级菜单
                                    $bigNavs[] = $v3;
                                }
                            }
                        }
                    }
                }
            }
        }

        return [
            'navs' => $bigNavs,
            'count' => $totalCount
        ];
    }

    // 查找一级菜单
    public function findTopMenu()
    {

        $condition = [
            'cate' => 1
        ];

        $totalCount = WebNav::model()->getDb()
            ->where($condition)
            ->count();

        $nav = WebNav::model()->getDb()
            ->where($condition)
            ->queryAll();

        return [
            'top' => $nav,
            'count' => $totalCount
        ];
    }

    // 查找二级菜单
    public function findSecondMenu()
    {
        $condition = [
            'cate' => 2
        ];

        $nav = WebNav::model()->getDb()
            ->where($condition)
            ->queryAll();

        return [
            'second' => $nav,
        ];
    }

    // 根据id查找单个菜单
    public function getNavById($id)
    {
        $data = WebNav::model()->getDb()
            ->where(['id' => $id])
            ->queryRow();
        return $data;
    }

    // 添加新栏目
    public function addMenu($data, $title)
    {
        $data['title'] = $title;
        // 写入
        $insert = WebNav::model()->getDb()->insert($data, 1);
        $fid = $data['fid'];
        $fmenu = $this->getNavById($fid);
        if($fmenu['children_code'] == 0){
            WebNav::model()->getDb()
                ->where(['id' => $fid])
                ->update(['children_code' => 1]);
        }
        if($fmenu['target'] == 1){
            WebNav::model()->getDb()
                ->where(['id' => $fid])
                ->update(['target' => 0]);
        }

        return $insert;
    }

}
