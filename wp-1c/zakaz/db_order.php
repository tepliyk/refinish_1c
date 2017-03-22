<?php
$qwery="SELECT
  wpcy_posts.ID,
  wpcy_posts.post_date,
  wpcy_posts.post_status
FROM wpcy_posts
WHERE wpcy_posts.post_type = 'shop_order'
AND (wpcy_posts.post_status = 'wc-on-hold'
OR wpcy_posts.post_status = 'wc-processing')";

//Заказы
$db = $wpdb->get_results($qwery);

$ZAKAZ = array(); 

foreach($db as $zakaz) {

$id_zakaz = (int)$zakaz->ID;
//тег заказа
$zakaz_teg = $id_zakaz."ORDER";

$ZAKAZ[$zakaz_teg]["ID"]=$id_zakaz;
$ZAKAZ[$zakaz_teg]["DATE"]=$zakaz->post_date;
$ZAKAZ[$zakaz_teg]["STATUS"]=$zakaz->post_status;

$qwery="SELECT
  wpcy_postmeta_1.post_id,
  wpcy_postmeta_1.meta_value,
  wpcy_postmeta_1.meta_key
FROM wpcy_postmeta wpcy_postmeta_1
WHERE wpcy_postmeta_1.post_id =".$id_zakaz;

$data = $wpdb->get_results($qwery);
foreach($data as $dt){
$id_zakaz2 = (int)$dt->post_id;

//тег заказа
$zakaz_teg2 = $id_zakaz2."ORDER";

if($dt->meta_key == "_billing_first_name")
$billing_first_name = $dt->meta_value;
if($dt->meta_key == "_billing_last_name")
$billing_last_name = $dt->meta_value;
$ZAKAZ[$zakaz_teg2]["FIO"] = $billing_first_name." ".$billing_last_name;

if($dt->meta_key == "_billing_company")
$ZAKAZ[$zakaz_teg2]["COMPANY"] = $dt->meta_value;

if($dt->meta_key == "_billing_email")
$ZAKAZ[$zakaz_teg2]["EMAIL"] = $dt->meta_value;

if($dt->meta_key == "_billing_phone")
$ZAKAZ[$zakaz_teg2]["PHONE"] = $dt->meta_value;

if($dt->meta_key == "_shipping_state")
$shipping_state = $dt->meta_value;
if($dt->meta_key == "_billing_address_1")
$billing_address_1 = $dt->meta_value;
if($dt->meta_key == "_billing_address_2")
$billing_address_2 = $dt->meta_value;
if($dt->meta_key == "_billing_city")
$billing_city = $dt->meta_value;

$ZAKAZ[$zakaz_teg2]["ADRESS"] =  $shipping_state." ".$billing_city." ".$billing_address_1." ".$billing_address_2;

if($dt->meta_key == "_payment_method_title")
$ZAKAZ[$zakaz_teg2]["METHOD_PAY"] = $dt->meta_value;

//if($dt->meta_key == "_order_total")
//$ZAKAZ[$zakaz_teg2]["PRICE_TOTAL"] = $dt->meta_value;
}
//------------------------------------------------------------------------------------------

$qwery="SELECT
  order_itemmeta.meta_key,
  order_itemmeta.meta_value,
  order_items.order_id,
  order_items.order_item_name,
  order_items.order_item_type
FROM wpcy_woocommerce_order_items order_items
  INNER JOIN wpcy_woocommerce_order_itemmeta order_itemmeta
    ON order_items.order_item_id = order_itemmeta.order_item_id
WHERE order_items.order_id =".$id_zakaz;
 
$tovar = $wpdb->get_results($qwery);

foreach($tovar as $vl){

$id_zakaz3 = (int)$vl->order_id;

//тег заказа
$zakaz_teg3 = $id_zakaz3."ORDER";

$name_tovar = $vl->order_item_name;
$name_atrib = md5($name_tovar)."TOVAR";



if($vl->meta_key == "method_id")
$method_id = $vl->meta_value;

if($vl->order_item_type=='line_item'){
	
$TOVAR[$name_atrib]["NAME"] = $name_tovar ;

if($vl->meta_key == "_qty")
$TOVAR[$name_atrib]["QTY"] = $vl->meta_value;

if($vl->meta_key == "_product_id")
$ID_p =(string)$vl->meta_value;
if($vl->meta_key == "_variation_id")
$ID_v = (string)$vl->meta_value;
$id_tovara = ($ID_v)? $ID_v : $ID_p;

$qwery="SELECT
  wpcy_postmeta_1.meta_key,
  wpcy_postmeta_1.meta_value
FROM wpcy_postmeta wpcy_postmeta_1
WHERE wpcy_postmeta_1.post_id =".$id_tovara;
$SV = $wpdb->get_results($qwery);
foreach($SV as $value){

if($value->meta_key == "_sku")
$TOVAR[$name_atrib]["SKU"] = $value->meta_value;

if($value->meta_key == "_price")
$TOVAR[$name_atrib]["PRICE"] = $value->meta_value;
}
}

}
 $total_price =0;
 foreach($TOVAR as $tovar)
 $total_price += $tovar["PRICE"]*$tovar["QTY"];
 $ZAKAZ[$zakaz_teg3]["PRICE_TOTAL"] = $total_price;
 
$ZAKAZ[$zakaz_teg3]["METHOD_ID"] = $method_id;

$ZAKAZ[$zakaz_teg3]['TOVARI'] = $TOVAR;

}