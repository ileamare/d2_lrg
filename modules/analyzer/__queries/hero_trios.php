<?php

function rg_query_hero_trios(&$pickban, $matches_total, $limiter = 0, $cluster = null, $team = null) {
  $result = [];

  $wheres = [];

  if ($team !== null) $wheres[] = "teams_matches.teamid = ".$id;
  if ($cluster !== null) $wheres[] = "matches.cluster IN (".implode(",", $cluster).")";

  $sql = "SELECT m1.heroid, m2.heroid, m3.heroid, SUM(1) match_count, SUM(NOT matches.radiantWin XOR m1.isRadiant)/SUM(1) winrate
          FROM matchlines m1
            JOIN matchlines m2
                ON m1.matchid = m2.matchid and m1.isRadiant = m2.isRadiant and m1.heroid < m2.heroid
              JOIN matchlines m3
                ON m1.matchid = m3.matchid and m1.isRadiant = m3.isRadiant and m2.heroid < m3.heroid
              JOIN matches
                ON m1.matchid = matches.matchid ".
              ($team === null ? "" : " JOIN teams_matches
                ON m1.matchid = teams_matches.matchid AND teams_matches.is_radiant = m1.isRadiant ").
          (!empty($wheres) ? "WHERE ".implode(" AND ", $cluster) : "").
        " GROUP BY m1.heroid, m2.heroid, m3.heroid
          HAVING match_count > $limiter
          ORDER BY match_count DESC, winrate DESC;";
  # limiting match count for hero pair to 3:
  # 1 match = every possible pair
  # 2 matches = may be a coincedence

  if ($conn->multi_query($sql) === TRUE) echo "[S] Requested data for HERO TRIPLETS.\n";
  else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

  $query_res = $conn->store_result();

  for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
    $hero1_pickrate = $pickban[$row[0]]['matches_picked'] / $matches_total;
    $hero2_pickrate = $pickban[$row[1]]['matches_picked'] / $matches_total;
    $hero3_pickrate = $pickban[$row[2]]['matches_picked'] / $matches_total;
    $expected_pair  = $hero1_pickrate * $hero2_pickrate * $hero3_pickrate * ($matches_total/3);

    $result[] = array (
      "heroid1" => $row[0],
      "heroid2" => $row[1],
      "heroid3" => $row[2],
      "matches" => $row[3],
      "winrate" => $row[4],
      "expectation" => $expected_pair
    );
  }

  $query_res->free_result();

  return $result;
}

function rg_query_hero_trios_matches(&$trios) {
  $result = [];

  foreach($trios as $pair) {
    $sql = "SELECT m1.matchid
            FROM matchlines m1
              JOIN matchlines m2
                  ON m1.matchid = m2.matchid and m1.isRadiant = m2.isRadiant and m1.heroid < m2.heroid
                JOIN matchlines m3
                  ON m1.matchid = m3.matchid and m1.isRadiant = m3.isRadiant and m2.heroid < m3.heroid
            WHERE m1.heroid = ".$pair['heroid1']." AND m2.heroid = ".$pair['heroid2']." AND m3.heroid = ".$pair['heroid3'].";";

    $result[$pair['heroid1']."-".$pair['heroid2']."-".$pair['heroid3']] = [];

    if ($conn->multi_query($sql) === TRUE) ;#echo "[S] Requested data for HERO TRIPLETS MATCHES.\n";
    else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

    $query_res = $conn->store_result();

    for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
      $result[$pair['heroid1']."-".$pair['heroid2']."-".$pair['heroid3']][] = $row[0];
    }

    $query_res->free_result();
  }

  return $result;
}