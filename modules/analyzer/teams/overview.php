<?php
$sql  = "SELECT SUM(NOT matches.radiantWin XOR teams_matches.is_radiant) wins, SUM(1) matches_total
         FROM matches JOIN teams_matches ON matches.matchid = teams_matches.matchid
         WHERE teams_matches.teamid = ".$id.";";

if ($conn->multi_query($sql) === TRUE);
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();

$row = $query_res->fetch_row();
$result['teams'][$id]['wins'] = $row[0];
$result['teams'][$id]['matches_total'] = $row[1];

$query_res->free_result();

if (!$result['teams'][$id]['matches_total']) return 1;

$sql = "SELECT playerid FROM teams_rosters WHERE teamid = ".$id.";";

if ($conn->multi_query($sql) === TRUE);
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();
$result['teams'][$id]['roster'] = array();
for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
  $result["teams"][$id]['roster'][] = $row[0];
}

$query_res->free_result();

$sql = "SELECT playerid FROM matchlines JOIN teams_matches
        ON matchlines.matchid = teams_matches.matchid AND matchlines.isRadiant = teams_matches.is_radiant
        WHERE teams_matches.teamid = ".$id." GROUP BY playerid;";

if ($conn->multi_query($sql) === TRUE);
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();
$result['teams'][$id]['active_roster'] = array();
for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
  $result["teams"][$id]['active_roster'][] = $row[0];
}

$query_res->free_result();

return 0;
?>
