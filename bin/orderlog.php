#!/usr/bin/php
<?php 
set_time_limit(1200);//脚本最大执行时间
date_default_timezone_set("PRC"); //时区设置
define("DOCROOT",dirname(dirname(__FILE__)));
$cfg = @parse_ini_file(DOCROOT."/config/system.ini");
/**
 * 默认为corntab任务脚本，可通过web请求入库指定范围内的订购记录
 */
$category_format_str=$cfg['category_format'];
$category_top_format=$cfg['category_top_format'];
if(preg_match('/\w:/', $category_format_str)){
	$category_format_str = "{".preg_replace('/[\w\x{4e00}-\x{9fa5}]+/u', '"$0"', $category_format_str)."}";
  
  }
$category_list=json_decode($category_format_str);
$starttime;
$endtime;
if( isset($_GET['starttime'])&&isset($_GET['endtime']) ){
	$starttime=$_GET['starttime'];
	$endtime=$_GET['endtime'];
}else{
	$time_str=floor(time()/(60*15))* 60*15; 
	$endtime=date("Y-m-d H:i:s",$time_str);
	$starttime=date("Y-m-d H:i:s",($time_str-900));
}


$local_dbh = new PDO("mysql:host=".$cfg['db_host'].";dbname=".$cfg['db_name'], $cfg['db_user'], $cfg['db_password'], array(PDO::ATTR_PERSISTENT=>false, PDO::MYSQL_ATTR_INIT_COMMAND=>"SET NAMES 'utf8';"));
$oss_dbh = new PDO("mysql:host=".$cfg['oss_db_host'].";dbname=".$cfg['oss_db_name'], $cfg['oss_db_user'], $cfg['oss_db_password'], array(PDO::ATTR_PERSISTENT=>false, PDO::MYSQL_ATTR_INIT_COMMAND=>"SET NAMES 'utf8';"));
$cms_dbh = new PDO("mysql:host=".$cfg['cms_db_host'].";dbname=".$cfg['cms_db_name'], $cfg['cms_db_user'], $cfg['cms_db_password'], array(PDO::ATTR_PERSISTENT=>false, PDO::MYSQL_ATTR_INIT_COMMAND=>"SET NAMES 'utf8';"));
get_oss_orderlog($starttime,$endtime);
/**
 * 根据支付时间从ossdb读取15分钟内的订购记录
 */
function get_oss_orderlog($starttime,$endtime){
	global $oss_dbh;
	$sql = <<<EOL
	SELECT DISTINCT(o.id),o.crmId,s.`name` AS productName,o.contentCategory,
	o.contentId,o.paymentTime,o.fuserId,o.amount,o.purchaseType FROM `orderlog` AS o
	LEFT JOIN service_product AS sp ON sp.productCode = o.productCode
	LEFT JOIN service AS s ON s.contentId = sp.serviceId
	WHERE o.paymentTime >= '{$starttime}' AND o.paymentTime < '{$endtime}';
EOL;
	
	$rs_oss=$oss_dbh->query($sql);
	echo "<br>{$sql}<br>";
	$data_oss=$rs_oss->fetchAll(PDO::FETCH_NAMED);
	foreach($data_oss as $oss_order_log){
		$id=$oss_order_log['id'];
		$crm_id=$oss_order_log['crmId'];
		$product_name=urlencode($oss_order_log['productName']);
		$category_top="";
		$category_parent="";
		$cp_name="";
		$series_name="";
		$payment_time=$oss_order_log['paymentTime'];
		$valid_time="null";
		$invalid_time="null";
		$ca_id="";
		$extend_user_id="";
		$email="";
		$amount=$oss_order_log['amount'];
		$isfree=$amount>0 |0;
		$purchase_type=$oss_order_log['purchaseType'];
		if(!$oss_order_log['contentCategory'] || $oss_order_log['purchaseType'] !=3 ){
			
		}else{
			$vodinfo_str=urldecode($oss_order_log['contentCategory']);
			$vodinfo=json_decode($vodinfo_str);
			$identityno=isset($vodinfo->categoryidentityno) ? $vodinfo->categoryidentityno : "";
			get_cms_category($identityno,$oss_order_log['contentId'],$category_top,$category_parent);
			$cpid=isset($vodinfo->cpid) ?$vodinfo->cpid :$vodinfo->cpname;
			get_cms_cpname($cpid,$cp_name);
			if(isset($vodinfo->invalidtime)){
				$invalid_time="'{$vodinfo->invalidtime}'";
			}
			if(isset($vodinfo->validtime)){
				$valid_time="'{$vodinfo->validtime}'";
			}
			$category_top=urlencode($category_top);
			$category_parent=urlencode($category_parent);
			$series_name=$vodinfo->seriesname;
		}
		get_oss_userinfo($oss_order_log['fuserId'],$ca_id,$email,$extend_user_id);

		$sql=<<<EOL
		INSERT INTO `order_log` (`id`, `crm_id`, `product_name`, `category_top`, `category_parent`, 
		`cp_name`, `series_name`, `payment_time`, `valid_time`,`invalid_time`, `ca_id`, `extend_user_id`,
		`email`, `amount`, `isfree`, `purchase_type`) VALUES ('{$id}', '{$crm_id}', '{$product_name}',
		'$category_top','{$category_parent}', '{$cp_name}', '{$series_name}', '{$payment_time}', 
		{$valid_time}, {$invalid_time}, '{$ca_id}', '{$extend_user_id}', '{$email}', '{$amount}', 
		'{$isfree}', '$purchase_type');
EOL;
		insert_orderlog($id,$sql);

	}
}

/**
 * 根据栏目编号获取栏目名称及父栏目名称
 */
function get_cms_category($identityno,$conentid,&$topcategoryname,&$parentcategorname){
	global $category_list,$cms_dbh,$category_top_format;
	if(!$identityno){
		return;
	}else if($identityno < 0){
		$key=abs($identityno);
		$val=$category_list->$key;
		$topcategoryname=get_cms_topcategoryname($conentid,$category_top_format);
		$parentcategorname=$val;
		return;
	}
	$topidentityno=substr($identityno,0,$category_top_format);
	$sql=<<<EOL
	SELECT name,identityno from category
	WHERE  identityno='{$topidentityno}' or identityno='{$identityno}';
EOL;
  echo "<br>$sql<br>";
   $rs_cms=$cms_dbh->query($sql);
   $data_cms=$rs_cms->fetchAll(PDO::FETCH_NAMED);
   foreach($data_cms as $category){
	   if($category['identityno'] == $identityno){
		   $parentcategorname = $category['name'];
	   }else{
		   $topcategoryname = $category['name'];
	   }
   }
   if(strlen($identityno)==$category_top_format){
	$parentcategorname.="*";
	$topcategoryname = get_cms_topcategoryname($conentid,$category_top_format);
	}

}

function  get_cms_topcategoryname($conentid,$category_top_format){
	if(trim($conentid)==''){
		return '';
	}
	global $cms_dbh;
	$sql=<<<EOL
	SELECT `name` FROM category 
	WHERE
		identityno = SUBSTR((SELECT mc.category_identityno FROM minimetadata_category mc
				WHERE
					mc.`contentId` = '{$conentid}'
				AND mc.`recycle` = 0
				ORDER BY
					(mc.category_identityno + 0) DESC
				LIMIT 0,
				1
			),
			1,
			{$category_top_format}
		);
EOL;
  
   $rs_cms=$cms_dbh->query($sql);
   $data_cms=$rs_cms->fetchAll(PDO::FETCH_NAMED);
   foreach($data_cms as $category){
			return $category['name'];
	}
	return '';
}
/**
 * 通过fuserid获取用户信息
 */
function get_oss_userinfo($fuserid,&$caid,&$email,&$extend_user_id){
	if(!$fuserid){
		return -1;
	}
	global $oss_dbh;
	$sql=<<<EOL
	SELECT u.caId,u.email,u.extendUserId FROM `userprofile` as u
	WHERE u.fuserid='{$fuserid}' ;
EOL;
$rs_oss=$oss_dbh->query($sql);
echo "<br>{$sql}<br>";
$data_oss=$rs_oss->fetch(PDO::FETCH_NAMED);
if($data_oss){
	$caid=$data_oss['caId'];
	$email=$data_oss['email'];
	$extend_user_id=$data_oss['extendUserId'];
}
}

/**
 * 通过cpid获取cp名称
 */
function get_cms_cpname($cpid,&$cpname){
	if(!$cpid){
		return -1;
	}
	global $cms_dbh;
	$sql=<<<EOL
	SELECT cp.`name` from contentprovider cp where cpId='{$cpid}' or name='{$cpid}';
EOL;
echo "<br>{$sql}<br>";
$rs_cms=$cms_dbh->query($sql);
$data_cms=$rs_cms->fetch(PDO::FETCH_NAMED);
if($data_cms){
	$cpname=$data_cms['name'];
}
}

/**
 * 将解析的订购记录入库
 */
function insert_orderlog($id,$sql){
	global $local_dbh;
	$rs_local=$local_dbh->exec($sql);
	echo "<br>{$sql}<br>";
	if($rs_local > 0){
		echo "ok !!<br>\n";
	}else{
		echo "fail !!<br>\n";
		log_print("err sql exec fail {$sql}");
	}
	
}

/**
 * 检查此条记录是否在本地存在
 */
function check_orderlog_id($id){
	return true;

}

function log_print($message){
	$log_path=DOCROOT."/log/sys".date("Ymd").".log";
	error_log(date("Ymd-H:i:s P")." {$message} \n",3,$log_path);
   
   }