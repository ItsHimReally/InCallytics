<?php
include('db.php');
include('gpt.php');
$link = connectDB();

function checkString($str) {
    if (strpos($str, 'True') !== false) {
        return 1;
    } elseif (strpos($str, 'False') !== false) {
        return -1;
    }
    return 0;
}
function cleanString($str) {
    $pattern = '/(\*|True\.?|False\.?)/i';
    $cleanedStr = preg_replace($pattern, '', $str);
    $cleanedStr = trim($cleanedStr);
    return $cleanedStr;
}

if (isset($_GET["id"])) {
    $result = checkOperationsGPT($_GET["id"]);
    if (is_null($result)) {
        echo "false";
    } else {
        $check = checkString($result);
        if ($check == 1) {
            $result = '<img src="../images/check.svg" alt="галочка" class="img-compare">'.cleanString($result);
        } else if ($check == -1) {
            $result = '<img src="../images/x.svg" alt="крестик" class="img-compare">'.cleanString($result);
        }
        echo $result;
    }
}