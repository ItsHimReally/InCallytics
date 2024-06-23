<?php
include('db.php');
include('gpt.php');
$link = connectDB();

if (isset($_GET["talk"], $_GET["id"])) {
    $result = checkOperationsGPT($_GET["id"]);
    $stmt = mysqli_prepare($link, "UPDATE `talks` SET `summary` = ? WHERE `talks`.`id` = ?");
    mysqli_stmt_bind_param($stmt, "ss", $result, $_GET["talk"]);
    mysqli_stmt_execute($stmt);
    if (is_null($result)) {
        echo "false";
    } else {
        echo $result;
    }
}