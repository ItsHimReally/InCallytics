<?php
    include('php/db.php');
    $link = connectDB();
?>

<html>
    <head>
        <title>InCallytics</title>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="css/style.css" media="all">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="Description" content="InCallytics">
        <meta http-equiv="Content-language" content="ru-RU">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;900&family=Roboto&display=swap" rel="stylesheet">
    </head>
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
        <div class="page pg-center">
	        <div class="titleFlex">
                <div class="title large">InCallytics</div>
		        <div class="stitle">Оцените качество ваших звонков</div>
	        </div>
            <div class="pc-content">
	            <form id="uploadForm" method="POST" enctype="multipart/form-data" action="handler.php">
		            <label class="input-file">
			            <input type="file" name="file" id="fileInput" accept=".zip,.wav" required>
			            <span>Выберите файл</span>
		            </label>
		            <a class="shadow" href="https://psai.tw1.su/talks/random.php"><img src="images/shuffle.svg" alt="Рандом"></a>
	            </form>
	            <span class="comment">Может занять некоторое время.<br>Создано для демонстрации. Более точный результат при запуске скриптов локально.</span>
            </div>
        </div>
    </div>
    <script>
    function toggle(el) {
        el.style.display = (el.style.display == 'block') ? '' : 'block'
    }
    document.getElementById('fileInput').addEventListener('change', function() {
        if (this.files.length > 0) {
            document.getElementById('uploadForm').submit();
        }
    });
    </script>
</html>
