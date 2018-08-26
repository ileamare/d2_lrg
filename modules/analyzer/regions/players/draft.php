<?php
$result["regions_data"][$region]["players_draft"] = [];
#echo "[S] Requested data for PLAYERS DRAFT STAGE.\n";

for ($pick = 0; $pick < 2; $pick++) {
  for ($stage = 1; $stage < 4; $stage++) {
    $sql = "SELECT matchlines.playerid player_id,
                    SUM(1) matches,
                    SUM(NOT matches.radiantWin XOR draft.is_radiant)/SUM(1) winrate
            FROM draft
            JOIN matches ON draft.matchid = matches.matchid
            JOIN matchlines ON matches.matchid = matchlines.matchid AND draft.hero_id = matchlines.heroid
            WHERE is_pick = ".($pick ? "true" : "false")." AND stage = ".$stage."
            AND matches.cluster IN (".implode(",", $clusters).")
            GROUP BY player_id ORDER BY winrate DESC, matches DESC";
    if ($conn->multi_query($sql) === TRUE);# echo "[S] Requested data for PLAYERS DRAFT STAGE $pick $stage.\n";
    else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

    $query_res = $conn->store_result();

    for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
      $result["regions_data"][$region]["players_draft"][$pick][$stage][] = [
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

?>
