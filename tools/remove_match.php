<?php
require_once("settings.php");

$options = getopt("m", [ "match" ]);

if(isset($opdions['m']))
  $mid = (int)$options['m'];
else die("[F] No match ID specified\n")



$sql = "DELETE from matchlines where matchid = $mid; DELETE from adv_matchlines where matchid = $mid; ".
       "DELETE from draft where matchid = $mid; ".
       ( $lg_settings['main']['teams'] ? "delete from teams_matches where matchid = $mid;" : "").
       "delete from matches where matchid = $mid;";

if ($conn->multi_query($sql) === TRUE) echo "[S] Removed matches.\n";
else die("[F] Unexpected problems when quering database.\n".$conn->error."\n");

$query_res = $conn->store_result();
$query_res->free_result();

echo "OK \n";

?>
