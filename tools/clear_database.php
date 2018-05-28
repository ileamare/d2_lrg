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


if ($conn->multi_query($sql) === TRUE) echo "[S] Cleared tables.\n";
else echo("[F] Unexpected problems when recording to database.\n".$conn->error."\n");

do {
  $conn->store_result();
} while($conn->next_result());

if ($conn->multi_query($matches) === TRUE) echo "[S] Deleted match data.\n";
else echo("[F] Unexpected problems when recording to database.\n".$conn->error."\n");
do {
  $conn->store_result();
} while($conn->next_result());

if ($conn->multi_query($players) === TRUE) echo "[S] Deleted players data.\n";
else echo("[F] Unexpected problems when recording to database.\n".$conn->error."\n");
do {
  $conn->store_result();
} while($conn->next_result());

if($lg_settings['main']['teams']) {
  if ($conn->multi_query($teams) === TRUE) echo "[S] Deleted teams data.\n";
  else echo("[F] Unexpected problems when recording to database.\n".$conn->error."\n");
}
?>
