<?php
session_start();
include "../php/db.php";
$link = connectDB();
header('Content-Type: application/json');

if (!isset($_SESSION['liveScript']) || empty($_SESSION['liveScript'])) {
    echo json_encode(['error' => 'No messages to save']);
    exit;
}

$chunks = [];
$diarization = [];
$text = '';

$firstTimestamp = $_SESSION['liveScript'][0]['timestamp'];
for ($i = 0; $i < count($_SESSION['liveScript']); $i++) {
    $message = $_SESSION['liveScript'][$i];
    $startTimestamp = $message['timestamp'] - $firstTimestamp; // Относительное время

    // Вычисление конца сообщения
    if ($i < count($_SESSION['liveScript']) - 1) {
        $nextMessage = $_SESSION['liveScript'][$i + 1];
        $endTimestamp = $nextMessage['timestamp'] - $firstTimestamp - 1;
    } else {
        $endTimestamp = $startTimestamp + 2; // Для последнего сообщения фиксированная длительность 2 секунды
    }

    $chunks[] = [
        'text' => $message['text'],
        'timestamp' => [$startTimestamp, $endTimestamp]
    ];
    $diarization[] = [
        'SPEAKER_' . ($message['speaker'] == 'AI' ? 'AI' : str_pad($message['speaker'], 2, '0', STR_PAD_LEFT)),
        $startTimestamp,
        $endTimestamp
    ];
    $text .= $message['text'] . ' ';
}

$jsonData = json_encode([
    'chunks' => $chunks,
    'diarization' => $diarization,
    'text' => trim($text)
]);

$stmt = mysqli_prepare($link, "INSERT INTO `talks` (`nameFile`, `transcript`, `isLive`, `aiHelper`, `summary`, `score`) VALUES (NULL, ?, '1', 'used', NULL, -1);");
mysqli_stmt_bind_param($stmt, 's', $jsonData);
if (mysqli_stmt_execute($stmt)) {
    $conversationId = mysqli_insert_id($link);
} else {
    echo json_encode(['error' => 'Failed to save data: ' . mysqli_error($link)]);
    exit();
}
mysqli_stmt_close($stmt);

session_unset();
session_destroy();

echo json_encode(['id' => $conversationId]);
?>
