<?php
/**
 * Powerd by ArPHP.
 *
 * Index service.
 *
 */
namespace arcms\ctl\main\service;

class Index
{
    // 读取系统基本信息
    public function systemSetting()
    {
        $systemInfo = \arcms\lib\model\CoopSystemSetting::model()->getDb()->queryRow();

        // 网站首页
        $systemInfo['homepage'] = $_SERVER['HTTP_HOST'];
        // 服务器ip
        $systemInfo['addr'] = $_SERVER['SERVER_ADDR'];
        // 服务器环境
        $systemInfo['server'] = php_uname();
        // 服务器端口
        $systemInfo['port'] = $_SERVER['SERVER_PORT'];
        // php版本
        $systemInfo['phpv'] = PHP_VERSION;
        // 数据库版本
        // $systemInfo['database'] = mysql_get_server_info();
        // 最大上传限制
        $systemInfo['maxupload'] = get_cfg_var ("upload_max_filesize")?get_cfg_var ("upload_max_filesize"):"不允许";
        return $systemInfo; 
    }

    // 更改系统参数
    public function changeInfo($data)
    {
        $update = \arcms\lib\model\CoopSystemSetting::model()->getDb()
            ->where(['id' => $data['id']])
            ->update($data, 1);

        return $update;
    }


}
