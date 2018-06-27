<?php
$result["pvp"] = array ();

$sql = "SELECT m1.playerid, m2.playerid, SUM(1) match_count, SUM(NOT matches.radiantWin XOR m1.isRadiant) player1_won, SUM(NOT matches.radiantWin XOR m1.isRadiant)/SUM(1) p1_winrate
    FROM matchlines m1
      JOIN matchlines m2
          ON m1.matchid = m2.matchid and m1.isRadiant <> m2.isRadiant and m1.playerid < m2.playerid
        JOIN matches
          ON m1.matchid = matches.matchid
    GROUP BY m1.playerid, m2.playerid;";

if ($conn->multi_query($sql) === TRUE) echo "[S] Requested data for PLAYER AGAINST PLAYER.\n";
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();

for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
  $result["pvp"][] = array (
    "playerid1" => $row[0],
    "playerid2" => $row[1],
    "matches" => $row[2],
    "p1won" => $row[3],
    "p1winrate" => $row[4]
  );
}

$query_res->free_result();

if ($lg_settings['ana']['pvp_matches']) {
  for ($i=0,$e=sizeof($result['pvp']); $i<$e; $i++) {
    $sql = "SELECT m1.matchid
        FROM matchlines m1
          JOIN matchlines m2
              ON m1.matchid = m2.matchid and m1.isRadiant <> m2.isRadiant
        WHERE m1.playerid = ".$result['pvp'][$i]['playerid1']." AND m2.playerid = ".$result['pvp'][$i]['playerid2'].";";

    if ($conn->multi_query($sql) === TRUE)  ;# echo "[S] Requested data for PLAYER AGAINST PLAYER.\n";
    else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

    $query_res = $conn->store_result();

    $result['pvp'][$i]['matchids'] = array();

    for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
      $result['pvp'][$i]['matchids'][] = $row[0];
    }

    $query_res->free_result();
  }
}
?>
