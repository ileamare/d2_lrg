<?php
$result["tvt"] = array ();

$sql = "SELECT m1.teamid, m2.teamid, SUM(1) match_count, SUM(NOT matches.radiantWin XOR m1.is_radiant) team1_won
    FROM teams_matches m1
      JOIN teams_matches m2
          ON m1.matchid = m2.matchid and m1.is_radiant <> m2.is_radiant and m1.teamid < m2.teamid
        JOIN matches
          ON m1.matchid = matches.matchid
    GROUP BY m1.teamid, m2.teamid;";

if ($conn->multi_query($sql) === TRUE) echo "[S] Requested data for TEAM VS TEAM.\n";
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();

for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
  $result["tvt"][] = array (
    "teamid1" => $row[0],
    "teamid2" => $row[1],
    "matches" => $row[2],
    "t1won" => $row[3]
  );
}

$query_res->free_result();
?>
