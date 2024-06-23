<?php
include('../php/db.php');
$link = connectDB();
$result = mysqli_query($link, "SELECT `id` FROM `talks`");
if ($result) {
    $ids = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $ids[] = $row['id'];
    }
    if (count($ids) > 0) {
        $random_id = $ids[array_rand($ids)];
    }
    mysqli_free_result($result);
}
mysqli_close($link);

header("location: /talks/talk.php?id=".$random_id);
exit();
?>
