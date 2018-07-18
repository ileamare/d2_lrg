<?php
$result['teams'][$id]["pickban_vs"] = array();

$sql = "SELECT draft.hero_id, count(distinct draft.matchid), SUM(NOT matches.radiantWin XOR draft.is_radiant) FROM
teams_matches JOIN draft ON draft.matchid = teams_matches.matchid AND draft.is_radiant <> teams_matches.is_radiant
JOIN matches ON draft.matchid = matches.matchid
WHERE draft.is_pick = true AND teams_matches.teamid = ".$id."
GROUP BY draft.hero_id;".
"SELECT draft.hero_id, count(distinct draft.matchid), SUM(NOT matches.radiantWin XOR draft.is_radiant) FROM
teams_matches JOIN draft ON draft.matchid = teams_matches.matchid AND draft.is_radiant <> teams_matches.is_radiant
JOIN matches ON draft.matchid = matches.matchid
WHERE draft.is_pick = false AND teams_matches.teamid = ".$id."
GROUP BY draft.hero_id;";

if ($conn->multi_query($sql) === TRUE);# echo "[S] Requested data for PICKS AND BANS VS team $id.\n";
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();

for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
  if(!isset($result['teams'][$id]["pickban_vs"][$row[0]])) {
    $result['teams'][$id]["pickban_vs"][$row[0]] = array(
      "matches_banned" => 0,
      "wins_banned" => 0
    );
  }
  $result['teams'][$id]["pickban_vs"][$row[0]]['matches_picked'] = $row[1];
  $result['teams'][$id]["pickban_vs"][$row[0]]['wins_picked'] = $row[2];
  $result['teams'][$id]["pickban_vs"][$row[0]]['matches_total'] = $row[1];
}

$query_res->free_result();
$conn->next_result();
$query_res = $conn->store_result();

for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
  if(!isset($result['teams'][$id]["pickban_vs"][$row[0]])) {
    $result['teams'][$id]["pickban_vs"][$row[0]] = array(
      "matches_picked" => 0,
      "wins_picked" => 0
    );
  }
  $result['teams'][$id]["pickban_vs"][$row[0]]['matches_banned'] = $row[1];
  $result['teams'][$id]["pickban_vs"][$row[0]]['wins_banned'] = $row[2];
  if(isset($result['teams'][$id]["pickban_vs"][$row[0]]['matches_total']))
    $result['teams'][$id]["pickban_vs"][$row[0]]['matches_total'] += $row[1];
  else $result['teams'][$id]["pickban_vs"][$row[0]]['matches_total'] = $row[1];
}

$query_res->free_result();

$result["teams"][$id]["draft_vs"] = array (
  "picks" => array(),
  "bans"  => array()
);

for ($pick = 0; $pick < 2; $pick++) {
  $result["teams"][$id]["draft_vs"][$pick] = array();
  for ($stage = 1; $stage < 4; $stage++) {
    $sql = "SELECT draft.hero_id hero_id, SUM(1) matches, SUM(NOT matches.radiantWin XOR draft.is_radiant)/SUM(1) winrate
            FROM draft JOIN matches ON draft.matchid = matches.matchid
                       JOIN teams_matches ON teams_matches.matchid = draft.matchid AND draft.is_radiant <> teams_matches.is_radiant
            WHERE is_pick = ".($pick ? "true" : "false")." AND stage = ".$stage." AND teams_matches.teamid = ".$id."
            GROUP BY draft.hero_id";
    if ($conn->multi_query($sql) === TRUE);
    else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

    $query_res = $conn->store_result();

    $result["teams"][$id]["draft_vs"][$pick][$stage] = array();

    for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
      $result["teams"][$id]["draft_vs"][$pick][$stage][] = array (
        "heroid" => $row[0],
        "matches"=> $row[1],
        "winrate"=> $row[2]
      );
    }

    $query_res->free_result();
  }
}
?>
