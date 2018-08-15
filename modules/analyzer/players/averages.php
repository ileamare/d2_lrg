<?php
# kills
$sql  = "SELECT \"kills\", playerid, SUM(kills)/SUM(1) value, SUM(1) mtch FROM matchlines GROUP BY playerid HAVING $limiter_lower < mtch ORDER BY value DESC;";
# least deaths
$sql .= "SELECT \"least_deaths\", playerid, SUM(deaths)/SUM(1) value, SUM(1) mtch FROM matchlines GROUP BY playerid HAVING $limiter_lower < mtch ORDER BY value ASC;";
# most deaths
$sql .= "SELECT \"most_deaths\", playerid, SUM(deaths)/SUM(1) value, SUM(1) mtch FROM matchlines GROUP BY playerid HAVING $limiter_lower < mtch ORDER BY value DESC;";
# assists
$sql .= "SELECT \"assists\", playerid, SUM(assists)/SUM(1) value, SUM(1) mtch FROM matchlines GROUP BY playerid HAVING $limiter_lower < mtch ORDER BY value DESC;";
# kda
$sql .= "SELECT \"kda\", playerid, (SUM(kills)+SUM(assists))/SUM(deaths) value, SUM(1) mtch  FROM matchlines GROUP BY playerid HAVING $limiter_lower < mtch ORDER BY value DESC;";

# gpm
$sql .= "SELECT \"gpm\", playerid, SUM(gpm)/SUM(1) value, SUM(1) mtch FROM matchlines GROUP BY playerid HAVING $limiter_lower < mtch ORDER BY value DESC;";
# xpm
$sql .= "SELECT \"xpm\", playerid, SUM(xpm)/SUM(1) value, SUM(1) mtch FROM matchlines GROUP BY playerid HAVING $limiter_lower < mtch ORDER BY value DESC;";
# lane efficiency
$sql .= "SELECT \"lane_efficiency\", playerid, SUM(efficiency_at10)/SUM(1) value, SUM(1) mtch
          FROM adv_matchlines GROUP BY playerid HAVING $limiter_lower < mtch ORDER BY value DESC;";
# last hits per min
$sql .= "SELECT \"lasthits_per_min\", matchlines.playerid heroid, SUM(matchlines.lastHits/(matches.duration/60))/SUM(1)
           value, SUM(1) mtch  FROM matchlines JOIN matches ON matchlines.matchid = matches.matchid GROUP BY playerid HAVING $limiter_lower < mtch ORDER BY value DESC;";
# last hits
$sql .= "SELECT \"lasthits\", matchlines.playerid playerid, SUM(matchlines.lastHits)/SUM(1)
          value, SUM(1) mtch  FROM matchlines JOIN matches ON matchlines.matchid = matches.matchid GROUP BY playerid HAVING $limiter_lower < mtch ORDER BY value DESC;";
# denies
$sql .= "SELECT \"denies\", playerid, SUM(denies)/SUM(1) value, SUM(1) mtch FROM matchlines GROUP BY playerid HAVING $limiter_lower < mtch ORDER BY value DESC;";

# hero damage / minute
$sql .= "SELECT \"hero_damage_per_min\", matchlines.playerid playerid, SUM(matchlines.heroDamage/(matches.duration/60))/SUM(1)
           value, SUM(1) mtch FROM matchlines JOIN matches ON matchlines.matchid = matches.matchid GROUP BY playerid HAVING $limiter_lower < mtch ORDER BY value DESC;";
# tower damage / minute
$sql .= "SELECT \"tower_damage_per_min\", matchlines.playerid playerid, SUM(matchlines.towerDamage/(matches.duration/60))/SUM(1)
           value, SUM(1) mtch FROM matchlines JOIN matches ON matchlines.matchid = matches.matchid GROUP BY playerid HAVING $limiter_lower < mtch ORDER BY value DESC;";
# taken damage / minute
$sql .= "SELECT \"taken_damage_per_min\", adv_matchlines.playerid playerid, SUM(adv_matchlines.damage_taken/(matches.duration/60))/SUM(1)
           value, SUM(1) mtch FROM adv_matchlines JOIN matches ON adv_matchlines.matchid = matches.matchid
           GROUP BY playerid HAVING $limiter_lower < mtch ORDER BY value DESC;";
# heal / minute
$sql .= "SELECT \"heal_per_min\", matchlines.playerid playerid, SUM(matchlines.heal/(matches.duration/60))/SUM(1)
           value, SUM(1) mtch FROM matchlines JOIN matches ON matchlines.matchid = matches.matchid GROUP BY playerid HAVING $limiter_lower < mtch ORDER BY value DESC;";

# stuns
$sql .= "SELECT \"stuns\", playerid, SUM(stuns)/SUM(1) value, SUM(1) mtch FROM adv_matchlines GROUP BY playerid HAVING $limiter_lower < mtch ORDER BY value DESC;";
# courier kills
$sql .= "SELECT \"courier_kills\", playerid, SUM(couriers_killed)/SUM(1) value, SUM(1) mtch FROM adv_matchlines
          GROUP BY playerid HAVING $limiter_lower < mtch ORDER BY value DESC;";
# roshan kills by hero's team
if ($lg_settings['main']['teams'])
  $sql .= "SELECT \"roshan_kills_by_team\", teams_matches.teamid, SUM(rs.rshs)/SUM(1) value, SUM(1) mtch FROM matchlines JOIN (
    SELECT matchid, SUM(roshans_killed) rshs FROM adv_matchlines GROUP BY matchid
  ) rs ON matchlines.matchid = rs.matchid
  JOIN teams_matches ON matchlines.matchid = teams_matches.matchid and matchlines.isradiant = teams_matches.is_radiant
  GROUP BY teams_matches.teamid HAVING $limiter_lower < mtch ORDER BY value DESC;";
else
  $sql .= "SELECT \"roshan_kills_with_team\", playerid, SUM(rs.rshs)/SUM(1) value, SUM(1) mtch FROM matchlines JOIN (
    SELECT matchid, SUM(roshans_killed) rshs FROM adv_matchlines GROUP BY matchid
  ) rs ON matchlines.matchid = rs.matchid
  GROUP BY playerid HAVING $limiter_lower < mtch ORDER BY value DESC;";
# wards destroyed
$sql .= "SELECT \"wards_destroyed\", playerid, SUM(wards_destroyed)/SUM(1) value, SUM(1) mtch FROM adv_matchlines
        GROUP BY playerid HAVING $limiter_lower < mtch ORDER BY value DESC;";

# longest killstreak
$sql .= "SELECT \"longest_killstreak_in_match\", playerid, SUM(streak)/SUM(1) value, SUM(1) mtch FROM adv_matchlines
          GROUP BY playerid HAVING $limiter_lower < mtch ORDER BY value DESC;";
# stacks
$sql .= "SELECT \"stacks\", playerid, SUM(stacks)/SUM(1) value, SUM(1) mtch FROM adv_matchlines GROUP BY playerid ORDER BY value DESC;";
# wards
$sql .= "SELECT \"wards_placed\", playerid, SUM(wards)/SUM(1) value, SUM(1) mtch FROM adv_matchlines GROUP BY playerid ORDER BY value DESC;";
# pings per minute
$sql .= "SELECT \"pings\", adv_matchlines.playerid playerid, SUM(adv_matchlines.pings/(matches.duration/60))/SUM(1)
           value, SUM(1) mtch FROM adv_matchlines JOIN matches ON adv_matchlines.matchid = matches.matchid
           GROUP BY playerid HAVING $limiter_lower < mtch ORDER BY value DESC;";
# hero pool size
$sql .= "SELECT \"hero_pool\", playerid, COUNT(DISTINCT heroid) value, SUM(1) mtch FROM matchlines GROUP BY playerid ORDER BY value DESC;";
# plyer diversity
$sql .= "SELECT \"diversity\", playerid, (COUNT(DISTINCT heroid)/mhpt.mhp) * (COUNT(DISTINCT heroid)/COUNT(DISTINCT matchid)) value, SUM(1) mtch, COUNT(DISTINCT matchid) matches
          FROM matchlines join ( select max(heropool) mhp from
              ( select COUNT(DISTINCT heroid) heropool, playerid from matchlines group by playerid ) _hp
            ) mhpt
          GROUP BY playerid HAVING $limiter_lower < mtch ORDER BY value DESC;";

$result["averages_players"] = array();

if ($conn->multi_query($sql) === TRUE) echo "[S] Requested data for AVERAGES PLAYERS.\n";
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

do {
 $query_res = $conn->store_result();

 $row = $query_res->fetch_row();
 $result["averages_players"][$row[0]] = array();

 for ($i=0; $i<$lg_settings['ana']['avg_limit'] && $row != null; $i++, $row = $query_res->fetch_row()) {
     $result["averages_players"][$row[0]][$i] = array (
     "playerid" => $row[1],
     "value"  => $row[2]
   );
 }

 $query_res->free_result();

} while($conn->next_result());
?>
