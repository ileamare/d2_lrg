<?php
$result["regions_data"][$region]["player_lane_combos"] = [];

$sql = "SELECT fm1.playerid, fm2.playerid,
          COUNT(distinct fm1.matchid) match_count,
          SUM(NOT matches.radiantWin XOR fm1.isRadiant)/SUM(1) winrate,
          fm1.lane lane
        FROM
          ( select m1.matchid, m1.playerid, am1.lane, m1.isRadiant
            from matchlines m1 JOIN adv_matchlines am1
            ON m1.matchid = am1.matchid AND m1.playerid = am1.playerid ) fm1
        JOIN
          ( select m2.matchid, m2.playerid, am2.lane, m2.isRadiant
            from matchlines m2 JOIN adv_matchlines am2
            ON m2.matchid = am2.matchid AND m2.playerid = am2.playerid ) fm2
        ON fm1.matchid = fm2.matchid and fm1.isRadiant = fm2.isRadiant and fm1.playerid < fm2.playerid
        JOIN matches ON fm1.matchid = matches.matchid
        WHERE fm1.lane = fm2.lane
        AND matches.cluster IN (".implode(",", $clusters).")
        GROUP BY fm1.playerid, fm2.playerid
        HAVING match_count > ".$result["regions_data"][$region]['settings']['limiter_lower']."
        ORDER BY match_count DESC, winrate DESC;";

if ($conn->multi_query($sql) === TRUE);# echo "[S] Requested data for PLAYER LANE COMBOS.\n";
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();

for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
  $result["regions_data"][$region]["player_lane_combos"][] = [
    "playerid1" => $row[0],
    "playerid2" => $row[1],
    "matches" => $row[2],
    "winrate" => $row[3],
    "lane" => $row[4]
  ];
}

$query_res->free_result();

?>
