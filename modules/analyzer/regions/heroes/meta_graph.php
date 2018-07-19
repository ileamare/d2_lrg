<?php
$result["regions_data"][$region]["heroes_meta_graph"] = [];

$sql = "SELECT m1.heroid, m2.heroid, COUNT(distinct m1.matchid) match_count, SUM(NOT matches.radiantWin XOR m1.isRadiant) winrate
        FROM matchlines m1 JOIN matchlines m2
          ON m1.matchid = m2.matchid and m1.isRadiant = m2.isRadiant and m1.heroid < m2.heroid
          JOIN matches ON m1.matchid = matches.matchid
        WHERE matches.cluster IN (".implode(",", $clusters).")
        GROUP BY m1.heroid, m2.heroid
        HAVING match_count > ".$result["regions_data"][$region]['settings']['limiter_graph']."
        ORDER BY match_count DESC, winrate DESC;";
# WARNING: big amount of matches may send client browser to a long trip

if ($conn->multi_query($sql) === TRUE);# echo "[S] Requested data for FULL HERO PAIRS.\n";
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();

for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
  $result["regions_data"][$region]["heroes_meta_graph"][] = [
    "heroid1" => $row[0],
    "heroid2" => $row[1],
    "matches" => $row[2],
    "wins" => $row[3]
  ];
}

$query_res->free_result();
?>
