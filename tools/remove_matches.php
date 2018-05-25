<?php
require_once("head.php");

$options = getopt("f:");

if(isset($options['f']))
  $file = $options['f'];
else die("[F] No match ID specified\n");

$mids = explode("\n", file_get_contents($file));

$conn = new mysqli($lrg_sql_host, $lrg_sql_user, $lrg_sql_pass, $lrg_sql_db);

if( !is_array($mids) ) $mids = [$mids];

foreach ($mids as $mid) {
    if (empty($mid)) continue;

    $sql = "DELETE from matchlines where matchid = $mid; DELETE from adv_matchlines where matchid = $mid; ".
       "DELETE from draft where matchid = $mid; ".
       ( $lg_settings['main']['teams'] ? "delete from teams_matches where matchid = $mid;" : "").
       "delete from matches where matchid = $mid;";

    if ($conn->multi_query($sql) === TRUE) echo "[S] Removed $mid.\n";
    else echo("[F] Unexpected problems when quering database.\n".$conn->error."\n");
    
    do {
        $conn->store_result();
    } while($conn->next_result());
}

$query_res = $conn->store_result();
$query_res->free_result();

echo "OK \n";

?>
