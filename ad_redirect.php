<?php
include_once(dirname(__FILE__) . "/phplibs/front_head.php");

/* 廣告轉跳頁 */
@$advertisement_id = params_security($_GET['advertisement_id']);
@$link = params_security($_GET['link']);

$query = "";
$query .= "UPDATE advertisement SET `count` = `count` + 1 WHERE advertisement_id = '" . $advertisement_id . "';";
$query .= "INSERT INTO `advertisement_day` (`advertisement_day_id`, `advertisement_id`, `display`, `count`, `pub_date`) VALUES (UUID(), '" . $advertisement_id . "', 0, 1, DATE(NOW())) ON DUPLICATE KEY UPDATE `count` = `count` +1; ";

if ($mysqli->multi_query($query)) {
    while ($mysqli->more_results() && $mysqli->next_result()) ;
}

echo "<script>document.location.href = '" . $link . "'</script>";