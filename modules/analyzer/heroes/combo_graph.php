<?php
$result["hero_combos_graph"] = array();

$sql = "SELECT fm1.heroid, fm2.heroid,
          COUNT(distinct fm1.matchid) match_count,
          SUM(NOT matches.radiantWin XOR fm1.isRadiant) winrate
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
        HAVING match_count > $limiter_graph
        ORDER BY match_count DESC, winrate DESC;";
# WARNING: big amount of matches may send client browser to a long trip

if ($conn->multi_query($sql) === TRUE) echo "[S] Requested data for FULL HERO PAIRS.\n";
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();

for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
  $hero1_pickrate = $result['pickban'][$row[0]]['matches_picked'] / $result['random']['matches_total'];
  $hero2_pickrate = $result['pickban'][$row[1]]['matches_picked'] / $result['random']['matches_total'];
  $expected_pair  = round($hero1_pickrate * $hero2_pickrate * ($result['random']['matches_total']/2));

  if($row[2]-$expected_pair < $row[2]*0.1) //min deviation 10% of total matches
    continue;

  $wr_diff = ($result['pickban'][$row[0]]['winrate_picked'] + $result['pickban'][$row[1]]['winrate_picked'])/2 - $row[3]/$row[2];
  $dev_pct = $expected_pair ? $row[2]/$expected_pair - 1 : 1;

  $result["hero_combos_graph"][] = array (
    "heroid1" => $row[0],
    "heroid2" => $row[1],
    "matches" => $row[2],
    "wins" => $row[3],
    "wr_diff" => $wr_diff,
    "dev_pct" => $dev_pct,
  );
}

$query_res->free_result();
?>
