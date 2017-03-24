<?php
$qwery = "SELECT
  wpcy_posts.ID,
  wpcy_posts.post_date,
  wpcy_posts.post_status,
  wpcy_posts.post_excerpt
FROM wpcy_posts
WHERE wpcy_posts.post_type = 'shop_order'
AND (wpcy_posts.post_status = 'wc-on-hold'
OR wpcy_posts.post_status = 'wc-processing'
OR wpcy_posts.post_status = 'wc-pending')";

//Заказы 
$db = $wpdb->get_results($qwery);

$ZAKAZ = array();

foreach ($db as $zakaz) {
    $id_zakaz = (int)$zakaz->ID;
//тег заказа
    $zakaz_teg = $id_zakaz . "ORDER";

    $ZAKAZ[$zakaz_teg]["ID"] = $id_zakaz;
    $ZAKAZ[$zakaz_teg]["DATE"] = $zakaz->post_date;
    if ($zakaz->post_status == 'wc-pending')
        $ZAKAZ[$zakaz_teg]["STATUS"] = "Ожидает оплаты";

    if ($zakaz->post_status == 'wc-on-hold')
        $ZAKAZ[$zakaz_teg]["STATUS"] = "Самовывоз";

    if ($zakaz->post_status == 'wc-processing')
        $ZAKAZ[$zakaz_teg]["STATUS"] = "Обработка";
    $ZAKAZ[$zakaz_teg]["COMENT"] = $zakaz->post_excerpt;

    $qwery = "SELECT
  wpcy_postmeta_1.post_id,
  wpcy_postmeta_1.meta_value,
  wpcy_postmeta_1.meta_key
FROM wpcy_postmeta wpcy_postmeta_1
WHERE wpcy_postmeta_1.post_id =" . $id_zakaz." ";

    $data = $wpdb->get_results($qwery);
    foreach ($data as $dt) {
        $id_zakaz2 = (int)$dt->post_id;

//тег заказа
        $zakaz_teg2 = $id_zakaz2 . "ORDER";

//БИЛИНГ 
        if ($dt->meta_key == "_billing_first_name")
            $billing_first_name = $dt->meta_value;
        if ($dt->meta_key == "_billing_last_name")
            $billing_last_name = $dt->meta_value;
        $ZAKAZ[$zakaz_teg2]["BILLING"]["FIO"] = $billing_first_name . " " . $billing_last_name;

        if ($dt->meta_key == "_billing_company")
            $ZAKAZ[$zakaz_teg2]["BILLING"]["COMPANY"] = $dt->meta_value;
        if ($dt->meta_key == "_billing_email")
            $ZAKAZ[$zakaz_teg2]["BILLING"]["EMAIL"] = $dt->meta_value;
        if ($dt->meta_key == "_billing_phone")
            $ZAKAZ[$zakaz_teg2]["BILLING"]["PHONE"] = $dt->meta_value;
        if ($dt->meta_key == "_billing_state")
            $billing_state = $dt->meta_value;
        if ($dt->meta_key == "_billing_address_1")
            $billing_address_1 = $dt->meta_value;
        if ($dt->meta_key == "_billing_address_2")
            $billing_address_2 = $dt->meta_value;
        if ($dt->meta_key == "_billing_city")
            $billing_city = $dt->meta_value;
        $ZAKAZ[$zakaz_teg2]["BILLING"]["ADRESS_BILL"] = $billing_state . " " . $billing_city . " " . $billing_address_1 . " " . $billing_address_2;

//Шипинг
        if ($dt->meta_key == "_shipping_first_name")
            $shipping_first_name = $dt->meta_value;
        if ($dt->meta_key == "_shipping_last_name")
            $shipping_last_name = $dt->meta_value;
        $ZAKAZ[$zakaz_teg2]["SHIPPING"]["FIO"] = $shipping_first_name . " " . $shipping_last_name;

        if ($dt->meta_key == "_shipping_company")
            $ZAKAZ[$zakaz_teg2]["SHIPPING"]["COMPANY"] = $dt->meta_value;
        if ($dt->meta_key == "_shipping_email")
            $ZAKAZ[$zakaz_teg2]["SHIPPING"]["EMAIL"] = $dt->meta_value;
        if ($dt->meta_key == "_shipping_phone")
            $ZAKAZ[$zakaz_teg2]["SHIPPING"]["PHONE"] = $dt->meta_value;
        if ($dt->meta_key == "_shipping_state")
            $shipping_state = $dt->meta_value;
        if ($dt->meta_key == "_shipping_address_1")
            $shipping_address_1 = $dt->meta_value;
        if ($dt->meta_key == "_shipping_address_2")
            $shipping_address_2 = $dt->meta_value;
        if ($dt->meta_key == "_shipping_city")
            $shipping_city = $dt->meta_value;
        $ZAKAZ[$zakaz_teg2]["SHIPPING"]["ADRESS"] = $shipping_state . " " . $shipping_city . " " . $shipping_address_1 . " " . $shipping_address_2;
        if ($dt->meta_key == "_shipping_postcode")
            $ZAKAZ[$zakaz_teg2]["SHIPPING"]["POSTCODE"] = $dt->meta_value;

//Склад новой почты
        if ($dt->meta_key == "_nova_pochta")
            $ZAKAZ[$zakaz_teg2]["SHIPPING"]["NOVA_POCHTA"] = $dt->meta_value;

//Метод оплаты 
        if ($dt->meta_key == "_payment_method_title")
            $ZAKAZ[$zakaz_teg2]['PAY']["METHOD_PAY"] = $dt->meta_value;

//Дисконт 
        if ($dt->meta_key == "_cart_discount")
            $ZAKAZ[$zakaz_teg2]["CART_DISCOUNT"] = $dt->meta_value;

//tax
        /* if($dt->meta_key == "_cart_discount_tax")
        $ZAKAZ[$zakaz_teg2]["CART_DISKOUNT_TAX"] = $dt->meta_value;
        if($dt->meta_key == "_order_tax")
        $ZAKAZ[$zakaz_teg2]["ORDER_TAX"] = $dt->meta_value;
        if($dt->meta_key == "_order_shipping_tax")
        $ZAKAZ[$zakaz_teg2]["SHIPPING_TAX"] = $dt->meta_value; */

//if($dt->meta_key == "_order_total")
//$ZAKAZ[$zakaz_teg2]["PRICE_TOTAL"] = $dt->meta_value;
    }
//------------------------------------------------------------------------------------------
    $qwery = "SELECT
  order_itemmeta.meta_key,
  order_itemmeta.meta_value,
  order_items.order_id,
  order_items.order_item_name,
  order_items.order_item_type
FROM wpcy_woocommerce_order_items order_items
  INNER JOIN wpcy_woocommerce_order_itemmeta order_itemmeta
    ON order_items.order_item_id = order_itemmeta.order_item_id
WHERE order_items.order_id =" . $id_zakaz;

    $tovar = $wpdb->get_results($qwery);
	
$ID_v = 0; $ID_p = 0;
foreach ($tovar as $vl) {

        $id_zakaz3 = (int)$vl->order_id;

//тег заказа
        $zakaz_teg3 = $id_zakaz3 . "ORDER";

        $name_tovar = $vl->order_item_name;
        $name_atrib = md5($name_tovar) . "TOVAR";

        if (($vl->order_item_type == 'shipping') && ($vl->meta_key == "cost")) {
            $shipping_cost = $vl->meta_value;
            $shipping_name = $vl->order_item_name;
        }

        if ($vl->order_item_type == 'line_item') {

            $TOVAR[$name_atrib]["NAME"] = $name_tovar;

            if ($vl->meta_key == "_qty")
                $TOVAR[$name_atrib]["QTY"] = $vl->meta_value;

            if ($vl->meta_key == "_product_id")
                $ID[$name_atrib]['product_id'] = (int)$vl->meta_value;
				
            if ($vl->meta_key == "_variation_id")
                $ID[$name_atrib]['variation'] = (int)$vl->meta_value;
		
        }

    }
	
	// Перебераем id продуктов и вариаций...
	foreach($ID as $key => $id_value){

$id_tovara =(int)($id_value["variation"]) ? $id_value["variation"] : $id_value["product_id"];

$qwery = "SELECT
  wpcy_postmeta_1.meta_key,
  wpcy_postmeta_1.meta_value
FROM wpcy_postmeta wpcy_postmeta_1
WHERE wpcy_postmeta_1.post_id =" . $id_tovara;

            $SV = $wpdb->get_results($qwery);
            foreach ($SV as $value) {

                if ($value->meta_key == "_sku")
                    $TOVAR[$key]["SKU"] = $value->meta_value;

                if ($value->meta_key == "_price")
                    $TOVAR[$key]["PRICE"] = $value->meta_value;
            }
	
	}
	
    $total_price = 0;
    foreach ($TOVAR as $tovar)
        $total_price += $tovar["PRICE"] * $tovar["QTY"];
    $ZAKAZ[$zakaz_teg3]['PAY']["PRICE_TOTAL"] = $total_price;

    $ZAKAZ[$zakaz_teg3]['PAY']["SHPPING_COST"] = $shipping_cost;
    $ZAKAZ[$zakaz_teg3]["SHIPPING"]['NAME_SHIPPING'] = $shipping_name;

    $ZAKAZ[$zakaz_teg3]['TOVARS'] = $TOVAR;
}