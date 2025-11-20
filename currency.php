<?php

function get_usd_rate() {
    $url = "https://open.er-api.com/v6/latest/VND";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) return 25000;

    $data = json_decode($response, true);

    if (!isset($data["result"]) || $data["result"] != "success") {
        return 25000;
    }

    // API trả về: 1 VND = X USD
    // Ta cần: 1 USD = 1 / X VND
    $vnd_to_usd = $data["rates"]["USD"];
    $usd_to_vnd = 1 / $vnd_to_usd;

    return $usd_to_vnd;
}

function vnd_to_usd($vnd) {
    $rate = get_usd_rate();
    return $vnd / $rate;
}

function format_vnd_with_usd($vnd) {
    $usd = vnd_to_usd($vnd);
    return number_format($vnd) . " VND (≈ " . number_format($usd, 2) . " USD)";
}
