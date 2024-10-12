<?php
    
    define(API_KEY, '');
    define(SELLER_ID, '1092511');

    $uniquecode = $_GET['uniquecode'];

// ### получаем токен
    $timestamp = time();
    $hashdata = (API_KEY . $timestamp);
    $sign = hash("sha256", $hashdata); // получаем хеш подписи

    $post_data = [
        "seller_id" => SELLER_ID,
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

    $token = json_decode($result)->token; 
// ### токен готов


// ### запрашиваем uniquecode
    $result = file_get_contents("https://api.digiseller.ru/api/purchases/unique-code/{$uniquecode}?token={$token}");

    $json = json_decode($result);

    $invoice_id = $json->inv; // номер заказа
    $invoice_date = $json->date_pay; // дата заказа
    $currency = $json->type_curr; // валюта
    switch ($currency) {
        case "WMZ": $currency = "USD"; break;
        case "WMR": $currency = "₽"; break;
        case "WME": $currency = "EURO"; break;
        case "WMX": $currency = "BTC"; break;
    }

// ### получаем инфу
    $result = file_get_contents("https://api.digiseller.ru/api/purchase/info/{$invoice_id}?token={$token}");

    $json = json_decode($result);
    
    $options = $json->content->options;
    $options = (array)$options[0];
    $user = $options["user_data"];

    $amount = $json->content->amount;
    $invoice_state = $json->content->invoice_state;
    $purchase_date = $json->content->purchase_date;
