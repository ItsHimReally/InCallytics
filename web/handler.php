<?php
include('php/db.php');
$link = connectDB();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['file']['tmp_name'];
        $fileName = $_FILES['file']['name'];
        $fileSize = $_FILES['file']['size'];
        $fileType = $_FILES['file']['type'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ($fileExtension == 'wav') {
            processWavFile($fileTmpPath, $fileName);
        } elseif ($fileExtension == 'zip') {
            $zip = new ZipArchive;
            if ($zip->open($fileTmpPath) === TRUE) {
                $zip->extractTo('myaw');
                $zip->close();

                $extractedFiles = scandir('myaw');
                foreach ($extractedFiles as $file) {
                    if (pathinfo($file, PATHINFO_EXTENSION) == 'wav') {
                        processWavFile('myaw' . $file);
                    }
                }
            } else {
                echo 'Failed to open the zip file.';
            }
        } else {
            echo 'Unsupported file type.';
        }
    }
}

function processWavFile($filePath, $originalFileName) {
    global $link;
    $apiUrl = 'http://psai.tw1.su:5000/speech_to_text?diarization=1';
//    $verbose = fopen('php://temp', 'w+');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ['audio' => new CURLFile($filePath, 'audio/wav', $originalFileName)]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//    curl_setopt($ch, CURLOPT_VERBOSE, true);
//    curl_setopt($ch, CURLOPT_STDERR, $verbose);

    $response = curl_exec($ch);
//    $errNo = curl_errno($ch);
//    $errMsg = curl_error($ch);
    curl_close($ch);

//    rewind($verbose);
//    $verboseLog = stream_get_contents($verbose);
//    fclose($verbose);

    if ($response === false) {
//        echo 'Error during API request: ' . $errMsg . ' (' . $errNo . ')';
//        echo 'Verbose information: ' . "\n" . $verboseLog;
        return;
    }

    //echo 'Verbose information: ' . "\n" . $verboseLog;

    $jsonResponse = json_decode($response, true);
    if (isset($jsonResponse)) {
        $result = $jsonResponse;

        $stmt = mysqli_prepare($link, "INSERT INTO `talks` (`nameFile`, `transcript`, `isLive`, `aiHelper`, `summary`, `score`) VALUES (?, ?, '0', NULL, NULL, ?);");
        if (!isset($result["score"])) {
            $result["score"] = '0';
        }
        mysqli_stmt_bind_param($stmt, 'sss', $originalFileName, $response, $result["score"]);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("location: /talks");
        exit();
    } else {
        echo "<pre>"; var_dump($response, new CURLFile($filePath)); echo "</pre>";
        die("Invalid response from API");
    }
}