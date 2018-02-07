#!/usr/bin/php
<?php 
set_time_limit(1200);//脚本最大执行时间
date_default_timezone_set("PRC"); //时区设置
define("DOCROOT",dirname(dirname(__FILE__)));
//等待文件上传,默认等待5s
sleep(10);
$cfg = @parse_ini_file(DOCROOT."/config/system.ini");
$key_list = explode(',',$cfg['viewlog_format']);
$category_format_str=$cfg['category_format'];
$category_top_format=$cfg['category_top_format'];
if(preg_match('/\w:/', $category_format_str)){
  $category_format_str = "{".preg_replace('/[\w\x{4e00}-\x{9fa5}]+/u', '"$0"', $category_format_str)."}";

}
$category_list=json_decode($category_format_str);
$local_dbh = new PDO("mysql:host=".$cfg['db_host'].";dbname=".$cfg['db_name'], $cfg['db_user'], $cfg['db_password'], array(PDO::ATTR_PERSISTENT=>false, PDO::MYSQL_ATTR_INIT_COMMAND=>"SET NAMES 'utf8';"));
$oss_dbh = new PDO("mysql:host=".$cfg['oss_db_host'].";dbname=".$cfg['oss_db_name'], $cfg['oss_db_user'], $cfg['oss_db_password'], array(PDO::ATTR_PERSISTENT=>false, PDO::MYSQL_ATTR_INIT_COMMAND=>"SET NAMES 'utf8';"));
$cms_dbh = new PDO("mysql:host=".$cfg['cms_db_host'].";dbname=".$cfg['cms_db_name'], $cfg['cms_db_user'], $cfg['cms_db_password'], array(PDO::ATTR_PERSISTENT=>false, PDO::MYSQL_ATTR_INIT_COMMAND=>"SET NAMES 'utf8';"));
//get_local_viewlog();
get_viewlog_tar();


function get_viewlog_tar(){
	global $cfg;
	$nowtime=date("YmdHis");
	$expired_date=date("Ymd", strtotime(date("Ymd")." -{$cfg['viewlog_interval_day']} day" ));
	$expired_dir=$cfg['viewlog_path']."/".$expired_date;
	if(is_dir($expired_dir)){
		$cmd="cd {$cfg['viewlog_path']};rm -rf {$expired_date};";
		@exec($cmd, $out, $ret);
	}
	$tar_dir=$cfg['viewlog_path']."/".date("Ymd");
//目录不存在，则停止解析	
	if(!is_dir($tar_dir)){
		die(0);
	}
	$tarlist=scandir($tar_dir);
	$tmp_dir="{$tar_dir}/{$nowtime}";
	if(!is_dir($tmp_dir)){
		mkdir($tmp_dir,0777,true);
	   }
	foreach($tarlist as $tar ){
		if(is_file($tar_dir."/".$tar)){
		rename($tar_dir."/".$tar,$tmp_dir."/".$tar);
		$phar= new PharData($tmp_dir."/".$tar);
		$phar->extractTo($tmp_dir);
		get_viewlog_txt($tmp_dir."/".str_replace("tar.gz","txt",$tar));
		}
		//echo $cfg['viewlog_path'].$tar."<br>";
}
}

function get_viewlog_txt($file_path){
	echo "load {$file_path} <br>";
	$viewlog_arr = file($file_path);
	foreach($viewlog_arr as $viewlog){
		parse_viewlog($viewlog);
	}
}

function parse_viewlog($viewlog){
	echo "parse_viewlog {$viewlog}";
	global $key_list;
	$val_list = explode('|',$viewlog);
	$key_count= count($key_list);
	if( $key_count == count($val_list)){
		$viewlog_data;
		for($i=0;$i<$key_count;$i++){			
			$viewlog_data[$key_list[$i]]=urldecode($val_list[$i]);
		}
		$sql;
		if(check_viewlog_id($viewlog_data['id'],$begintime)){
			if($viewlog_data['endtime']!=''){
				$endtime=$viewlog_data['endtime'];
				//echo $begintime . "-".$endtime."<br>";
				$actualtime=strtotime($endtime)-strtotime($begintime);
				$sql=<<<EOL
				UPDATE `view_log` SET `endtime`='{$endtime}',`actualtime`='{$actualtime}',`init`=1 WHERE id='{$viewlog_data['id']}';
EOL;
			insert_viewlog($viewlog_data['id'],$sql);
				//echo $sql."<br>";
			}else{
				log_print("err update viewlog session_id {$viewlog_data['id']} ,but endtime is null ".$viewlog);
			}
		}else{
			if($viewlog_data['begintime']!=''){
				get_cms_category($viewlog_data['categoryidentityno'],$topcategoryname,$parentcategorname);	
				get_oss_userinfo($viewlog_data['fuserid'],$caid,$email,$extend_user_id);
				get_oss_orderinfo($viewlog_data,$productname,$ordertime,$amount);
				get_cms_cpname($viewlog_data['cpname'],$cpname);
				if(!$cpname){
					log_print("waring add viewlog session_id {$viewlog_data['id']} ,cpname undefined ".$viewlog);
				}
				$isfree=$amount>0 |0;	
				$sql= <<<EOL
			INSERT INTO `view_log` (`id`, `crm_id`, `product_name`, `category_top`, `category_parent`, 
			`cp_name`, `series_name`,`program_name`, `ordertime`, `begintime`, `endtime`,`playbegintime`,`durationlen`, `caid`, `buserid`,
			`email`, `amount`, `isfree`,`type`,`actualtime`) VALUES ('{$viewlog_data['id']}','{$viewlog_data['crmid']}', '{$productname}','{$topcategoryname}',
			'{$parentcategorname}','{$cpname}', '{$viewlog_data['seriesname']}', '{$viewlog_data['programname']}' ,{$ordertime}, '{$viewlog_data['begintime']}',
			SUBDATE('{$viewlog_data['begintime']}',INTERVAL -15 MINUTE),'{$viewlog_data['playbegintime']}', '{$viewlog_data['durationlen']}','{$caid}', '{$extend_user_id}', '{$email}',
			'{$amount}', '{$isfree}','{$viewlog_data['type']}','900');
EOL;
			//echo $sql."<br>";
			insert_viewlog($viewlog_data['id'],$sql);
			}else{
				 log_print("err add viewlog session_id {$viewlog_data['id']} ,but begintime is null ".$viewlog);
			}
		}
	}else{
		log_print("err parse_viewlog fail {{$key_count}-".count($val_list)."}".$viewlog);
	}
}


/**
 * 检查此条记录是否在本地存在
 */
function check_viewlog_id($id,&$begintime){
	global $local_dbh;
	$sql = <<<EOL
	SELECT begintime from view_log 
	where id="{$id}";
EOL;
	$local_oss=$local_dbh->query($sql);
	$old_data=$local_oss->fetch(PDO::FETCH_NAMED);
	if($old_data){
		$begintime=$old_data['begintime'];
		return true;
	}else{
		return false;
	}

}



/**
 * 查询内容订购记录
 */
function get_oss_orderinfo($viewlog_data,&$productname,&$ordertime,&$amount){
	$ordertime='null';
	$amount=0;
	if(empty($viewlog_data['fuserid'])||$viewlog_data['fuserid']=='guest'||$viewlog_data['type']==0){
		return;
	}
	if($viewlog_data['type'] > 0){
		global $oss_dbh;
		$sql = <<<EOL
		SELECT o.paymentTime AS ordertime,o.amount,s.`name` AS productName,
		IF(o.contentId="{$viewlog_data['seriescontentid']}","0","1") AS num from orderlog o
		LEFT JOIN service_product AS sp on sp.productCode=o.productCode
		LEFT JOIN content_service AS cs on cs.serviceId=sp.serviceId
		LEFT JOIN service AS s on s.contentId=cs.serviceId
		WHERE cs.contentId='{$viewlog_data['seriescontentid']}' AND o.fuserId='{$viewlog_data['fuserid']}' 
		AND o.paymentTime < '{$viewlog_data['begintime']}' AND o.deactiveTime > '{$viewlog_data['begintime']}'
		ORDER BY num ASC limit 0,1;
EOL;
	
		$rs_oss=$oss_dbh->query($sql);
//	echo $sql."<br>";
		$data_oss=$rs_oss->fetch(PDO::FETCH_NAMED);
		if($data_oss){
			$ordertime="'{$data_oss['ordertime']}'";
			$amount=$data_oss['amount'];
			$productname=$data_oss['productName'];
		}
	}

}

/**
 * 根据栏目编号获取栏目名称及父栏目名称
 */
function get_cms_category($identityno,&$topcategoryname,&$parentcategorname){
	global $category_list,$cms_dbh,$category_top_format;
	if(!$identityno){
		return;
	}else if($identityno < 0){
		$key=abs($identityno);
		$val=$category_list->$key;
		$topcategoryname=$val;
		$parentcategorname=$val;
		return;
	}
	$topidentityno=substr($identityno,0,$category_top_format);
	$sql=<<<EOL
	SELECT name,identityno from category
	WHERE  identityno='{$topidentityno}' or identityno='{$identityno}';
EOL;
  
   $rs_cms=$cms_dbh->query($sql);
   $data_cms=$rs_cms->fetchAll(PDO::FETCH_NAMED);
 //  echo $sql."<br>";;
 //  print_r($data_cms);
   foreach($data_cms as $category){
	   $topcategoryname = $category['name'];
	   if($category['identityno'] == $identityno){
		   $parentcategorname = $category['name'];
	   }else{
		   $topcategoryname = $category['name'];
	   }
   }

}
/**
 * 通过fuserid获取用户信息,guest用户直接返回guest
 */
function get_oss_userinfo($fuserid,&$caid,&$email,&$extend_user_id){
	if(!$fuserid){
		return -1;
	}else if(empty($fuserid)||$fuserid =='guest'){
		$extend_user_id='guest';
		return;
	}
	global $oss_dbh;
	$sql=<<<EOL
	SELECT u.caId,u.email,u.extendUserId FROM `userprofile` as u
	WHERE u.fuserid='{$fuserid}' ;
EOL;
$rs_oss=$oss_dbh->query($sql);
//echo $sql."<br>";;
$data_oss=$rs_oss->fetch(PDO::FETCH_NAMED);
if($data_oss){
	$caid=$data_oss['caId'];
	$email=$data_oss['email'];
	$extend_user_id=$data_oss['extendUserId'];
}
//print_r($data_oss);
}

/**
 * 通过cpid获取cp名称
 */
function get_cms_cpname($cpid,&$cpname){
	if(!$cpid){
		return false;
	}
	global $cms_dbh;
	$sql=<<<EOL
	SELECT cp.`name` from contentprovider cp where cpId='{$cpid}' or name='{$cpid}';
EOL;
$rs_cms=$cms_dbh->query($sql);
$data_cms=$rs_cms->fetch(PDO::FETCH_NAMED);
if($data_cms){
	$cpname=$data_cms['name'];
}
}

/**
 * 将解析的订购记录入库
 */
function insert_viewlog($id,$sql){
	global $local_dbh;
	$rs_local=$local_dbh->exec($sql);
	echo "sql exec {$sql}";
	if($rs_local > 0){
		echo " ok <br> \n";
	}else{
		echo " fail <br> \n";
		log_print("err sql exec fail {$sql}");
	}
	
}

function log_print($message){
	$log_path=DOCROOT."/log/sys".date("Ymd").".log";
	error_log(date("Ymd-H:i:s P")." {$message} \n",3,$log_path);
   
   }