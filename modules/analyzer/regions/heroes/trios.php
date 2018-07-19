<?php
$result["regions_data"][$region]["hero_trios"] = [];

$sql = "SELECT m1.heroid, m2.heroid, m3.heroid, SUM(1) match_count, SUM(NOT matches.radiantWin XOR m1.isRadiant)/SUM(1) winrate
        FROM matchlines m1
          JOIN matchlines m2
              ON m1.matchid = m2.matchid and m1.isRadiant = m2.isRadiant and m1.heroid < m2.heroid
            JOIN matchlines m3
              ON m1.matchid = m3.matchid and m1.isRadiant = m3.isRadiant and m2.heroid < m3.heroid
            JOIN matches
              ON m1.matchid = matches.matchid
        WHERE matches.cluster IN (".implode(",", $clusters).")
        GROUP BY m1.heroid, m2.heroid, m3.heroid
        HAVING match_count > ".$result["regions_data"][$region]['settings']['limiter_lower']."
        ORDER BY match_count DESC, winrate DESC;";
# limiting match count for hero pair to 3:
# 1 match = every possible pair
# 2 matches = may be a coincedence

if ($conn->multi_query($sql) === TRUE);# echo "[S] Requested data for HERO TRIPLETS.\n";
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();

for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
  $hero1_pickrate = $result["regions_data"][$region]['pickban'][$row[0]]['matches_picked'] / $result["regions_data"][$region]['main']['matches'];
  $hero2_pickrate = $result["regions_data"][$region]['pickban'][$row[1]]['matches_picked'] / $result["regions_data"][$region]['main']['matches'];
  $hero3_pickrate = $result["regions_data"][$region]['pickban'][$row[2]]['matches_picked'] / $result["regions_data"][$region]['main']['matches'];
  $expected_pair  = $hero1_pickrate * $hero2_pickrate * $hero3_pickrate * ($result["regions_data"][$region]['main']['matches']/3);

  $result["regions_data"][$region]["hero_trios"][] = [
    "heroid1" => $row[0],
    "heroid2" => $row[1],
    "heroid3" => $row[2],
    "matches" => $row[3],
    "winrate" => $row[4],
    "expectation" => $expected_pair
  ];
}

$query_res->free_result();

?>
