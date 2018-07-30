<?php
/**
 * Powerd by ArPHP.
 *
 * SystemSetting service.
 *
 */
namespace arcms\ctl\main\service;
use arcms\lib\model\ModelList as modelListModel;
use arcms\lib\model\ModelList;
use arcms\lib\model\CoopadminModelDetail;
use arcms\lib\model\ModelFK;

define('COOP_SYSTEM', dirname(dirname(dirname(dirname(dirname(__FILE__))))));
/**
 * 用户服务组件
 */
class SystemSetting
{
    // seesion 组件
    protected $_seesion = null;

    function __construct() {
        $this->_session = \ar\core\comp('lists.session');
    }

    // 数据表
    public function tableLists()
    {
        // 获取公告配置文件中的数据库名
        $dbInfo = $this->dbInfo();
        $tablename_in_db = 'Tables_in_'.$dbInfo['dbname'];

        $sqlShowTables = 'show tables;';
        $tables = \ar\core\comp('db.mysql')->sqlQuery($sqlShowTables);

        $countTables = count($tables);

        $newTables = array();
        $count = 0;
        for ($i=0; $i < $countTables; $i++) {
            $str = $tables[$i][$tablename_in_db];
            // 判断是否为系统表
            if(substr($str, 0, 9) != 'coopadmin') {
                $tableName = $tables[$i][$tablename_in_db];
                $modelRow = ModelListModel::model()->getDb()
                    ->where(['tablename' => $tableName])
                    ->queryRow();
                // 判断是否存在模型
                if(!$modelRow){
                    $newTables[$i]['ismodel'] = '0';
                } else {
                    $newTables[$i]['ismodel'] = '1';
                }
                $newTables[$i]['name'] = $tableName;
                $count++;

            }
        }

        return ['tableLists' => $newTables, 'count' => $count];

    }

    // 模型表
    public function modelLists()
    {
        // $system_model = COOP_SYSTEM."/arcms/lib/model";

        // $handler = opendir($system_model);
        // $count = 1;
        // $arr = array();
        // while (($filename = readdir($handler)) !== false) {
        //     // 略过 linux 目录的名字为‘.’和‘..’的文件
        //     if ($filename != '.' && $filename != '..') {
        //         // 输出文件名
        //         //echo $filename.'<br>';

        //         //$weizhi = strpos($filename, '.');
        //         //echo $weizhi."<hr>";
        //         $modelName = strstr($filename, '.', true);
        //         $arr[]['name'] = $modelName;

        //     }
        // }
        // //var_dump($arr);echo "<hr>";

        // closedir($handler);

        // return ['modelLists' => $arr];


        // 分页数据
        // $cpage = $request['page'];
        // $cpage = !empty($request['page']) ? $request['page'] : 1;
        // $limit = !empty($request['limit']) ? $request['limit'] : 10;
        $limit = \ar\core\request('limit');
        $limit = !empty($limit) ? $limit : 10;

        $condition = [];
        // 搜索
        $key = !empty($request['key']) ? $request['key'] : '';
        if ($key) {
            $condition['modelname like '] = '%'. $key . '%';
        }
        $totalCount = ModelListModel::model()->getDb()->where($condition)->count();
        $page = new \arcms\lib\ext\Page($totalCount, $limit);

        $modelLists = ModelListModel::model()->getDb()
            ->where($condition)
            ->limit($page->limit())
            ->order('id desc')
            ->queryAll();

        return [
            'modelLists' => $modelLists,
            'count' => $totalCount
        ];
    }

    // 生成模型
    public function addModel($data)
    {
        $modelRow = ModelListModel::model()->getDb()
            ->where(['modelname' => $data['modelname']])
            ->queryRow();

        if($modelRow){
            // 如果数据存在则返回false
            return false;
        } else {
            $add = ModelList::model()->getDb()->insert($data, 1);
            if($add) {
                // 创建文件
                $this->changeModelFile($data['modelname'], $data['tablename']);

                // 修改状态
                $update = ModelList::model()->getDb()
                    ->where(['tablename' => $data['tablename']])
                    ->update(['status' => 1]);
                if($update){
                    //return $update;
                    // coopadmin_model_detail表中记录新生成的模型数据表字段信息
                    // 查询新生成模型的数据表的字段
                    $modelTableCols = $this->viewField($data);
                    $totalCols = count($modelTableCols['fileds']);
                    for ($i=0; $i < $totalCols-1; $i++) { // modelTableCols数组中增加了一个数据表名信息，所以需要长度减一
                        $insertCon = [
                            'tablename' => $data['tablename'],
                            'colname' => $modelTableCols['fileds'][$i],
                            'isshow' => 1,
                            'isedit' => 1,
                            'order' => 1
                        ];
                        // coopadmin_model_detail表中插入字段信息
                        $insertModelTableCols = CoopadminModelDetail::model()->getDb()->insert($insertCon,1);

                    }

                    // coopadmin_model_detail 表中，查询刚刚插入的值
                    $newInsertCols = CoopadminModelDetail::model()->getDb()->where(['tablename' => $data['tablename']])->count();
                    if ($newInsertCols == $totalCols-1) {// modelTableCols数组中增加了一个数据表名信息，所以需要长度减一
                        return true;
                    }
                }

            }
        }

    }

    // 生成模型文件
    public function changeModelFile($modelname, $tablename)
    {
        // 路径
        $system_model = COOP_SYSTEM."/arcms/lib/model";

        // 创建文件
        $myfile = fopen($system_model."/".$modelname.".php", "w") or die("Unable to open file!");

        // 文件内容
        $txt = "<?php\n" .
        "namespace arcms\lib\model;\n" .
        "class ". $modelname ." extends \ar\core\Model\n" .
        "{\n" .
        "    public "."$"."tableName = '" . $tablename . "';\n" .
        "}\n";

        // 写入文件内容
        fwrite($myfile, $txt);

        fclose($myfile);

    }

    // 查看数据表字段
    public function viewField($tableDetal)
    {
        $fileds = \ar\core\comp('db.mysql')->table($tableDetal['tablename'])->getColumns();
        $fileds['tablename'] = $tableDetal['tablename'];
        return ['fileds' => $fileds];

    }

    // 查找模型定制功能
    public function getFuncList($tname)
    {
        $model = ModelList::model()->getDb()
            ->where(['tablename' => $tname])
            ->queryRow();
        $mid = $model['id'];
        $funcList = \arcms\lib\model\ModelMenuFunc::model()->getDb()
            ->where(['mid' => $mid])
            ->queryAll();
        return $funcList;
    }

    // coopadmin_model_detail 表中，指定数据表的字段
    public function tableCols($tname)
    {

        $condition = ['tablename' => $tname];

        $tableCols = CoopadminModelDetail::model()->getDb()
            ->where($condition)
            ->order('colname asc, ordernum desc')
            ->queryAll();

        return [
            'tableCols' => $tableCols,
        ];
    }

    // coopadmin_model_detail 表中，指定数据表的外键字段
    public function tableFkCols($tname)
    {
        $condition = ['tablename' => $tname, 'type' => 8];

        $tableCols = CoopadminModelDetail::model()->getDb()
            ->where($condition)
            ->queryAll();

        $model = ModelList::model()->getDb()
            ->where(['tablename' => $tname])
            ->queryRow();
        $modelName = $model['modelname'];
        $modelId = $model['id'];

        foreach($tableCols as &$col){
            $col['modelid'] = $modelId;
            $col['modelname'] = $modelName;

            $col['hasfk'] = 0;

            $mtname = $col['tablename'];
            $mcname = $col['colname'];
            $con = ['mtablename' => $mtname, 'mcolname' => $mcname];
            $hasFk = ModelFK::model()->getDb()->where($con)->queryRow();
            if($hasFk){
                $col['hasfk'] = 1;
            }
        }

        return [
            'tableCols' => $tableCols,
        ];
    }

    // 编辑字段
    public function manageCols($data, $colsInfo, $modelName)
    {
        // 获取数据表公共配置，ip、数据表名称、登录名、密码
        $dbInfo = $this->dbInfo();

        $servername = $dbInfo['servername'];
        $dbusername = $dbInfo['dbusername'];
        $dbpassword = $dbInfo['dbpassword'];
        $dbname = $dbInfo['dbname'];

        if(in_array($data['colname'], $colsInfo)) {  // 字段colname 已存在，编辑;

            return CoopadminModelDetail::model()->getDb()
                ->where(['tablename' => $data['tablename'], 'colname' => $data['colname']])
                ->update($data, 1);
        } else {  // 字段colname 不存在，新增字段
            // 在 $data['table']表中，新增 $data['colname'] 字段，默认 varchar(100) NOT NULL
            $link = mysqli_connect($servername, $dbusername, $dbpassword, $dbname);
            if ($link) {
                mysqli_query($link ,"ALTER TABLE ".$data['tablename']." ADD ".$data['colname']." varchar(100) NOT NULL ") or die(mysql_error());
            } else {
                return false;
            }
            mysqli_close($link);

            // coopadmin_model_detail 表中新增字段
            $addColRecord = CoopadminModelDetail::model()->getDb()
                ->where(['tablename' => $data['tablename']])
                ->insert($data, 1);
            $newCol = CoopadminModelDetail::model()->getDb()->where(['id' => $addColRecord])->queryRow();
            if ($newCol['tablename'] == $data['tablename'] && $newCol['colname'] == $data['colname']) {
                return true;
            } else {
                return false;
            }
        }

    }

    // 获取数据表配置信息
    public function dbInfo()
    {
        $dbinfo = \ar\core\cfg("components.db.mysql.config.read.default");//
        $dbhostInfo = $dbinfo['dsn'];
        $dbusername = $dbinfo['user'];
        $dbpassword = $dbinfo['pass'];

        $arr = explode(";", $dbhostInfo);
        $servername = substr($arr[0], strpos($arr[0], "=")+1);
        $dbname = substr($arr[1], strpos($arr[1], "=")+1);

        return ['servername' => $servername, 'dbusername' => $dbusername, 'dbpassword' => $dbpassword, 'dbname' => $dbname];
    }

    // 在coopadmin_model_detail表中，用数据表名查询已有的字段信息，后续用来判断是编辑或新增字段操作
    public function colsInfo($tablename)
    {
        $con = ['tablename' => $tablename];
        $colsInfo = CoopadminModelDetail::model()->getDb()->where($con)->select(['colname'])->queryAll();

        $arr = array();
        foreach ($colsInfo as $key => $value) {
            $arr[] = $value['colname'];
        }

        return $arr;
    }

    // 获取基本系统信息
    public function systemSetting()
    {
        $systemInfo = \arcms\lib\model\CoopSystemSetting::model()->getDb()->queryRow();
        return ['systemInfo' => $systemInfo];
    }

    // 由控制器生成菜单
    public function addModelMenu($data)
    {
        // 创建文件
        $this->addMenuControllerFile($data);

    }

    // 往menu控制器里面追加内容
    public function addMenuControllerFile($data)
    {
        // 文件
        $filename = COOP_SYSTEM."/arcms/ctl/main/Menu.php";



    }


}
