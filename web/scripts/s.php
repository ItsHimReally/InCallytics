<?php
include('../php/db.php');
$link = connectDB();

$stmt = mysqli_prepare($link, "SELECT * FROM `scripts` WHERE `id` = ?");
mysqli_stmt_bind_param($stmt, "s", $_GET["id"]);
mysqli_stmt_execute($stmt);
$script = mysqli_fetch_array(mysqli_stmt_get_result($stmt));
?>
<html>
<head>
    <title>InCallytics</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/css/style.css" media="all">
    <link rel="stylesheet" href="/css/users.css" media="all">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="Description" content="InCallytics">
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
            <div class="title">Просмотр скрипта № <?=$script["id"]?></div>
        </div>
        <div class="content">
            <pre><?=$script["script"]?></pre>
        </div>
    </div>
</div>
</body>
</html>