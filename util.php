<?php
set_time_limit(30000);//脚本最大执行时间
ini_set('memory_limit', '-1');
date_default_timezone_set("PRC");
if(!is_file("./config/system.ini")){
  header('Location: install.php');
}
$cfg = @parse_ini_file("./config/system.ini");
if($cfg['init'] < 1){
  header('Location: install.php');
}
?>