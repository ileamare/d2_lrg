<?php

require_once('head.php');

$conn = new mysqli($lrg_sql_host, $lrg_sql_user, $lrg_sql_pass, $lrg_sql_db);

if ($conn->connect_error) die("[F] Connection to SQL server failed: ".$conn->connect_error."\n");

$sql = "SELECT matchid FROM matches;";

if ($conn->multi_query($sql) === TRUE) echo "[S] Requested MatchIDs.\n";
else die("[F] Unexpected problems when recording to database.\n".$conn->error."\n");

$query_res = $conn->store_result();

for ($matches = "", $row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
  $matches .= "DELETE FROM `matches` WHERE `matches`.`matchid` = ".$row[0].";";
}

$query_res->free_result();

$sql = "SELECT playerid FROM players;";

if ($conn->multi_query($sql) === TRUE) echo "[S] Requested Playerss.\n";
else die("[F] Unexpected problems when recording to database.\n".$conn->error."\n");

$query_res = $conn->store_result();

$players = "";
for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
  $players .= "DELETE FROM `players` WHERE `players`.`playerID` = ".$row[0].";";
}

if($lg_settings['main']['teams']) {
  $sql = "SELECT teamid FROM teams;";

  if ($conn->multi_query($sql) === TRUE) echo "[S] Requested Teams.\n";
  else die("[F] Unexpected problems when recording to database.\n".$conn->error."\n");

  $query_res = $conn->store_result();

  $teams = "";
  for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
    $teams .= "DELETE FROM `teams` WHERE `teams`.`teamid` = ".$row[0].";";
  }


}

$sql = "TRUNCATE TABLE `adv_matchlines`; TRUNCATE TABLE `draft`; TRUNCATE TABLE `matchlines`;";
if($lg_settings['main']['teams'])
  $sql .= "TRUNCATE TABLE `teams_matches`; TRUNCATE TABLE `teams_rosters`;";
if($lg_settings['main']['items']) {
  $_sql = "SELECT COUNT(*) z
  FROM information_schema.tables WHERE table_schema = '$lrg_sql_db' 
  AND table_name = 'itemslines' HAVING z > 0;";

  $query = $conn->query($_sql);
  if (!isset($query->num_rows) || !$query->num_rows) {
    $lg_settings['main']['itemslines'] = false;
    echo "[N] Set &settings.items to false.\n";
  } else {
    $lg_settings['main']['itemslines'] = true;
    echo "[N] Set &settings.itemslines to true.\n";
  }

  if ($lg_settings['main']['itemslines']) {
    $sql .= "TRUNCATE TABLE `itemslines`;";
  } else {
    $sql .= "TRUNCATE TABLE `items`;";
  }
}

if ($conn->multi_query($sql) === TRUE) echo "[S] Cleared tables.\n";
else echo("[F] Unexpected problems when recording to database.\n".$conn->error."\n");

while( $conn->more_results() ) {
  $conn->next_result();
  $conn->store_result();
}

if ($conn->multi_query($matches) === TRUE) echo "[S] Deleted match data.\n";
else echo("[F] Unexpected problems when recording to database.\n".$conn->error."\n");

while( $conn->more_results() ) {
  $conn->next_result();
  $conn->store_result();
}

if ($conn->multi_query($players) === TRUE) echo "[S] Deleted players data.\n";
else echo("[F] Unexpected problems when recording to database.\n".$conn->error."\n");

while( $conn->more_results() ) {
  $conn->next_result();
  $conn->store_result();
}

if($lg_settings['main']['teams']) {
  if ($conn->multi_query($teams) === TRUE) echo "[S] Deleted teams data.\n";
  else echo("[F] Unexpected problems when recording to database.\n".$conn->error."\n");
}
?>
