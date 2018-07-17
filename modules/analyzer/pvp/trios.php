<?php
$result["player_triplets"] = array();

$sql = "SELECT m1.playerid, m2.playerid, m3.playerid, SUM(1) match_count, SUM(NOT matches.radiantWin XOR m1.isRadiant)/SUM(1) winrate
        FROM matchlines m1
          JOIN matchlines m2
              ON m1.matchid = m2.matchid and m1.isRadiant = m2.isRadiant and m1.playerid < m2.playerid
            JOIN matchlines m3
              ON m1.matchid = m3.matchid and m1.isRadiant = m3.isRadiant and m2.playerid < m3.playerid
            JOIN matches
              ON m1.matchid = matches.matchid
        GROUP BY m1.playerid, m2.playerid, m3.playerid HAVING match_count > $limiter_lower
        ORDER BY match_count DESC, winrate DESC;";
# limiting match count for hero pair to 3:
# 1 match = every possible pair
# 2 matches = may be a coincedence

if ($conn->multi_query($sql) === TRUE) echo "[S] Requested data for PLAYER PAIRS.\n";
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();

for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
  $p1_matchrate = $result['players_summary'][$row[0]]['matches_s'] / $result['random']['matches_total'];
  $p2_matchrate = $result['players_summary'][$row[1]]['matches_s'] / $result['random']['matches_total'];
  $p3_matchrate = $result['players_summary'][$row[2]]['matches_s'] / $result['random']['matches_total'];
  $expected_pair  = $p1_matchrate * $p2_matchrate * $p3_matchrate * ($result['random']['matches_total']/3);

  $result["player_triplets"][] = array (
    "playerid1" => $row[0],
    "playerid2" => $row[1],
    "playerid3" => $row[2],
    "matches" => $row[3],
    "winrate" => $row[4],
    "expectation" => $expected_pair
  );
}

$query_res->free_result();

if($lg_settings['ana']['player_triplets_matches']) {
  $result["player_triplets_matches"] = array();
  foreach($result["player_triplets"] as $pair) {
    $result["player_triplets_matches"][$pair['playerid1']."-".$pair['playerid2']."-".$pair['playerid3']] = array();

    $sql = "SELECT m1.matchid
            FROM matchlines m1
              JOIN matchlines m2
                  ON m1.matchid = m2.matchid and m1.isRadiant = m2.isRadiant and m1.playerid < m2.playerid
                JOIN matchlines m3
                  ON m1.matchid = m3.matchid and m1.isRadiant = m3.isRadiant and m2.playerid < m3.playerid
                JOIN matches
                  ON m1.matchid = matches.matchid
            WHERE m1.playerid = ".$pair['playerid1']." AND m2.playerid = ".$pair['playerid2']." AND m3.playerid = ".$pair['playerid3'].";";

    if ($conn->multi_query($sql) === TRUE) ;#echo "[S] Requested data for PLAYER TRIPLES.\n";
    else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

    $query_res = $conn->store_result();

    for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
      $result["player_triplets_matches"][$pair['playerid1']."-".$pair['playerid2']."-".$pair['playerid3']][] = $row[0];
    }

    $query_res->free_result();
  }
}
?>
