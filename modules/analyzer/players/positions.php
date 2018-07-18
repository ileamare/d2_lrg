<?php
$result["player_positions"] = array();
echo "[S] Requested data for PLAYER POSITIONS.\n";

for ($core = 0; $core < 2; $core++) {
  for ($lane = 1; $lane < 6 && $lane > 0; $lane++) {
    if (!$core) { $lane = 0; }
    $result["player_positions"][$core][$lane] = array();

    $sql = "SELECT
              am.playerid playerid,
              SUM(1) matches,
              SUM(NOT m.radiantWin XOR ml.isradiant)/SUM(1) winrate,
              SUM(ml.gpm)/SUM(1) gpm,
              SUM(ml.xpm)/SUM(1) xpm,
              SUM( ml.heal / (m.duration/60) )/SUM(1) avg_heal,
              SUM( ml.heroDamage / (m.duration/60) )/SUM(1) avg_hero_dmg,
              SUM( ml.towerDamage / (m.duration/60) )/SUM(1) avg_tower_dmg,
              SUM( am.damage_taken / (m.duration/60) )/SUM(1) avg_dmg_taken,
              SUM(am.stuns)/SUM(1) stuns,
              SUM(am.lh_at10)/SUM(1) lh_10,
              SUM(ml.denies)/SUM(1) denies,
              SUM(m.duration)/(SUM(1)*60) avg_duration,
              (SUM(ml.kills)+SUM(ml.assists))/(SUM(ml.deaths)) kills,
              COUNT(DISTINCT ml.heroid) heropool,
              (COUNT(DISTINCT ml.heroid)/mhpt.mhp)*(COUNT(DISTINCT ml.heroid)/SUM(1)) diversity,
              SUM(ml.lasthits)/(SUM(m.duration)/60) lh
            FROM adv_matchlines am JOIN
              matchlines ml
                  ON am.matchid = ml.matchid AND am.playerid = ml.playerid
                JOIN matches m
                  ON m.matchid = am.matchid
                join ( select max(heropool) mhp from
                  ( select COUNT(DISTINCT heroid) heropool, playerid from matchlines group by playerid ) _hp
                ) mhpt ".
           ($core == 0 ? "WHERE am.isCore = 0"
          :"WHERE am.isCore = 1 AND am.lane = $lane")
          ." GROUP BY am.playerid
            ORDER BY matches DESC, winrate DESC;";
    if ($conn->multi_query($sql) === TRUE);# echo "[S] Requested data for PLAYER POSITIONS $core $lane.\n";
    else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

    $query_res = $conn->store_result();

    for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
      $result["player_positions"][$core][$lane][] = array (
        "playerid" => $row[0],
        "matches_s"=> $row[1],
        "winrate_s"=> $row[2],
        "kda" => $row[13],
        "hero_pool" => $row[14],
        "diversity" => $row[15],
        "gpm"  => $row[3],
        "xpm" => $row[4],
        "heal_per_min_s" => $row[5],
        "hero_damage_per_min_s" => $row[6],
        "tower_damage_per_min_s"=> $row[7],
        "taken_damage_per_min_s" => $row[8],
        "stuns" => $row[9],
        "lh_at10" => $row[10],
        "lasthits_per_min_s" => $row[16],
        "denies_s" => $row[11],
        "duration" => $row[12]
      );
    }

    $query_res->free_result();

    if($lg_settings['ana']['player_positions_matches']) {
      $result["player_positions_matches"][$core][$lane] = array();

      foreach($result["player_positions"][$core][$lane] as $playerline) {
        $result["player_positions_matches"][$core][$lane][$playerline['playerid']] = array();
        $sql = "SELECT matchid
                FROM adv_matchlines WHERE ".
               ($core == 0 ? "isCore = 0" : "isCore = 1 AND lane = $lane")
              ." AND playerid = ".$playerline['playerid'].";";

        if ($conn->multi_query($sql) === TRUE);
        else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

        $query_res = $conn->store_result();

        for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
          $result["player_positions_matches"][$core][$lane][$playerline['playerid']][] = $row[0];
        }

        $query_res->free_result();
      }
    }

    if (!$core) { break; }
  }
}
?>
