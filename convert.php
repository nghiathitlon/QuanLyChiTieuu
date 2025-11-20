<?php
require 'functions.php';
header('Content-Type: application/json');

$amount = isset($_GET['amount']) ? (float)$_GET['amount'] : 0;
$direction = isset($_GET['direction']) ? $_GET['direction'] : 'vnd_to_usd'; // mặc định VND → USD

if($amount <= 0){
    echo json_encode(['ok'=>false,'error'=>'Số tiền không hợp lệ']);
    exit;
}

$usd_rate = get_usd_rate();

if($direction === 'vnd_to_usd'){
    $result = round($amount / $usd_rate, 2);
    $vnd_display = number_format($amount, 0, ',', '.');
    $usd_display = number_format($result, 2, '.', ',');
    echo json_encode([
        'ok' => true,
        'from' => $vnd_display . ' VND',
        'to' => $usd_display . ' USD'
    ]);
} elseif($direction === 'usd_to_vnd'){
    $result = round($amount * $usd_rate, 0);
    $usd_display = number_format($amount, 2, '.', ',');
    $vnd_display = number_format($result, 0, ',', '.');
    echo json_encode([
        'ok' => true,
        'from' => $usd_display . ' USD',
        'to' => $vnd_display . ' VND'
    ]);
} else {
    echo json_encode(['ok'=>false,'error'=>'Hướng chuyển đổi không hợp lệ']);
}
