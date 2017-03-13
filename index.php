<!DOCTYPE HTML>
<html lang="ru-ru">
    <head>
        <meta charset="utf-8">
        <title>auto ОБНОВЛЕНИЕ ЦЕН</title>
        <link href="bootstrap.css" rel="stylesheet" media="screen"> 
    </head>
    <body>
        <header></header>
			<div class = "main"
			style="
    width: 320px;
    margin-left: calc(50% - 300px);
    margin-top: 50px;
    padding: 30px;
    border: solid 1px;
    border-radius: 10px;
    box-shadow: 0 0 5px black;
	height: 200px;
    overflow: auto;
"> ТЕСТ ТЕСТ ТЕСТ ТЕСТ ТЕСТ ТЕСТ ТЕСТ ТЕСТ ТЕСТ ТЕСТ ТЕСТ ТЕСТ ТЕСТ ТЕСТ 
<!-- Тип кодирования данных, enctype, ДОЛЖЕН БЫТЬ указан ИМЕННО так -->
<form enctype="multipart/form-data" action="#" method="POST">
    <!-- Поле MAX_FILE_SIZE должно быть указано до поля загрузки файла -->

    <!-- Название элемента input определяет имя в массиве $_FILES -->
    Отправить этот файл: <input name="datafile" type="file" />
    <input type="submit" value="Send File" />
</form>
<?

require_once('paserXML.php');

//var_dump($TOVAR_XML); 
//if (empty($wp)) {require_once( '../wp-load.php' );wp( array( 'tb' => '1' ) );}

//Перебор товаров 
foreach ($TOVAR_XML as $ID_main => $VALUE_main){

//ОСТАТОК 
$sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = %s WHERE 
meta_key = '_stock'  AND post_id = %d", $VALUE_main['STOK'], $ID_main );
$wpdb->query($sql);
//Наличие
$stock_status = ($VALUE_main['STOK']==0) ? "outofstock" : "instock";
$sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = %s WHERE 
meta_key = '_stock_status'  AND post_id = %s", $stock_status, $ID_main );
$wpdb->query($sql);

// Проверки  на больше меньше и приведение форматов
$var_price_reg_main = number_format($VALUE_main['PRICE'], 2, '.', '');
$var_price_sale_main = ($VALUE_main['PRICE_SALE']<$VALUE_main['PRICE'])? number_format($VALUE_main['PRICE_SALE'], 2, '.', '') : NULL;
$var_price_main = ($var_price_sale_main) ? number_format($VALUE_main['PRICE_SALE'], 2, '.', '') : number_format($VALUE_main['PRICE'], 2, '.', '');

//ОСНОВНАЯ стоимость 
$wpdb->show_errors();
$sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = %s WHERE 
meta_key = '_regular_price' AND post_id = %d", $var_price_reg_main, $ID_main );
$wpdb->query($sql);

$sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = %s WHERE 
 meta_key = '_sale_price' AND post_id = %d", $var_price_sale_main, $ID_main );
$wpdb->query($sql);

$sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = %s WHERE 
meta_key = '_price'  AND post_id = %d", $var_price_main, $ID_main );
$wpdb->query($sql);

//Если Вариаций больше одной
if(count($VALUE_main['VARIACIA'])>0){
//находим значения и ID максимальной и минимальной вариации
$price_variation = array();
$price_variation_sale = array();

foreach ($VALUE_main['VARIACIA'] as $ID_variacia => $VALUE_variation){
$price_variation[$ID_variacia] = $VALUE_variation['PRICE'];
$price_variation_sale[$ID_variacia]  = $VALUE_variation['PRICE_SALE'];
}

foreach ($VALUE_main['VARIACIA'] as $ID_variacia => $VALUE_variation){

if($VALUE_variation['PRICE'] == max($price_variation)){$id_max_variation_reg = $ID_variacia; $max_price_reg = $VALUE_variation['PRICE'];}

if($VALUE_variation['PRICE'] == min($price_variation)){$id_min_variation_reg = $ID_variacia; $min_price_reg = $VALUE_variation['PRICE'];}

if($VALUE_variation['PRICE_SALE'] == max($price_variation_sale)){$id_max_variation_sale = $ID_variacia; $max_price_sale = $VALUE_variation['PRICE_SALE'];}

if($VALUE_variation['PRICE_SALE'] == min($price_variation_sale)){$id_min_variation_sale = $ID_variacia; $min_price_sale = $VALUE_variation['PRICE_SALE']; }
}

$max_price = ($max_price_reg > $max_price_sale) ? $max_price_sale : $max_price_reg ;
$min_price = ($min_price_reg > $min_price_sale) ? $min_price_sale : $min_price_reg ;

$id_max_variation = ($max_price_reg > $max_price_sale) ? $id_max_variation_sale : $id_max_variation_reg ;
$id_min_variation = ($min_price_reg > $min_price_sale) ? $id_min_variation_sale : $id_min_variation_reg ;

//---------------------------------------------------------------------------------------------------------------------------------------
//максимальная стоимость ВАРИАЦИИ
$sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = %s WHERE 
meta_key = '_max_variation_regular_price' AND post_id = %d", $max_price_reg, $ID_main );
$wpdb->query($sql);

//максимальная ID ВАРИАЦИИ
$sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = %s WHERE 
meta_key = '_max_regular_price_variation_id' AND post_id = %d", $id_max_variation_reg, $ID_main );
$wpdb->query($sql);

//минимальная стоимость ВАРИАЦИИ
$sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = %s WHERE 
meta_key = '_min_variation_regular_price' AND post_id = %d", $min_price_reg, $ID_main );
$wpdb->query($sql);

//мминимальная ID ВАРИАЦИИ
$sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = %s WHERE 
meta_key = '_min_regular_price_variation_id'  AND post_id = %d", $id_min_variation_reg, $ID_main );
$wpdb->query($sql);

//----------------------------------------------------------------------------------------------------------------------------------------
//максимальная стоимость ВАРИАЦИИ
 $sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = %s WHERE 
meta_key = '_max_variation_sale_price' AND post_id = %d", $max_price_sale, $ID_main );
$wpdb->query($sql);

//максимальная ID ВАРИАЦИИ
$sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = %s WHERE 
meta_key = '_max_sale_price_variation_id' AND post_id = %d", $id_max_variation_sale, $ID_main );
$wpdb->query($sql);

//минимальная стоимость ВАРИАЦИИ
$sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = %s WHERE 
meta_key = '_min_variation_sale_price' AND post_id = %d", $min_price_sale, $ID_main );
$wpdb->query($sql);

//мминимальная ID ВАРИАЦИИ
$sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = %s WHERE 
meta_key = '_min_sale_price_variation_id'   AND post_id = %d", $id_min_variation_sale, $ID_main );
$wpdb->query($sql);

//-------------------------------------------------------------------------------------------------------------------------------------------- 
//МИН МАКС стоимость распрадажи
$sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = %s WHERE 
meta_key = '_max_variation_price'  AND post_id = %d", $max_price, $ID_main );
$wpdb->query($sql);

//максимальная ID ВАРИАЦИИ
$sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = %s WHERE 
meta_key = '_max_price_variation_id' AND post_id = %d", $id_max_variation, $ID_main );
$wpdb->query($sql);

//минимальная стоимость ВАРИАЦИИ
$sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = %s WHERE 
meta_key = '_min_variation_price'  AND post_id = %d", $min_price, $ID_main );
$wpdb->query($sql);

//мминимальная ID ВАРИАЦИИ
$sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = %s WHERE 
meta_key = '_min_price_variation_id' AND post_id = %d", $id_min_variation, $ID_main );
$wpdb->query($sql);

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
//***************************ВАРИАЦИИ**********************************************************
//***************************ВАРИАЦИИ**********************************************************
//***************************ВАРИАЦИИ**********************************************************
foreach ($VALUE_main['VARIACIA'] as $ID_variacia => $VALUE_variation)
{
//ОСТАТКИ 
$sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = %s WHERE 
meta_key = '_stock'  AND post_id = %d", $VALUE_variation['STOK'], $ID_variacia );
$wpdb->query($sql);

//Наличие
$stock_status = ($VALUE_variation['STOK']==0) ? "outofstock" : "instock";
$sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = %s WHERE 
meta_key = '_stock_status'  AND post_id = %s", $stock_status, $ID_variacia );
$wpdb->query($sql);

// Проверки  на больше меньше и приведение форматов
$var_price_reg = number_format($VALUE_variation['PRICE'], 2, '.', '');
$var_price_sale = ($VALUE_variation['PRICE_SALE']<$VALUE_variation['PRICE']) ? number_format($VALUE_variation['PRICE_SALE'], 2, '.', '') : NULL;
$var_price = ($var_price_sale) ? number_format($VALUE_variation['PRICE_SALE'], 2, '.', '') : number_format($VALUE_variation['PRICE'], 2, '.', '');

//СТОИМОСТЬ  ВАРИАЦИИ
$sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = %s WHERE 
meta_key = '_regular_price' AND post_id = %d", $var_price_reg, $ID_variacia );
$wpdb->query($sql);

$sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = %s WHERE 
meta_key = '_sale_price' AND post_id = %d", $var_price_sale, $ID_variacia );
$wpdb->query($sql);

$sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = %s WHERE 
meta_key = '_price'  AND post_id = %d", $var_price, $ID_variacia);
$wpdb->query($sql);
}

}
 
}
?>
<a class="close" data-dismiss="alert" href="#">Ок!</a>
        <footer></footer>
		</div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
		
    </body>
</html>