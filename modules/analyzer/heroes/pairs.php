<?php
$result["hero_pairs"] = array();

$sql = "SELECT m1.heroid, m2.heroid, COUNT(distinct m1.matchid) match_count, SUM(NOT matches.radiantWin XOR m1.isRadiant)/SUM(1) winrate
        FROM matchlines m1 JOIN matchlines m2
          ON m1.matchid = m2.matchid and m1.isRadiant = m2.isRadiant and m1.heroid < m2.heroid
          JOIN matches ON m1.matchid = matches.matchid
        GROUP BY m1.heroid, m2.heroid
        HAVING match_count > $limiter
        ORDER BY match_count DESC, winrate DESC;";
# limiting match count for hero pair to 3:
# 1 match = every possible pair
# 2 matches = may be a coincedence

if ($conn->multi_query($sql) === TRUE) echo "[S] Requested data for HERO PAIRS.\n";
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();

for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
  //$result['random']['matches_total']
  //$result['pickban'][]['matches_picked']
  $hero1_pickrate = $result['pickban'][$row[0]]['matches_picked'] / $result['random']['matches_total'];
  $hero2_pickrate = $result['pickban'][$row[1]]['matches_picked'] / $result['random']['matches_total'];
  $expected_pair  = $hero1_pickrate * $hero2_pickrate * ($result['random']['matches_total']/2);
  $pair_percentage = $row[2] / $expected_pair;

  //if($pair_percentage < 0.05) continue;

  $result["hero_pairs"][] = array (
    "heroid1" => $row[0],
    "heroid2" => $row[1],
    "matches" => $row[2],
    "winrate" => $row[3],
    "expectation" => $expected_pair
  );
}

$query_res->free_result();


if ($lg_settings['ana']['hero_pairs_matches']) {
  $result["hero_pairs_matches"] = array ();

  foreach($result['hero_pairs'] as $pair) {
    $sql = "SELECT m1.matchid
            FROM matchlines m1 JOIN matchlines m2
              ON m1.matchid = m2.matchid and m1.isRadiant = m2.isRadiant and m1.heroid < m2.heroid
            WHERE m1.heroid = ".$pair['heroid1']." AND m2.heroid = ".$pair['heroid2'].";";

    $result["hero_pairs_matches"][$pair['heroid1']."-".$pair['heroid2']] = array();

    if ($conn->multi_query($sql) === TRUE) ;#echo "[S] Requested data for HERO PAIRS MATCHES.\n";
    else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

    $query_res = $conn->store_result();

    for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
      $result["hero_pairs_matches"][$pair['heroid1']."-".$pair['heroid2']][] = $row[0];
    }

    $query_res->free_result();
  }
}
?>
