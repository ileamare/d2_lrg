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
  $result["regions_data"][$region]["players_parties_graph"][] = [
    "playerid1" => $row[0],
    "playerid2" => $row[1],
    "matches" => $row[3],
    "wins" => $row[2]
  ];
}

$query_res->free_result();
?>
