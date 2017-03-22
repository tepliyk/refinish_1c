<?php
//Подключаем библиотеки вордпреса 
//if(!$_FILES['datafile']['tmp_name']) 
	die();

$uploadfile = $_FILES['datafile']['tmp_name'];

if (file_exists($uploadfile)) {

 echo "<!-- ФАЙЛ ПОЛУЧЕН -->"; 
 
$err =	(base64_decode(file_get_contents($_FILES['datafile']['tmp_name'])));
$movies  =	new SimpleXMLElement($err);
//var_dump($movies);
	
} else {
  die('<!--Не удалось открыть файл. - '.$uploadfile.'-->');
} 

//-----------------------------------------------------------------------------------------------
$TOVAR_XML = array(); $i = 0;
//-----------------------------------------------------------------------------------------------
foreach($movies->MAIN_TOVAR as $MAIN_TOVAR){
// Получаем ID по артиклу 
$ID = $wpdb->get_results("SELECT post_id FROM $wpdb->postmeta WHERE meta_value = '".$MAIN_TOVAR->SKU."'");
$post_id = (int)$ID[0]->post_id;
if($post_id){
$i++;
//-----------------------------------------------------------------------------------------------
$TOVAR_XML[$post_id]['SKU'] = (string)$MAIN_TOVAR->SKU;
$TOVAR_XML[$post_id]['PRICE'] = ((int)$MAIN_TOVAR->PRICE) ? (float)$MAIN_TOVAR->PRICE : 0;
$TOVAR_XML[$post_id]['PRICE_SALE'] = (string)$MAIN_TOVAR->PRICE_SALE ;
$TOVAR_XML[$post_id]['STOK'] = (int)$MAIN_TOVAR->STOK;
if($MAIN_TOVAR->VARIACIA)
foreach($MAIN_TOVAR->VARIACIA as $VARIACIA){
// Получаем ID по артиклу 
//Для цен максимальное значение
$ID_var = $wpdb->get_results("SELECT post_id FROM $wpdb->postmeta WHERE meta_value = '".$VARIACIA->SKU."'");
if(count($ID_var)>1){
foreach ($ID_var as $ID){
$post_id_v = (int)$ID->post_id;
$SKU = $wpdb->get_results("SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = '_sku' AND post_id ='".$post_id_v."'");
$sku_var = (string)$SKU[0]->meta_value;
if ($sku_var == (string)$VARIACIA->SKU) Break;
}
                   }
else
$post_id_v = (int)$ID_var[0]->post_id;
if($post_id_v){
$TOVAR_XML[$post_id]['VARIACIA'][$post_id_v]['SKU'] = (string)$VARIACIA->SKU;
$TOVAR_XML[$post_id]['VARIACIA'][$post_id_v]['PRICE'] = ((int)$VARIACIA->PRICE) ? (float)$VARIACIA->PRICE : 0;
$TOVAR_XML[$post_id]['VARIACIA'][$post_id_v]['PRICE_SALE'] = (string)$VARIACIA->PRICE_SALE;
$TOVAR_XML[$post_id]['VARIACIA'][$post_id_v]['STOK'] = (int)$VARIACIA->STOK;             		  
              }
			  }
			    }
			      }
                                        
//var_dump($TOVAR_XML); 
?>