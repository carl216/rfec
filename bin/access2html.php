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
$ylist=array("500"=>"","404"=>"","302"=>"","304"=>"","200"=>"");
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
$ylist["500"].='"'.$status_500.'",';
$ylist["404"].='"'.$status_404.'",';
$ylist["302"].='"'.$status_302.'",';
$ylist["304"].='"'.$status_304.'",';
$ylist["200"].='"'.$status_200.'",';
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>ECharts</title>
    <script src="http://www.echartsjs.com/gallery/vendors/echarts/echarts-all-3.js?_v_=1510583853276"></script>
</head>
<body>
    <div id="main" style="width: 1200px;height:600px;"></div>
    <script type="text/javascript">
        var myChart = echarts.init(document.getElementById('main'));
        option = {
				title: {
      		  		text: '<?php echo $ymd; ?>访问统计图'
  				},				
        	    tooltip : {
        	        trigger: 'axis'
        	    },
        	    legend: {
        	        data:['用户请求200','用户请求302','用户请求304','用户请求404','用户请求500']
        	    },
        	    toolbox: {
        	        show : true,
        	        feature : {
        	            mark : {show: true},
        	            dataView : {show: true, readOnly: false},
        	            magicType : {show: true, type: ['line', 'bar', 'stack', 'tiled']},
        	            restore : {show: true},
        	            saveAsImage : {show: true}
        	        }
        	    },
        	    calculable : true,
        	    xAxis : [
        	        {
        	            type : 'category',
        	            boundaryGap : false,
        	            data : [<?php echo $xlist; ?>]
        	        }
        	    ],
        	    yAxis : [
        	        {
        	            type : 'value'
        	        }
        	    ],
				
        	    series : [
					<?php foreach($ylist as $key=>$y){ ?>
        	        {
        	            name:'用户请求<?php echo $key; ?>',
        	            type:'line',
			   			areaStyle: {normal: {}},
        	            stack: '总量',
        	            data:[<?php echo $y; ?>]
        	        },
				<?php }?>
        	    ]
        	};
        	                    

        myChart.setOption(option);
    </script>
</body>
</html>

continue