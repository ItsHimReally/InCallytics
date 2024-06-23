<?php
session_start();
header('Content-Type: application/json');
include "../php/gpt.php";
if (!isset($_SESSION['liveScript'])) {
    $_SESSION['liveScript'] = [];
}
if (!isset($_SESSION['liveAIRequests'])) {
    $_SESSION['liveAIRequests'] = [];
}
if (!isset($_SESSION["prompt"])) {
    $_SESSION["prompt"] = [
        [
            "role" => "system",
            "text" => 'Твоя задача оценивать и помогать спикеру 0 (оператору колл-центра) достичь цели звонка со спикером 1 (клиентом). В следующих сообщениях будут указаны по 5 сообщений диалога. Твоя задача прокомментировать, посоветовать дальнейшие действия оператору, НО НЕ ОТВЕЧАТЬ ЗА НЕГО, например, помочь поработать с отказом клиента или посоветовать рассказать ему о других сторонах медали. Ты всегда на стороне оператора. Если тебе нечего добавить, или твой комментарий будет неуместен конкретно в этом моменте, то напиши только "Без комментариев".'
        ]
    ];
}
if (!isset($_SESSION["busyNumber"])) {
    $_SESSION["busyNumber"] = [];
}
function sendRequestToYaGPT() {
    $prompt = "";
    $scriptLength = count($_SESSION['liveScript']);
    $start = max(0, $scriptLength - 5);
    for ($i = $start; $i < $scriptLength; $i++) {
        $role = $_SESSION['liveScript'][$i]['speaker'];
        $text = $_SESSION['liveScript'][$i]['text'];
        $prompt .= "$role: $text ";
    }
    $sessionPrompt = [
        "role" => "user",
        "text" => $prompt
    ];
    foreach ($_SESSION["prompt"] as $val) {
        if ($val[0]["text"] == $prompt) {
            return null;
        }
    }
    $_SESSION["prompt"][] = $sessionPrompt;
    $requestId = newOperationGPT($_SESSION["prompt"]);
    return $requestId;
}
function checkRequestStatus($requestId) {
    $result = checkOperationsGPT($requestId);
    if (isset($result)) {
        if ($result != "Без комментариев.") {
            $_SESSION['liveScript'][] = [
                'speaker' => 'AI',
                'text' => $result,
                'timestamp' => time()
            ];
        }
        $_SESSION["prompt"][] = [
            "role" => "assistant",
            "text" => $result
        ];
        foreach ($_SESSION["liveAIRequests"] as $key => $val) {
            if ($val["id"] == $requestId) {
                unset($_SESSION['liveAIRequests'][$key]);
            }
        }
    }
}

function countUserOperatorMessages($liveScript) {
    $count = 0;
    foreach ($liveScript as $message) {
        if ($message['speaker'] == '0' || $message['speaker'] == '1') {
            $count++;
        }
    }
    return $count;
}
$n = countUserOperatorMessages($_SESSION['liveScript']);
if ($n % 5 == 0 && $n > 0 && !in_array($n, $_SESSION["busyNumber"])) {
    $_SESSION["busyNumber"][] = $n;
    $requestId = sendRequestToYaGPT();
    if (!is_null($requestId)) {
        $_SESSION['liveAIRequests'][] = [
            'id' => $requestId,
            'timestamp' => time()
        ];
    }
}
foreach ($_SESSION['liveAIRequests'] as $key => $request) {
    checkRequestStatus($request["id"]);
}
echo json_encode(['chunks' => $_SESSION['liveScript']]);