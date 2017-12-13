#!/usr/bin/php
<?php 
set_time_limit(1200);//脚本最大执行时间
date_default_timezone_set("PRC"); //时区设置
$cfg = @parse_ini_file("/opt/fonsview/NE/epg/etc/esb.ini"); //配置加载
define("ESB_DIR", $cfg['esb_dir']);
define("FTP_SERVER",$cfg['ftp_ip']);
define("FTP_PORT",$cfg['ftp_port']);
define("FTP_TIMEOUT",$cfg['ftp_timeout']);
define("FTP_USER",$cfg['ftp_user']);
define("FTP_PASS",$cfg['ftp_pass']);
define("ESB_LOG_DIR",$cfg['log_dir']);
define("PACODE",$cfg['code']);
define("NAME",$cfg['name']);
define("SERVER_NAME",$cfg['server_name']);

check_esblog_file();

function check_esblog_file() {
     $time_str=floor(time()/(60*15))* 60*15;
     $date_str=date("YmdHi",$time_str);
     $tar_name=PACODE."_IPTV_".NAME."_".SERVER_NAME."_EPG-PM_0015_{$date_str}00_0_0_0";
     $log_name=PACODE."_IPTV_".NAME."_*_EPG-PM_0015_{$date_str}00_0_0_0";
     $cmd="cd ".ESB_DIR.";mkdir tmp; mv  view*.log tmp/; cat tmp/view*.log>{$tar_name}.txt;tar -zcvf {$tar_name}.tar.gz {$tar_name}.txt;rm -rf {$tar_name}.txt;rm -rf tmp/;";
echo  $cmd;
	 @exec($cmd, $out, $ret);

	 if($ret=="0"){
		sleep(2);//延迟2秒上载
		uploadviewlog($tar_name.".tar.gz",0);
	}		
     
}


function uploadviewlog($file, $count) {
	$fp = fopen(ESB_DIR.$file, 'r');
    $conn_id = ftp_connect(FTP_SERVER,FTP_PORT,FTP_TIMEOUT);
    if (!$conn_id) {
    	epg_log("ftp：".FTP_SERVER." 无法连接!!");
		return 0;
    }
    $login_result = ftp_login($conn_id, FTP_USER, FTP_PASS);
	if (!$login_result) {
		epg_log("ftp账号或密码错误!!! user=".FTP_USER." pass=".FTP_PASS);
		return 0;
	}
	$cdir = "iptvdata/".PACODE."/".NAME."/EPG-PM/".date("Ymd")."/";
	if (! empty($cdir)) {
		dir_mkdirs($cdir, $conn_id);
	}
	if (ftp_fput($conn_id, $file.".tmp", $fp, FTP_BINARY)) {           
		if (@ftp_rename($conn_id, $file.".tmp", $file)) {
			epg_log("文件{$file}上传成功！");
		}
		ftp_close($conn_id);
		fclose($fp);
	} else {
		ftp_close($conn_id);
		fclose($fp);
		if ($count > 4) {
			exit;
		}
		sleep(3);
		epg_log("上传失败".ESB_DIR.date("Ymd")."/".$file."   重试    ......".($count + 1)."\n");
		uploadviewlog($file, $count + 1);
	}
}
function dir_mkdirs($path, $conn_id) {
	$path_arr = explode('/', $path);
	$file_name = array_pop($path_arr);
	$path_div = count($path_arr);
	if (! empty($file_name) && $path_div == 0) {
		epg_log("跳转至FTP目录".$file_name);
		if(@ftp_chdir($conn_id, $file_name)==false){
			epg_log("FTP目录".$file_name."不存在！创建此目录！{$file_name}");
			if (ftp_mkdir($conn_id, $file_name)) {
				if(!@ftp_chdir($conn_id, $file_name)){
					epg_log("FTP目录".$file_name."跳转失败，权限不足！{$file_name}");
				}
			} else {
				epg_log("FTP目录".$file_name."创建失败，权限不足！");
				exit;
			}
		}
	}else{
		foreach ($path_arr as $val) {
			epg_log("FTP跳转至目录".$val);
			if (@ftp_chdir($conn_id, $val) == FALSE) {
				epg_log("FTP目录".$val."不存在!创建此目录!");
				if (@ftp_mkdir($conn_id, $val) == FALSE) {
					epg_log("FTP目录".$val."创建失败，权限不足!");
					exit;
                }
                if(!@ftp_chdir($conn_id, $val)){
                	epg_log("FTP目录".$val."跳转失败，权限不足!");
                }
			}
		}
	}
}
    
function epg_log($message){
	error_log(print_r(date("Y-m-d H:i:s" )." : {$message}\n",true),3,ESB_LOG_DIR."esb.".date("Y-m-d" ,time()).".log");
}
