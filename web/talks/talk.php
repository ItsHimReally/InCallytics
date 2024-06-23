<?php
include('../php/db.php');
$link = connectDB();

$stmt = mysqli_prepare($link, "SELECT * FROM `talks` WHERE `id` = ?");
mysqli_stmt_bind_param($stmt, "s", $_GET["id"]);
mysqli_stmt_execute($stmt);
$talk = mysqli_fetch_array(mysqli_stmt_get_result($stmt));

$data = json_decode($talk["transcript"], true);
if ($_GET['action'] == 'summary') {
    $api_url = 'http://80.90.184.116:5000/send_completion';
    $prompt_text = $data["text"];
    $ch = curl_init($api_url);
    $data = json_encode(['prompt' => $prompt_text]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $response = curl_exec($ch);
    curl_close($ch);
    $response_data = json_decode($response, true);
    $completion_id = $response_data['completion_id'];
}
?>
<html>
<head>
    <title>InCallytics</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/css/style.css" media="all">
    <link rel="stylesheet" href="/css/users.css" media="all">
	<link rel="stylesheet" href="/css/dia.css" media="all">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="Description" content="USEcure">
    <meta http-equiv="Content-language" content="ru-RU">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;900&family=Roboto&display=swap" rel="stylesheet">
	<?php if ($_GET['action'] == 'summary'): ?>
	<script>
        document.addEventListener("DOMContentLoaded", function() {
            const completionId = '<?=$completion_id?>';
            function fetchSummary() {
                fetch(`https://psai.tw1.su/php/summary.php?id=${completionId}&talk=<?=$_GET["id"]?>`)
                    .then(function(response) {
                        return response.text();
                    })
                    .then(function(text) {
                        if (text === 'false') {
                            setTimeout(fetchSummary, 5000);
                        } else {
                            document.getElementById('text').innerText = text;
                            document.getElementById('loading').style.display = 'none';
                        }
                    })
                    .catch(function(error) {
                        console.error('Error:', error);
                        document.getElementById('text').innerText = "Ошибка при генерации.";
                        document.getElementById('loading').style.display = 'none';
                    });
            }
            setTimeout(fetchSummary, 2000);
        });
	</script>
	<?php endif; ?>
</head>
<body>
<div class="wrapper">
    <div class="sideBar">
        <div class="menu">
	        <a href="/"><img class="icon" src="/images/speedometer.svg" alt="Главная"></a>
	        <a href="/talks"><img class="icon" src="/images/telephone-inbound-fill.svg" alt="Стенограммы"></a>
	        <a href="/scripts"><img class="icon" src="/images/file-earmark-text-fill.svg" alt="Скрипты"></a>
	        <a href="/ai"><img class="icon" src="/images/stars.svg" alt="AI-помощник"></a>
        </div>
    </div>
    <div class="mheader">
	    <a href="/"><img class="icon" src="/images/speedometer.svg" alt="Главная"></a>
	    <a href="/talks"><img class="icon" src="/images/telephone-inbound-fill.svg" alt="Стенограммы"></a>
	    <a href="/scripts"><img class="icon" src="/images/file-earmark-text-fill.svg" alt="Скрипты"></a>
	    <a href="/ai"><img class="icon" src="/images/stars.svg" alt="AI-помощник"></a>
    </div>
    <div class="page">
        <div class="titleFlex title">Диалог № <?=$talk["id"]?></div>
        <div class="content">
            <?php
            $data = json_decode($talk["transcript"], true);
            $chunks = $data['chunks'];
            $diarization = $data['diarization'];

            // Функция для округления времени
            function roundTimestamp($timestamp) {
                return round($timestamp, 2);
            }

            // Округляем все таймкоды в diarization
            foreach ($diarization as &$entry) {
                $entry[1] = roundTimestamp($entry[1]);
                $entry[2] = roundTimestamp($entry[2]);
            }
            unset($entry);

            // Функция для определения спикера по таймстампу
            function getSpeaker($start, $end, $diarization) {
                foreach ($diarization as $entry) {
                    if ($entry[1] <= $end && $entry[2] >= $start) {
                        return $entry[0];
                    }
                }
                return 'SPEAKER_01'; // Спикер по умолчанию
            }

            // Функция для преобразования таймстампа в формат минута:секунда
            function formatTimestamp($timestamp) {
                $minutes = floor($timestamp / 60);
                $seconds = $timestamp % 60;
                return sprintf('%02d:%02d', $minutes, $seconds);
            }

            // Функция для преобразования времени в формате часы:минуты:секунды
            function formatDuration($seconds) {
                $hours = floor($seconds / 3600);
                $minutes = floor(($seconds % 3600) / 60);
                $seconds = $seconds % 60;
                return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
            }

            // Вычисляем продолжительность диалога
            $firstTimestamp = $chunks[0]['timestamp'][0];
            $lastTimestamp = end($chunks)['timestamp'][1];
            $durationSeconds = $lastTimestamp - $firstTimestamp;
            $duration = formatDuration($durationSeconds);

            // Начинаем формировать HTML
            $html = '<div class="script">';

            foreach ($chunks as $chunk) {
                $text = trim($chunk['text']);
                $start = roundTimestamp($chunk['timestamp'][0]);
                $end = roundTimestamp($chunk['timestamp'][1]);

                $speaker = getSpeaker($start, $end, $diarization);
				if ($speaker == "SPEAKER_AI") {
                    $speakerClass = "sAI"; $speakerName = "AI";
				} else {
                    $speakerClass = $speaker == 'SPEAKER_00' ? 's0' : 's1';
                    $speakerName = $speaker == 'SPEAKER_00' ? '0' : '1';
                }

                $html .= '<div class="message ' . $speakerClass . '">';
                $html .= '<span class="avatar">' . $speakerName . '</span>';
                $html .= '<span class="text">' . $text . '</span>';
                $html .= '<span class="timestamp">' . formatTimestamp($start) . ' - ' . formatTimestamp($end) . '</span>';
                $html .= '</div>';
            }

            $html .= '</div>';

            // Выводим HTML
            echo $html;
            ?>
	        <div class="info">
				<div class="blockinfo">
					<span class="info-title">Длительность</span>
					<span class="info-value"><?=$duration?></span>
				</div>
		        <div class="blockinfo">
			        <span class="info-title">Время звонка</span>
			        <span class="info-value"><?=$talk["time"]?></span>
		        </div>
		        <?php
                if ($talk["score"] == 1) {
                    $htmlScore = '<div class="div-stat"><img src="../images/check.svg" alt="галочка" class="img-stat"><span>Успех</span></div>';
                } else if ($talk["score"] == 0) {
                    $htmlScore = '<div class="div-stat"><img src="../images/x.svg" alt="крестик" class="img-stat"><span>Неудача</span></div>';
                } else {
                    $htmlScore = '<div class="div-stat"><img src="../images/question.svg" alt="вопросик" class="img-stat"><span>Неизвестно</span></div>';
                }
		        ?>
		        <div class="blockinfo">
			        <span class="info-title">Результат звонка</span>
			        <span class="info-value"><?=$htmlScore?></span>
		        </div>
		        <?php
		        if ($_GET["action"] != "summary") {
                    if (!isset($talk["summary"])) {
                        $talk["summary"] = '<a href="talk.php?id=' . $talk["id"] . '&action=summary">Запросить</a>';
                    }
                } else {
                    $talk["summary"] = '<img src="../images/svg.svg" alt="Загрузка..." id="loading">
				<span id="text"></span>';
		        }
		        ?>
		        <div class="blockinfo">
			        <span class="info-title">Подведение итогов звонка</span>
			        <div class="info-value" id="summary"><?=$talk["summary"]?></div>
		        </div>
                <?php
                $q = mysqli_query($link, "SELECT * FROM scripts");
                ?>
		        <div class="blockinfo">
			        <span class="info-title">Сравнение со скриптом</span>
			        <form method="get" action="compare.php" id="compareForm">
				        <input type="hidden" name="id" value="<?=$talk["id"]?>">
				        <select name="script">
					        <?php while ($s = mysqli_fetch_array($q)): ?>
					        <option value="<?=$s["id"]?>"><?=$s["id"]?> - <?=$s["description"]?></option>
							<?php endwhile; ?>
				        </select>
				        <button type="submit">Сравнить</button>
			        </form>
		        </div>
	        </div>
        </div>
    </div>
</div>
</body>
</html>