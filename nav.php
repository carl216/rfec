<?php
    $total=0;
    $local_dbh = new PDO("mysql:host=".$cfg['db_host'].";dbname=".$cfg['db_name'], $cfg['db_user'], $cfg['db_password'], array(PDO::ATTR_PERSISTENT=>false, PDO::MYSQL_ATTR_INIT_COMMAND=>"SET NAMES 'utf8';"));
    $sql=<<<EOL
      SELECT type ,count(*)as total FROM `work_log` where status=5 GROUP BY type;
EOL;
    $rs=$local_dbh->query($sql);
    $xsl_total_list;
    $data_list=$rs->fetchall(PDO::FETCH_NAMED);
    $xsl_total_list['xls_vod']=0;
    $xsl_total_list['xls_ad']=0;
    $xsl_total_list['xls_order']=0;
    foreach($data_list as $data){
       if($data['type']==1){
        $xsl_total_list['xls_vod']=$data['total'];
       }else if($data['type']==2){
        $xsl_total_list['xls_ad']=$data['total'];
       }else if($data['type']==3){
        $xsl_total_list['xls_order']=$data['total'];
      } 
    }
    $sql=<<<EOL
    SELECT count(*)as total FROM `work_log`;
EOL;
  $rs=$local_dbh->query($sql);
  $data_list=$rs->fetch(PDO::FETCH_NAMED);
  $total=$data_list['total'];
  $xsl_total_list['index']=$total;
    
    ?>
  <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#"><?php echo $cfg["html_name"] ? $cfg["html_name"]:"数据打捞web版";  ?></a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-right">
          <?php 
                $menu_list_format_str=$cfg['menu_list_format'];
                if(preg_match('/\w:/', $menu_list_format_str)){
                  $menu_list_format_str = "{".preg_replace('/[\w\x{4e00}-\x{9fa5}]+/u', '"$0"', $menu_list_format_str)."}";
          
                }
                $menu_list_format_arr=json_decode($menu_list_format_str);
                foreach( $menu_list_format_arr as $key=>$val){             
                $html_active =  $page_name==$key ? 'class="active"' : '';
                $html_current = $page_name=="xls_order" ? '<span class="sr-only">(current)</span>':"" ;
                $html_badge= ($cfg['show_total'] == 2 || $cfg['show_total'] == 3  )&& isset($xsl_total_list[$key]) ? "<span class=\"badge pull-right\">{$xsl_total_list[$key]}</span>":"";
                echo "<li {$html_active}><a href=\"{$key}.php\">{$val}{$html_current}{$html_badge}</a></li>";
                }
          
          ?>
          </ul>
        </div>
      </div>
    </nav>

    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-3 col-md-2 sidebar">
          <ul class="nav nav-sidebar">
<?php 
                foreach( $menu_list_format_arr as $key=>$val){             
                  $html_active =  $page_name==$key ? 'class="active"' : '';
                  $html_current = $page_name=="xls_order" ? '<span class="sr-only">(current)</span>':"" ;
                  $html_badge= ($cfg['show_total'] == 1 || $cfg['show_total'] == 3) &&isset($xsl_total_list[$key]) ? "<span class=\"badge pull-right\">{$xsl_total_list[$key]}</span>":"";
                  echo "<li {$html_active}><a href=\"{$key}.php\">{$val}{$html_current}{$html_badge}</a></li>";
                  }
?>
          </ul>
        </div>
