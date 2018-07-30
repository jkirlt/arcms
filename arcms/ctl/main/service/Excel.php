<?php
/**
 * @Author: yaoxf
 * @Date:   2018-07-12 17:39:24
 * @Last Modified by:   Marte
 * @Last Modified time: 2018-07-12 22:38:57
 */
namespace arcms\ctl\main\service;
/**
 * 数据服务组件
 */
class Excel
{
    public function init()
    {

    }

     // 导出成 Excel 格式
    public function downAsExcel($mid)
    {
        $modelInfo = \ar\core\service('Data')->getModel($mid);
        $modelName = '\arcms\lib\model\\'.$modelInfo['modelname'];
        $tableName = $modelInfo['tablename']; 

        if(!empty($modelInfo['explain'])) 
        {
            $fileName = $modelInfo['explain'];
        } else {
            $fileName = $modelInfo['tablename']; 
        }

        // 获取显示的字段名，用作 Excel 表的列名
        $columnIsshows = \arcms\lib\model\CoopadminModelDetail::model()->getDb()
            ->where(['tablename' => $tableName, 'isshow' => 1])
            ->queryAll();

        foreach ($columnIsshows as &$column) {
            $showColumn[] = $column['colname'];  // 字段名
            // 导出的 Excel 的列名
            $downTitle[] = $column['explain'] ? $column['explain'] : $column['colname'];
        }
        
        $downInfos = $modelName::model()->getDb()->select($showColumn)->queryAll();

        foreach ($downInfos as $downInfos_key => &$downInfo) {

            foreach ($downInfo as $downInfo_key => &$downInfo_value) {

                // 在 modeltail 表中查询当前字段类型
                $columnType = \arcms\lib\model\CoopadminModelDetail::model()->getDb()
                    ->where(['tablename' => $tableName, 'colname' => $downInfo_key])
                    ->queryRow();

                switch ($columnType['type']) {
                    case '0':   // 当前字段类型为0，是字符串
                        # code...
                        break;
                    case '1':   // 当前字段类型为1，是多个状态值
                    case '2':   // 当前字段类型为2，是两个状态值
                        $typeexplain = explode('|', $columnType['typeexplain']);
                        foreach ($typeexplain as $typeexplain_key => $typeexplain_value) {
                            $tmp = explode(':', $typeexplain_value);
                            for ($i=0; $i < count($tmp); $i++) { 
                                $tmp2[$tmp[0]] = $tmp[1];
                            }
                        }

                        foreach ($tmp2 as $tmp2_key => $tmp2_value) {
                            if ($downInfo_value == $tmp2_key) {
                                $downInfo_value = $tmp2_value;
                            }
                        }
                        break;
                    
                    case '5':   // 当前字段类型为，是时间戳
                        $downInfo_value = date('Y-m-d H:i:s', $downInfo_value);
                        break;
                    case '8':   // 当前字段类型为8，是外键
                        // 查询外键关联的数据信息
                        $linkTable = \arcms\lib\model\ModelFK::model()->getDb()
                            ->where(['mid' => $mid, 'mcolname' => $downInfo_key])
                            ->queryRow();
                        $linkTableModelName = '\arcms\lib\model\\' . $linkTable['fmodelname'];

                        $linkTableInfos = $linkTableModelName::model()->getDb()
                            ->where([$linkTable['funival'] => $downInfo_value])
                            ->queryRow();
                        // 将原值替换成外键对应边里的字段值
                        $downInfo_value = $linkTableInfos[$linkTable['fcolname']];

                        break;
                    
                    default:
                        # code...
                        break;
                }
            }       

        }

        $this->exportexcel($downTitle, $downInfos, $fileName, './', true);
        
    }

    /** 
     * 数据导出 
     * @param array $title   标题行名称 
     * @param array $data   导出数据 
     * @param string $fileName 文件名 
     * @param string $savePath 保存路径 
     * @param $type   是否下载  false--保存   true--下载 
     * @return string   返回文件全路径 
     * @throws PHPExcel_Exception 
     * @throws PHPExcel_Reader_Exception 
    */  
    public function exportExcel($title=array(), $data=array(), $fileName='', $savePath='./', $isDown=false)
    {  
        define('ARCMS_PATH', dirname(dirname(dirname(dirname(__FILE__)))));
        
        require_once(ARCMS_PATH."\lib\\ext\Classes\PHPExcel.php");
        
        $obj = new \PHPExcel();
  
        //横向单元格标识  
        $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');  
          
        $obj->getActiveSheet(0)->setTitle($fileName);   //设置sheet名称  
        $_row = 1;   //设置纵向单元格标识  
        if($title){  
            $_cnt = count($title);  
            $obj->getActiveSheet(0)->mergeCells('A'.$_row.':'.$cellName[$_cnt-1].$_row);   //合并单元格  
            $obj->setActiveSheetIndex(0)->setCellValue('A'.$_row, '数据导出时间：'.date('Y-m-d H:i:s'));  //设置合并后的单元格内容  
            $_row++;  
            $i = 0;  
            foreach($title AS $v){   //设置列标题  
                $obj->setActiveSheetIndex(0)->setCellValue($cellName[$i].$_row, $v);  
                $i++;  
            }  
            $_row++;  
        }  
  
        //填写数据  
        if($data){  
            $i = 0;  
            foreach($data AS $_v){  
                $j = 0;  
                foreach($_v AS $_cell){  
                    $obj->getActiveSheet(0)->setCellValue($cellName[$j] . ($i+$_row), $_cell);  
                    $j++;  
                }  
                $i++;  
            }  
        }  
      
        //文件名处理  
        if(!$fileName){  
            $fileName = uniqid(time(),true);  
        }  
      
        $objWrite = \PHPExcel_IOFactory::createWriter($obj, 'Excel2007');  
      
        if($isDown){   //网页下载  
            header('pragma:public');  
            header("Content-Disposition:attachment;filename=$fileName.xlsx");  
            $objWrite->save('php://output');exit;  
        }  
  
        $_fileName = iconv("utf-8", "gb2312", $fileName);   //转码  
        $_savePath = $savePath.$_fileName.'.xlsx';  
         $objWrite->save($_savePath);  
      
         return $savePath.$fileName.'.xlsx';  
    }

}