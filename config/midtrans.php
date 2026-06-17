<?php
define('MIDTRANS_SERVER_KEY', 'SB-Mid-server-IYftE8VPjamqFzmU9O7oL2L5');
define('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-dbYYHDftx7NAczbb');
define('MIDTRANS_IS_PRODUCTION', false);

function midtrans_get_snap_token($order_id, $gross_amount, $items, $customer, $finish_url = '') {
    $is_sandbox = !MIDTRANS_IS_PRODUCTION;
    $api_url = $is_sandbox
        ? 'https://app.sandbox.midtrans.com/snap/v1/transactions'
        : 'https://app.midtrans.com/snap/v1/transactions';

    $server_key = MIDTRANS_SERVER_KEY;
    $order_id_str = 'MBM-' . $order_id;

    $params = [
        'transaction_details' => [
            'order_id' => $order_id_str,
            'gross_amount' => (int) $gross_amount,
        ],
        'item_details' => $items,
        'customer_details' => $customer,
    ];

    if ($finish_url) {
        $params['callbacks'] = ['finish' => $finish_url];
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Basic ' . base64_encode($server_key . ':'),
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($http_code !== 201) {
        error_log("Midtrans API error (HTTP $http_code): " . ($response ?: $error));
        return null;
    }

    $result = json_decode($response, true);
    return $result;
}
