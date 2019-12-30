<?php 

function rg_query_player_pairs(&$conn, &$psummary, $matches_total, $limiter = 0, $cluster = null) {
  $res = [];

  $sql = "SELECT fm1.playerid, fm2.playerid,
      COUNT(distinct fm1.matchid) match_count,
      SUM(NOT matches.radiantWin XOR fm1.isRadiant)/SUM(1) winrate,
      SUM(fm1.lane = fm2.lane)/SUM(1) lane_rate
    FROM
      ( select m1.matchid, m1.playerid, am1.lane, m1.isRadiant
        from matchlines m1 JOIN adv_matchlines am1
        ON m1.matchid = am1.matchid AND m1.playerid = am1.playerid ) fm1
    JOIN
      ( select m2.matchid, m2.playerid, am2.lane, m2.isRadiant
        from matchlines m2 JOIN adv_matchlines am2
        ON m2.matchid = am2.matchid AND m2.playerid = am2.playerid ) fm2
    ON fm1.matchid = fm2.matchid and fm1.isRadiant = fm2.isRadiant and fm1.playerid < fm2.playerid
    JOIN matches ON fm1.matchid = matches.matchid ".
    ($cluster !== null ? " WHERE matches.cluster IN (".implode(",", $cluster).") " : "").
  " GROUP BY fm1.playerid, fm2.playerid
    HAVING match_count > $limiter
    ORDER BY match_count DESC, winrate DESC;";

  if ($conn->multi_query($sql) === TRUE) echo "[S] Requested data for PLAYER PAIRS.\n";
  else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

  $query_res = $conn->store_result();

  for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
    $p1_matchrate = $psummary[$row[0]]['matches_s'] / $matches_total;
    $p2_matchrate = $psummary[$row[1]]['matches_s'] / $matches_total;
    $expected_pair  = $p1_matchrate * $p2_matchrate * ($matches_total/2);

    $wr_diff = ($psummary[$row[0]]['winrate_s'] + $psummary[$row[1]]['winrate_s'])/2 - $row[3];

    $res[] = [
      "playerid1" => $row[0],
      "playerid2" => $row[1],
      "matches" => $row[2],
      "winrate" => $row[3],
      "expectation" => $expected_pair,
      "lane_rate" => $row[4],
      "wr_diff" => $wr_diff
    ];
  }

  $query_res->free_result();

  return $res;
}

function rg_query_player_pairs_matches(&$conn, &$context) {
  $result = [];

  foreach($context as $pair) {
    $result[$pair['playerid1']."-".$pair['playerid2']] = [];

    $sql = "SELECT m1.matchid
            FROM matchlines m1 JOIN matchlines m2
              ON m1.matchid = m2.matchid and m1.isRadiant = m2.isRadiant and m1.playerid < m2.playerid
              JOIN matches ON m1.matchid = matches.matchid
            WHERE m1.playerid = ".$pair['playerid1']." AND m2.playerid = ".$pair['playerid2'].";";

    if ($conn->multi_query($sql) === TRUE) ;#echo "[S] Requested data for PLAYER PAIRS.\n";
    else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

    $query_res = $conn->store_result();

    for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
      $result[$pair['playerid1']."-".$pair['playerid2']][] = $row[0];
    }

    $query_res->free_result();
  }

  return $result;
}