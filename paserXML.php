<?php
//Подключаем библиотеки вордпреса 
if (empty($wp)) {require_once( '../wp-load.php' );wp( array( 'tb' => '1' ) );}

if(!$_FILES['datafile']['tmp_name']) exit("Файл не получен!");

$uploaddir = 'c:/OpenServer/domains/refinish.ua/wp-1c/';
$uploadfile = $uploaddir . basename($_FILES['datafile']['name']);

echo '<pre>';
if (move_uploaded_file($_FILES['datafile']['tmp_name'], $uploadfile)) {
    echo "Файл корректен и был успешно загружен.\n";
} else {
    echo "Возможная атака с помощью файловой загрузки!\n";
}
 
if (file_exists($uploadfile)) {
    $movies = simplexml_load_file($uploadfile);
 // print_r($movies);
} else {
    exit('Не удалось открыть файл. - '.$uploadfile);
} 
//ПЕРЕименуем  файлик 
rename($uploadfile, $uploaddir."/up/".date('H_i_s_d_m_y').".xml"); 

//echo 'Некоторая отладочная информация:';
//print_r($_FILES);

//-----------------------------------------------------------------------------------------------
$TOVAR_XML = array(); $i = 0; $buff ="";
//-----------------------------------------------------------------------------------------------
foreach($movies->MAIN_TOVAR as $MAIN_TOVAR){
// Получаем ID по артиклу 
$ID = $wpdb->get_results("SELECT post_id FROM $wpdb->postmeta WHERE meta_value = '".$MAIN_TOVAR->SKU."'");
$post_id = (int)$ID[0]->post_id;
if($post_id){
$i++;
//-----------------------------------------------------------------------------------------------
$TOVAR_XML[$post_id]['SKU'] = $MAIN_TOVAR->SKU;
$TOVAR_XML[$post_id]['PRICE'] = ((int)$MAIN_TOVAR->PRICE) ? (float)$MAIN_TOVAR->PRICE : 0;
$TOVAR_XML[$post_id]['PRICE_SALE'] = (string)$MAIN_TOVAR->PRICE_SALE ;
$TOVAR_XML[$post_id]['STOK'] = (int)$MAIN_TOVAR->STOK;
if($MAIN_TOVAR->VARIACIA)
foreach($MAIN_TOVAR->VARIACIA as $VARIACIA){
// Получаем ID по артиклу //Для цен максимальное значение
$ID_var = $wpdb->get_results("SELECT post_id FROM $wpdb->postmeta WHERE meta_value = '".$VARIACIA->SKU."' ORDER BY post_id DESC");
$post_id_v = (int)$ID_var[0]->post_id;
if($post_id_v){
$TOVAR_XML[$post_id]['VARIACIA'][$post_id_v]['SKU'] = $VARIACIA->SKU;
$TOVAR_XML[$post_id]['VARIACIA'][$post_id_v]['PRICE'] = ((int)$VARIACIA->PRICE) ? (float)$VARIACIA->PRICE : 0;
$TOVAR_XML[$post_id]['VARIACIA'][$post_id_v]['PRICE_SALE'] = (string)$VARIACIA->PRICE_SALE;
$TOVAR_XML[$post_id]['VARIACIA'][$post_id_v]['STOK'] = (int)$VARIACIA->STOK; 
              }
//Для опций минимальное значение
$ID_var = $wpdb->get_results("SELECT post_id FROM $wpdb->postmeta WHERE meta_value = '".$VARIACIA->SKU."'");
$post_id_v = (int)$ID_var[0]->post_id;
if($post_id_v){
$TOVAR_XML[$post_id]['OPTION'][$post_id_v]['PRICE'] = ((int)$VARIACIA->PRICE) ? (float)$VARIACIA->PRICE : 0;
$TOVAR_XML[$post_id]['OPTION'][$post_id_v]['PRICE_SALE'] = (string)$VARIACIA->PRICE_SALE;
             }
			    }
			      }
                    }                      
//var_dump($TOVAR_XML); 

echo "Всего - ".$i. " шт.";
print "</pre>";
?>