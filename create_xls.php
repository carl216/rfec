<?php 
include("util.php");
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);
ob_end_clean();
require_once 'core/phpexcel/Classes/PHPExcel.php';
require_once 'core/phpexcel/Classes/PHPExcel/IOFactory.php';
require_once 'core/phpexcel/Classes/PHPExcel/Reader/Excel5.php';
include_once("core/XLSXWriter/xlsxwriter.class.php");
$page_name="create_xls";
?>
<!DOCTYPE html>
<html lang="zh-CN">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- 上述3个meta标签*必须*放在最前面，任何其他内容都*必须*跟随其后！ -->
    <title>报表管理后台</title>

    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://cdn.bootcss.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
     <link href="css/dashboard.css" rel="stylesheet">
     <link rel="stylesheet" href="css/font-awesome/css/font-awesome.min.css">
     <link rel="stylesheet" href="css/ystep.css">
     <link href="css/bootstrap-datetimepicker.min.css" rel="stylesheet" />  
     <link rel="stylesheet" href="css/bootstrapValidator.min.css"/>

  </head>
  <body>
<?php
include("nav.php");
?>
       <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
          <!--<h1 class="page-header">Dashboard</h1>-->
          <h3 class="sub-header">生成excl <small class="warning">創建過程中請勿關閉頁面</small></h3>
 					<div class="ystep1"></div>
 					
 					<div class="row"><hr></div>
    			<div class="row center-block col-sm-8">
    				<form class="form-horizontal " id="myform" style="display: none;" role="form" method="post" >
        			<div class="form-group has-feedback">
									 <label class="sr-only" for="starttime">起始時間</label>
           				 <div class='input-group date ' id='datetimepicker6'>
           				 <span class="input-group-addon">起始時間</span>
               		 <input type='text' id="starttime" name="starttime" class="form-control"   />
                		<span class="input-group-addon">
                    	<span class="glyphicon glyphicon-calendar"></span>
                		</span>
           				 </div>
       				</div>


        			<div class="form-group has-feedback">
        			<label class="sr-only" for="endtime">結束時間</label>
            	<div class='input-group date ' id='datetimepicker7'>       	
            		 <span class="input-group-addon">結束時間</span>
                <input type='text' id="endtime" name="endtime" class="form-control" />
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
            	</div>
       			  </div>

        			<div class="form-group has-feedback">
        			<label class="sr-only" for="xlstype">報表類型</label>
            	<div class='input-group'>       	
            		 <span class="input-group-addon">報表類型</span>
                <select class="form-control" id="xlstype" name="xlstype">
 								 <option value="1" txt="vod" >點播記錄</option>
 								 <option value="2" txt="ad">廣告記錄</option>
  							 <option value="3" txt="order">訂購記錄</option>
                 <option value="4" txt="trailer">片花記錄</option>

							</select>
		
            	</div>
       			  </div>
              <div class="form-group  has-feedback">
                  <label class="sr-only" for="captcha">報表類型</label>
                  <div class='input-group'> 
                    <span class="input-group-addon" id="captchaOperation"></span>          
                    <input type="text" class="form-control" id="" name="captcha" />
									</div>
              </div>
        			<div class="form-group has-feedback   ">
        				<div class="col-sm-offset-3 col-md-offset-2  pull-right">
        					<input type="hidden" name="xls_id" id="xls_id">
        				   	<button type="button" id="myButton" data-toggle="modal"  class="btn btn-primary" >創建</button>
							</div>
       			  </div>


   				  </form>
   				  <div class="row" id="debug">

   				  	
   				  </div>
   				  <div class="row">
   				  		  	
   				  	<div class="progress" id="myprogress" style="display: none;">
 									 <div class="progress-bar progress-bar-striped progress-bar-animated" id="myprogressval" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
							</div>
   				  </div>
   				  
    			</div>
        </div>
      </div>
    </div>
<!--彈出框 -->
   <div id="gridSystemModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="gridModalLabel" style="display: none;">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
          <h4 class="modal-title" id="gridModalLabel">信息確認</h4>
        </div>
        <div class="modal-body">
          <div class="row" id="mymessgae">

          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
          <button type="button" id="createxls" class="btn btn-primary">提交</button>
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="js/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
    <script src="js/moment-with-locales.min.js"></script>  
		<script src="js/bootstrap-datetimepicker.min.js"></script>  
    <script src="js/ystep.js"></script>
    <script type="text/javascript" src="js/bootstrapValidator.js"></script>
    <script type="text/javascript">
 $(".ystep1").loadStep({
      //ystep的外观大小
      //可选值：small,large
      size: "large",
      //ystep配色方案
      //可选值：green,blue
      color: "blue",
      //ystep中包含的步骤
      steps: [{
        //步骤名称
        title: "发起",
        //步骤内容(鼠标移动到本步骤节点时，会提示该内容)
        content: "选择报表类型及时间范围"
      },{
        title: "模板读取",
        content: "加载报表模板"
      },{
        title: "数据搜索",
        content: "从数据库打捞数据"
      },{
        title: "数据打包",
        content: "将数据转换为excl格式文件流"
      },{
        title: "完成",
        content: "保存成excl文件"
      }]
    });
    
    //跳转到下一个步骤
    //$(".ystep1").nextStep();
    //跳转到上一个步骤
    //$(".ystep1").prevStep();
    //跳转到指定步骤
    //$(".ystep1").setStep(2);
    //获取当前在第几步
    //$(".ystep1").getStep();

    </script>
<?php
flush();
$clientIP = empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_X_FORWARDED_FOR'];
$nowday=date("Ymd");
if(!is_dir("log/worklog/{$nowday}")){
 mkdir("log/worklog/{$nowday}",0777,true);
}
$log_path = "log/worklog/{$nowday}/";

if(!isset($_POST["starttime"])||!isset($_POST["endtime"])){
	 ?>
	 	<script type="text/javascript">
	$("#myform").show();
	 $(function () {
	 	    function randomNumber(min, max) {
        return Math.floor(Math.random() * (max - min + 1) + min);
    };
    $('#captchaOperation').html([randomNumber(1, 100), '+', randomNumber(1, 200), '='].join(' '));
        $('#datetimepicker6').datetimepicker({
               allowInputToggle: true,
               focusOnShow:true,
               format: 'YYYY-MM-DD HH:mm:ss'
   
            }).on('dp.hide',function(e) {  
                $('#myform').data('bootstrapValidator')  
                    .updateStatus('starttime', 'NOT_VALIDATED',null)  
                    .validateField('starttime');  
            }).on("dp.change", function (e) {
            $('#datetimepicker7').data("DateTimePicker").minDate(e.date);
        });
        $('#datetimepicker7').datetimepicker({
        	               allowInputToggle: true,
               focusOnShow:true,
               format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false //Important! See issue #1075
        }).on('dp.hide',function(e) {  
                $('#myform').data('bootstrapValidator')  
                    .updateStatus('endtime', 'NOT_VALIDATED',null)  
                    .validateField('endtime');  
        }).on("dp.change", function (e) {
            $('#datetimepicker6').data("DateTimePicker").maxDate(e.date);
        });;


          $('#myform').bootstrapValidator({
        		message: 'This value is not valid',
        		feedbackIcons: {
          		  valid: 'glyphicon glyphicon-ok',
           		 invalid: 'glyphicon glyphicon-remove',
           		 validating: 'glyphicon glyphicon-refresh'
       		 },
       		 fields: {
            starttime: {
                message: 'The starttime is not valid',
                validators: {
                    notEmpty: {
                        message: '起始時間不能為空!'
                    }
                }
            },
            endtime: {
                message: 'The endtime is not valid',
                validators: {
                    notEmpty: {
                        message: '結束時間不能為空'
                    }
                }
            },
            xlstype: {
                message: 'The endtime is not valid',
                validators: {
                    notEmpty: {
                        message: '請選擇一個類型'
                    }
                }
            },
            captcha: {
                validators: {
                    callback: {
                        message: '校驗失敗',
                        callback: function(value, validator) {
                            var items = $('#captchaOperation').html().split(' '), sum = parseInt(items[0]) + parseInt(items[2]);
                            return value == sum;
                        }
                    }
                }
            }
        }
    }).on('success.form.bv', function(e) {
        e.preventDefault();
    });

    });
      $('#myButton').click(function() {
      	var bootstrapValidator = $('#myform').data('bootstrapValidator');
      	bootstrapValidator.validate();
      	if(bootstrapValidator.isValid()){
      		 var xls_id="web_"+$("#xlstype").find("option:selected").attr("txt")+"_"+getNowFormatDate();
  		  	$("#xls_id").val(xls_id);
					var htmltxt='<div class="col-md-8">事务id: '+ $("#xls_id").val()+'</div>'
					htmltxt+='<div class="col-md-8">起始時間: '+$("#starttime").val()+'</div>';
    			htmltxt+='<div class="col-md-8">結束時間: '+$("#endtime").val()+'</div>';
    			htmltxt+='<div class="col-md-8">類型: '+$("#xlstype").find("option:selected").text()+'</div>';
    			$('#mymessgae').html(htmltxt);
      		$('#gridSystemModal').modal('show')   
      	}
    });
function getNowFormatDate() {
    var date = new Date();
    var currentdate = date.getFullYear() +""+  num2Str(date.getMonth()+1) +""+ num2Str(date.getDate())+""
           + num2Str(date.getHours())+""+num2Str(date.getMinutes())+""
           + num2Str(date.getSeconds());
    return currentdate;
}

function num2Str(num) {
  if (num >= 0 && num <= 9) {
       num = "0" + num;
    }
    return num;
}

  $("#createxls").on("click",function(){
  		var b=$(this);
  		b.button("loading")
			$('#myform').data('bootstrapValidator').defaultSubmit()
			b.button("reset")
  	});

  	
		$("#myprogress").hide();
</script>	
	 
	 

	 
<?php 
flush();
}else{
  $log_path.=$_POST['xls_id'].".log";
  $local_dbh = new PDO("mysql:host=".$cfg['db_host'].";dbname=".$cfg['db_name'], $cfg['db_user'], $cfg['db_password'], array(PDO::ATTR_PERSISTENT=>false, PDO::MYSQL_ATTR_INIT_COMMAND=>"SET NAMES 'utf8';"));
  $now_time=date('Y-m-d H:i:s');
  $request_type=(strpos($_POST['xls_id'],'web')!==false) ? "1":"0";
  $sql=<<<EOL
 INSERT INTO `work_log` (`id`, `file_name`, `create_time`, `description`, `type`, `status`, `log_path`, `update_time`,`request_type`) VALUES 
 ('{$_POST['xls_id']}', '{$_POST['xls_id']}.xlsx','{$now_time}', '{$_POST['starttime']}至{$_POST['endtime']}', '{$_POST['xlstype']}', '0', '{$log_path}', '{$now_time}','{$request_type}');
EOL;
  debug("exec {$sql}","info");
  $rs_local=$local_dbh->exec($sql);
  if(  $rs_local > 0){
    $sys_status="2";
    $file_size=0;
    debug("create work id {$_POST['xls_id']} ","success");
    $nowday=date("Ymd");
    $file_path="xls/{$_POST['xlstype']}/{$nowday}/";
    if(!is_dir($file_path)){
     mkdir($file_path,0777,true);
    }
	?>


<script type="text/javascript">
	$("#myform").hide();
	$(".ystep1").setStep(2);
	 $debugtxt=$("#debug").html();
  $debugtxt+='<div class="alert alert-info alert-dismissible fade in" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button><?php echo date("Y-m-d H:i:s") ?><strong> 讀取模板文件</strong> </div>'
  $("#debug").html($debugtxt);
</script>	


    
<?php
flush();
debug("Ready to load template templates/template{$_POST['xlstype']}.xls ","info");
$objReader = PHPExcel_IOFactory::createReader('Excel5');
$objPHPExcel = new PHPExcel();
$objPHPExcel = $objReader->load("templates/template{$_POST['xlstype']}.xls"); 
$sheet = $objPHPExcel->getSheet(0);
$highestRow = $sheet->getHighestRow(); // 取得总行数
$highestColumn = $sheet->getHighestColumn(); // 取得总列数
$objTitle=$objPHPExcel->getActiveSheet()->getTitle();
$key_arr;
$title_arr;
$sql;
for($i=ord('A'); $i <= ord($highestColumn); $i++){
$title=$objPHPExcel->getActiveSheet()->getCell(chr($i)."1")->getValue();
$key=$objPHPExcel->getActiveSheet()->getCell(chr($i)."2")->getValue();

if( $title == "sql" ){
	$sql=$key;
	break;
}else{
  $title_arr[addslashes($title)]='string';
}
$key_arr[$i]=$key;
}
if(!$sql){
  debug("load template templates/template{$_POST['xlstype']}.xls  fail !","danger");
?>

<script type="text/javascript">
  $debugtxt=$("#debug").html();
  $debugtxt+='<div class="alert alert-danger  alert-dismissible fade in" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button><?php echo date("Y-m-d H:i:s") ?><strong> 模板文件讀取異常</strong> </div>'
  $("#debug").html($debugtxt);
</script>	

<?php
flush();
}else {
  debug("load template templates/template{$_POST['xlstype']}.xls  success ","success");
  $sys_status="3";
?>
<script type="text/javascript">
  $debugtxt=$("#debug").html();
  $debugtxt+='<div class="alert alert-success alert-dismissible fade in" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button><?php echo date("Y-m-d H:i:s") ?><strong> 模板文件加載完畢</strong> </div>'
  $("#debug").html($debugtxt);
    $(".ystep1").setStep(3);
</script>	


<?php 

flush();
$dbh;
debug("Ready to connect db {$cfg['db_host']} {$cfg['db_name']}","info");
try{
$dbh = new PDO("mysql:host={$cfg['db_host']};dbname={$cfg['db_name']}", "{$cfg['db_user']}", "{$cfg['db_password']}", array(PDO::ATTR_PERSISTENT=>false, PDO::MYSQL_ATTR_INIT_COMMAND=>"SET NAMES 'utf8';",PDO::ATTR_TIMEOUT =>10));
}catch(Exception $e){ 

}

if(!$dbh){
  debug("connect db {$cfg['db_host']} {$cfg['db_name']} fail !","danger");
?>
<script type="text/javascript">
  $debugtxt=$("#debug").html();
  $debugtxt+='<div class="alert alert-danger  alert-dismissible fade in" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button><?php echo date("Y-m-d H:i:s") ?><strong> 數據庫連接失敗</strong> </div>'
  $("#debug").html($debugtxt);
</script>	
<?php
flush();
}else{
  debug("db {$cfg['db_host']} {$cfg['db_name']}  connected ","success");
?>
<script type="text/javascript">
  $debugtxt=$("#debug").html();
  $debugtxt+='<div class="alert alert-success  alert-dismissible fade in" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button><?php echo date("Y-m-d H:i:s") ?><strong> 與數據庫成功建立連接</strong> </div>'
  $("#debug").html($debugtxt);
</script>	
<?php	
flush();
if($_POST['xlstype']==3){
  $sql.="  where  payment_time >= '{$_POST['starttime']}' and payment_time <= '{$_POST['endtime']}' ORDER BY payment_time";
}else{
  $sql.=" and  begintime >= '{$_POST['starttime']}' and begintime <= '{$_POST['endtime']}' ORDER BY begintime";
}

$ret=$dbh->query($sql);
debug(" query sql ：{$sql}","info");
if(!$ret){
debug("query fail !","danger");
//echo "error \n";
?>
<script type="text/javascript">
  $debugtxt=$("#debug").html();
  $debugtxt+='<div class="alert alert-danger  alert-dismissible fade in" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button><?php echo date("Y-m-d H:i:s") ?><strong> 數據庫查詢失敗</strong> </div>'
  $("#debug").html($debugtxt);
</script>	

<?php
flush();

}else{
$retarr=$ret->fetchAll(PDO::FETCH_NAMED);
//计算数据的长度
$len=count($retarr);
debug(" find {$len} record ","success");
$sys_status="4";
?>

<script type="text/javascript">
  $debugtxt=$("#debug").html();
  $debugtxt+='<div class="alert alert-success  alert-dismissible fade in" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button><?php echo date("Y-m-d H:i:s") ?><strong> 數據庫連接成功 共搜索到 <?php echo $len ?> 條記錄</strong> </div>'
  $("#debug").html($debugtxt);
  $(".ystep1").setStep(4);
  $("#myprogress").show();
</script>	
<?php
flush();
$tmp=2;
//print_r($key_arr);
//echo "highestColumn:".$highestColumn;
$starttime=time();
$sheet_line_max = isset($cfg['sheet_line_max']) ? $cfg['sheet_line_max'] : 50000;
$writer = new XLSXWriter();
$writer->setAuthor('Carl'); 
$sname='Sheet';
if($len < 1){
  $sname=$objTitle."0";
  $writer->writeSheetHeader($sname, $title_arr );
}
for($i=0;$i< $len; $i++ ){
if($i%$sheet_line_max==0){
  $sname=$objTitle.$i;
  $writer->writeSheetHeader($sname, $title_arr );
  //$writer->writeSheetRow($sname, $title_arr);
}
  $writer->writeSheetRow($sname, $retarr[$i]);


	$nowtime=time();
	if(($nowtime-$starttime)<2){

			flush();
		   continue;
	}else{
    $starttime=$nowtime;
    ?>
 <script type="text/javascript">

$("#myprogressval").width("<?php echo floor((($i+1)/$len)*100)?>%");
$("#myprogressval").html("<?php echo ($i+1).'/'.$len; ?>")
</script>	
    <?php  

	}
}
?>


<script type="text/javascript">
  $debugtxt=$("#debug").html();
  $debugtxt+='<div class="alert alert-info  alert-dismissible fade in" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button><?php echo date("Y-m-d H:i:s") ?><strong> 正在生成文件，這個過程大約需要2分鐘</strong> </div>'
  $("#debug").html($debugtxt);
</script>	
<?php
flush();
$writer->writeToFile($file_path.$_POST['xls_id'].".xlsx");
$file_size=filesize($file_path.$_POST['xls_id'].".xlsx");
debug("save xls  {$file_path}{$_POST['xls_id']}.xlsx","success");
$sys_status="5";
?>
<script type="text/javascript">
	$("#myprogress").hide();
	$(".ystep1").setStep(5);
  $debugtxt=$("#debug").html();
  $debugtxt+='<div class="alert alert-success  alert-dismissible fade in" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button><?php echo date("Y-m-d H:i:s") ?><strong> 執行成功，文件名 <?php echo "{$_POST['xls_id']}.xlsx" ?> ，<a href="<?php echo "{$file_path}{$_POST['xls_id']}.xlsx" ?>" class="fa fa-download" aria-hidden="true">點擊下载</a></strong> </div>'
  $("#debug").html($debugtxt);
</script>	

<?php
flush();
}
}
}
/**
 * 
 */ 
$sql=<<<EOL
  UPDATE `work_log` SET `status`='{$sys_status}', `update_time`='{$now_time}',`file_size`={$file_size},`file_path`='{$file_path}{$_POST['xls_id']}.xlsx' WHERE (`id`='{$_POST['xls_id']}');
EOL;
debug("exec {$sql}","info");
$rs_local=$local_dbh->exec($sql);


}else{
?>
<script type="text/javascript">
  $debugtxt=$("#debug").html();
  $debugtxt+='<div class="alert alert-danger  alert-dismissible fade in" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>任务编号：<?php echo $_POST['xls_id'] ?>已存在，请勿重复提交！<a href="create_xls.php">重新提交</a> </div>'
  $("#debug").html($debugtxt);
</script>	
<?php
}
}

function debug($message,$level){
 global $log_path,$clientIP;
 $lab = $level=="danger" ? "strong" : "p";
 error_log("<{$lab} class=\"text-{$level}\">  ".date("Ymd-H:i:s P")." {$clientIP}  {$message} </{$lab}> \n",3,$log_path);

}
?>
  </body>
</html>