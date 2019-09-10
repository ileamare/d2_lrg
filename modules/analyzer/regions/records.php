<?php
# records
$result["regions_data"][$region]["records"] = [];

# gpm
$sql  = "SELECT \"gpm\" cap, matches.matchid, ml.gpm, ml.playerid, ml.heroid FROM matchlines ml
        JOIN matches on ml.matchid = matches.matchid
        WHERE matches.cluster IN (".implode(",", $clusters).")
        ORDER BY ml.gpm DESC LIMIT 1;";
# xpm
$sql .= "SELECT \"xpm\" cap, matches.matchid, ml.xpm, ml.playerid, ml.heroid FROM matchlines ml
        JOIN matches on ml.matchid = matches.matchid
        WHERE matches.cluster IN (".implode(",", $clusters).")
        ORDER BY ml.xpm DESC LIMIT 1;";
# kills
$sql .= "SELECT \"kills\" cap, matches.matchid, ml.kills, ml.playerid, ml.heroid FROM matchlines ml
        JOIN matches on ml.matchid = matches.matchid
        WHERE matches.cluster IN (".implode(",", $clusters).")
        ORDER BY ml.kills DESC LIMIT 1;";
# deaths
$sql .= "SELECT \"deaths\" cap, matches.matchid, ml.deaths, ml.playerid, ml.heroid FROM matchlines ml
        JOIN matches on ml.matchid = matches.matchid
        WHERE matches.cluster IN (".implode(",", $clusters).")
        ORDER BY ml.deaths DESC LIMIT 1;";
# assists
$sql .= "SELECT \"assists\" cap, matches.matchid, ml.assists, ml.playerid, ml.heroid FROM matchlines ml
        JOIN matches on ml.matchid = matches.matchid
        WHERE matches.cluster IN (".implode(",", $clusters).")
        ORDER BY ml.assists DESC LIMIT 1;";
# kda0
$sql .= "SELECT \"kda0\" cap, matches.matchid, ml.kills+ml.assists val, ml.playerid, ml.heroid FROM matchlines ml
        JOIN matches on ml.matchid = matches.matchid
        WHERE matches.cluster IN (".implode(",", $clusters).") AND ml.deaths = 0
        ORDER BY val DESC LIMIT 1;";
# kda1
$sql .= "SELECT \"kda1\" cap, matches.matchid, (ml.kills+ml.assists)/ml.deaths val, ml.playerid, ml.heroid FROM matchlines ml
        JOIN matches on ml.matchid = matches.matchid
        WHERE matches.cluster IN (".implode(",", $clusters).") AND ml.deaths > 0
        ORDER BY val DESC LIMIT 1;";
# networth
$sql .= "SELECT \"networth\" cap, matches.matchid, ml.networth, ml.playerid, ml.heroid FROM matchlines ml
        JOIN matches on ml.matchid = matches.matchid
        WHERE matches.cluster IN (".implode(",", $clusters).")
        ORDER BY ml.networth DESC LIMIT 1;";
# lasthits
$sql .= "SELECT \"lasthits\" cap, matches.matchid, ml.lasthits, ml.playerid, ml.heroid FROM matchlines ml
        JOIN matches on ml.matchid = matches.matchid
        WHERE matches.cluster IN (".implode(",", $clusters).")
        ORDER BY ml.lasthits DESC LIMIT 1;";
# hero damage
$sql .= "SELECT \"hero_damage\" cap, matches.matchid, ml.heroDamage, ml.playerid, ml.heroid FROM matchlines ml
        JOIN matches on ml.matchid = matches.matchid
        WHERE matches.cluster IN (".implode(",", $clusters).")
        ORDER BY ml.heroDamage DESC LIMIT 1;";
# tower damage
$sql .= "SELECT \"tower_damage\" cap, matches.matchid, ml.towerDamage, ml.playerid, ml.heroid FROM matchlines ml
        JOIN matches on ml.matchid = matches.matchid
        WHERE matches.cluster IN (".implode(",", $clusters).")
        ORDER BY ml.towerDamage DESC LIMIT 1;";
# heal
$sql .= "SELECT \"heal\" cap, matches.matchid, ml.heal, ml.playerid, ml.heroid FROM matchlines ml
        JOIN matches on ml.matchid = matches.matchid
        WHERE matches.cluster IN (".implode(",", $clusters).")
        ORDER BY ml.heal DESC LIMIT 1;";

# damage taken
$sql .= "SELECT \"damage_taken\" cap, matches.matchid, ml.damage_taken, ml.playerid, ml.heroid FROM adv_matchlines ml
        JOIN matches on ml.matchid = matches.matchid
        WHERE matches.cluster IN (".implode(",", $clusters).")
        ORDER BY ml.damage_taken DESC LIMIT 1;";
# lane efficiency
$sql .= "SELECT \"lane_efficiency\" cap, matches.matchid, ml.efficiency_at10, ml.playerid, ml.heroid FROM adv_matchlines ml
        JOIN matches on ml.matchid = matches.matchid
        WHERE matches.cluster IN (".implode(",", $clusters).")
        ORDER BY ml.efficiency_at10 DESC LIMIT 1;";
# wards
$sql .= "SELECT \"wards_placed\" cap, matches.matchid, ml.wards, ml.playerid, ml.heroid FROM adv_matchlines ml
        JOIN matches on ml.matchid = matches.matchid
        WHERE matches.cluster IN (".implode(",", $clusters).")
        ORDER BY ml.wards DESC LIMIT 1;";
# sentries
$sql .= "SELECT \"sentries_placed\" cap, matches.matchid, ml.sentries, ml.playerid, ml.heroid FROM adv_matchlines ml
        JOIN matches on ml.matchid = matches.matchid
        WHERE matches.cluster IN (".implode(",", $clusters).")
        ORDER BY ml.sentries DESC LIMIT 1;";
# wards destroyed
$sql .= "SELECT \"wards_destroyed\" cap, matches.matchid, ml.wards_destroyed, ml.playerid, ml.heroid FROM adv_matchlines ml
        JOIN matches on ml.matchid = matches.matchid
        WHERE matches.cluster IN (".implode(",", $clusters).")
        ORDER BY ml.wards_destroyed DESC LIMIT 1;";
# pings by player
$sql .= "SELECT \"pings\" cap, matches.matchid, ml.pings, ml.playerid, ml.heroid FROM adv_matchlines ml
        JOIN matches on ml.matchid = matches.matchid
        WHERE matches.cluster IN (".implode(",", $clusters).")
        ORDER BY ml.pings DESC LIMIT 1;";
# stuns
$sql .= "SELECT \"stuns\" cap, matches.matchid, ml.stuns, ml.playerid, ml.heroid FROM adv_matchlines ml
        JOIN matches on ml.matchid = matches.matchid
        WHERE matches.cluster IN (".implode(",", $clusters).")
        ORDER BY ml.stuns DESC LIMIT 1;";
# courier kills by player
$sql .= "SELECT \"couriers_killed_by_player\" cap, matches.matchid, ml.couriers_killed, ml.playerid, ml.heroid FROM adv_matchlines ml
        JOIN matches on ml.matchid = matches.matchid
        WHERE matches.cluster IN (".implode(",", $clusters).")
        ORDER BY ml.couriers_killed DESC LIMIT 1;";

# couriers killed in game
$sql .= "SELECT \"couriers_killed_in_game\" cap, matches.matchid, SUM(ml.couriers_killed) cours, 0 playerid, 0 heroid FROM adv_matchlines ml
        JOIN matches on ml.matchid = matches.matchid
        WHERE matches.cluster IN (".implode(",", $clusters).")
        GROUP BY ml.matchid
        ORDER BY cours DESC LIMIT 1;";
# roshans killed in game
$sql .= "SELECT \"roshans_killed_in_game\" cap, matches.matchid, SUM(ml.roshans_killed) roshs, 0 playerid, 0 heroid FROM adv_matchlines ml
        JOIN matches on ml.matchid = matches.matchid
        WHERE matches.cluster IN (".implode(",", $clusters).")
        GROUP BY ml.matchid
        ORDER BY roshs DESC LIMIT 1;";

# stomp
$sql .= "SELECT \"stomp\" cap, matchid, stomp, 0 playerid, 0 heroid FROM matches
          WHERE matches.cluster IN (".implode(",", $clusters).") ORDER BY stomp DESC LIMIT 1;";
# comeback
$sql .= "SELECT \"comeback\" cap, matchid, comeback, 0 playerid, 0 heroid FROM matches
          WHERE matches.cluster IN (".implode(",", $clusters).") ORDER BY comeback DESC LIMIT 1;";
# length
$sql .= "SELECT \"longest_match\" cap, matchid, duration/60, 0 playerid, 0 heroid FROM matches
          WHERE matches.cluster IN (".implode(",", $clusters).") ORDER BY duration DESC LIMIT 1;";
$sql .= "SELECT \"shortest_match\" cap, matchid, duration/60, 0 playerid, 0 heroid FROM matches
          WHERE matches.cluster IN (".implode(",", $clusters).") ORDER BY duration ASC LIMIT 1;";
# kills total
$sql .= "SELECT \"bloodbath\" cap, m.matchid, SUM(ml.kills) val, 0 playerid, 0 heroid
          FROM matches m JOIN matchlines ml ON m.matchid = ml.matchid
          WHERE m.cluster IN (".implode(",", $clusters).")
          GROUP BY m.matchid ORDER BY val DESC LIMIT 1;";

# widest hero pool
$sql .= "SELECT \"widest_hero_pool\" cap, 0 matchid, COUNT(distinct ml.heroid) val, ml.playerid, 0 heroid FROM matchlines ml
        JOIN matches on ml.matchid = matches.matchid
        WHERE matches.cluster IN (".implode(",", $clusters).")
        GROUP BY ml.playerid
        ORDER BY val DESC LIMIT 1;";
# smallest hero pool
$sql .= "SELECT \"smallest_hero_pool\" cap, 0 matchid, COUNT(distinct ml.heroid) val, ml.playerid, 0 heroid FROM matchlines ml
        JOIN matches on ml.matchid = matches.matchid
        WHERE matches.cluster IN (".implode(",", $clusters).")
        GROUP BY ml.playerid
        ORDER BY val ASC LIMIT 1;";

if ($lg_settings['main']['teams']) {
   # widest hero pool team
   $sql .= "SELECT \"widest_hero_pool_team\" cap, 0 matchid, COUNT(distinct ml.heroid) val, teams_matches.teamid, 0 heroid
            FROM matchlines ml JOIN teams_matches ON ml.matchid = teams_matches.matchid AND teams_matches.is_radiant = ml.isRadiant
            JOIN matches on ml.matchid = matches.matchid
            WHERE matches.cluster IN (".implode(",", $clusters).")
            GROUP BY teams_matches.teamid ORDER BY val DESC LIMIT 1;";
   # smallest hero pool team
   $sql .= "SELECT \"smallest_hero_pool_team\" cap, 0 matchid, COUNT(distinct ml.heroid) val, teams_matches.teamid, 0 heroid
            FROM matchlines ml JOIN teams_matches ON ml.matchid = teams_matches.matchid AND teams_matches.is_radiant = ml.isRadiant
            JOIN matches on ml.matchid = matches.matchid
            WHERE matches.cluster IN (".implode(",", $clusters).")
            GROUP BY teams_matches.teamid ORDER BY val ASC LIMIT 1;";
}


#   playerid and heroid = 0 for matchrecords

if ($conn->multi_query($sql) === TRUE);# echo "[S] Requested data for RECORDS.\n";
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

do {
  $query_res = $conn->store_result();

  $row = $query_res->fetch_row();

  $result["regions_data"][$region]["records"][$row[0]] = [
    "matchid"  => $row[1],
    "value"    => $row[2],
    "playerid" => $row[3],
    "heroid"   => $row[4]
  ];

  $query_res->free_result();
} while($conn->next_result());

?>
