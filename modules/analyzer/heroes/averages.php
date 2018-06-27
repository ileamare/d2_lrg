<?php
# kills
$sql  = "SELECT \"kills\", heroid, SUM(kills)/SUM(1) value, SUM(1) mtch FROM matchlines GROUP BY heroid HAVING $limiter_lower < mtch ORDER BY value DESC;";
# least deaths
$sql .= "SELECT \"least_deaths\", heroid, SUM(deaths)/SUM(1) value, SUM(1) mtch  FROM matchlines GROUP BY heroid HAVING $limiter_lower < mtch ORDER BY value ASC;";
# most deaths
$sql .= "SELECT \"most_deaths\", heroid, SUM(deaths)/SUM(1) value, SUM(1) mtch  FROM matchlines GROUP BY heroid HAVING $limiter_lower < mtch ORDER BY value DESC;";
# assists
$sql .= "SELECT \"assists\", heroid, SUM(assists)/SUM(1) value, SUM(1) mtch  FROM matchlines GROUP BY heroid HAVING $limiter_lower < mtch ORDER BY value DESC;";
# kda
$sql .= "SELECT \"kda\", heroid, (SUM(kills)+SUM(assists))/SUM(deaths) value, SUM(1) mtch  FROM matchlines GROUP BY heroid HAVING $limiter_lower < mtch ORDER BY value DESC;";

# gpm
$sql .= "SELECT \"gpm\", heroid, SUM(gpm)/SUM(1) value, SUM(1) mtch  FROM matchlines GROUP BY heroid HAVING $limiter_lower < mtch ORDER BY value DESC;";
# xpm
$sql .= "SELECT \"xpm\", heroid, SUM(xpm)/SUM(1) value, SUM(1) mtch  FROM matchlines GROUP BY heroid HAVING $limiter_lower < mtch ORDER BY value DESC;";
# last hits per min
$sql .= "SELECT \"lasthits_per_min\", matchlines.heroid heroid, SUM(matchlines.lastHits/(matches.duration/60))/SUM(1)
           value, SUM(1) mtch  FROM matchlines JOIN matches ON matchlines.matchid = matches.matchid GROUP BY heroid HAVING $limiter_lower < mtch ORDER BY value DESC;";
# denies
$sql .= "SELECT \"denies\", heroid, SUM(denies)/SUM(1) value, SUM(1) mtch  FROM matchlines GROUP BY heroid HAVING $limiter_lower < mtch ORDER BY value DESC;";


# stuns
$sql .= "SELECT \"stuns\", heroid, SUM(stuns)/SUM(1) value, SUM(1) mtch  FROM adv_matchlines GROUP BY heroid HAVING $limiter_lower < mtch ORDER BY value DESC;";
# stacks
$sql .= "SELECT \"stacks\", heroid, SUM(stacks)/SUM(1) value, SUM(1) mtch  FROM adv_matchlines GROUP BY heroid HAVING $limiter_lower < mtch ORDER BY value DESC;";
# courier kills
$sql .= "SELECT \"courier_kills\", heroid, SUM(couriers_killed)/SUM(1) value, SUM(1) mtch FROM adv_matchlines
          GROUP BY heroid HAVING $limiter_lower < mtch ORDER BY value DESC;";
# roshan kills by hero's team
$sql .= "SELECT \"roshan_kills_with_team\", heroid, SUM(rs.rshs)/SUM(1) value, SUM(1) mtch FROM matchlines JOIN (
  SELECT matchid, SUM(roshans_killed) rshs FROM adv_matchlines GROUP BY matchid
) rs ON matchlines.matchid = rs.matchid GROUP BY heroid HAVING $limiter_lower < mtch ORDER BY value DESC;";

# hero damage / minute
$sql .= "SELECT \"hero_damage_per_min\", matchlines.heroid heroid, SUM(matchlines.heroDamage/(matches.duration/60))/SUM(1) value, SUM(1) mtch
          FROM matchlines JOIN matches ON matchlines.matchid = matches.matchid GROUP BY heroid HAVING $limiter_lower < mtch ORDER BY value DESC;";
# tower damage / minute
$sql .= "SELECT \"tower_damage_per_min\", matchlines.heroid heroid, SUM(matchlines.towerDamage/(matches.duration/60))/SUM(1) value, SUM(1) mtch
          FROM matchlines JOIN matches ON matchlines.matchid = matches.matchid GROUP BY heroid HAVING $limiter_lower < mtch ORDER BY value DESC;";
# taken damage / minute
$sql .= "SELECT \"taken_damage_per_min\", adv_matchlines.heroid heroid, SUM(adv_matchlines.damage_taken/(matches.duration/60))/SUM(1) value, SUM(1) mtch
            FROM adv_matchlines JOIN matches ON adv_matchlines.matchid = matches.matchid GROUP BY heroid HAVING $limiter_lower < mtch ORDER BY value DESC;";
# heal / minute
$sql .= "SELECT \"heal_per_min\", matchlines.heroid heroid, SUM(matchlines.heal/(matches.duration/60))/SUM(1) value, SUM(1) mtch
            FROM matchlines JOIN matches ON matchlines.matchid = matches.matchid GROUP BY heroid HAVING $limiter_lower < mtch ORDER BY value DESC;";



$result["averages_heroes"] = array();

if ($conn->multi_query($sql) === TRUE) echo "[S] Requested data for AVERAGE FOR HEROES.\n";
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

do {
  $query_res = $conn->store_result();

  $row = $query_res->fetch_row();
  $result["averages_heroes"][$row[0]] = array();

  for ($i=0; $i<$lg_settings['ana']['avg_limit'] && $row != null; $i++, $row = $query_res->fetch_row()) {
    $result["averages_heroes"][$row[0]][$i] = array (
      "heroid" => $row[1],
      "value"  => $row[2]
    );
  }

  $query_res->free_result();

} while($conn->next_result());
?>
