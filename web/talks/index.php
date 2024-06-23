<?php
include('../php/db.php');
$link = connectDB();
?>
<html>
<head>
    <title>InCallytics</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/css/style.css" media="all">
	<link rel="stylesheet" href="/css/users.css" media="all">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="Description" content="USEcure">
    <meta http-equiv="Content-language" content="ru-RU">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;900&family=Roboto&display=swap" rel="stylesheet">
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
        <div class="titleFlex">
            <div class="title">Диалоги</div>
        </div>
        <div class="content">
            <div class="table">
	            <table class="users">
		            <thead>
		            <tr>
			            <th>ID</th>
			            <th>Время</th>
			            <th>Начало диалога</th>
			            <th>Суммаризация диалога</th>
			            <th>Результат</th>
			            <th>Действия</th>
		            </tr>
		            </thead>
		            <tbody>
		            <?php
    $query = mysqli_query($link, "SELECT * FROM `talks` ORDER BY `talks`.`id` DESC");
	while ($u = mysqli_fetch_array($query)) {
		if ($u["score"] == 1) {
			$htmlScore = '<div class="div-stat"><img src="../images/check.svg" alt="галочка" class="img-stat"><span>Успех</span></div>';
		} else if ($u["score"] == 0) {
            $htmlScore = '<div class="div-stat"><img src="../images/x.svg" alt="крестик" class="img-stat"><span>Неудача</span></div>';
		} else {
            $htmlScore = '<div class="div-stat"><img src="../images/question.svg" alt="вопросик" class="img-stat"><span>Неизвестно</span></div>';
		}
		if (isset($u["summary"])) {
			$summ = mb_substr($u["summary"], 0, 200, "utf8");
		} else {
			$summ = "Не запрашивалась";
		}
		echo '
		            <tr>
			            <td>'.$u["id"].'</td>
			            <td>'.$u["time"].'</td>
			            <td>"'.json_decode($u["transcript"], true)["chunks"][0]["text"].'"</td>
			            <td class="cen"><p>'.$summ.'</p></td>
			            <td>'.$htmlScore.'</td>
			            <td>
			                <a href="talk.php?id='.$u['id'].'" class="button-edit">Просмотр</a>
			                <a href="delete.php?id='.$u['id'].'" class="button-delete">Удалить</a>
						</td>
		            </tr>'
		;
    }
		            ?>
		            </tbody>
	            </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>