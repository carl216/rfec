        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
          <!--<h1 class="page-header">Dashboard</h1>-->
          <h3 class="sub-header"><?php echo $menu_list_format_arr->$page_name; ?></h3>
          <div class="table-responsive">
            <table class="table table-striped table-hover">
              <thead>
                <tr>
<?php   
      $table_format_str;
      if(isset($xls_type)){
        $table_format_str=$cfg["xls_table_format"];
      }else{
        $table_format_str=$cfg["new_table_format"];
      }

      if(preg_match('/\w:/', $table_format_str)){
        $table_format_str = "{".preg_replace('/[\w\x{4e00}-\x{9fa5}]+/u', '"$0"', $table_format_str)."}";

      }
      $table_format_arr=json_decode( $table_format_str);
      foreach($table_format_arr as $key=>$val){
        echo " <th>{$val}</th>";

      }
      //var_dump(json_decode( $table_format_str));

?>              
                  <th></th>
                </tr>
              </thead>
              <tbody>
<?php
$page_index=isset($_GET['page_index']) ? $_GET['page_index'] :1;
$page_size=10;
$total=$xsl_total_list[$page_name];
if($total < 1){
  ?>
              <tr>
              <td colspan="6"><p class="text-center">暂无数据</p></td>

            </tr>
  <?php

}else{
           $xls_type_str=array("未知","点播","广告","订购","片花");
           $request_type_str=array("cron","web");
           $status_str=explode(',',$cfg['xls_table_status_format']);
           $show_btn_color=$cfg['show_btn_color'] && $cfg['show_btn_color']> 0 ? "btn btn-primary" :"";
           $sql="SELECT * FROM `work_log` ".(isset($xls_type) ? " where type=".$xls_type." and status=5 "  :"")." order by update_time desc limit ".($page_index-1)*$page_size.",{$page_size};";
           $rs=$local_dbh->query($sql);
           $data=$rs->fetchAll(PDO::FETCH_NAMED);
           foreach( $data as $xls){   
            $status_class= "info";
            $hasfile=false;
            if($xls['status'] == 5){
              if(is_file($xls['file_path'])){
                $hasfile=true;
                $status_class="success";
              }else{
                $xls['status']=6;
              }

            }else if($xls['status']  > 1 ){
              $status_class="danger";
            }
?>
              <tr class="<?php echo $cfg['show_status_color'] > 0 ? $status_class:"" ; ?>">
              <?php 
               foreach($table_format_arr as $key=>$val){
                 $tmp_str=$xls[$key];
                if($key=="type"){
                  $tmp_str=$xls_type_str[$tmp_str];
                }else if($key=="request_type"){
                  $tmp_str=$request_type_str[$tmp_str];
                }else if($key=="status"){
                  $tmp_str=$status_str[$tmp_str];
                }

                 echo " <td>";
                 if($key=="file_name"&&$hasfile){
                  
                 echo "<a href=\"{$xls['file_path']}\"><img src=\"./img/fu_exl.gif\" />{$tmp_str}<i class=\"fa fa-download\" ></i></a>";
                 }else if($key=="file_name"&&!$hasfile){
                  echo "<del><img src=\"./img/fu_exl.gif\" />{$tmp_str}</del>";
                 }else{
                  echo $tmp_str;
                 }               
                 echo "</td>";

            }
              ?>
              <td>
              <a data-toggle="modal" href="<?php echo $xls['log_path'] ?>" data-target="#gridSystemModal"  class="fa fa-eye  <?php echo $show_btn_color ?>" >日誌</i></a>&nbsp;
              <?php 
                echo  $hasfile ? "<a href=\"{$xls['file_path']}\" class=\"fa fa-download {$show_btn_color}\" aria-hidden=\"true\">下載</a>":"";    
              ?>             	
              </td>
            </tr>
<?php 
          }
?>
              </tbody>
              <tfoot>
                <tr>
                <td colspan="6">
                <nav aria-label="Page navigation">
                <ul class="pagination pull-right">

                  <?php 
                  $maxPageSize=ceil($total/$page_size);
                  $hasPrevious=$page_index > 1;
                  $hasNext=$page_index < $maxPageSize;
                  ?>
                  <li class="<?php echo $hasPrevious ? '' :'disabled' ?>">
                  <?php
                    if( $hasPrevious) {
                      echo "<a href=\"?page_index=".($page_index-1)."\" aria-label=\"Previous\">";
                    }
                    echo "<span aria-hidden=\"true\">&laquo;</span>";
                    if( $hasPrevious) {
                      echo "</a>";
                    }
                    ?>
                  </li>

                  <?php
                    for($i=1;$i<=$maxPageSize;$i++){ 
                      if($i==$page_index){
                        echo "<li class=\"active\"><a href=\"?page_index={$i}\">{$i}<span class=\"sr-only\">(current)</span></a></li>";
                      }else{
                        echo "<li><a href=\"?page_index={$i}\">{$i}</a></li>";
                      }
                     
                    }              
                  ?>       
                  <li class="<?php echo $hasNext ? '' :'disabled' ?>">
                  <?php
                    if( $hasNext) {
                      echo "<a href=\"?page_index=".($page_index+1)."\" aria-label=\"Next\">";
                    }
                    echo "<span aria-hidden=\"true\">&raquo;</span>";
                    if( $hasNext) {
                      echo "</a>";
                    }
                    ?>
                  </li>        
                </ul>
              </nav>
        </td>
        </tr>
              </tfoot>
        <?php }?>         
            </table>
          </div>
        </div>
      </div>
    </div>
   <div id="gridSystemModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="gridModalLabel" style="display: none;">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
          <h4 class="modal-title" id="gridModalLabel">Modal title</h4>
        </div>
        <div class="modal-body">
          <div class="row" id="mymessgae">

          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div>
