#!/usr/bin/php
<?php
set_time_limit(1200);//脚本最大执行时间
date_default_timezone_set("PRC"); //时区设置
if($argc != 2){
echo "Usage:  ./test.php  20171221 > test.html\n";
exit;
}
$time_str=strtotime($argv[1]);
$ymd=date("Y-m-d",$time_str);
$xlist="";
$ylist=array("","","","","","");
for($i=0 ; $i <=47 ; $i++){
$starttime=date("His",($time_str+$i*60*30));
$endtime="115959";
if($i!=47){
$endtime=date("His",($time_str + ($i+1)*60*30));
}
$status_200=0;
$status_404=0;
$status_302=0;
$status_304=0;
$status_500=0;
$status_all=0;

$cmd="cat localhost_access_log.{$ymd}*txt |awk -F '[,\"]' '{print $14\" \"$24}'|awk -F '[: ]' '{ if($2$3$4 > \"{$starttime}\" && $2$3$4 <= \"{$endtime}\"){print $6}}'";
exec($cmd,$output)."\n";
$status_all=count($output);
foreach($output as $status){
    if($status=="200"){
        $status_200++;
    }else if($status=="302"){
        $status_302++;
    }else if($status=="304"){
        $status_304++;
    }else if($status=="404"){
        $status_404++;
    }else if($status=="500"){
        $status_500++;
    }else{
        $status_200++;
    }
}
$xlist.="'".date("H:i",($time_str+$i*60*30))."',";
$ylist[0].='"'.$status_200.'",';
$ylist[1].='"'.$status_302.'",';
$ylist[2].='"'.$status_304.'",';
$ylist[3].='"'.$status_404.'",';
$ylist[4].='"'.$status_500.'",';
$ylist[4].='"'.$status_all.'",';
}
