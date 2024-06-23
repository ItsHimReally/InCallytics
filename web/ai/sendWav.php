<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

include "../php/keys.php";

$token = IAM;
$folderId = FOLDER;

if (!isset($_FILES['voice']) || !isset($_GET['speaker'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$speaker = (int)$_GET['speaker'];

// Определяем директорию для загрузки и формируем уникальное имя файла
$uploadDir = '../voice/';
$typeFile = explode('/', $_FILES['voice']['type']);
$wavFile = $uploadDir . basename(md5($_FILES['voice']['tmp_name'] . time()) . '.wav');
$oggFile = $uploadDir . basename(md5($_FILES['voice']['tmp_name'] . time()) . '.ogg');

// Перемещаем загруженный файл в директорию
if (!move_uploaded_file($_FILES['voice']['tmp_name'], $wavFile)) {
    http_response_code(500);
    echo json_encode(['result' => 'ERROR', 'data' => '']);
    exit;
}

// Конвертируем WAV файл в OGG
exec("ffmpeg -i $wavFile -c:a libopus -b:a 65536 $oggFile", $output, $return_var);
if ($return_var !== 0) {
    http_response_code(500);
    echo json_encode(['result' => 'ERROR', 'data' => 'Failed to convert audio file']);
    exit;
}

// Открываем OGG файл для отправки на Yandex Speech-to-Text API
$file = fopen($oggFile, 'rb');
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://stt.api.cloud.yandex.net/speech/v1/stt:recognize?lang=ru-RU&folderId=${folderId}&format=oggopus");
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $token));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
curl_setopt($ch, CURLOPT_INFILE, $file);
curl_setopt($ch, CURLOPT_INFILESIZE, filesize($oggFile));
$res = curl_exec($ch);
curl_close($ch);
fclose($file);
// Обработка ответа от Yandex API
$decodedResponse = json_decode($res, true);
if (isset($decodedResponse["result"])) {
    $message = $decodedResponse["result"];
    if (!isset($_SESSION['liveScript'])) {
        $_SESSION['liveScript'] = [];
    }
    if ($message != "") {
        $timestamp = time();
        $_SESSION['liveScript'][] = [
            'speaker' => $speaker,
            'text' => $message,
            'timestamp' => $timestamp
        ];
    }
    echo json_encode(['result' => 'OK', 'data' => $message]);
} else {
    http_response_code(500);
    echo json_encode([
        'result' => 'ERROR',
        'error_code' => $decodedResponse["error_code"] ?? 'unknown_error',
        'error_message' => $decodedResponse["error_message"] ?? 'Unknown error occurred'
    ]);
}

// Удаляем временные файлы
//unlink($wavFile);
//unlink($oggFile);
?>