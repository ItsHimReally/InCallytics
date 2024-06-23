<?php
include "keys.php";

function checkOperationsGPT($id) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://llm.api.cloud.yandex.net/operations/' . $id);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer '.IAM,
        'x-folder-id: '.FOLDER,
        'Content-Type: application/json'
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Ошибка cURL: ' . curl_error($ch);
    }
    curl_close($ch);
    $json = json_decode($response, true);
    if (!$json["done"]) {
        return null;
    }
    return $json["response"]["alternatives"][0]["message"]["text"];
}

function newOperationGPT($prompt) {
    $folder = FOLDER;
    $data = json_encode([
        "modelUri" => "gpt://${folder}/yandexgpt/latest",
        "completionOptions" => [
            "stream" => false,
            "temperature" => 0.3,
            "maxTokens" => "3000"
        ],
        "messages" => $prompt
    ]);
//    [
//        [
//            "role" => "system",
//            "text" => "Ты — умный аналитик экономической ситуации в компаниях. Попробуй обработать поданную статистику о компании, чтобы кратко и емко рассказать о прогнозах, и проблемах бизнеса. Не бойся выражать скепсис."
//        ],
//        [
//            "role" => "user",
//            "text" => $prompt
//        ]
//    ]
    $url = 'https://llm.api.cloud.yandex.net/foundationModels/v1/completionAsync';
    $headers = [
        'Authorization: Bearer '.IAM,
        'x-folder-id: '.FOLDER,
        'Content-Type: application/json'
    ];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        $json = json_decode($response, true);
        if (isset($json['id'])) {
            return $json['id'];
        }
    }
    return 0;
}