<?php
function value_set ($id, $stok, $price_reg, $price_sale)
{
	global $wpdb;
//Проверка изменилось значение или нет
$SK = $wpdb->get_results("SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = '_stock' AND post_id ='".$id."'");
$stock = (int)$SK[0]->meta_value;
if((int)$stock != (int)$stok){
$log["COL"]= (int)$stock."->".(int)$stok;
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
$var_price_sale_main = (($price_sale<$price_reg)&& (int)$price_sale) ? number_format($price_sale, 2, '.', '') : NULL;
$var_price_main = ($var_price_sale_main) ? number_format($price_sale, 2, '.', '') : number_format($price_reg, 2, '.', '');
//-----------------------------------------------------------------------------------------------------------------------
$PR = $wpdb->get_results("SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = '_price' AND post_id ='".$id."'");
$price = $PR[0]->meta_value;

$PR_RG = $wpdb->get_results("SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = '_regular_price' AND post_id ='".$id."'");
$price_reg = $PR_RG[0]->meta_value;
//Если изменилась регулярная стоимость или стоимость 
if((($price != $var_price_main) || ($price_reg != $var_price_reg_main)) && (int)$var_price_main)
{
$log["PRICE"] = $price."->".$var_price_main;
if($price==$var_price_main) $log["PRICE"] .= "(".$price_reg."->".$var_price_reg_main.")";
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