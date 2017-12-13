<?php 
set_time_limit(30000);//脚本最大执行时间
ini_set('memory_limit', '3072M');
date_default_timezone_set("PRC");
ob_end_clean();
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
     <link rel="stylesheet" href="css/bootstrapValidator.min.css"/>
  </head>
  <body>
  <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="index.php">报表管理后台</a>
        </div>
      </div>
    </nav>

    <div class="container-fluid">
		  <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
		  <!--<h1 class="page-header">Dashboard</h1>-->
        <h3 class="sub-header">系统安装 <small class="warning">配置初始化</small></h3>
        <?php   
    if($_POST){
    $initxt="";
     foreach($_POST as $key=>$val){
      $initxt.=$key."=".$val."\n";
     }
     $status=true;
     try{
     file_put_contents("config/system.ini",$initxt);
     echo '<div class="alert alert-success" role="alert">配置保存成功，开始确认配置</div>';
     }catch(Exception $e){ 
      $status=false;
      ?>
      <div class="alert alert-danger">配置保存失败，请检查目录是否有读写权限</div>
      <?php
     }
     ?>
    <div class="alert alert-info" role="alert">本地数据库连接测试，请稍等...</div>
     <?php
     flush();
     try{
      $dbh = new PDO("mysql:host=".$_POST['db_host'].";dbname=".$_POST['db_name'], $_POST['db_user'], $_POST['db_password'], array(PDO::ATTR_PERSISTENT=>false, PDO::MYSQL_ATTR_INIT_COMMAND=>"SET NAMES 'utf8';",PDO::ATTR_TIMEOUT=>10));
      echo '<div class="alert alert-success">本地数据库连接测试通过</div>'; 
     }catch(Exception $e){ 
        $status=false;
        echo '<div class="alert alert-danger">本地数据库连接测试失败</div>'."mysql:host=".$_POST['db_host'].";dbname=".$_POST['db_name']. $_POST['db_user']. $_POST['db_password'];
     }
 
     ?>
     <div class="alert alert-info">CMS数据库连接测试</div>

      <?php
     flush();
     try{
      $dbh = new PDO("mysql:host=".$_POST['cms_db_host'].";dbname=".$_POST['cms_db_name'], $_POST['cms_db_user'], $_POST['cms_db_password'], array(PDO::ATTR_PERSISTENT=>false, PDO::MYSQL_ATTR_INIT_COMMAND=>"SET NAMES 'utf8';",PDO::ATTR_TIMEOUT=>10));
      echo '<div class="alert alert-success">CMS数据库连接测试通过</div>'; 
     }catch(Exception $e){ 
        $status=false;
        echo '<div class="alert alert-danger">CMS数据库连接测试失败</div>';
     }
 
     ?>
    <div class="alert alert-info">oss数据库连接测试</div>
    <?php
     flush();
     try{
      $dbh = new PDO("mysql:host=".$_POST['oss_db_host'].";dbname=".$_POST['oss_db_name'], $_POST['oss_db_user'], $_POST['oss_db_password'], array(PDO::ATTR_PERSISTENT=>false, PDO::MYSQL_ATTR_INIT_COMMAND=>"SET NAMES 'utf8';",PDO::ATTR_TIMEOUT=>10));
      echo '<div class="alert alert-success">OSS数据库连接测试通过</div>'; 
     }catch(Exception $e){ 
      $status=false;
      echo '<div class="alert alert-danger">OSS数据库连接测试失败</div>';
     }
     $initxt.="init=". $status;
     file_put_contents("config/system.ini",$initxt);
     ?>
    <div class="alert alert-<?php echo $status ? "success" :"danger"; ?>"><?php echo  $status ? '校验成功，点击<a href="index.php"><strong>首页</strong></a>进入' :' 校验失败，点击<a href="install.php"><strong> 配置</strong></a>重新填写';  ?> </div>
     <?php
     flush();
    }else{
      $data;
      $carl=false;
      if(isset($_GET['who'])&&$_GET['who']=="carl"){
        $carl=true;
      }
     
      if(is_file("config/system.ini")){
        $data=parse_ini_file("config/system.ini");
      }
    ?>
        <ul class="nav nav-tabs">
         <li class="active"><a href="#sys-config-tab" data-toggle="tab">基本配置 <i class="fa"></i></a></li>
         <li><a href="#db-cndif-tab" data-toggle="tab">本地数据库连接配置 <i class="fa"></i></a></li>
         <li><a href="#cms-db-config-tab" data-toggle="tab">CMS数据库连接配置<i class="fa"></i></a></li>
         <li><a href="#oss-db-config-tab" data-toggle="tab">OSS数据库连接配置 <i class="fa"></i></a></li>
        <?php if($carl){
           echo  "<li><a href=\"#html-config-tab\" data-toggle=\"tab\">页面配置(保留配置) <i class=\"fa\"></i></a></li>";
        }?> 
        </ul>
			  <div class="row center-block col-sm-8">
          <form id="myform" method="post" class="form-horizontal"  style="margin-top: 20px;">
           <div class="tab-content">
            <div class="tab-pane active" id="sys-config-tab">
              <div class="form-group container-fluid">
                <label class="sr-only" for="viewlog_path">收视纪录存放路径</label>
                <div class='input-group' >
                  <span class="input-group-addon">收视纪录存放路径</span>
                  <input type='text' id="viewlog_path" name="viewlog_path" class="form-control input-sm"  value="<?php echo $data["viewlog_path"] ? $data["viewlog_path"] :"";  ?>"  />
                </div>
              </div>
              <div class="form-group container-fluid">
                <label class="sr-only" for="viewlog_path">收视纪录保留天数</label>
                <div class='input-group' >
                  <span class="input-group-addon">收视纪录保留天数</span>
                  <input type='number' id="viewlog_interval_day" name="viewlog_interval_day" class="form-control input-sm"  value="<?php echo $data["viewlog_interval_day"] ? $data["viewlog_interval_day"] :"30";  ?>" />
                  <span class="input-group-addon">天&nbsp;</span>
                </div>
              </div>   
              <div class="form-group container-fluid">
                <label class="sr-only" for="viewlog_path">系统运行日志保留天数</label>
                <div class='input-group' >
                  <span class="input-group-addon">系统运行日志保留天数</span>
                  <input type='number' id="log_interval_day" name="log_interval_day" class="form-control input-sm" value="<?php echo $data["log_interval_day"] ? $data["log_interval_day"] :"30";  ?>"  />
                  <span class="input-group-addon" >天&nbsp;</span>
                </div>
              </div>
              <div class="form-group container-fluid">
                <label class="sr-only" for="viewlog_format">收视纪录解析格式</label>
                <div class='input-group' >
                  <span class="input-group-addon">收视纪录解析格式</span>
                  <input type='text' id="viewlog_format" name="viewlog_format" class="form-control input-sm" value="<?php echo $data["viewlog_format"]? $data["viewlog_format"] :"id,crmid,categoryidentityno,cpname,seriesname,programname,seriescontentid,begintime,endtime,playbegintime,durationlen,caid,fuserid,type" ?>"  <?php echo $carl ? '':'readonly' ?> />
                </div>
              </div>    
              <div class="form-group container-fluid">
                <label class="sr-only" for="viewlog_interval">收视纪录入库周期</label>
                <div class='input-group' >
                  <span class="input-group-addon">收视纪录入库周期</span>
                  <input type='text' id="viewlog_interval" name="viewlog_interval" class="form-control input-sm" value="<?php echo $data["viewlog_interval"] ? $data["viewlog_interval"] :"15";  ?>"  <?php echo $carl ? '':'readonly' ?> />
                  <span class="input-group-addon" >分钟&nbsp;</span>
                </div>
              </div>
              <div class="form-group container-fluid">
                <label class="sr-only" for="orderlog_interval">订购纪录入库周期</label>
                <div class='input-group' >
                  <span class="input-group-addon">订购纪录入库周期</span>
                  <input type='text' id="orderlog_interval" name="orderlog_interval" class="form-control input-sm" value="<?php echo $data["orderlog_interval"] ? $data["orderlog_interval"] :"15";  ?>"  <?php echo $carl ? '':'readonly' ?> />
                  <span class="input-group-addon" >分钟&nbsp;</span>
                </div>
              </div> 
              <div class="form-group container-fluid">
                <label class="sr-only" for="category_format">特殊栏目解析</label>
                <div class='input-group' >
                  <span class="input-group-addon">特殊栏目解析</span>
                  <input type='text' id="category_format" name="category_format" class="form-control input-sm" value="<?php echo $data["category_format"]? $data["category_format"] :"1:书签,2:收藏,3:搜索,4:广告位,5:推荐" ?>"  />
                </div>
              </div>
              <div class="form-group container-fluid">
                <label class="sr-only" for="category_top_format">顶级栏目获取规则</label>
                <div class='input-group' >
                  <span class="input-group-addon">顶级栏目截取规则</span>
                  <input type='number' id="category_top_format" name="category_top_format" class="form-control input-sm" value="<?php echo $data["category_top_format"]? $data["category_top_format"] :"9" ?>"   />
                </div>
              </div>
              <div class="form-group container-fluid">
                <label class="sr-only" for="sheet_line_max">表格sheet最大行数限制</label>
                <div class='input-group' >
                  <span class="input-group-addon">表格sheet最大行数限制</span>
                  <input type='number' id="sheet_line_max" name="sheet_line_max" class="form-control input-sm" value="<?php echo $data["sheet_line_max"]? $data["sheet_line_max"] :"50000" ?>"   />
                </div>
              </div>                                                                                                                                             
            </div><!--/sys-config-tab -->
            <div class="tab-pane" id="db-cndif-tab">
              <div class="form-group container-fluid">
					    	<label class="sr-only" for="db_host">数据库地址</label>
           			<div class='input-group date'>
           				<span class="input-group-addon">数据库地址</span>
               		<input type='text' id="db_host" name="db_host" class="form-control input-sm"  value="<?php echo $data["db_host"] ? $data["db_host"] :"localhost";  ?>" />
           			</div>
               </div>
               <div class="form-group container-fluid">
					    	<label class="sr-only" for="db_name">数据库名称</label>
           			<div class='input-group'>
           				<span class="input-group-addon">数据库名称</span>
               		<input type='text' id="db_name" name="db_name" class="form-control input-sm"  value="<?php echo $data["db_name"] ? $data["db_name"] :"xlsmdb";  ?>" />
           			</div>
               </div>
               <div class="form-group container-fluid">
						    <label class="sr-only" for="db_user">用户名</label>
           			<div class='input-group date' >
           			  <span class="input-group-addon">用户名</span>
               		<input type='text' id="db_user" name="db_user" class="form-control input-sm"  value="<?php echo $data["db_user"] ? $data["db_user"] :"";  ?>" />
           			</div>
               </div>
               <div class="form-group container-fluid">
					    	<label class="sr-only" for="db_password">密码</label>
           			<div class='input-group date'>
           				<span class="input-group-addon">密码</span>
               		<input type='password' id="db_password" name="db_password" class="form-control input-sm"  value="<?php echo $data["db_password"] ? $data["db_password"] :"" ; ?>" />
           			</div>
               </div>
            </div><!--/db-config-tab -->
            <div class="tab-pane" id="cms-db-config-tab">
              <div class="form-group container-fluid">
                <label class="sr-only" for="cms_db_host">CMS数据库地址</label>
                <div class='input-group' >
                  <span class="input-group-addon">CMS数据库地址</span>
                  <input type='text' id="cms_db_host" name="cms_db_host" class="form-control input-sm"  value="<?php echo $data["cms_db_host"] ? $data["cms_db_host"] :"";  ?>" />
                </div>
              </div>
              <div class="form-group container-fluid">
                <label class="sr-only" for="cms_db_name">CMS数据库名称</label>
                <div class='input-group'>
                  <span class="input-group-addon">CMS数据库名称</span>
                  <input type='text' id="cms_db_name" name="cms_db_name" class="form-control input-sm" value="<?php echo $data["cms_db_name"] ? $data["cms_db_name"] :"cmsdb";  ?>"  />
                </div>
              </div>
              <div class="form-group container-fluid">
                <label class="sr-only" for="cms_db_user">用户名</label>
                <div class='input-group' >
                  <span class="input-group-addon">用户名</span>
                  <input type='text' id="cms_db_user" name="cms_db_user" class="form-control input-sm" value="<?php echo $data["cms_db_user"] ? $data["cms_db_user"] :"";  ?>"  />
                </div>
              </div>
              <div class="form-group container-fluid">
                <label class="sr-only" for="cms_db_password">密码</label>
                <div class='input-group'>
                  <span class="input-group-addon">密码</span>
                  <input type='password' id="cms_db_password" name="cms_db_password" class="form-control input-sm"   value="<?php echo $data["cms_db_password"] ? $data["cms_db_password"] :"";  ?>" />
                </div>
              </div>
            </div><!--/cms-db-config-tab -->
            <div class="tab-pane" id="oss-db-config-tab">
            <div class="form-group container-fluid">
          <label class="sr-only" for="oss_db_host">OSS数据库地址</label>
                  <div class='input-group'>
                  <span class="input-group-addon">OSS数据库地址</span>
                    <input type='text' id="oss_db_host" name="oss_db_host" class="form-control input-sm"  value="<?php echo $data["oss_db_host"] ? $data["oss_db_host"] :"";  ?>"  />
                 </div>
             </div>
             <div class="form-group container-fluid">
          <label class="sr-only" for="oss_db_name">OSS数据库名称</label>
                  <div class='input-group' >
                  <span class="input-group-addon">OSS数据库名称</span>
                    <input type='text' id="oss_db_name" name="oss_db_name" class="form-control input-sm"    value="<?php echo $data["oss_db_name"] ? $data["oss_db_name"] :"ossdb";  ?>" />
                 </div>
             </div>
             <div class="form-group container-fluid">
          <label class="sr-only" for="oss_db_user">用户名</label>
                  <div class='input-group' >
                  <span class="input-group-addon">用户名</span>
                    <input type='text' id="oss_db_user" name="oss_db_user" class="form-control input-sm"   value="<?php echo $data["oss_db_user"] ? $data["oss_db_user"] :"ossdb";  ?>" />
                 </div>
             </div>
             <div class="form-group container-fluid">
            <label class="sr-only" for="oss_db_password">密码</label>
                  <div class='input-group'>
                  <span class="input-group-addon">密码</span>
                    <input type='password' id="oss_db_password" name="oss_db_password" class="form-control input-sm" value="<?php echo $data["oss_db_password"] ? $data["oss_db_password"] :"";  ?>"  />
                 </div>
             </div>
            </div><!--/oss-db-config-tab -->
            <div class="tab-pane" id="html-config-tab">
            <div class="form-group container-fluid">
                 <label class="sr-only" for="html_name">站点名称</label>
                  <div class='input-group'>
                  <span class="input-group-addon">站点名称设置</span>
                    <input type='text' id="html_name" name="html_name" class="form-control input-sm"  value='<?php echo $data["html_name"] ? $data["html_name"]:"数据打捞web版";  ?>'  />
                 </div>
            </div>   
            <div class="form-group container-fluid">
                 <label class="sr-only" for="menu_list_format">导航显示设置</label>
                  <div class='input-group'>
                  <span class="input-group-addon">导航显示设置</span>
                    <input type='text' id="menu_list_format" name="menu_list_format" class="form-control input-sm"  value='<?php echo $data["menu_list_format"] ? $data["menu_list_format"]:"index:最新动态,xls_vod:点播报表,xls_ad:广告报表,xls_order:订购报表,create_xls:数据导出,install:系统初始化";  ?>'  />
                 </div>
            </div>    
            <div class="form-group container-fluid">
                 <label class="sr-only" for="new_table_format">最新动态字段设置</label>
                  <div class='input-group'>
                  <span class="input-group-addon">最新动态字段设置</span>
                    <input type='text' id="new_table_format" name="new_table_format" class="form-control input-sm"  value='<?php echo $data["new_table_format"] ? $data["new_table_format"]:"create_time:创建时间,type:报表类型,status:状态,request_type:请求类型,description:数据范围";  ?>'  />
                 </div>
            </div>
            <div class="form-group container-fluid">
            <label class="sr-only" for="xls_table_format">报表类字段设置</label>
                  <div class='input-group'>
                  <span class="input-group-addon">报表类字段设置</span>
                    <input type='text' id="xls_table_format" name="xls_table_format" class="form-control input-sm" value="<?php echo $data["xls_table_format"] ? $data["xls_table_format"] :"file_name:文件名称,file_size:文件大小,status:状态,request_type:请求类型,description:数据范围";  ?>"  />
                 </div>
             </div>
            <div class="form-group container-fluid">
            <label class="sr-only" for="xls_table_status_format">表格状态文字显示</label>
                  <div class='input-group'>
                  <span class="input-group-addon">表格状态文字显示</span>
                    <input type='text' id="xls_table_status_format" name="xls_table_status_format" class="form-control input-sm" value="<?php echo $data["xls_table_status_format"] ? $data["xls_table_status_format"] :"初始化,数据打包失败,数据打包失败,数据打包失败,完成,文件被删除";  ?>"  />
                 </div>
             </div>
           <div class="form-group container-fluid">
           <label class="sr-only" for="show_status_color">是否开启状态颜色加强</label>
                  <div class='input-group' >
                  <span class="input-group-addon">是否开启状态颜色加强</span>
                    <input type='text' id="show_status_color" name="show_status_color" class="form-control input-sm"    value="<?php echo isset($data["show_status_color"])&&$data["show_status_color"]<1 ? $data["show_status_color"] :"1";  ?>" />
                 </div>
             </div>
             <div class="form-group container-fluid">
           <label class="sr-only" for="show_btn_color">是否开启操作按钮加强</label>
                  <div class='input-group' >
                  <span class="input-group-addon">是否开启操作按钮加强</span>
                    <input type='text' id="show_btn_color" name="show_btn_color" class="form-control input-sm"    value="<?php echo isset($data["show_btn_color"])&&$data["show_btn_color"]<1 ? $data["show_btn_color"] :"1";  ?>" />
                 </div>
             </div>             
             <div class="form-group container-fluid">
             <label class="sr-only" for="show_total">是否在导航处显示数据总数</label>
                  <div class='input-group' >
                  <span class="input-group-addon">是否在导航处显示数据总数</span>
                    <input type='text' id="show_total" name="show_total" class="form-control input-sm"   value="<?php echo $data["show_total"] ? $data["show_total"] :"1";  ?>" />
                 </div>
             </div>
             <div class="form-group container-fluid">
            <label class="sr-only" for="has_validator">表单提交是否开启加法验证</label>
                  <div class='input-group'>
                  <span class="input-group-addon">表单提交是否开启加法验证</span>
                    <input type='text' id="has_validator" name="has_validator" class="form-control input-sm" value="<?php echo isset($data["has_validator"])&&$data["has_validator"]<1 ? $data["has_validator"] :"1";  ?>"  />
                 </div>
             </div>
            </div><!--/oss-db-config-tab -->
        </div><!--/tab-content-->
        <div class="form-group">
            <div class="form-group  pull-right">
                <button type="submit" class="btn btn-primary">下一步</button>
            </div>
        </div>
    </form>
      </div>

      <?php   
    }
    
    
    ?>

    </div>

  </div>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="js/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
    <script type="text/javascript" src="js/bootstrapValidator.js"></script>
    <script type="text/javascript">
$(document).ready(function() {
  $('#myform').bootstrapValidator({
    excluded: [':disabled'],
  feedbackIcons: {
      valid: 'glyphicon glyphicon-ok',
      invalid: 'glyphicon glyphicon-remove',
      validating: 'glyphicon glyphicon-refresh'
  },
  fields: {
  viewlog_path: {
         validators: {
          notEmpty: {
              message: '收视纪录存放路径不能為空!'
          }
      }
  },
  log_interval_day:{
  validators: {
          notEmpty: {
              message: '保留天数不能為空!'
          },
          greaterThan: {
              value: 1,
              inclusive: false,
              message: '保存时间不得低于1天'
          }
      }
  },
  viewlog_interval_day:{
  validators: {
          notEmpty: {
              message: '保留天数不能為空!'
          },
          greaterThan: {
              value: 3,
              inclusive: false,
              message: '保存时间不得低于3天'
          }
      }
  },
  db_host: {
         validators: {
          notEmpty: {
              message: '数据库地址不能為空!'
          }
      }
  },
  db_name: {
         validators: {
          notEmpty: {
              message: '数据库名不能為空!'
          }
      }
  },
  db_user: {
         validators: {
          notEmpty: {
              message: '数据库用户名不能為空!'
          }
      }
  },
  db_password: {
         validators: {
          notEmpty: {
              message: '数据库用户名不能為空!'
          }
      }
  },
  cms_db_password: {
         validators: {
          notEmpty: {
              message: '数据库用户名不能為空!'
          }
      }
  },
  oss_db_password: {
         validators: {
          notEmpty: {
              message: '数据库用户名不能為空!'
          }
      }
  },
  cms_db_host: {
         validators: {
          notEmpty: {
              message: 'CMS数据库地址不能為空!'
          }
      }
  },
  cms_db_name: {
         validators: {
          notEmpty: {
              message: 'CMS数据库名不能為空!'
          }
      }
  },
  cms_db_user: {
         validators: {
          notEmpty: {
              message: 'CMS数据库用户名不能為空!'
          }
      }
  },
  oss_db_host: {
         validators: {
          notEmpty: {
              message: 'OSS数据库地址不能為空!'
          }
      }
  },
  oss_db_name: {
         validators: {
          notEmpty: {
              message: 'OSS数据库名不能為空!'
          }
      }
  },
  oss_db_user: {
         validators: {
          notEmpty: {
              message: 'OSS数据库用户名不能為空!'
          }
      }
  }
}
}).on('status.field.bv', function(e, data) {
  var $form     = $(e.target),
      validator = data.bv,
      $tabPane  = data.element.parents('.tab-pane'),
      tabId     = $tabPane.attr('id');
  
  if (tabId) {
      var $icon = $('a[href="#' + tabId + '"][data-toggle="tab"]').parent().find('i');

      // Add custom class to tab containing the field
      if (data.status == validator.STATUS_INVALID) {
          $icon.removeClass('fa-check').addClass('fa-times');
      } else if (data.status == validator.STATUS_VALID) {
          var isValidTab = validator.isValidContainer($tabPane);
          $icon.removeClass('fa-check fa-times')
               .addClass(isValidTab ? 'fa-check' : 'fa-times');
      }
  }
});


});

    </script>
  </body>
</html>

