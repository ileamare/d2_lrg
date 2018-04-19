<?php
require_once("head.php");

$options = getopt("m:", [ "match" ]);

if(isset($options['m']))
  $mids = $options['m'];
else die("[F] No match ID specified\n");



$conn = new mysqli($lrg_sql_host, $lrg_sql_user, $lrg_sql_pass, $lrg_sql_db);

if( !is_array($mids) ) $mids = [$mids];

foreach ($mids as $mid) {
    $sql = "DELETE from matchlines where matchid = $mid; DELETE from adv_matchlines where matchid = $mid; ".
       "DELETE from draft where matchid = $mid; ".
       ( $lg_settings['main']['teams'] ? "delete from teams_matches where matchid = $mid;" : "").
       "delete from matches where matchid = $mid;";

    if ($conn->multi_query($sql) === TRUE) echo "[S] Removed matches.\n";
    else echo("[F] Unexpected problems when quering database.\n".$conn->error."\n");
}

$query_res = $conn->store_result();
//$query_res->free_result();

echo "OK \n";

?>
