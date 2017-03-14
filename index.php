ТЕСТ ТЕСТ ТЕСТ ТЕСТ ТЕСТ ТЕСТ ТЕСТ ТЕСТ ТЕСТ ТЕСТ ТЕСТ ТЕСТ ТЕСТ ТЕСТ 
<form enctype="multipart/form-data" action="#" method="POST">
    Отправить этот файл: <input name="datafile" type="file" />
    <input type="submit" value="Send File" />
</form>

<?
$start = microtime(true);
require_once('paserXML.php');
require_once('function.php');
//var_dump($TOVAR_XML); 
//if (empty($wp)) {require_once( '../wp-load.php' );wp( array( 'tb' => '1' ) );}
$log = array();
//Перебор товаров 
foreach ($TOVAR_XML as $ID_main => $VALUE_main){
	
$wpdb->show_errors();
//##########################если нет вариаций##################################################
if(count($VALUE_main['VARIACIA']) == 0)
{
	$buff = value_set ($ID_main, $VALUE_main['STOK'], $VALUE_main['PRICE'], $VALUE_main['PRICE_SALE']);
    if($buff) $log[$VALUE_main['SKU']] = $buff;
}

else //Если Вариаций больше одной
{
foreach ($VALUE_main['VARIACIA'] as $ID_variacia => $VALUE_variation)
{ 
   $buff_var = value_set ($ID_variacia, $VALUE_variation['STOK'], $VALUE_variation['PRICE'], $VALUE_variation['PRICE_SALE']);	
   if($buff_var) $log[$VALUE_main['SKU']][$VALUE_variation['SKU']] = $buff_var;
}
//---------------------------------------------------------------------------------
if(count($log[$VALUE_variation['SKU']]['PRICE'])>0){

//находим значения и ID максимальной и минимальной вариации
$price_variation = array();
$price_variation_sale = array();
foreach ($VALUE_main['VARIACIA'] as $ID_variacia => $VALUE_variation){
$price_variation[$ID_variacia] = $VALUE_variation['PRICE'];
$price_variation_sale[$ID_variacia]  = $VALUE_variation['PRICE_SALE'];
}
$value_variation = array();
foreach ($VALUE_main['VARIACIA'] as $ID_variacia => $VALUE_variation){
	
if($VALUE_variation['PRICE'] == max($price_variation)){
$value_variation['_max_regular_price_variation_id'] = $ID_variacia; $value_variation['_max_variation_regular_price'] = $VALUE_variation['PRICE'];
}
if($VALUE_variation['PRICE'] == min($price_variation)){
$value_variation['_min_regular_price_variation_id'] = $ID_variacia; $value_variation['_min_variation_regular_price'] = $VALUE_variation['PRICE'];
}
if($VALUE_variation['PRICE_SALE'] == max($price_variation_sale)){
$value_variation['_max_sale_price_variation_id'] = $ID_variacia; $value_variation['_max_variation_sale_price'] = $VALUE_variation['PRICE_SALE'];
}
if($VALUE_variation['PRICE_SALE'] == min($price_variation_sale)){
$value_variation['_min_sale_price_variation_id'] = $ID_variacia; $value_variation['_min_variation_sale_price'] = $VALUE_variation['PRICE_SALE'];
}
                                                                      }
$value_variation['_max_variation_price'] = (($value_variation['_max_variation_regular_price'] > $value_variation['_max_variation_sale_price'])&&(int)$value_variation['_max_variation_sale_price'])? 
$value_variation['_max_variation_sale_price'] : $value_variation['_max_variation_regular_price'] ;
$value_variation['_min_variation_price'] = (($value_variation['_min_variation_regular_price'] > $value_variation['_min_variation_sale_price'])&&(int)$value_variation['_min_variation_sale_price'])? 
$value_variation['_min_variation_sale_price'] : $value_variation['_min_variation_regular_price'] ;
$value_variation['_max_price_variation_id'] = (($value_variation['_max_variation_regular_price'] > $value_variation['_max_variation_sale_price'])&&(int)$value_variation['_max_variation_sale_price'])? 
$value_variation['_max_sale_price_variation_id'] : $value_variation['_max_regular_price_variation_id'];
$value_variation['_min_price_variation_id'] = (($value_variation['_min_variation_regular_price'] > $value_variation['_min_variation_sale_price'])&&(int)$value_variation['_min_variation_sale_price'])? 
$value_variation['_min_sale_price_variation_id'] : $value_variation['_min_regular_price_variation_id'];

foreach ($value_variation as $KEY => $VALUE){
//МИН МАКС стоимость распрадажи
$sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = %s WHERE 
meta_key = %s  AND post_id = %d", $VALUE, $KEY, $ID_main);
$wpdb->query($sql);
}
//#################################################################################################################################################
// РАБОТАЕМ C таблицей опций-----------------------------------------------------------------------------------------------------------------------
//#################################################################################################################################################
$option = Get_option('_transient_wc_var_prices_'.$ID_main);
$option = json_decode($option, true);
//var_dump($option);
//echo "<br>";
$option_new = array();
if($option){// ЕСЛИ ЗАПИCЬ ЕСТЬ!
foreach($option as $key_op => $value_op){
foreach ($VALUE_main['VARIACIA'] as $ID_variacia => $VALUE_variation)
{
$option_new[$key_op]["regular_price"][$ID_variacia] = number_format($VALUE_variation['PRICE'], 2, '.', '');
$option_new[$key_op]["sale_price"][$ID_variacia] = (($VALUE_variation['PRICE_SALE']<$VALUE_variation['PRICE'])&& (int)$VALUE_variation['PRICE_SALE'])  ? number_format($VALUE_variation['PRICE_SALE'], 2, '.', '') : number_format($VALUE_variation['PRICE'], 2, '.', '');
$option_new[$key_op]['price'][$ID_variacia] = (($VALUE_variation['PRICE_SALE']<$VALUE_variation['PRICE'])&& (int)$VALUE_variation['PRICE_SALE']) ? number_format($VALUE_variation['PRICE_SALE'], 2, '.', '') : number_format($VALUE_variation['PRICE'], 2, '.', '');
}
//Сортировка 
asort($option_new[$key_op]['price']);
asort($option_new[$key_op]['regular_price']);
asort($option_new[$key_op]['sale_price']);
}
$option_save = json_encode($option_new);
//var_dump($option);
Update_option('_transient_wc_var_prices_'.$ID_main, $option_save);
} 
//#################################################################################################################################################
}
      }                                      
        	  }											 
//Запись лога
echo "<br>";
echo "Изменено -".count($log)." товаров.<br>"; 
$log_save = json_encode($log);
echo "<br>";
//var_dump($log_save);
echo "<br>";
$time = microtime(true) - $start;
printf('УСПЕХ! Скрипт выполнялся %.4F сек.', $time);
//Запись лога в базу
$sql = $wpdb->prepare("INSERT INTO `wpcy_1c_stat` (date, sek, col_all, col_edit, data) VALUES (now(), %d, %d, %d, %s)", $time, $i, count($log), $log_save);
$wpdb->query($sql);


























?>