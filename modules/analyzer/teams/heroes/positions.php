<?php
$result["teams"][$id]["hero_positions"] = array ();

for ($core = 0; $core < 2; $core++) {
  for ($lane = 1; $lane > 0 && $lane < 6; $lane++) {
    if (!$core) { $lane = 0; }
    $result["teams"][$id]["hero_positions"][$core][$lane] = array();

    $sql = "SELECT
              am.heroid heroid,
              SUM(1) matches,
              SUM(NOT m.radiantWin XOR ml.isradiant)/SUM(1) winrate,
              SUM(ml.kills)/SUM(1) kills,
              SUM(ml.deaths)/SUM(1) deaths,
              SUM(ml.assists)/SUM(1) assists,
              SUM(ml.gpm)/SUM(1) gpm,
              SUM(ml.xpm)/SUM(1) xpm,
              SUM( ml.heal / (m.duration/60) )/SUM(1) avg_heal,
              SUM( ml.heroDamage / (m.duration/60) )/SUM(1) avg_hero_dmg,
              SUM( ml.towerDamage / (m.duration/60) )/SUM(1) avg_tower_dmg,
              SUM( am.damage_taken / (m.duration/60) )/SUM(1) avg_dmg_taken,
              SUM(am.stuns)/SUM(1) stuns,
              SUM(am.lh_at10)/SUM(1) lh_10,
              SUM(m.duration)/(SUM(1)*60) avg_duration
            FROM adv_matchlines am JOIN
              matchlines ml
                  ON am.matchid = ml.matchid AND am.heroid = ml.heroid
                JOIN matches m
                  ON m.matchid = am.matchid
                JOIN teams_matches
                  ON m.matchid = teams_matches.matchid AND teams_matches.is_radiant = ml.isradiant
                WHERE teams_matches.teamid = ".$id." AND ".
           ($core == 0 ? "am.isCore = 0"
          :"am.isCore = 1 AND am.lane = $lane")
          ." \nGROUP BY am.heroid
            ORDER BY matches DESC, winrate DESC;";

    if ($conn->multi_query($sql) === TRUE);# echo "[S] Requested data for HERO POSITIONS $core $lane.\n";
    else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

    $query_res = $conn->store_result();

    for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
      $result["teams"][$id]["hero_positions"][$core][$lane][] = array (
        "matches_s"=> $row[1],
        "winrate_s"=> $row[2],
        "kills"  => $row[3],
        "deaths" => $row[4],
        "assists"=> $row[5],
        "gpm"    => $row[6],
        "xpm"    => $row[7],
        "heal_per_min_s" => $row[8],
        "hero_damage_per_min_s" => $row[9],
        "tower_damage_per_min_s"=> $row[10],
        "taken_damage_per_min_s" => $row[11],
        "stuns" => $row[12],
        "lh_at10" => $row[13],
        "duration" => $row[14]
      );
    }

    $query_res->free_result();
    if (!$core) { break; }
  }
}
?>
