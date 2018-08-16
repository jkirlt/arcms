<?php
/**
 * Powerd by ArPHP.
 *
 * User service.
 *
 */
namespace arcms\ctl\main\service;

use arcms\lib\model\ModelList;
use arcms\lib\model\Nav as Nav;
use arcms\lib\model\RoleNav as RoleNavModel;
use arcms\lib\model\ModelFK;
use arcms\lib\model\News as News;
/**
 * 数据服务组件
 */
class Data
{
    public function init()
    {

    }


    // 查找一级菜单
    public function findTopMenu()
    {

        $condition = [
                'cate' => 1
            ];

        $nav = Nav::model()->getDb()
            ->where($condition)
            ->queryAll();

        return [
            'top' => $nav,
        ];
    }

    // 查找用户能访问的一级菜单
    public function findUserTopMenu($topid)
    {
        $nav = [];
        $cont = 0;

        foreach($topid as $t){
            $menu = Nav::model()->getDb()
                ->where(['nav_id'=>$t,'cate'=>1])
                ->queryRow();
            array_push($nav,$menu);
            $cont++;
        }

        return [
            'top' => $nav,
            'cont' => $cont
        ];
    }



    // 查找二级菜单
    public function findSecondMenu()
    {
        $condition = [
            'cate' => 2
        ];

        $nav = Nav::model()->getDb()
            ->where($condition)
            ->queryAll();

        return [
            'second' => $nav,
        ];
    }

    // 查找子级菜单
    public function findChildMenu($id,$navsid)
    {
        $condition = [
            'fid' => $id
        ];

        $navs = [];

        $menus = Nav::model()->getDb()
            ->where($condition)
            ->queryAll();

        foreach($navsid as $key => $value){
            foreach($menus as $k => $v){
                if($v['nav_id'] == $value)
                array_push($navs,$v);
            }
        }

        foreach ($navs as &$nav) {
            if ($nav['modeid']) {
                $nav['href'] =  'Data/dlist/mid/' . $nav['modeid'];
            }
        }

        return [
            'menu' => $navs,
        ];

    }

    // 根据id查找单个顶级菜单
    public function getTopNavOne($id)
    {
        $condition = [
            'cate' => 1,
            'nav_id' => $id
        ];
        $menu = Nav::model()->getDb()
            ->where($condition)
            ->queryRow();
        return $menu;
    }

    // 根据id查找单个菜单
    public function getNavOne($id)
    {
        $condition = [
            'nav_id' => $id
        ];
        $menu = Nav::model()->getDb()
            ->where($condition)
            ->queryRow();
        return $menu;
    }

    // 菜单列表
    public function navslist()
    {

        $totalCount = Nav::model()->getDb()->count();

        // 准备装一级菜单
        $cate1 = [];
        // 准备装二级菜单
        $cate2 = [];
        // 准备装三级菜单
        $cate3 = [];

        $navs = Nav::model()->getDb()
            ->queryAll();
        foreach ($navs as $key => $value) {
            if($value['cate'] == 1){
            } else if($value['cate'] == 2) {
                $navs[$key]['title'] = '&nbsp;&nbsp;' . $value['title'];
            } else if($value['cate'] == 3) {
                $navs[$key]['title'] = '&nbsp;&nbsp;&nbsp;&nbsp;' . $value['title'];
            }
            if($value['fid'] > 0) {
                $row = Nav::model()->getDb()->where(['nav_id' => $value['fid']])->queryRow();
                $navs[$key]['fmenu'] = $row['title'];
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
                    if($v2['fid'] == $v1['nav_id']) {
                        // 放入二级菜单
                        $bigNavs[] = $v2;
                        if($v2['children_code']==1) {
                            foreach ($cate3 as $k3 => $v3) {
                                if($v3['fid'] == $v2['nav_id']) {
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

    // 添加菜单
    public function addMenu($data)
    {
        if ($id = $data['nav_id']) {
            // 更新
            $update = Nav::model()->getDb()
                ->where(['nav_id' => $id])
                ->update($data, 1);
            $fid = $data['fid'];
            $fmenu = Nav::model()->getDb()
                ->where(['nav_id' => $fid])
                ->queryRow();
            if($fmenu['children_code'] == 0){
                Nav::model()->getDb()
                    ->where(['nav_id' => $fid])
                    ->update(['children_code' => 1]);
            }
            return $update;
        } else {
            if ($data['mid']) {
                $data['issystem'] = 0;
                $data['modeid'] = $data['mid'];
            }
            // 写入
            $insert = Nav::model()->getDb()->insert($data, 1);
            // 写入模型菜单表
            if ($data['mid']) {
                \arcms\lib\model\ModelList::model()
                    ->getDb()
                    ->where(['id' => $data['mid']])
                    ->update(['menu' => $insert]);
            }

            $fid = $data['fid'];
            $fmenu = Nav::model()->getDb()
                ->where(['nav_id' => $fid])
                ->queryRow();
            if($fmenu['children_code'] == 0){
                Nav::model()->getDb()
                    ->where(['nav_id' => $fid])
                    ->update(['children_code' => 1]);
            }

            return $insert;
        }
    }

    // 删除菜单
    public function delMenu($id)
    {
        $nav = Nav::model()->getDb()
            ->where(['nav_id' => $id])
            ->queryRow();
        // 判断是否为系统菜单
        if($nav['issystem'] == 1){
            return false;
        } else {
            if ($nav['modeid']) {
                \arcms\lib\model\ModelList::model()
                    ->getDb()
                    ->where(['id' => $nav['modeid']])
                    ->update(['menu' => 0]);
            }
            $del = Nav::model()->getDb()->where(['nav_id' => $id])->delete();
            RoleNavModel::model()->getDb()->where(['nav_id' => $id])->delete();
            return $del;
        }

    }

    // 删除管理员
    public function delAdmin($id)
    {
        $del = \arcms\lib\model\User::model()->getDb()->where(['id' => $id])->delete();
        return $del;

    }

    // 根据id查找自定义功能详情
    public function getFuncById($id)
    {
        return \arcms\lib\model\ModelMenuFunc::model()->getDb()
            ->where(['id' => $id])
            ->queryRow();
    }

    // 根据mid查找所有自定义功能详情
    public function getFuncByMid($mid)
    {
        return \arcms\lib\model\ModelMenuFunc::model()->getDb()
            ->where(['mid' => $mid])
            ->queryAll();
    }

    // 添加自定义功能
    public function addFunc($data)
    {
        if($data['id']){
            $update = \arcms\lib\model\ModelMenuFunc::model()->getDb()
                ->where(['id' => $data['id']])
                ->update($data, 1);
            return $update;
        } else {
            $insert = \arcms\lib\model\ModelMenuFunc::model()->getDb()->insert($data, 1);
            return $insert;
        }
    }

    // 删除自定义功能
    public function delFunc($id)
    {
        $del = \arcms\lib\model\ModelMenuFunc::model()->getDb()
            ->where(['id' => $id])
            ->delete();
        return $del;
    }

    // 模型通用数据列表
    public function modelDataList($mid, $unikey, $keyword)
    {
        // 分页数据
        // $cpage = $request['page'];
        // $cpage = !empty($request['page']) ? $request['page'] : 1;
        $limit = \ar\core\request('limit');
        $limit = !empty($limit) ? $limit : 10;

        $condition = [];

        // 获取模型名称
        $model = \arcms\lib\model\ModelList::model()
            ->getDb()
            ->where(['id' => $mid])
            ->queryRow();

        $modelName = '\arcms\lib\model\\' . $model['modelname'];

        // 查出模型字段
        $modelDetailColumns = \arcms\lib\model\CoopadminModelDetail::model()->getDb()
            ->where(['tablename' => $model['tablename']])
            ->queryAll('colname');

        // 查出 isshow = 1 的模型字段，用于搜索功能
        $columnIsshow = \arcms\lib\model\CoopadminModelDetail::model()->getDb()
            ->where(['tablename' => $model['tablename'], 'isshow' => 1])
            ->order('ordernum desc')
            ->queryAll();
        foreach ($columnIsshow as $key => &$value) {
            $showColumn[] = $value['colname'];
            $typeexplain[] = $value['typeexplain'];
        }


        // modeltail表里查询搜索的字段类型
        $columnType = \arcms\lib\model\CoopadminModelDetail::model()->getDb()
            ->where(['tablename' => $model['tablename'], 'colname' => $showColumn[0]])
            ->queryRow();

        if (empty($keyword)) {
            $totalCount = $modelName::model()->getDb()->where($condition)->count();
            $page = new \arcms\lib\ext\Page($totalCount, $limit);
            // 数据
            $datas = $modelName::model()->getDb()
                ->where($condition)
                ->limit($page->limit())
                ->queryAll();
        } else {
            switch ($columnType['type']) {
                case '0':  // 搜索项是字符串
                    $showColumnCondtion = $showColumn[0] . " like ";
                    $condition[$showColumnCondtion] = '%' . $keyword . '%';

                    $totalCount = $modelName::model()->getDb()->where($condition)->count();
                    $page = new \arcms\lib\ext\Page($totalCount, $limit);
                    // 数据
                    $datas = $modelName::model()->getDb()
                        ->where($condition)
                        ->limit($page->limit())
                        ->queryAll();

                    break;
                case '8':  // 搜索项是外键
                    // 查询关联表名称
                    $linkTable = \arcms\lib\model\ModelFK::model()->getDb()
                        ->where(['mid' => $mid, 'mcolname' => $showColumn[0]])
                        ->queryRow();
                    $linkTableModelName = '\arcms\lib\model\\' . $linkTable['fmodelname'];
                    $con = $linkTable['fcolname'] . " like";
                    $searchCon[$con] = '%' . $keyword . '%';
                    $linkTableInfos = $linkTableModelName::model()->getDb()
                        ->where($searchCon)
                        ->select($linkTable['fcolname'] . "," . $linkTable['funival'])
                        ->queryAll();

                    foreach ($linkTableInfos as $linkTableInfo) {
                        $needs[] = $linkTableInfo[$linkTable['funival']];
                    }

                    $totalCount = count($needs);
                    $page = new \arcms\lib\ext\Page($totalCount, $limit);
                    for ($i = 0; $i < $totalCount; $i++) {
                        // 数据列表
                        $datas = $modelName::model()->getDb()
                            ->where([$showColumn[0] => $needs[$i]])
                            ->limit($page->limit())
                            ->queryAll();

                    }

                    break;
                default:
                    # code...
                    break;
            }

        }

        // 处理格式
        foreach ($datas as &$data) {
            foreach ($data as $keyColumn => &$d) {
                // 唯一键的值
                $unval = $data[$unikey];
                if(isset($modelDetailColumns[$keyColumn])){
                    $keyColumnDetail = $modelDetailColumns[$keyColumn];
                    // 按字段类型处理格式
                    switch($keyColumnDetail['type']) {
                        // type=0 字符串
                        // type=1 多个状态值
                        case '1':
                            // 字段类型说明
                            $typeex = $keyColumnDetail['typeexplain'];
                            // 根据'|'截取字符串并放入数组
                            $str1 = explode("|",$typeex);
                            foreach($str1 as $t){
                                // 截取':'前面的内容
                                $tn1 = substr($t,0,strpos($t, ':'));
                                // 截取':'后面的内容
                                $tn2 = substr($t,strpos($t, ':')+1);
                                if($d==$tn1){
                                    $d=$tn2;
                                }
                            }
                            break;
                        // type=2 开关状态值
                        case '2':
                            // 字段类型说明
                            $typeex = $keyColumnDetail['typeexplain'];
                            $colname = $keyColumnDetail['colname'];
                            // 截取'|'前面的内容
                            $s0 = substr($typeex,0,strpos($typeex, '|'));
                            // 截取':'后面的内容
                            $sn0 = substr($s0,strpos($s0, ':')+1);
                            // 截取'|'后面的内容
                            $s1 = substr($typeex,strpos($typeex, '|')+1);
                            // 截取':'后面的内容
                            $sn1 = substr($s1,strpos($s1, ':')+1);
                            $d==0 ? $check = '' : $check = 'checked';
                            $d='<input type="checkbox" value="'.$unval.'" name="'.$colname.'" lay-skin="switch" lay-filter="'.$colname.'" lay-text="'.$sn1.'|'.$sn0.'"'.$check.'>';
                            break;
                        // type=3 文章
                        case '3':
                            $d = stripslashes($d);
                            $d = stripcslashes($d);
                            break;
                        // type=4 图片
                        case '4':
                            $img = '<img src="' . $d . '" style="height:32px;width:32px;">';
                            $d = $img;
                            break;
                        // type=5 时间戳
                        case '5':
                            $d = date('Y-m-d H:i:s', $d);
                            break;
                        // type=6 整数
                        // type=7 浮点数
                        // type=8 外键
                        case '8':
                            // 根据表名及字段名称查找模型外键关联表信息
                            $mtablename = $keyColumnDetail['tablename'];
                            $mcolname = $keyColumnDetail['colname'];
                            $con = [
                                'mtablename' => $mtablename,
                                'mcolname' => $mcolname
                            ];
                            $fkDetail = $this->getFkModel($con);
                            $fid = $fkDetail['fid'];
                            $unival = $d;
                            if($fid > 0){
                                // 关联模型名
                                $fmodelname = '\arcms\lib\model\\' . $fkDetail['fmodelname'];
                                // 关联模型字段名
                                $fcolname = $fkDetail['fcolname'];
                                // 关联表映射键名 $fkid
                                $fkid = $fkDetail['funival'];
                                // 映射键值 $unival
                                // 查找关联模型信息
                                $fmodelDetail = $fmodelname::model()->getDb()
                                    ->where([$fkid => $unival])
                                    ->queryRow();
                                // 查找关联字段值
                                $d = $fmodelDetail[$fcolname];
                            } else {
                                $d = $unival;
                            }
                            break;
                    }
                }
            }
        }
        return [
            'data' => $datas,
            'count' => $totalCount
       ];
    }

    // 设置开关状态值
    public function setSwitchNow($mid, $colname, $id, $value)
    {
        $uniKey = $this->modelUniqueKey($mid);
        $modelName = $this->getModelName($mid, true);
        $con = [
            $uniKey => $id
        ];
        return $modelName::model()->getDb()
            ->where($con)
            ->update([$colname => (int)$value]);
    }

    // 设置菜单是否可编辑删除
    public function setMenuNow($colname, $id, $value)
    {
        $con = ['id' => $id];

        return \arcms\lib\model\ModelList::model()->getDb()
            ->where($con)
            ->update([$colname => (int)$value]);
    }

    // 开关自定义功能
    public function setFuncNow($colname, $id, $value)
    {
        $con = ['id' => $id];

        return \arcms\lib\model\ModelMenuFunc::model()->getDb()
            ->where($con)
            ->update([$colname => (int)$value]);
    }

    // 添加外键模型
    public function addFk($data)
    {
        return ModelFK::model()->getDb()->insert($data);
    }

    // 更新关联模型名称
    public function updateFkModel($fid,$mid)
    {
        $fmodelDetial = ModelList::model()->getDb()
            ->where(['id' => $fid])
            ->queryRow();
        $data = [
            'fid' => $fid,
            'ftablename' => $fmodelDetial['tablename'],
            'fmodelname' => $fmodelDetial['modelname'],
            'updatetime' => time()
        ];

        return ModelFK::model()->getDb()
            ->where(['id' => $mid])
            ->update($data);
    }

    // 更新关联模型字段名称
    public function updateFkCol($did,$mid,$unikey)
    {
        $fcolDetial = \arcms\lib\model\CoopadminModelDetail::model()->getDb()
            ->where(['id' => $did])
            ->queryRow();
        $uniDetial = \arcms\lib\model\CoopadminModelDetail::model()->getDb()
            ->where(['id' => $unikey])
            ->queryRow();
        $data = [
            'funival' => $uniDetial['colname'],
            'fcolname' => $fcolDetial['colname'],
            'fexplain' => $fcolDetial['explain'],
            'updatetime' => time()
        ];

        return ModelFK::model()->getDb()
            ->where(['id' => $mid])
            ->update($data);
    }

    // 根据表名和字段名获取关联模型
    public function getFkModel($data)
    {
        return ModelFK::model()->getDb()->where($data)->queryRow();
    }

    // 查找所有模型
    public function getModelList()
    {
        $modelLists = \arcms\lib\model\ModelList::model()->getDb()
            ->queryAll();

        return [
            'modelLists' => $modelLists,
        ];
    }

    // 根据模型名称查找模型字段
    public function getCol($tablename)
    {
        $colLists = \arcms\lib\model\CoopadminModelDetail::model()->getDb()
            ->where(['tablename' => $tablename])
            ->queryAll();

        return [
          'colLists' => $colLists,
        ];
    }

    // 删除模板数据
    public function delModelData($mid, $id)
    {
        $uniKey = $this->modelUniqueKey($mid);
        $modelName = $this->getModelName($mid, true);
        return $modelName::model()->getDb()->where([$uniKey => $id])->delete();
    }

    // 根据模型名及条件查看否存在
    public function getModelDetail($con)
    {
         return \arcms\lib\model\CoopadminModelDetail::model()->getDb()
            ->where($con)
            ->queryAll();
    }

    // 获取模型
    public function getModel($mid)
    {
        return \arcms\lib\model\ModelList::model()
            ->getDb()
            ->where(['id' => $mid])
            ->queryRow();
    }

    // 根据表名获取模型
    public function getModelByName($tname)
    {
        return \arcms\lib\model\ModelList::model()
            ->getDb()
            ->where(['tablename' => $tname])
            ->queryRow();
    }

    // 获取模型名称
    public function getModelName($mid, $full = true)
    {
        $model = $this->getModel($mid);
        if ($full) {
            return '\arcms\lib\model\\' . $model['modelname'];
        } else {
            return $model['modelname'];
        }
    }

    // 获取模型数据表
    public function getModelTabelName($mid)
    {
        $model = $this->getModel($mid);
        return $model['tablename'];
    }

    // 获取模型
    public function getModelColumns($mid)
    {
        $model = $this->getModel($mid);
        $columns = \arcms\lib\model\CoopadminModelDetail::model()
            ->getDb()
            ->order('isunique desc, ordernum desc')
            ->where(['tablename' => $model['tablename']])
            ->queryAll();
        foreach ($columns as &$column) {
            $column['colname'] = trim($column['colname']);
            $column['colshowname'] = $column['explain'] ? $column['explain'] : $column['colname'];
            if($column['type']==8){
                $mtablename = $column['tablename'];
                $mcolname = $column['colname'];
                $con = [
                    'mtablename' => $mtablename,
                    'mcolname' => $mcolname
                ];
                $fkDetail = $this->getFkModel($con);
                $fcolname = $fkDetail['fexplain'] ? $fkDetail['fexplain'] : $fkDetail['fcolname'];
                $column['colshowname'] = $column['colshowname'];
            }
            $column['issort'] == 1 ? $column['issort']=true : $column['issort']=false;
        }
        return $columns;
    }

    // 获取模型是否有唯一键
    public function modelHasUniqueKey($mid)
    {
        $model = $this->getModel($mid);
        return \arcms\lib\model\CoopadminModelDetail::model()
            ->getDb()
            ->order('ordernum desc')
            ->where(['isunique' => 1, 'tablename' => $model['tablename']])
            ->count() > 0;
    }

    // 获取模型的唯一键
    public function modelUniqueKey($mid)
    {
        $model = $this->getModel($mid);
        $unikey = \arcms\lib\model\CoopadminModelDetail::model()
            ->getDb()
            ->where(['isunique' => 1, 'tablename' => $model['tablename']])
            ->queryColumn('colname');
        if ($unikey) {
            return $unikey;
        } else {
            return 'id';
        }
    }

    // 获取数据
    public function getDataByUniKey($mid, $id)
    {
        $tableName = $this->getModelTabelName($mid);
        $uniKey = $this->modelUniqueKey($mid);
        return \ar\core\comp('db.mysql')->table($tableName)
            ->where([$uniKey => $id])
            ->queryRow();
    }

    // 编辑数据
    public function modelDataEdit($mid, $request)
    {
        $tableName = $this->getModelTabelName($mid);
        $uniKey = $this->modelUniqueKey($mid);
        $errMsg = '';
        if ($request[$uniKey]) {
            \ar\core\comp('db.mysql')->table($tableName)
                ->where([$uniKey => $request[$uniKey]])
                ->update($request, true);
        } else {
            \ar\core\comp('db.mysql')->table($tableName)
                ->insert($request, 1);
        }
        return $errMsg;
    }

    // 自定义显示列
    public function define_show_column($isshow_column, $allColname, $tablename)
    {
        // 先将原有列全部设置为 isshow = 0
        for ($i=0; $i < count($allColname) ; $i++) { 
            $con = ['tablename' => $tablename, 'colname' => $allColname[$i], 'isshow' => 1];
            $hidden_column = \arcms\lib\model\CoopadminModelDetail::model()
                ->getDb()
                ->where($con)
                ->update(['isshow' => 0], 1);
        }
        // 再根据提交过来的信息，设置指定字段的 isshow = 1
        for ($j=0; $j < count($isshow_column) ; $j++) { 
            $define_show_column = \arcms\lib\model\CoopadminModelDetail::model()
                ->getDb()
                ->where(['tablename' => $tablename, 'colname' => $allColname[$isshow_column[$j]]])
                ->update(['isshow' => 1], 1);
        }
        return true;
    }

    // 添加模型说明
    public function addTableExplain($data)
    {
        return \arcms\lib\model\ModelList::model()
            ->getDb()
            ->where(['id' => $data['id']])
            ->update($data);
    }

    // 高级搜索的弹出页面
    public function searchCond($mid)
    {
        // 当前模型信息
        $modelInfo = \arcms\lib\model\ModelList::model()->getDb()
            ->where(['id' => $mid])
            ->queryRow();

        // 当前数据表显示的字段，即可供搜索的内容
        $modelDetailInfo = \arcms\lib\model\CoopadminModelDetail::model()->getDb()
            ->where(['tablename' => $modelInfo['tablename'], 'isshow' => 1])
            ->queryAll();

        foreach ($modelDetailInfo as $key => $modelDetailInfo_value) {
            $showColumn[] = $modelDetailInfo_value['explain'] ? $modelDetailInfo_value['explain'] : $modelDetailInfo_value['colname'];
            $colname[] = $modelDetailInfo_value['colname'];
        }
        return ['showColumn' => $showColumn, 'colname' => $colname];
    }

    // 高级搜索
    public function setHighSearch($mid, $params)
    {
var_dump($mid, $params);exit('mid, params');
        // 获取高级搜索的条件
        $params = explode('&', $params);

        foreach ($params as $params_key => $params_value) {
            $tmp = explode('=', $params_value);
            if (!empty($tmp[1])) {
                $tmp2['search_col'] = $tmp[0];
                $tmp2['search_keyword'] = $tmp[1];
                
                $searchs[] = $tmp2;
            }
            
        }

        // 当前模型信息
        $modelInfo = \arcms\lib\model\ModelList::model()->getDb()
            ->where(['id' => $mid])
            ->queryRow();
        // 模型名称
        $modelName = '\arcms\lib\model\\' . $modelInfo['modelname'];
        $tableName = $modelInfo['tablename']; 

        $limit = \ar\core\request('limit');
        $limit = !empty($limit) ? $limit : 10;
        
// var_dump($searchs);exit('searchs');
        foreach ($searchs as $searchs_key => $searchs_value) {
            // coopadminModelDetail 表中，查看当前数据表的搜索字段的类型
            $columnType = \arcms\lib\model\CoopadminModelDetail::model()->getDb()
                ->where(['tablename' => $tableName, 'colname' => $searchs_value['search_col']])
                ->queryRow();

            switch ($columnType['type']) {
                case '0':  // 字段类型, 0为字符串 
                    $searchColumnCondtion = $searchs_value['search_col']." like ";
                    $condition[$searchColumnCondtion] = '%'. $searchs_value['search_keyword'] . '%';

                    $totalCount = $modelName::model()->getDb()->where($condition)->count();
                    $page = new \arcms\lib\ext\Page($totalCount, $limit);
                    // 数据
                    $datas = $modelName::model()->getDb()
                        ->where($condition)
                        ->limit($page->limit())
                        ->queryAll();
                    break;
                case '1':  // 字段类型, 1为多个状态值
                    $typeexplain = explode('|', $columnType['typeexplain']);
                    foreach ($typeexplain as $typeexplain_key => $typeexplain_value) {
                        $tmp3 = explode(':', $typeexplain_value);
                        for ($i=0; $i < count($tmp3); $i++) { 
                            $tmp4[$tmp3[0]] = $tmp3[1];
                        }
                    }

                    // 当前搜索的数据表的信息
                    $tableInfos = $modelName::model()->getDb()->queryAll();
                    foreach ($tableInfos as $tableInfo) {
                        foreach ($tmp4 as $tmp4_key => $tmp4_value) {
                            if ($tableInfo[$searchs_value['search_col']] == $tmp4_key) {
                                // 多状态值的含义，比如 islogin=0,则stateMeaning='未登录'，islogin=1,则stateMeaning='已登录'，
                                $tableInfo['stateMeaning'] = $tmp4_value;
                            }
                        }
                    }

                    $condition['stateMeaning like'] = $searchs_value['search_keyword'];
                    $totalCount = $modelName::model()->getDb()->where($condition)->count();
                    $page = new \arcms\lib\ext\Page($totalCount, $limit);

                    // 数据
                    $datas = $modelName::model()->getDb()
                        ->where($condition)
                        ->limit($page->limit())
                        ->queryAll();

                    break;
                case '2':  // 字段类型, 2为两个状态值
                    # code...
                    break;
                case '5':  // 字段类型, 5为时间戳
                    // 当前搜索的数据表的信息
                    $tableInfos = $modelName::model()->getDb()->queryAll();
                    foreach ($tableInfos as $tableInfo) {
                        $tableInfo[$searchs_value['search_col']] = date('Y-m-d', $searchs_value['search_col']);
                    }

                    $condition[$searchs_value['search_col']." like"] = '%'. $searchs_value['search_keyword'] . '%';

                    $totalCount = $modelName::model()->getDb()->where($condition)->count();
                    $page = new \arcms\lib\ext\Page($totalCount, $limit);
                    // 数据
                    $datas = $modelName::model()->getDb()
                        ->where($condition)
                        ->limit($page->limit())
                        ->queryAll();

                    break;
                case '8':  // 字段类型, 8为外键
                    // 查询关联表名称
                    $linkTable = \arcms\lib\model\ModelFK::model()->getDb()
                        ->where(['mid' => $mid, 'mcolname' => $searchs_value['search_col']])
                        ->queryRow();         
                    $linkTableModelName = '\arcms\lib\model\\' . $linkTable['fmodelname'];
var_dump($linkTable);exit('linkTable');
                    // $con = $searchs_value['search_col']." like";
                    $con = $linkTable['fcolname']." like";
                    $searchCon[$con] = '%'. $searchs_value['search_keyword'] . '%';

                    $linkTableInfos = $linkTableModelName::model()->getDb()
                        ->where($searchCon)
                        ->select($linkTable['fcolname'].",".$linkTable['funival'])
                        ->queryAll();
                   
                    foreach ($linkTableInfos as $linkTableInfo) {
                        $needs[] = $linkTableInfo[$linkTable['funival']];
                    }
var_dump($linkTableModelName, $linkTableInfos, $needs);
exit('linkTableModelName, linkTableInfos, needs');
                    $totalCount = count($needs);
                    $page = new \arcms\lib\ext\Page($totalCount, $limit);
                    for ($i=0; $i < $totalCount; $i++) { 
                        // 数据
                        $datas = $modelName::model()->getDb()
                            ->where([$searchs_value['search_col'] => $needs[$i]])
                            ->limit($page->limit())
                            ->queryAll();
                        
                    }
                    break;
                
                default:
                    # code...
                    break;
            }



        }

        return $datas;
        

       

//         $want = $needs;
//          // 搜索
//         if (!empty($want)) {
//             for ($k=0; $k < count($want); $k++) { 
//                 foreach ($want as $want_key => $want_value) {
//                     $tmp3 = $want_value['needs_key']." like";
//                     $condition[$tmp3] = '%'. $want_value['needs_value'] . '%';
//                 }
                
//             }
            
//         }

//         $limit = \ar\core\request('limit');
//         $limit = !empty($limit) ? $limit : 10;
//         $totalCount = $modelName::model()->getDb()->where($condition)->count();
//         $page = new \arcms\lib\ext\Page($totalCount, $limit);

//         // 高级搜索
//         $searchResult = $modelName::model()->getDb()
//             ->where($condition)
//             ->limit($page->limit())
//             ->queryAll();

// var_dump($searchResult);exit('searchResult');

//        //  return [
//        //      'data' => $searchResult,
//        //      'count' => $totalCount
//        // ];
//         return $searchResult;


    }

}
