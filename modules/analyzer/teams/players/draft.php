<?php
$result["teams"][$id]["players_draft"] = [];

for ($pick = 0; $pick < 2; $pick++) {
  for ($stage = 1; $stage < 4; $stage++) {
    $sql = "SELECT matchlines.playerid player_id,
                    SUM(1) matches,
                    SUM(NOT matches.radiantWin XOR draft.is_radiant)/SUM(1) winrate
            FROM draft
            JOIN matches ON draft.matchid = matches.matchid
            JOIN matchlines ON matches.matchid = matchlines.matchid AND draft.hero_id = matchlines.heroid
            JOIN teams_matches ON teams_matches.matchid = draft.matchid AND draft.is_radiant = teams_matches.is_radiant
            WHERE is_pick = ".($pick ? "true" : "false")." AND stage = ".$stage."
                  AND teams_matches.teamid = ".$id."
            GROUP BY player_id ORDER BY winrate DESC, matches DESC";
    if ($conn->multi_query($sql) === TRUE);# echo "[S] Requested data for PLAYERS DRAFT STAGE $pick $stage.\n";
    else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

    $query_res = $conn->store_result();

    for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
      $result["teams"][$id]["players_draft"][$pick][$stage][] = [
        "playerid" => $row[0],
        "matches"=> $row[1],
        "winrate"=> $row[2]
      ];
    }

    $query_res->free_result();
  }
}

# stages: 5
# types: 2 (pick and ban)
# total of 10 requests

# SUPPORTING: pickban context for teams

$result['teams'][$id]["players_draft_pb"] = [];

$sql = "SELECT matchlines.playerid, count(distinct matches.matchid), SUM(NOT matches.radiantWin XOR teams_matches.is_radiant)
FROM teams_matches
JOIN matches ON teams_matches.matchid = matches.matchid
JOIN matchlines ON matchlines.matchid = matches.matchid
WHERE teams_matches.teamid = ".$id."
GROUP BY matchlines.playerid;";

if ($conn->multi_query($sql) === TRUE); #echo "[S] Requested data for PICKS AND BANS for team $id.\n";
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();

for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
  if(!isset($result['teams'][$id]["players_draft_pb"][$row[0]])) {
    $result['teams'][$id]["players_draft_pb"][$row[0]] = array(
      "matches_banned" => 0,
      "winrate_banned" => 0
    );
  }
  $result['teams'][$id]["players_draft_pb"][$row[0]]['matches_picked'] = $row[1];
  $result['teams'][$id]["players_draft_pb"][$row[0]]['winrate_picked'] = $row[2]/$row[1];
  $result['teams'][$id]["players_draft_pb"][$row[0]]['matches_total'] = $row[1];
}

?>
