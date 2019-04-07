<?php
$result["regions_data"][$region]["players_parties_graph"] = [];

$sql = "SELECT m1.playerid, m2.playerid, SUM(NOT matches.radiantWin XOR m1.isRadiant) wins, SUM(1) match_count
        FROM matchlines m1 JOIN matchlines m2
          ON m1.matchid = m2.matchid and m1.isRadiant = m2.isRadiant and m1.playerid < m2.playerid
          JOIN matches ON m1.matchid = matches.matchid
        WHERE matches.cluster IN (".implode(",", $clusters).")
        GROUP BY m1.playerid, m2.playerid HAVING match_count > ".$result["regions_data"][$region]['settings']['limiter_graph'].";";
# only wis makes more sense for players combo graph

if ($conn->multi_query($sql) === TRUE);# echo "[S] Requested data for PLAYER PAIRS.\n";
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();

for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
  $p1_matchrate = $result["regions_data"][$region]['players_summary'][$row[0]]['matches_s'] / $result["regions_data"][$region]['main']['matches'];
  $p2_matchrate = $result["regions_data"][$region]['players_summary'][$row[1]]['matches_s'] / $result["regions_data"][$region]['main']['matches'];
  $expected_pair  = $p1_matchrate * $p2_matchrate * ($result["regions_data"][$region]['main']['matches']/2);

  if($row[3]-$expected_pair < $row[2]*0.1) //min deviation 10% of total matches
    continue;

  $wr_diff = ($result["regions_data"][$region]['players_summary'][$row[0]]['winrate_s'] + 
              $result["regions_data"][$region]['players_summary'][$row[1]]['winrate_s'])/2 - $row[2]/$row[3];
  $dev_pct = $expected_pair ? $row[2]/$expected_pair - 1 : 1;

  $result["regions_data"][$region]["players_parties_graph"][] = [
    "playerid1" => $row[0],
    "playerid2" => $row[1],
    "matches" => $row[3],
    "wins" => $row[2],
    "wr_diff" => $wr_diff,
    "dev_pct" => $dev_pct,
  ];
}

$query_res->free_result();
?>
