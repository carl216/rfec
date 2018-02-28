#!/bin/bash
 tmpdate=`date "+%Y-%m"`"-01";
 starttime=`date -d "$tmpdate -1 month" "+%Y-%m-%d %H:%M:%S"`;
 endtime=`date -d "$tmpdate -1 second" "+%Y-%m-%d %H:%M:%S"`;
 nowtime=`date "+%Y%m%d%H%M%S"`;
 xls_id1="cron_vod_$nowtime"
 xls_id2="cron_ad_$nowtime"
 xls_id3="cron_order_$nowtime"

 curl -d "starttime=$starttime&endtime=$endtime&xlstype=1&xls_id=$xls_id1" "http://127.0.0.1:81/xlsm/create_xls.php"
 curl -d "starttime=$starttime&endtime=$endtime&xlstype=2&xls_id=$xls_id2" "http://127.0.0.1:81/xlsm/create_xls.php"
 curl -d "starttime=$starttime&endtime=$endtime&xlstype=3&xls_id=$xls_id3" "http://127.0.0.1:81/xlsm/create_xls.php"
