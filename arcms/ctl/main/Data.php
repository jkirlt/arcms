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
class Data extends Base
{
    // 列表
    public function dlist()
    {
        $mid = \ar\core\request('mid');
        // 获取模型
        $columns = $this->getDataService()->getModelColumns($mid);
        // 获取模型是否有唯一键
        $hasUnique = $this->getDataService()->modelHasUniqueKey($mid);
        // 获取模型的唯一键
        $uniKey = $this->getDataService()->modelUniqueKey($mid);
        // 获取模型
        $modelDetail = $this->getDataService()->getModel($mid);

        $hasFunc = 0;
        // 获取模型的自定义功能
        $func = $this->getDataService()->getFuncByMid($mid);
        if($func){
            $hasFunc = 1;
            $this->assign(['func' => $func]);
        }

        $this->assign(['columns' => $columns, 'hasUnique' => $hasUnique, 'uniKey' => $uniKey]);
        $this->assign(['hasFunc' => $hasFunc, 'modelDetail' => $modelDetail]);
        $this->display();
    }

    // 数据列表
    public function dataList()
    {
        $mid = \ar\core\request('mid');
        // 获取模型
        $columns = $this->getDataService()->getModelColumns($mid);
        // 获取模型是否有唯一键
        $hasUnique = $this->getDataService()->modelHasUniqueKey($mid);
        // 获取模型的唯一键
        $uniKey = $this->getDataService()->modelUniqueKey($mid);
        // 获取数据
        $datalists = $this->getDataService()->modelDataList($mid);

        $this->assign(['columns' => $columns, 'hasUnique' => $hasUnique, 'uniKey' => $uniKey]);
        $this->assign(['count' => $datalists['count'],'data' => $datalists['data']]);
        $this->display();
    }

    // 编辑页面
    public function edit()
    {
        $mid = \ar\core\request('mid');
        $id = \ar\core\request('id');
        $columns = $this->getDataService()->getModelColumns($mid);
        $doedit = 0;
        if ($id) {
            $row = $this->getDataService()->getDataByUniKey($mid, $id);
            $doedit = 1;
            $this->assign(['row' => $row]);
        }

        $hasUnique = $this->getDataService()->modelHasUniqueKey($mid);
        $uniKey = $this->getDataService()->modelUniqueKey($mid);

        $this->assign(['columns' => $columns, 'hasUnique' => $hasUnique, 'uniKey' => $uniKey]);
        $this->assign(['doedit' => $doedit]);
        $this->display();
    }

    // 自定义显示列
    public function define_show_column()
    {
        $mid = \ar\core\request('mid');
        $columns = $this->getDataService()->getModelColumns($mid);
    
        foreach ($columns as $values) {
            $allColname[] = $values['colshowname'];
            $showStatus[] = $values['isshow'];
        }
        
        $this->assign(['allColname' => $allColname, 'showStatus' => $showStatus]);
        $this->display();
    }

    // 编辑显示列
    public function edit_show_column()
    {
        $mid = \ar\core\request('mid');
        $params = \ar\core\request('params');

        $columns = $this->getDataService()->getModelColumns($mid);
        foreach ($columns as $values) {
            // $allColname[] = $values['colshowname'];
            $allColname[] = $values['colname'];
            $showStatus[] = $values['isshow'];
            $tablename = $values['tablename'];
        }

        $isshow_column = explode('&', $params);
        
        $define_show_column = $this->getDataService()->define_show_column($isshow_column, $allColname, $tablename);
        if ($define_show_column) {
            $this->showJson(['define_show_column' => $define_show_column]);
        }else{
            $this->showJsonError('反正就是不对头！');
        }
     
    }

    // 导出成 Excel 格式
    public function downAsExcel()
    {
        $mid = \ar\core\request('mid');
        
        // $downResult = $this->getDataService()->downAsExcel($mid);
        $downResult = $this->getExcelService()->downAsExcel($mid);
        return true;
    }

    // 编辑自定义功能
    public function editFunc()
    {
        $tname = \ar\core\request('tname');
        $id = \ar\core\request('id');

        $modelDetail = $this->getDataService()->getModelByName($tname);
        $doedit = 0;

        if($id){
            $funcDetail = $this->getDataService()->getFuncById($id);
            $doedit = 1;
            $this->assign(['funcDetail' => $funcDetail]);
        }

        $this->assign(['modelDetail' => $modelDetail, 'doedit' => $doedit]);
        $this->display();
    }

    // 选择高级搜索项的弹出页面
    public function highSearch()
    {
        $mid = \ar\core\request('mid');

        // 搜索的条件
        $searchCond = $this->getDataService()->searchCond($mid);

        $this->assign([
                'showColumn' => $searchCond['showColumn'], 
                'colname' => $searchCond['colname']
                ]);

        $this->display('/data/highSearch');

    }

    // 根据关键字进行高级搜索
    public function setHighSearch()
    {
        $mid = \ar\core\request('mid');
        $params = \ar\core\request('params');

        // 高级搜索
        $setHighSearch = $this->getDataService()->setHighSearch($mid, $params);
       
        if ($setHighSearch) {
            $this->showJson(['setHighSearch' => $setHighSearch]);
        }else{
            $this->showJsonError('没有此类信息，请更换搜索条件', '3001');
            
        }
        
    }

}
