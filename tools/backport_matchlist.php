<?php
require_once('head.php');
$conn = new mysqli($lrg_sql_host, $lrg_sql_user, $lrg_sql_pass, $lrg_sql_db);

if ($conn->connect_error) die("[F] Connection to SQL server failed: ".$conn->connect_error."\n");

$sql = "SELECT matchid FROM matches;";

if ($conn->multi_query($sql) === TRUE) echo "[S] Requested MatchIDs.\n";
else die("[F] Unexpected problems when recording to database.\n".$conn->error."\n");

$query_res = $conn->store_result();

for ($matches = [], $row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
  $matches[] = $row[0];
}

$query_res->free_result();

$matches = implode("\n", $matches);

file_put_contents("matchlists/$lrg_league_tag.list", $matches);

?>
