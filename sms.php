<?php

$url = 'https://www.24bulksmsbd.com/api/DynamicSMSApi';

$data = [
    "customer_id" => 1,
    "api_key" => "178474548628691201883311400",
    "messages" => [
        [
            "to" => "018xxxxxxxx",
            "message" => "Test Dynamic SMS 1"
        ],
        [
            "to" => "017xxxxxxxx",
            "message" => "Test Dynamic SMS 2"
        ]
    ]
];

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json'
    ],
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo curl_error($ch);
}

curl_close($ch);

echo $response;