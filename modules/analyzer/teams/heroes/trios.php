<?php
$result["teams"][$id]["hero_triplets"] = array();

$sql = "SELECT m1.heroid, m2.heroid, m3.heroid, SUM(1) match_count, SUM(NOT matches.radiantWin XOR m1.isRadiant)/SUM(1) winrate
        FROM matchlines m1
          JOIN matchlines m2
              ON m1.matchid = m2.matchid and m1.isRadiant = m2.isRadiant and m1.heroid < m2.heroid
            JOIN matchlines m3
              ON m1.matchid = m3.matchid and m1.isRadiant = m3.isRadiant and m2.heroid < m3.heroid
            JOIN matches
              ON m1.matchid = matches.matchid
            JOIN teams_matches
              ON m1.matchid = teams_matches.matchid
        WHERE teams_matches.teamid = ".$id."
        GROUP BY m1.heroid, m2.heroid, m3.heroid
        HAVING match_count > $limiter_lower
        ORDER BY match_count DESC;";
# limiting match count for hero pair to 3:
# 1 match = every possible pair
# 2 matches = may be a coincedence

if ($conn->multi_query($sql) === TRUE);# echo "[S] Requested data for HERO TRIPLETS.\n";
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();

for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
  $hero1_pickrate = $result["teams"][$id]['pickban'][$row[0]]['matches_picked'] / $result["teams"][$id]['matches_total'];
  $hero2_pickrate = $result["teams"][$id]['pickban'][$row[1]]['matches_picked'] / $result["teams"][$id]['matches_total'];
  $hero3_pickrate = $result["teams"][$id]['pickban'][$row[2]]['matches_picked'] / $result["teams"][$id]['matches_total'];
  $expected_pair  = $hero1_pickrate * $hero2_pickrate * $hero3_pickrate * ($result["teams"][$id]['matches_total']/3);

  $result["teams"][$id]["hero_triplets"][] = array (
    "heroid1" => $row[0],
    "heroid2" => $row[1],
    "heroid3" => $row[2],
    "matches" => $row[3],
    "winrate" => $row[4],
    "expectation" => $expected_pair
  );
}

$query_res->free_result();
?>
