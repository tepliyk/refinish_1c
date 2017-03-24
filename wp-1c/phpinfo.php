<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="Refresh" content="600" />
    <title>Лог файл Обмена 1С</title>

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">

<!-- Optional theme -->
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css">



    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
	<table class="table table-hover table-condensed table-bordered table-striped" style="
    width: 90%;
    margin-left: 5%;
    margin-right: 5%;
    margin-top: 10px;
">
      <thead>
        <tr>
          <th>#</th>
          <th>Дата</th>
          <th>Загрузка(сек.)</th>
          <th>Распозано</th>
		  <th>Изменено</th>
          <th>Лог изменений</th>
		  <th style="width: 15%;">Заказы в обмене</th>
        </tr>
      </thead>
      <tbody>
   <?php 
   error_reporting( E_ERROR );
   //Подключаем библиотеки вордпреса 
    if (empty($wp)) {require_once( '../../wp-load.php' );wp( array( 'tb' => '1' ) );}	
	$ID = $wpdb->get_results("SELECT * FROM wpcy_1c_stat order by id desc");
	
    foreach($ID as $key=>$id) {	
echo "<tr><td>".$id->id."</td><td>".date("d.m.Y H:i:s", strtotime($id->date))."</td><td>".$id->sek."</td><td>".$id->col_all."</td><td>".$id->col_edit."</td><td>".$id->data."</td><td>";
//Вывод  перечня заказов

$order_now =array(); 
$order_last =array();
$order_now = $ID[$key]->order_new;
if($order_now){$order_now = json_decode($order_now, true); $order_now[]=0;} else $order_now =array();
$order_last =  $ID[$key+1]->order_new;
if($order_last){$order_last = json_decode($order_last, true); $order_last[]=0;} else $order_last =array();

$new = array_diff($order_now, $order_last);

$delete = array_diff($order_last, $order_now);

$old = array_diff($order_now, $new, $delete);

foreach($new as $id)
if($id)
echo ' <a target="_blank" class="btn btn-success" style="padding: 3px; margin: 2px;" href="/wp-admin/post.php?post='.$id.'&action=edit">'.$id.'</a> ';

foreach($delete as $id)
if($id)
echo ' <a target="_blank" class="btn btn-danger" style="padding: 3px; margin: 2px;" href="/wp-admin/post.php?post='.$id.'&action=edit">'.$id.'</a> ';

foreach($old as $id)
if($id)
echo ' <a target="_blank" class="btn btn-info" style="padding: 3px; margin: 2px;" href="/wp-admin/post.php?post='.$id.'&action=edit">'.$id.'</a> ';

//var_dump(
//var_dump(
echo "</td></tr>";
} 

	?>	    
      </tbody>
    </table>
	
	<?require_once("./zakaz/index.php");?>
	
	
	
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <!-- Latest compiled and minified JavaScript -->
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
  </body>
</html>