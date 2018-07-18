<?php
$result['teams'][$id]["pickban"] = [];

$sql = "SELECT draft.hero_id, count(distinct draft.matchid), SUM(NOT matches.radiantWin XOR draft.is_radiant) FROM
teams_matches JOIN draft ON draft.matchid = teams_matches.matchid AND draft.is_radiant = teams_matches.is_radiant
JOIN matches ON draft.matchid = matches.matchid
WHERE draft.is_pick = true AND teams_matches.teamid = ".$id."
GROUP BY draft.hero_id;".
"SELECT draft.hero_id, count(distinct draft.matchid), SUM(NOT matches.radiantWin XOR draft.is_radiant) FROM
teams_matches JOIN draft ON draft.matchid = teams_matches.matchid AND draft.is_radiant = teams_matches.is_radiant
JOIN matches ON draft.matchid = matches.matchid
WHERE draft.is_pick = false AND teams_matches.teamid = ".$id."
GROUP BY draft.hero_id;";

if ($conn->multi_query($sql) === TRUE) echo "[S] Requested data for PICKS AND BANS for team $id.\n";
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();

for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
  if(!isset($result['teams'][$id]["pickban"][$row[0]])) {
    $result['teams'][$id]["pickban"][$row[0]] = array(
      "matches_banned" => 0,
      "winrate_banned" => 0
    );
  }
  $result['teams'][$id]["pickban"][$row[0]]['matches_picked'] = $row[1];
  $result['teams'][$id]["pickban"][$row[0]]['winrate_picked'] = $row[2]/$row[1];
  $result['teams'][$id]["pickban"][$row[0]]['matches_total'] = $row[1];
}

$query_res->free_result();
$conn->next_result();
$query_res = $conn->store_result();

for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
  if(!isset($result['teams'][$id]["pickban"][$row[0]])) {
    $result['teams'][$id]["pickban"][$row[0]] = array(
      "matches_picked" => 0,
      "winrate_picked" => 0
    );
  }
  $result['teams'][$id]["pickban"][$row[0]]['matches_banned'] = $row[1];
  $result['teams'][$id]["pickban"][$row[0]]['winrate_banned'] = $row[2]/$row[1];
  if(isset($result['teams'][$id]["pickban"][$row[0]]['matches_total']))
    $result['teams'][$id]["pickban"][$row[0]]['matches_total'] += $row[1];
  else $result['teams'][$id]["pickban"][$row[0]]['matches_total'] = $row[1];
}

$query_res->free_result();
?>
