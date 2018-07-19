<?php
$result["regions_data"][$region]["player_trios"] = [];

$sql = "SELECT m1.playerid, m2.playerid, m3.playerid, SUM(1) match_count, SUM(NOT matches.radiantWin XOR m1.isRadiant)/SUM(1) winrate
        FROM matchlines m1
          JOIN matchlines m2
              ON m1.matchid = m2.matchid and m1.isRadiant = m2.isRadiant and m1.playerid < m2.playerid
            JOIN matchlines m3
              ON m1.matchid = m3.matchid and m1.isRadiant = m3.isRadiant and m2.playerid < m3.playerid
            JOIN matches
              ON m1.matchid = matches.matchid
        WHERE matches.cluster IN (".implode(",", $clusters).")
        GROUP BY m1.playerid, m2.playerid, m3.playerid HAVING match_count > ".$result["regions_data"][$region]['settings']['limiter_lower']."
        ORDER BY match_count DESC, winrate DESC;";
# limiting match count for hero pair to 3:
# 1 match = every possible pair
# 2 matches = may be a coincedence

if ($conn->multi_query($sql) === TRUE);# echo "[S] Requested data for PLAYER PAIRS.\n";
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();

for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
  $p1_matchrate = $result["regions_data"][$region]['players_summary'][$row[0]]['matches_s'] / $result["regions_data"][$region]['main']['matches'];
  $p2_matchrate = $result["regions_data"][$region]['players_summary'][$row[1]]['matches_s'] / $result["regions_data"][$region]['main']['matches'];
  $p3_matchrate = $result["regions_data"][$region]['players_summary'][$row[2]]['matches_s'] / $result["regions_data"][$region]['main']['matches'];
  $expected_pair  = $p1_matchrate * $p2_matchrate * $p3_matchrate * ($result["regions_data"][$region]['main']['matches']/3);

  $result["regions_data"][$region]["player_trios"][] = [
    "playerid1" => $row[0],
    "playerid2" => $row[1],
    "playerid3" => $row[2],
    "matches" => $row[3],
    "winrate" => $row[4],
    "expectation" => $expected_pair
  ];
}

$query_res->free_result();

?>
