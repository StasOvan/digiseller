<?php
    
	$_API_KEY = '';
    $_SELLER_ID = '1092511';

    $uniquecode = $_GET['uniquecode'];

	// получаем токен
    $timestamp = time();
    $hashdata = ($_API_KEY . $timestamp);
    $sign = hash("sha256", $hashdata);

    $post_data = [
        "seller_id" => $_SELLER_ID,
        "timestamp" => $timestamp,
        "sign" => $sign
    ];
    $data_json = json_encode($post_data); 

    $opts = [
        'http' => [
            'method' => 'POST',
            'header' => ['Content-type: application/json', 'Accept: application/json'],
            'content' => $data_json
        ]
    ];
    $context = stream_context_create($opts);
    $result = file_get_contents("https://api.digiseller.ru/api/apilogin", false, $context);

    //if ($result) echo $result; else echo "false";

    $token = json_decode($result)->token; // токен готов

    // запрашиваем наличие $uniquecode
    $result = file_get_contents("https://api.digiseller.ru/api/purchases/unique-code/{$uniquecode}?token={$token}");
    $invoice_id = json_decode($result)->inv; // номер заказа
    $invoice_date = json_decode($result)->date_pay; // дата заказа
    $currency = json_decode($result)->type_curr;

    if ($currency == "WMZ") $currency = "USD";
    if ($currency == "WMR") $currency = "₽";
    if ($currency == "WME") $currency = "EURO";
    if ($currency == "WMX") $currency = "BTC";

    // получаем инфу
    $result = file_get_contents("https://api.digiseller.ru/api/purchase/info/{$invoice_id}?token={$token}");

    $json = json_decode($result);
    $purchase_date = $json->content->purchase_date; 
    
    $options = $json->content->options;
    $options = (array)$options[0];
    $user = $options["user_data"];

    $amount = $json->content->amount;
    $invoice_state = $json->content->invoice_state;
