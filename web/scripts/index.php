<?php
include('../php/db.php');
$link = connectDB();

if (isset($_POST['subm'])) {
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $fileTmpPath = $_FILES['file']['tmp_name'];
        $fileName = $_FILES['file']['name'];
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        if ($fileExtension == 'txt') {
            $fileContent = file_get_contents($fileTmpPath);
            $fileContent = $link->real_escape_string($fileContent);
            $fileName = $link->real_escape_string($fileName);
            $sql = "INSERT INTO scripts (description, script) VALUES ('$fileName', '$fileContent')";
            if ($link->query($sql) === TRUE) {
                header("location: /scripts");
				exit();
            }
        }
    }
}
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
            <div class="title">Скрипты</div>
	        <div class="add">
		        <form id="uploadForm" method="POST" enctype="multipart/form-data">
			        <label class="input-file but-small">
				        <input type="hidden" name="subm" value="1">
				        <input type="file" name="file" id="fileInput" accept=".txt" required>
				        <span>Добавить в .txt</span>
			        </label>
		        </form>
	        </div>
        </div>
        <div class="content">
            <div class="table">
                <table class="users">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Описание</th>
                        <th>Время создания</th>
                        <th>Начало скрипта</th>
                        <th>Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $query = mysqli_query($link, "SELECT * FROM `scripts`");
                    while ($u = mysqli_fetch_array($query)) {
                        echo '
		            <tr>
			            <td>'.$u['id'].'</td>
			            <td>'.mb_substr($u["description"], 0, 300, "utf8").'</td>
			            <td>'.$u["time"].'</td>
			            <td class="cen"><p>'.mb_substr($u["script"], 0, 300, "utf8").'</p></td>
			            <td>
			                <a href="s.php?id='.$u['id'].'" class="button-edit">Просмотр</a>
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
<script>
    document.getElementById('fileInput').addEventListener('change', function() {
        if (this.files.length > 0) {
            document.getElementById('uploadForm').submit();
        }
    });
</script>
</body>
</html>