<?php
if (!isset($_GET["id"])) {
    header("location: /talks");
    exit();
}

include('../php/db.php');
include('../php/gpt.php');
$link = connectDB();

$stmt = mysqli_prepare($link, "SELECT * FROM `talks` WHERE `id` = ?");
mysqli_stmt_bind_param($stmt, "s", $_GET["id"]);
mysqli_stmt_execute($stmt);
$talk = mysqli_fetch_array(mysqli_stmt_get_result($stmt));
$data = json_decode($talk["transcript"], true);
$talkText = $data["text"];
$stmt = mysqli_prepare($link, "SELECT * FROM `scripts` WHERE `id` = ?");
mysqli_stmt_bind_param($stmt, "s", $_GET["script"]);
mysqli_stmt_execute($stmt);
$script = mysqli_fetch_array(mysqli_stmt_get_result($stmt));

$prompt = [
    [
        "role" => "system",
        "text" => "Ты — эксперт колл-центра и работаешь с звонками других операторов, направляя тех при отклонении от скрипта. Скрипт оператора начинается со слова 'SCRIPT: '. Записанный звонок начинается с 'RECORD: '. Проанализируй скрипт оператора и определи цель скрипта. Поправь оператора и укажи где конкретно он ошибся (в какой строчке): если оператор отклонился от скрипта; если цель скрипта ОТЛИЧАЕТСЯ от цели звонка. Первой строчкой должно быть True (НЕ ВЫДЕЛЯЙ ее звездочками), если все хорошо, и звонок соответствует скрипту, или False, если что-то пошло не так. НЕ БОЙСЯ ПРОЯВЛЯТЬ СКЕПСИС, одно несовпадение цели или неточность - сразу ставь False. Цель звонка должна ТОЧНО совпадать с целью скрипта. Никакие технические проблемы в этих звонках не решаются и НЕ ОБСУЖДАЮТСЯ. Ставь True чаще чем False. Если что-то пошло не так, то укажи конкретно, какая строчка записанного диалога противоречит строчке скрипта."
    ],
    [
        "role" => "user",
        "text" => "SCRIPT: ".$script["script"]." // RECORD: ".$talkText
    ]
];

$operID = newOperationGPT($prompt);
?>
<html>
<head>
    <title>InCallytics</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/css/style.css" media="all">
    <link rel="stylesheet" href="/css/dia.css" media="all">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="Description" content="USEcure">
    <meta http-equiv="Content-language" content="ru-RU">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;900&family=Roboto&display=swap" rel="stylesheet">
	<script>
        document.addEventListener("DOMContentLoaded", function() {
            const completionId = '<?=$operID?>';
            function fetchSummary() {
                fetch(`https://psai.tw1.su/php/compare.php?id=${completionId}`)
                    .then(function(response) {
                        return response.text();
                    })
                    .then(function(text) {
                        if (text === 'false') {
                            setTimeout(fetchSummary, 5000);
                        } else {
                            document.getElementById('text').innerHTML = text;
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
</div>
<div class="page">
    <div class="content">
        <div class="blockinfo">
	        <div class="info-title">Ответ от платформы по результатам сравнения скрипта и диалога:</div>
	        <div class="info-value" id="compare" style="margin-top: 20px;">
		        <img src="../images/svg.svg" alt="Загрузка..." id="loading">
		        <span id="text"></span>
	        </div>
        </div>
	    <a href="talk.php?id=<?=$_GET["id"]?>" style="font-family: 'Inter', sans-serif; font-size: 16px;">Вернуться назад</a>
    </div>
</div>
</body>
</html>
