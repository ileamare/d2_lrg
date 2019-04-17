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

// ********** Team's last match date

$sql = "SELECT MAX(matches.start_date) FROM matches JOIN teams_matches
        ON matches.matchid = teams_matches.matchid
        WHERE teams_matches.teamid = ".$id.";";

if ($conn->multi_query($sql) === TRUE);
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();
$row = $query_res->fetch_row();

$result['teams'][$id]['last_match'] = $row[0];

$query_res->free_result();

// ********** Team's roster

$sql = "SELECT playerid FROM teams_rosters WHERE teamid = ".$id.";";

if ($conn->multi_query($sql) === TRUE);
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();
$result['teams'][$id]['roster'] = array();
for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
  $result["teams"][$id]['roster'][] = $row[0];
}

$query_res->free_result();

$sql = "SELECT playerid, COUNT(DISTINCT matchlines.matchid) as matches_num, MAX(matches.start_date) as last_match
        FROM matchlines JOIN teams_matches
        ON matchlines.matchid = teams_matches.matchid AND matchlines.isRadiant = teams_matches.is_radiant
        JOIN matches ON matches.matchid = teams_matches.matchid
        WHERE teams_matches.teamid = ".$id."
        GROUP BY playerid";

if ($conn->multi_query($sql) === TRUE);
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();
$result['teams'][$id]['active_roster'] = [];
for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
  if ($row[1] > ($result['teams'][$id]['matches_total']/10) &&
      $result['teams'][$id]['last_match']-$row[2] < (3600*24*14) &&
      $row[1] > ($result['players_summary'][$row[0]]['matches_s'] ?? 0)/10) {
  // Rules for fetching roster members based on matches:
  // 1. More than 10% of team's matches
  // 2. Last match played with the tag within two weeks
  // 3. More than 10% of player's matches
  // TODO 4. Player had a match after last match with the team
    $result["teams"][$id]['active_roster'][] = $row[0];

    if(!isset($result["players_additional"][$row[0]]))
      $result["players_additional"][$row[0]] = [];
    $result["players_additional"][$row[0]]['team'] = $id;
  } else {
    // If a player was playing for a team and still listed in it's roster, but
    // is not part of the team anymore or isn't active at the moment
    $player_index = array_search($row[0], $result["teams"][$id]['roster']);
    if($player_index !== FALSE)
      unset($result["teams"][$id]['roster'][$player_index]);
    $result["teams"][$id]['roster'] = array_values($result["teams"][$id]['roster']); // resetting array's keys
  }
}

if(isset($player_index)) unset($player_index);

$query_res->free_result();

return 0;
?>
