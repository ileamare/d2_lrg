<?php
require_once("head.php");

$conn = new mysqli($lrg_sql_host, $lrg_sql_user, $lrg_sql_pass, $lrg_sql_db);

$teams = [];

$sql = "SELECT teamid FROM teams;";

if ($conn->multi_query($sql) === TRUE) echo "[S] Requested data for TEAMS LIST.\n";
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();

for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
  $result[] = $row[0];
}

$query_res->free_result();

foreach($result as $id) {
  $json = file_get_contents('https://api.steampowered.com/IDOTA2Match_570/GetTeamInfoByTeamID/v001/?key='.$steamapikey.'&teams_requested=1&start_at_team_id='.$id);
  $team = json_decode($json, true);

  for($i=0; isset($team['result']['teams'][0]['player_'.$i.'_account_id']); $i++)
      $sql .= "\n\t(".$id.",".$team['result']['teams'][0]['player_'.$i.'_account_id'].", 0),";

  if(!empty($sql)) {
    $sql[strlen($sql)-1] = ";";
    $sql = "INSERT INTO teams_rosters (teamid, playerid, position) VALUES ".$sql;

    if ($conn->multi_query($sql) === TRUE) echo "[S] Successfully recorded new teams rosters to database.\n";
    else die("[F] Unexpected problems when recording to database.\n".$conn->error."\n");
  }
}

echo "OK \n";

?>
