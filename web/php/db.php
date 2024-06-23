<?php
function connectDB() {
    $login = "root";
    $pass = "UjlFn%Wd67Ku216Y9d";
    $server = "127.0.0.1";
    $name_db = "db";
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $link = mysqli_connect($server, $login, $pass, $name_db);
    mysqli_set_charset($link, 'utf8mb4');
    return $link;
}
?>
