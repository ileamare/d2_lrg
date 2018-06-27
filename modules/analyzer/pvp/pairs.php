<?php
$result["player_pairs"] = array();

$sql = "SELECT m1.playerid, m2.playerid, SUM(1) match_count, SUM(NOT matches.radiantWin XOR m1.isRadiant)/SUM(1) winrate
        FROM matchlines m1 JOIN matchlines m2
          ON m1.matchid = m2.matchid and m1.isRadiant = m2.isRadiant and m1.playerid < m2.playerid
          JOIN matches ON m1.matchid = matches.matchid
        GROUP BY m1.playerid, m2.playerid
        HAVING match_count > $limiter
        ORDER BY match_count DESC;";


# limiting match count for hero pair to 3:
# 1 match = every possible pair
# 2 matches = may be a coincedence

if ($conn->multi_query($sql) === TRUE) echo "[S] Requested data for PLAYER PAIRS.\n";
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();

for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
  $result["player_pairs"][] = array (
    "playerid1" => $row[0],
    "playerid2" => $row[1],
    "matches" => $row[2],
    "winrate" => $row[3]
  );
}

$query_res->free_result();

if($lg_settings['ana']['player_pairs_matches']) {
  $result["player_pairs_matches"] = array();
  foreach($result["player_pairs"] as $pair) {
    $result["player_pairs_matches"][$pair['playerid1']."-".$pair['playerid2']] = array();

    $sql = "SELECT m1.matchid
            FROM matchlines m1 JOIN matchlines m2
              ON m1.matchid = m2.matchid and m1.isRadiant = m2.isRadiant and m1.playerid < m2.playerid
              JOIN matches ON m1.matchid = matches.matchid
            WHERE m1.playerid = ".$pair['playerid1']." AND m2.playerid = ".$pair['playerid2'].";";

    if ($conn->multi_query($sql) === TRUE) ;#echo "[S] Requested data for PLAYER PAIRS.\n";
    else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

    $query_res = $conn->store_result();

    for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
      $result["player_pairs_matches"][$pair['playerid1']."-".$pair['playerid2']][] = $row[0];
    }

    $query_res->free_result();
  }
}
?>
