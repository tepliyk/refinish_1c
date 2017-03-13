ТЕСТ ТЕСТ ТЕСТ ТЕСТ ТЕСТ ТЕСТ ТЕСТ ТЕСТ ТЕСТ ТЕСТ ТЕСТ ТЕСТ ТЕСТ ТЕСТ 

<form enctype="multipart/form-data" action="#" method="POST">
    Отправить этот файл: <input name="datafile" type="file" />
    <input type="submit" value="Send File" />
</form>

<?
$start = microtime(true);
require_once('paserXML.php');
//var_dump($TOVAR_XML); 
//if (empty($wp)) {require_once( '../wp-load.php' );wp( array( 'tb' => '1' ) );}
$log = array();
//Перебор товаров 
foreach ($TOVAR_XML as $ID_main => $VALUE_main){
	
$wpdb->show_errors();
//##########################если нет вариаций##################################################
if(count($VALUE_main['VARIACIA']) == 0)
{
	$log = value_set ($ID_main, $VALUE_main['SKU'], $VALUE_main['STOK'], $VALUE_main['PRICE'], $VALUE_main['PRICE_SALE']);
}
//Если Вариаций больше одной
else {
	
foreach ($VALUE_main['VARIACIA'] as $ID_variacia => $VALUE_variation)
{ 
   $log = value_set ($ID_variacia, $VALUE_variation['SKU'], $VALUE_variation['STOK'], $VALUE_variation['PRICE'], $VALUE_variation['PRICE_SALE']);	
}
//---------------------------------------------------------------------------------
//находим значения и ID максимальной и минимальной вариации
//---------------------------------------------------------------------------------
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
$value_variation'_max_sale_price_variation_id'] = $ID_variacia; $value_variation['_max_variation_sale_price'] = $VALUE_variation['PRICE_SALE'];
}
if($VALUE_variation['PRICE_SALE'] == min($price_variation_sale)){
$value_variation['_min_sale_price_variation_id'] = $ID_variacia; $value_variation['_min_variation_sale_price'] = $VALUE_variation['PRICE_SALE'];
}
                                                                      }
$value_variation['_max_variation_price'] = ($value_variation['_max_variation_regular_price'] > $value_variation['_max_variation_sale_price']) ? $value_variation['_max_variation_sale_price'] : $value_variation['_max_variation_regular_price'] ;
$value_variation['_min_variation_price'] = ($value_variation['_min_variation_regular_price'] > $value_variation['_min_variation_sale_price']) ? $value_variation['_min_variation_sale_price'] : $value_variation['_min_variation_regular_price'] ;
$value_variation['_max_price_variation_id'] = ($value_variation['_max_variation_regular_price'] > $value_variation['_max_variation_sale_price']) ? $value_variation['_max_sale_price_variation_id'] : $value_variation['_max_regular_price_variation_id'];
$value_variation['_min_price_variation_id'] = ($value_variation['_min_variation_regular_price'] > $value_variation['_min_variation_sale_price']) ? $value_variation['_min_sale_price_variation_id'] : $value_variation['_min_regular_price_variation_id'];

foreach ($value_variation as $KEY => $VALUE){
//МИН МАКС стоимость распрадажи
$sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = %s WHERE 
meta_key = %s  AND post_id = %d", $VALUE, $KEY, $ID_main);
$wpdb->query($sql);
}
//-----------------------------------------------------------------------------------------

//#################################################################################################################################################
// РАБОТАЕМ C таблицей опций-----------------------------------------------------------------------------------------------------------------------
//#################################################################################################################################################
$option = Get_option('_transient_wc_var_prices_'.$ID_main);
$option = json_decode($option, true);
if($option) // ЕСЛИ ЗАПИМЬ ЕСТЬ!
foreach($option as $key_op => $value_op) 
foreach($value_op as $key =>$value)
foreach ($VALUE_main['OPTION'] as $ID_variacia => $VALUE_variation)
{
$option[$key_op]["regular_price"][$ID_variacia] = number_format($VALUE_variation['PRICE'], 2, '.', '');
$option[$key_op]["sale_price"][$ID_variacia] = ($VALUE_variation['PRICE_SALE']<$VALUE_variation['PRICE']) ? number_format($VALUE_variation['PRICE_SALE'], 2, '.', '') : number_format($VALUE_variation['PRICE'], 2, '.', '');
$option[$key_op]['price'][$ID_variacia] = ($VALUE_variation['PRICE_SALE']<$VALUE_variation['PRICE']) ? number_format($VALUE_variation['PRICE_SALE'], 2, '.', '') : number_format($VALUE_variation['PRICE'], 2, '.', '');
asort($option[$key_op]['price']);
asort($option[$key_op]['regular_price']);
asort($option[$key_op]['sale_price']);
} 
$option = json_encode($option);
Update_option('_transient_wc_var_prices_'.$ID_main, $option );
//#################################################################################################################################################
}
                                             }
											 
$time = microtime(true) - $start;
printf('УСПЕХ! Скрипт выполнялся %.4F сек.', $time);
echo "<br>";
@header('HTTP/1.1 200 Ok');
@header('Content-type: text/html; charset=windows-1251');
//Запись лога
var_dump($log);


function value_set ($id, $sku, $stok, $price_reg, $price_sale)
{
	global $wpdb;
//Проверка изменилось значение или нет
$SK = $wpdb->get_results("SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = '_stock' AND post_id ='".$id."'");
$stock = (int)$SK[0]->meta_value;
if((int)$stock != (int)$stok){
$log["TXT"][] = "Товар КОЛ - ".(int)$stock."->".(int)$stok;
$log["SKU"][] = (string)$sku; 
//ОСТАТОК 
$sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = %s WHERE 
meta_key = '_stock'  AND post_id = %d", $stok, $id );
$wpdb->query($sql);
//Наличие
$stock_status = ($stok==0) ? "outofstock" : "instock";
$sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = %s WHERE 
meta_key = '_stock_status'  AND post_id = %s", $stock_status, $id );
$wpdb->query($sql);
}
// Проверки  на больше меньше и приведение форматов
$var_price_reg_main = number_format($price_reg, 2, '.', '');
$var_price_sale_main = ($price_sale<$price_reg)? number_format($price_sale, 2, '.', '') : NULL;
$var_price_main = ($var_price_sale_main) ? number_format($price_sale, 2, '.', '') : number_format($price_reg, 2, '.', '');
//-----------------------------------------------------------------------------------------------------------------------
$PR = $wpdb->get_results("SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = '_price' AND post_id ='".$id."'");
$price = $PR[0]->meta_value;
if(($price != $var_price_main))
{
$log["TXT"][] = "Товар ЦЕНА - ".$price."->".$var_price_main;
$log["SKU"][] = (string)$sku; 
//ОСНОВНАЯ стоимость 
$sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = %s WHERE 
meta_key = '_regular_price' AND post_id = %d", $var_price_reg_main, $id );
$wpdb->query($sql);
$sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = %s WHERE 
 meta_key = '_sale_price' AND post_id = %d", $var_price_sale_main, $id );
$wpdb->query($sql);
$sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = %s WHERE 
meta_key = '_price'  AND post_id = %d", $var_price_main, $id);
$wpdb->query($sql);
}
	
return $log;	
}
?>