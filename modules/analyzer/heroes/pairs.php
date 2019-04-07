<?php
$result["hero_pairs"] = [];

$sql = "SELECT fm1.heroid, fm2.heroid,
          COUNT(distinct fm1.matchid) match_count,
          SUM(NOT matches.radiantWin XOR fm1.isRadiant)/SUM(1) winrate,
          SUM(fm1.lane = fm2.lane)/SUM(1) lane_rate
        FROM
          ( select m1.matchid, m1.heroid, am1.lane, m1.isRadiant
            from matchlines m1 JOIN adv_matchlines am1
            ON m1.matchid = am1.matchid AND m1.heroid = am1.heroid ) fm1
        JOIN
          ( select m2.matchid, m2.heroid, am2.lane, m2.isRadiant
            from matchlines m2 JOIN adv_matchlines am2
            ON m2.matchid = am2.matchid AND m2.heroid = am2.heroid ) fm2
        ON fm1.matchid = fm2.matchid and fm1.isRadiant = fm2.isRadiant and fm1.heroid < fm2.heroid
        JOIN matches ON fm1.matchid = matches.matchid
        GROUP BY fm1.heroid, fm2.heroid
        HAVING match_count > $limiter
        ORDER BY match_count DESC, winrate DESC;";
# limiting match count for hero pair to 3:
# 1 match = every possible pair
# 2 matches = may be a coincedence

if ($conn->multi_query($sql) === TRUE) echo "[S] Requested data for HERO PAIRS.\n";
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();

for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
  $hero1_pickrate = $result['pickban'][$row[0]]['matches_picked'] / $result['random']['matches_total'];
  $hero2_pickrate = $result['pickban'][$row[1]]['matches_picked'] / $result['random']['matches_total'];
  $expected_pair  = round($hero1_pickrate * $hero2_pickrate * ($result['random']['matches_total']/2));
  $wr_diff = ($result['pickban'][$row[0]]['winrate_picked'] + $result['pickban'][$row[1]]['winrate_picked'])/2 - $row[3];

  $result["hero_pairs"][] = [
    "heroid1" => $row[0],
    "heroid2" => $row[1],
    "matches" => $row[2],
    "winrate" => $row[3],
    "expectation" => $expected_pair,
    "lane_rate" => $row[4],
    "wr_diff" => $wr_diff,
  ];
}

$query_res->free_result();


if ($lg_settings['ana']['hero_pairs_matches']) {
  $result["hero_pairs_matches"] = [];

  foreach($result['hero_pairs'] as $pair) {
    $sql = "SELECT m1.matchid
            FROM matchlines m1 JOIN matchlines m2
              ON m1.matchid = m2.matchid and m1.isRadiant = m2.isRadiant and m1.heroid < m2.heroid
            WHERE m1.heroid = ".$pair['heroid1']." AND m2.heroid = ".$pair['heroid2'].";";

    $result["hero_pairs_matches"][$pair['heroid1']."-".$pair['heroid2']] = [];

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
