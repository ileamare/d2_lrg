<?php
# pick/ban draft stages stats
$result["teams"][$id]["draft"] = array (
  "picks" => array(),
  "bans"  => array()
);

for ($pick = 0; $pick < 2; $pick++) {
  $result["teams"][$id]["draft"][$pick] = array();
  for ($stage = 1; $stage < 4; $stage++) {
    $sql = "SELECT draft.hero_id hero_id, SUM(1) matches, SUM(NOT matches.radiantWin XOR draft.is_radiant)/SUM(1) winrate
            FROM draft JOIN matches ON draft.matchid = matches.matchid
                       JOIN teams_matches ON teams_matches.matchid = draft.matchid AND draft.is_radiant = teams_matches.is_radiant
            WHERE is_pick = ".($pick ? "true" : "false")." AND stage = ".$stage." AND teams_matches.teamid = ".$id."
            GROUP BY draft.hero_id";
    if ($conn->multi_query($sql) === TRUE);
    else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

    $query_res = $conn->store_result();

    $result["teams"][$id]["draft"][$pick][$stage] = array();

    for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
      $result["teams"][$id]["draft"][$pick][$stage][] = array (
        "heroid" => $row[0],
        "matches"=> $row[1],
        "winrate"=> $row[2]
      );
    }

    $query_res->free_result();
  }
}

# stages: 5
# types: 2 (pick and ban)
# total of 10 requests
?>
