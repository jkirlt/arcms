<?php
define('AR_OUTER_START', true);
include '../index.php';
if (file_exists(AR_ROOT_PATH . 'cfg/install.lock')) {
		exit('请先删除cfg/install.lock');
}

header("Content-type: text/html;charset=utf-8");
error_reporting(E_ERROR | E_PARSE);
@set_time_limit(0);
ini_set("magic_quotes_runtime", 0);
if(PHP_VERSION < '4.1.0') {
	$_GET         = &$HTTP_GET_VARS;
	$_POST        = &$HTTP_POST_VARS;
	$_COOKIE      = &$HTTP_COOKIE_VARS;
	$_SERVER      = &$HTTP_SERVER_VARS;
	$_ENV         = &$HTTP_ENV_VARS;
	$_FILES       = &$HTTP_POST_FILES;
}
function randStr($i){
  $str = "abcdefghijklmnopqrstuvwxyz";
  $finalStr = "";
  for($j=0;$j<$i;$j++)
  {
    $finalStr .= substr($str,mt_rand(0,25),1);
  }
  return $finalStr;
}
function deldir_in($fileDir,$type = 0){
	@clearstatcache();
	$fileDir = substr($fileDir, -1) == '/' ? $fileDir : $fileDir . '/';
	if(!is_dir($fileDir)){
		return false;
	}
	$resource = opendir($fileDir);
	@clearstatcache();
	while(($file = readdir($resource))!== false){
		if($file == '.' || $file == '..'){
			continue;
		}
		if(!is_dir($fileDir.$file)){
			delfile_in($fileDir.$file);
		}else{
			deldir_in($fileDir.$file);
		}
	}
	closedir($resource);
	@clearstatcache();
	if($type==0)rmdir($fileDir);
	return true;
}

function delfile_in($fileUrl){
	@clearstatcache();
	if(file_exists($fileUrl)){
		unlink($fileUrl);
		return true;
	}else{
		return false;
	}
	@clearstatcache();
}

define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
isset($_REQUEST['GLOBALS']) && exit('Access Error');
foreach(array('_COOKIE', '_POST', '_GET') as $_request) {
	foreach($$_request as $_key => $_value) {
		$_key{0} != '_' && $$_key = daddslashes($_value);
	}
}
$m_now_time     = time();
$m_now_date     = date('Y-m-d H:i:s',$m_now_time);
$nowyear    = date('Y',$m_now_time);
$localurl="http://";
$localurl.=$_SERVER['HTTP_HOST'].$_SERVER["PHP_SELF"];
$install_url=$localurl;

if(file_exists('../config/install.lock')){
	exit('对不起，该程序已经安装过了。<br/>
	      如你要重新安装，请手动删除config/install.lock文件。');
}
deldir_in('../cache', 1);
switch ($action)
{
	case 'apitest':
	{
		$post=array('t'=>'t');
    echo 'ok';
		// echo curl_post($post,15);
		die();
	}
	case 'inspect':
	{
		$mysql_support = (function_exists( 'mysqli_connect')) ? ON : OFF;
		if(function_exists( 'mysqli_connect')){
			$mysql_support  = 'ON';
			$mysql_ver_class ='OK';
		}else {
			$mysql_support  = 'OFF';
			$mysql_ver_class ='WARN';
		}
		if(PHP_VERSION<'5.3.0'){
			$ver_class = 'WARN';
			$errormsg['version']='php 版本过低';
		}else {
			$ver_class = 'OK';
			$check=1;
		}
		$function='OK';
		if(!function_exists('file_put_contents')){
			$function='WARN';
			$fstr.="<li class='WARN'>空间不支持file_put_contents函数，系统无法写文件。</li>";
		}
		if(!function_exists('fsockopen')&&!function_exists('pfsockopen')&&!function_exists('stream_socket_client')){
			$function='WARN';
			$fstr.="<li class='WARN'>空间不支持fsockopen，pfsockopen,stream_socket_client函数，系统邮件功能不能使用。请至少开启其中一个。</li>";
		}
		if(!function_exists('copy')){
			$function='WARN';
			$fstr.="<li class='WARN'>空间不支持copy函数，无法上传文件。</li>";
		}
		if(!function_exists('fsockopen')&&!function_exists('pfsockopen')&&(!get_extension_funcs('curl')||!function_exists('curl_init')||!function_exists('curl_setopt')||!function_exists('curl_exec')||!function_exists('curl_close'))){
				$function='WARN';
				$fstr.="<li class='WARN'>空间不支持fsockopen，pfsockopen函数，curl模块(需同时开启curl_init,curl_setopt,curl_exec,curl_close)，系统在线更新，短信发送功能无法使用。请至少开启其中一个。</li>";
		}
		if(!get_extension_funcs('gd')){
			$function='WARN';
			$fstr.="<li class='WARN'>空间不支持gd模块，图片打水印和缩略生成功能无法使用。</li>";
		}
		if(!function_exists('gzinflate')){
			$function='WARN';
			$fstr.="<li class='WARN'>空间不支持gzinflate函数，无法在线解压ZIP文件。（无法通过后台上传模板和数据备份文件）</li>";
		}
		if(!function_exists('fopen')){
			$function='WARN';
			$fstr.="<li class='WARN'>空间不支持fopen函数，无法在线解压ZIP文件。（无法通过后台上传模板和数据备份文件）</li>";
		}
		if(!function_exists('opendir')){
			$function='WARN';
			$fstr.="<li class='WARN'>空间不支持opendir函数，无法在线解压ZIP文件。（无法通过后台上传模板和数据备份文件）</li>";
		}
		if(!function_exists('crc32')){
			$function='WARN';
			$fstr.="<li class='WARN'>空间不支持crc32函数，无法在线解压ZIP文件。（无法通过后台上传模板和数据备份文件）</li>";
		}
		if(!function_exists('gzopen')){
			$function='WARN';
			$fstr.="<li class='WARN'>空间不支持gzopen函数，无法在线解压ZIP文件。（无法通过后台上传模板和数据备份文件）</li>";
		}
		if(!function_exists('unpack')){
			$function='WARN';
			$fstr.="<li class='WARN'>空间不支持unpack函数，无法在线解压ZIP文件。（无法通过后台上传模板和数据备份文件）</li>";
		}
		if(!function_exists('bin2hex')){
			$function='WARN';
			$fstr.="<li class='WARN'>空间不支持bin2hex函数，无法在线解压ZIP文件。（无法通过后台上传模板和数据备份文件）</li>";
		}
		if(!function_exists('pack')){
			$function='WARN';
			$fstr.="<li class='WARN'>空间不支持pack函数，无法在线解压ZIP文件。（无法通过后台上传模板和数据备份文件）</li>";
		}
		if(!function_exists('php_uname')){
			$function='WARN';
			$fstr.="<li class='WARN'>空间不支持php_uname函数，无法在线解压ZIP文件。（无法通过后台上传模板和数据备份文件）</li>";
		}
		if(!function_exists('ini_set')){
			$function='WARN';
			$fstr.="<li class='WARN'>空间不支持ini_set函数，系统无法正常包含文件，导致后台会出现空白现象。</li>";
		}



        if(!function_exists('mb_strlen')){
			$function='WARN';
			$fstr.="<li class='WARN'>空间不支持mb_strlen函数，系统无法正常包含文件，会导致前台显示不全。</li>";
		}


		session_start();
		if($_SESSION['install']!='coopcoder'){
			$function='WARN';
			$fstr.="<li class='WARN'>空间不支持session，无法登陆后台。</li>";
		}
		$w_check=array(
		'../assets/',
		'../ctl/',
		'../lib/',
		'../themes/',
		'../view/',
		);
		$class_chcek=array();
		$check_msg = array();
		$count=count($w_check);
		for($i=0; $i<$count; $i++){
			if(!file_exists($w_check[$i])){
				$check_msg[$i].= '文件或文件夹不存在请上传';$check=0;
				$class_chcek[$i] = 'WARN';
			} elseif(is_writable_met($w_check[$i])){
				$check_msg[$i].= '通 过';
				$class_chcek[$i] = 'OK';
				$check=1;
			} else{
				$check_msg[$i].='777属性检测不通过'; $check=0;
				$class_chcek[$i] = 'WARN';
			}
			if($check!=1 and $disabled!='disabled'){$disabled = 'disabled';}
		}
		include template('inspect');
		break;
	}
	case 'db_setup':
	{
		if($setup==1){
			$db_prefix      = trim($db_prefix);
            if (strstr($db_host, ":")) {
                $arr = explode(":", $db_host);
                $db_host = $arr[0];
                $db_port = $arr[1];
            }else{
                $db_host        = trim($db_host);
                $db_port           = "3306";
            }
            $db_username    = trim($db_username);
            $db_pass        = trim($db_pass);
            $db_name        = trim($db_name);
			$db_port        = trim($db_port);
			$config="<?php
      /**
       * 数据库配置文件
       */
      return array(
          // 组件配置
          'components' => array(
              // 依赖懒加载组件
             'lazy' => true,
             // db 组件配置
             'db' => array(
                  // 定义组件名称mysql
                 'mysql' => array(
                      // 通用配置格式
                     'config' => array(
                         'read' => array(
                             'default' => array(
                                 'dsn' => 'mysql:host={$db_host};dbname={$db_name};port={$db_port}',
                                  // 用户名
                                 'user' => '{$db_username}',
                                  // 密码
                                 'pass' => '{$db_pass}',
                                 // 表前缀 建议为空
                                 'prefix' => '',
                                 // 连接选项 数据库需要支持PDO扩展 PDO开启后请取消注释下面行，否则看不到SQL报错或者编码错误
                                 'option' => array(
                                      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                                      PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                                      PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
                                  ),
                              ),
                          ),
                      ),
                  ),
              ),
          ),
      );";

			$fp=fopen("../../cfg/base.php",'w+');
			fputs($fp,$config);
			fclose($fp);
			$db = mysqli_connect($db_host,$db_username,$db_pass,'',$db_port) or die('连接数据库失败: ' . mysqli_connect_error());
			if(!@mysqli_select_db($db , $db_name)){
				mysqli_query($db, "CREATE DATABASE $db_name ") or die('创建数据库失败'.mysqli_error($db));
			}
			mysqli_select_db($db , $db_name);
			if(mysqli_get_server_info($db)>4.1){
			 mysqli_query($db , "set names utf8");
			}
			if(mysqli_get_server_info($db)>'5.0.1'){
                mysqli_query($db , "SET sql_mode=''",$link);
			}
			if(mysqli_get_server_info($db)>='4.1'){
                mysqli_query($db , "set names utf8");
				$content=readover("coopadmin.sql");
                #$content=preg_replace("/{#(.+?)}/eis",'$lang[\\1]',$content);
                $content = preg_replace_callback("/{#(.+?)}/is", function($r)use($lang){ return $lang[$r[1]]; }, $content);
                $installinfo=creat_table($content, $db);
			}else {
				echo "<SCRIPT language=JavaScript>alert('你的mysql版本过低，请确保你的数据库编码为utf-8,官方建议你升级到mysql4.1.0以上');</SCRIPT>";
				die();
			}
			header("location:index.php?action=adminsetup&cndata={$cndata}&endata={$endata}&showdata={$showdata}");exit;
		}else {
			include template('databasesetup');
		}
		break;
	}
	case 'adminsetup':
	{
		if($setup==1){
			$superAdmin = [
					'username' => $regname,
					'password' => \arcms\lib\model\User::pwd($regpwd),
			];
			\ar\core\comp('db.mysql')->table('coopadmin_user')
					->where(['id' => 1])
					->update($superAdmin);

			$fp = @fopen('../../cfg/install.lock', 'w');
			@fwrite($fp," ");
			@fclose($fp);
			@chmod('../../cfg/install.lock',0554);
			include template('finished');
		}else {

			include template('adminsetup');
		}
		break;
	}
	case 'license':
		include template('license');
	break;
	default:
	{
		session_start();
		$_SESSION['install']='coopcoder';
		include template('index');
	}
}

function creat_table($content , $link) {
	global $installinfo,$db_prefix,$db_setup,$install_url;
	$install_url2=str_replace("install/index.php","",$install_url);
	$sql=explode("\n",$content);
	$query='';
	$j=0;
    foreach($sql as $key => $value){
        $value=trim($value);
        if(!$value || $value[0]=='#') continue;
        if(preg_match("/\;$/",$value)){
			$query.=$value;
			if(preg_match("/^CREATE/",$query)){
				$name=substr($query,13,strpos($query,'(')-13);
				$c_name=str_replace('met_',$db_prefix,$name);
				$i++;
			}
			$query = str_replace('met_',$db_prefix,$query);
			$query = str_replace('metconfig_','met_',$query);
			$query = str_replace('web_coopcoder_url',$install_url2,$query);
			if(!mysqli_query($link , $query) && !mysqli_error($link)){
                $db_setup=0;
                if($j!='0'){
                    echo '<li class="WARN">出错：'.mysqli_error($link).'<br/>sql:'.$query.'</li>';
                }
            }else {
                #var_dump($query);
                if(preg_match("/^CREATE/",$query)){
					$installinfo=$installinfo.'<li class="OK"><font color="#0000EE">建立数据表'.$i.'</font>'.$c_name.' ... <font color="#0000EE">完成</font></li>';
				}
				$db_setup=1;
			}
			$query='';
		} else{
			$query.=$value;
		}
		$j++;
	}
	return $installinfo;
}

function readover($filename,$method="rb"){
	if($handle=@fopen($filename,$method)){
		flock($handle,LOCK_SH);
		$filedata=@fread($handle,filesize($filename));
		fclose($handle);
	}
	return $filedata;
}

function daddslashes($string, $force = 0) {
	!defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
	if(!MAGIC_QUOTES_GPC || $force) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = daddslashes($val, $force);
			}
		} else {
			$string = addslashes($string);
		}
	}
	return $string;
}

function template($template,$EXT="htm"){
	global $met_skin_user,$skin;
	unset($GLOBALS[con_db_id],$GLOBALS[con_db_pass],$GLOBALS[con_db_name]);
	$path = "templates/$template.$EXT";
	return  $path;
}

function is_writable_met($dir){
	$str='';
	$is_dir=0;
	if(is_dir($dir)){
		$dir=$dir.'coopcoder.txt';
		$is_dir=1;
		$info='coopcoder';
	}
	else{
		$fp = @fopen($dir,'r+');
		$i=0;
		while($i<10){
			$info.=@fgets($fp);
			$i++;
		}
		@fclose($fp);
		if($info=='')return false;
	}
	$fp = @fopen($dir,'w+');
	@fputs($fp, $info);
	@fclose($fp);
	if(!file_exists($dir))return false;
	$fp = @fopen($dir,'r+');
	$i=0;
	while($i<10){
		$str.=@fgets($fp);
		$i++;
	}
	@fclose($fp);
	if($str!=$info)return false;
	if($is_dir==1){
		@unlink($dir);
	}
	return true;
}

function curl_post1($post,$timeout){
global $met_weburl;
	$host='list.qq.com/cgi-bin/qf_compose_send';
	if(get_extension_funcs('curl')&&function_exists('curl_init')&&function_exists('curl_setopt')&&function_exists('curl_exec')&&function_exists('curl_close')){
		$curlHandle=curl_init();
		curl_setopt($curlHandle,CURLOPT_URL,'http://'.$host);
		curl_setopt($curlHandle,CURLOPT_REFERER,$met_weburl);
		curl_setopt($curlHandle,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($curlHandle,CURLOPT_CONNECTTIMEOUT,$timeout);
		curl_setopt($curlHandle,CURLOPT_TIMEOUT,$timeout);
		curl_setopt($curlHandle,CURLOPT_POST, 1);
		curl_setopt($curlHandle,CURLOPT_POSTFIELDS, $post);
		$result=curl_exec($curlHandle);
		curl_close($curlHandle);
	}
	else{
		if(function_exists('fsockopen')||function_exists('pfsockopen')){
			$post_data=$post;
			$post='';
			@ini_set("default_socket_timeout",$timeout);
			while (list($k,$v) = each($post_data)) {
				$post .= rawurlencode($k)."=".rawurlencode($v)."&";
			}
			$post = substr( $post , 0 , -1 );
			$len = strlen($post);
			if(function_exists(fsockopen)){
				$fp = @fsockopen($host,80,$errno,$errstr,$timeout);
			}
			else{
				$fp = @pfsockopen($host,80,$errno,$errstr,$timeout);
			}
			if (!$fp) {
				$result='';
			}
			else {
				$result = '';
				$out = "POST $file HTTP/1.0\r\n";
				$out .= "Host: $host\r\n";
				$out .= "Referer: $met_weburl\r\n";
				$out .= "Content-type: application/x-www-form-urlencoded\r\n";
				$out .= "Connection: Close\r\n";
				$out .= "Content-Length: $len\r\n";
				$out .="\r\n";
				$out .= $post."\r\n";
				fwrite($fp, $out);
				$inheader = 1;
				while(!feof($fp)){
					$line = fgets($fp,1024);
						if ($inheader == 0) {
							$result.=$line;
						}
						if ($inheader && ($line == "\n" || $line == "\r\n")) {
							$inheader = 0;
					}

				}

				while(!feof($fp)){
					$result.=fgets($fp,1024);
				}
				fclose($fp);
				str_replace($out,'',$result);
			}
		}
		else{
			$result='';
		}
	}
	return '订阅邮件已发送到你的邮箱！';
}

function curl_post($post,$timeout){
global $met_weburl,$met_host,$met_file;
	$host='api.coopcoder.cn';
	$file='/test/apilinktest.php';
	if(get_extension_funcs('curl')&&function_exists('curl_init')&&function_exists('curl_setopt')&&function_exists('curl_exec')&&function_exists('curl_close')){
		$curlHandle=curl_init();
		curl_setopt($curlHandle,CURLOPT_URL,'http://'.$host.$file);
		curl_setopt($curlHandle,CURLOPT_REFERER,$met_weburl);
		curl_setopt($curlHandle,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($curlHandle,CURLOPT_CONNECTTIMEOUT,$timeout);
		curl_setopt($curlHandle,CURLOPT_TIMEOUT,$timeout);
		curl_setopt($curlHandle,CURLOPT_POST, 1);
		curl_setopt($curlHandle,CURLOPT_POSTFIELDS, $post);
		$result=curl_exec($curlHandle);
		curl_close($curlHandle);
	}
	else{
		if(function_exists('fsockopen')||function_exists('pfsockopen')){
			$post_data=$post;
			$post='';
			@ini_set("default_socket_timeout",$timeout);
			while (list($k,$v) = each($post_data)) {
				$post .= rawurlencode($k)."=".rawurlencode($v)."&";
			}
			$post = substr( $post , 0 , -1 );
			$len = strlen($post);
			if(function_exists(fsockopen)){
				$fp = @fsockopen($host,80,$errno,$errstr,$timeout);
			}
			else{
				$fp = @pfsockopen($host,80,$errno,$errstr,$timeout);
			}
			if (!$fp) {
				$result='';
			}
			else {
				$result = '';
				$out = "POST $file HTTP/1.0\r\n";
				$out .= "Host: $host\r\n";
				$out .= "Referer: $met_weburl\r\n";
				$out .= "Content-type: application/x-www-form-urlencoded\r\n";
				$out .= "Connection: Close\r\n";
				$out .= "Content-Length: $len\r\n";
				$out .="\r\n";
				$out .= $post."\r\n";
				fwrite($fp, $out);
				$inheader = 1;
				while(!feof($fp)){
					$line = fgets($fp,1024);
						if ($inheader == 0) {
							$result.=$line;
						}
						if ($inheader && ($line == "\n" || $line == "\r\n")) {
							$inheader = 0;
					}

				}

				while(!feof($fp)){
					$result.=fgets($fp,1024);
				}
				fclose($fp);
				str_replace($out,'',$result);
			}
		}
		else{
			$result='';
		}
	}
	$result=trim($result);
	if(substr($result,0,7)=='coopcoder'){
		return substr($result,7);
	}
	else{
		return 'nohost';
	}
}

function met_rand_i($length){
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$password = '';
	for ( $i = 0; $i < $length; $i++ ) {
		$password .= $chars[ mt_rand(0, strlen($chars) - 1) ];
	}
	return $password;
}

function deldir($dir,$dk=1) {
  $dh=opendir($dir);
  while ($file=readdir($dh)) {
    if($file!="." && $file!="..") {
      $fullpath=$dir."/".$file;
      if(!is_dir($fullpath)) {
          unlink($fullpath);
      } else {
          deldir($fullpath);
      }
    }
  }
  closedir($dh);
  if($dk==0 && $dir!='../../upload')$dk=1;
  if($dk==1){
	  if(rmdir($dir)){
		return true;
	  }else{
		return false;
	  }
  }
}

function get_sql($data) {
$sql = "";
    foreach ($data as $key => $value) {
        $sql .= " {$key} = '{$value}',";
    }
    return trim($sql,',');
}

function install_tag_templates($db,$templates,$skin_name,$lang)
{

	$template_json = "../templates/{$skin_name}/install/template.json";

		if(file_exists($template_json)){
			$configs = json_decode(file_get_contents($template_json),true);
			$query = "DELETE FROM {$templates} WHERE no = '{$skin_name}' AND lang = '{$lang}'";

			$db->query($query);
				foreach ($configs as $k => $v) {
					$cid = $v['id'];
					$sub = $v['sub'];
					$v['lang'] = $lang;
					unset($v['id'],$v['sub']);
					$v['no'] = $skin_name;
					$area_sql  = get_sql($v);
					$query = "INSERT INTO {$templates} SET {$area_sql}";
					$db->query($query);
					$area_id = $db->insert_id();
					foreach ($sub as $m => $s) {
						unset($s['id']);
						$s['lang'] = $lang;
						$s['bigclass'] = $area_id;
						$s['no'] = $skin_name;
						$sub_sql = get_sql($s);
						$sub_query = "INSERT INTO {$templates} SET {$sub_sql}";

						$db->query($sub_query);
					}
			}
		}

}
# This program is an open source system, commercial use, please consciously to purchase commercial license.
# Copyright (C) 智数 Co., Ltd. (http://www.coopcoder.cn). All rights reserved.
?>
