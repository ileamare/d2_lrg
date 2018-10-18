<?php
# records
$result["records"] = array();

# gpm
$sql  = "SELECT \"gpm\" cap, matchid, gpm, playerid, heroid FROM matchlines ORDER BY gpm DESC;";
# xpm
$sql .= "SELECT \"xpm\" cap, matchid, xpm, playerid, heroid FROM matchlines ORDER BY xpm DESC;";
# kills
$sql .= "SELECT \"kills\" cap, matchid, kills, playerid, heroid FROM matchlines ORDER BY kills DESC;";
# deaths
$sql .= "SELECT \"deaths\" cap, matchid, deaths, playerid, heroid FROM matchlines ORDER BY deaths DESC;";
# assists
$sql .= "SELECT \"assists\" cap, matchid, assists, playerid, heroid FROM matchlines ORDER BY assists DESC;";
# kda0
$sql .= "SELECT \"kda0\" cap, matchid, kills+assists val, playerid, heroid FROM matchlines WHERE deaths = 0 ORDER BY val DESC;";
# kda1
$sql .= "SELECT \"kda1\" cap, matchid, (kills+assists)/deaths val, playerid, heroid FROM matchlines WHERE deaths > 0 ORDER BY val DESC;";
# gold earned
$sql .= "SELECT \"gold_earned\" cap, matchid, networth, playerid, heroid FROM matchlines ORDER BY networth DESC;";
# lasthits
$sql .= "SELECT \"lasthits\" cap, matchid, lastHits, playerid, heroid FROM matchlines ORDER BY lastHits DESC;";
# hero damage
$sql .= "SELECT \"hero_damage\" cap, matchid, heroDamage, playerid, heroid FROM matchlines ORDER BY heroDamage DESC;";
# tower damage
$sql .= "SELECT \"tower_damage\" cap, matchid, towerDamage, playerid, heroid FROM matchlines ORDER BY towerDamage DESC;";
# heal
$sql .= "SELECT \"heal\" cap, matchid, heal, playerid, heroid FROM matchlines ORDER BY heal DESC;";

# damage taken
$sql .= "SELECT \"damage_taken\" cap, matchid, damage_taken, playerid, heroid FROM adv_matchlines ORDER BY damage_taken DESC;";
# lane efficiency
$sql .= "SELECT \"lane_efficiency\" cap, matchid, efficiency_at10, playerid, heroid FROM adv_matchlines ORDER BY efficiency_at10 DESC;";
# wards
$sql .= "SELECT \"wards_placed\" cap, matchid, wards, playerid, heroid FROM adv_matchlines ORDER BY wards DESC;";
# sentries
$sql .= "SELECT \"sentries_placed\" cap, matchid, sentries, playerid, heroid FROM adv_matchlines ORDER BY sentries DESC;";
# teamfight participation
$sql .= "SELECT \"teamfight_participation\" cap, matchid, teamfight_part, playerid, heroid FROM adv_matchlines ORDER BY teamfight_part DESC;";
# wards destroyed
$sql .= "SELECT \"wards_destroyed\" cap, matchid, wards_destroyed, playerid, heroid FROM adv_matchlines ORDER BY wards_destroyed DESC;";
# pings by player
$sql .= "SELECT \"pings\" cap, matchid, pings, playerid, heroid FROM adv_matchlines ORDER BY pings DESC;";
# stuns
$sql .= "SELECT \"stuns\" cap, matchid, stuns, playerid, heroid FROM adv_matchlines ORDER BY stuns DESC;";
# courier kills by player
$sql .= "SELECT \"couriers_killed_by_player\" cap, matchid, couriers_killed, playerid, heroid FROM adv_matchlines ORDER BY couriers_killed DESC;";

# couriers killed in game
$sql .= "SELECT \"couriers_killed_in_game\" cap, matchid, SUM(couriers_killed) cours, 0 playerid, 0 heroid FROM adv_matchlines GROUP BY matchid ORDER BY cours DESC;";
# roshans killed in game
$sql .= "SELECT \"roshans_killed_in_game\" cap, matchid, SUM(roshans_killed) roshs, 0 playerid, 0 heroid FROM adv_matchlines GROUP BY matchid ORDER BY roshs  DESC;";

# stomp
$sql .= "SELECT \"stomp\" cap, matchid, stomp, 0 playerid, 0 heroid FROM matches ORDER BY stomp DESC;";
# comeback
$sql .= "SELECT \"comeback\" cap, matchid, comeback, 0 playerid, 0 heroid FROM matches ORDER BY comeback DESC;";
# length
$sql .= "SELECT \"longest_match\" cap, matchid, duration/60, 0 playerid, 0 heroid FROM matches ORDER BY duration DESC;";
$sql .= "SELECT \"shortest_match\" cap, matchid, duration/60, 0 playerid, 0 heroid FROM matches ORDER BY duration ASC;";
# kills total
$sql .= "SELECT \"bloodbath\" cap, m.matchid, SUM(ml.kills) val, 0 playerid, 0 heroid
          FROM matches m JOIN matchlines ml ON m.matchid = ml.matchid GROUP BY m.matchid ORDER BY val DESC;";

# widest hero pool
$sql .= "SELECT \"widest_hero_pool\" cap, 0 matchid, COUNT(distinct heroid) val, playerid, 0 heroid FROM matchlines GROUP BY playerid ORDER BY val DESC;";
# smallest hero pool
$sql .= "SELECT \"smallest_hero_pool\" cap, 0 matchid, COUNT(distinct heroid) val, playerid, 0 heroid FROM matchlines GROUP BY playerid ORDER BY val;";

if ($lg_settings['main']['teams']) {
   # widest hero pool team
   $sql .= "SELECT \"widest_hero_pool_team\" cap, 0 matchid, COUNT(distinct heroid) val, teams_matches.teamid, 0 heroid
            FROM matchlines JOIN teams_matches ON matchlines.matchid = teams_matches.matchid AND teams_matches.is_radiant = matchlines.isRadiant
            GROUP BY teams_matches.teamid ORDER BY val DESC;";
   # smallest hero pool team
   $sql .= "SELECT \"smallest_hero_pool_team\" cap, 0 matchid, COUNT(distinct heroid) val, teams_matches.teamid, 0 heroid
            FROM matchlines JOIN teams_matches ON matchlines.matchid = teams_matches.matchid AND teams_matches.is_radiant = matchlines.isRadiant
            GROUP BY teams_matches.teamid ORDER BY val ASC;";
}


#   playerid and heroid = 0 for matchrecords

if ($conn->multi_query($sql) === TRUE) echo "[S] Requested data for RECORDS.\n";
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

do {
  $query_res = $conn->store_result();

  $row = $query_res->fetch_row();

  $result["records"][$row[0]] = array (
    "matchid"  => $row[1],
    "value"    => $row[2],
    "playerid" => $row[3],
    "heroid"   => $row[4]
  );

  $query_res->free_result();
} while($conn->next_result());

?>
