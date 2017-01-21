#!/usr/bin/env php
<?php

echo 'type Consumer Key(API Key): ';
$api_key = fscanf(STDIN, '%s')[0];

echo 'type Consumer Secret (API Secret): ';
$api_secret = fscanf(STDIN, '%s')[0];

$endpoint = 'https://api.twitter.com/oauth2/token';

$credential = base64_encode($api_key.':'.$api_secret);

$context = [
    'http' => [
        'method' => 'POST',
        'header' => [
            'Authorization: Basic '.$credential,
            'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
        ],
        'content' => http_build_query(['grant_type' => 'client_credentials']),
    ],
];

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $endpoint);
curl_setopt($curl, CURLOPT_HEADER, 1);
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $context['http']['method']);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, $context['http']['header']);
curl_setopt($curl, CURLOPT_POSTFIELDS, $context['http']['content']);
curl_setopt($curl, CURLOPT_TIMEOUT, 5);
$result = curl_exec($curl);
$info = curl_getinfo($curl);
$curl_error = curl_error($curl);
curl_close($curl);

if ($curl_error) {
    echo "Faild. curl error.\n\n";
    echo $curl_error;
    exit(1);
}

$body = substr($result, $info['header_size']);
$decoded_json = json_decode($body, true);

if ($info['http_code'] == 500) {
    echo "Faild. 500 error occurred\n\n";
    print_r([$info, $decoded_json]);
    exit(1);
}

if ($info['http_code'] == 404) {
    echo "Faild. 404 not founnd.\n";
    echo "endpoint in this code may have been discontinued.\n\n";

    echo 'called this: '.$endpoint;
    exit(1);
}

if (isset($decoded_json['errors'])) {
    echo "Faild.\n";
    echo "api response has any errors.\n\n";
    print_r($decoded_json['errors']);
    exit(1);
}

if ($decoded_json['token_type'] !== 'bearer') {
    echo "Faild.\n";
    echo 'it got token_type is not bearer.';
    exit(1);
}

echo "Success.\n";
echo "Followng your bearer token.\n\n";

echo $decoded_json['access_token'];
