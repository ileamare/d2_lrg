<?php
# kills
$sql  = "SELECT \"kills\", heroid, SUM(kills)/SUM(1) value, SUM(1) mtch FROM matchlines
         JOIN matches ON matchlines.matchid = matches.matchid
         WHERE matches.cluster IN (".implode(",", $clusters).")
         GROUP BY heroid HAVING ".$result["regions_data"][$region]['settings']['limiter_lower']." < mtch ORDER BY value DESC LIMIT $avg_limit;";
# least deaths
$sql .= "SELECT \"least_deaths\", heroid, SUM(deaths)/SUM(1) value, SUM(1) mtch FROM matchlines
          JOIN matches ON matchlines.matchid = matches.matchid
          WHERE matches.cluster IN (".implode(",", $clusters).")
          GROUP BY heroid HAVING ".$result["regions_data"][$region]['settings']['limiter_lower']." < mtch ORDER BY value ASC LIMIT $avg_limit;";
# most deaths
$sql .= "SELECT \"most_deaths\", heroid, SUM(deaths)/SUM(1) value, SUM(1) mtch FROM matchlines
          JOIN matches ON matchlines.matchid = matches.matchid
          WHERE matches.cluster IN (".implode(",", $clusters).")
          GROUP BY heroid HAVING ".$result["regions_data"][$region]['settings']['limiter_lower']." < mtch ORDER BY value DESC LIMIT $avg_limit;";
# assists
$sql .= "SELECT \"assists\", heroid, SUM(assists)/SUM(1) value, SUM(1) mtch FROM matchlines
          JOIN matches ON matchlines.matchid = matches.matchid
          WHERE matches.cluster IN (".implode(",", $clusters).")
          GROUP BY heroid HAVING ".$result["regions_data"][$region]['settings']['limiter_lower']." < mtch ORDER BY value DESC LIMIT $avg_limit;";
# kda
$sql .= "SELECT \"kda\", heroid, (SUM(kills)+SUM(assists))/SUM(deaths) value, SUM(1) mtch FROM matchlines
          JOIN matches ON matchlines.matchid = matches.matchid
          WHERE matches.cluster IN (".implode(",", $clusters).")
          GROUP BY heroid HAVING ".$result["regions_data"][$region]['settings']['limiter_lower']." < mtch ORDER BY value DESC LIMIT $avg_limit;";
# gpm
$sql .= "SELECT \"gpm\", heroid, SUM(gpm)/SUM(1) value, SUM(1) mtch FROM matchlines
          JOIN matches ON matchlines.matchid = matches.matchid
          WHERE matches.cluster IN (".implode(",", $clusters).")
          GROUP BY heroid HAVING ".$result["regions_data"][$region]['settings']['limiter_lower']." < mtch ORDER BY value DESC LIMIT $avg_limit;";
# xpm
$sql .= "SELECT \"xpm\", heroid, SUM(xpm)/SUM(1) value, SUM(1) mtch  FROM matchlines
          JOIN matches ON matchlines.matchid = matches.matchid
          WHERE matches.cluster IN (".implode(",", $clusters).")
          GROUP BY heroid HAVING ".$result["regions_data"][$region]['settings']['limiter_lower']." < mtch ORDER BY value DESC LIMIT $avg_limit;";
# last hits per min
$sql .= "SELECT \"lasthits_per_min\", matchlines.heroid heroid, SUM(matchlines.lastHits/(matches.duration/60))/SUM(1)
           value, SUM(1) mtch
           FROM matchlines JOIN matches ON matchlines.matchid = matches.matchid
           WHERE matches.cluster IN (".implode(",", $clusters).")
           GROUP BY heroid HAVING ".$result["regions_data"][$region]['settings']['limiter_lower']." < mtch ORDER BY value DESC LIMIT $avg_limit;";
# last hits
$sql .= "SELECT \"lasthits\", matchlines.heroid heroid, SUM(matchlines.lastHits)/SUM(1)
          value, SUM(1) mtch
          FROM matchlines JOIN matches ON matchlines.matchid = matches.matchid
          WHERE matches.cluster IN (".implode(",", $clusters).")
          GROUP BY heroid HAVING ".$result["regions_data"][$region]['settings']['limiter_lower']." < mtch ORDER BY value DESC LIMIT $avg_limit;";

# denies
$sql .= "SELECT \"denies\", heroid, SUM(denies)/SUM(1) value, SUM(1) mtch  FROM matchlines
          JOIN matches ON matchlines.matchid = matches.matchid
          WHERE matches.cluster IN (".implode(",", $clusters).")
          GROUP BY heroid HAVING ".$result["regions_data"][$region]['settings']['limiter_lower']." < mtch ORDER BY value DESC LIMIT $avg_limit;";

# stuns
$sql .= "SELECT \"stuns\", heroid, SUM(stuns)/SUM(1) value, SUM(1) mtch  FROM adv_matchlines
          JOIN matches ON adv_matchlines.matchid = matches.matchid
          WHERE matches.cluster IN (".implode(",", $clusters).")
          GROUP BY heroid HAVING ".$result["regions_data"][$region]['settings']['limiter_lower']." < mtch ORDER BY value DESC LIMIT $avg_limit;";
# stacks
$sql .= "SELECT \"stacks\", heroid, SUM(stacks)/SUM(1) value, SUM(1) mtch  FROM adv_matchlines
          JOIN matches ON adv_matchlines.matchid = matches.matchid
          WHERE matches.cluster IN (".implode(",", $clusters).")
          GROUP BY heroid HAVING ".$result["regions_data"][$region]['settings']['limiter_lower']." < mtch ORDER BY value DESC LIMIT $avg_limit;";

# courier kills
$sql .= "SELECT \"courier_kills\", heroid, SUM(couriers_killed)/SUM(1) value, SUM(1) mtch FROM adv_matchlines
          JOIN matches ON adv_matchlines.matchid = matches.matchid
          WHERE matches.cluster IN (".implode(",", $clusters).")
          GROUP BY heroid HAVING ".$result["regions_data"][$region]['settings']['limiter_lower']." < mtch ORDER BY value DESC LIMIT $avg_limit;";

# roshan kills by hero's team
$sql .= "SELECT \"roshan_kills_with_team\", heroid, SUM(rs.rshs)/SUM(1) value, SUM(1) mtch FROM matchlines JOIN (
            SELECT matchid, SUM(roshans_killed) rshs FROM adv_matchlines GROUP BY matchid
          ) rs ON matchlines.matchid = rs.matchid
          JOIN matches ON matchlines.matchid = matches.matchid
          WHERE matches.cluster IN (".implode(",", $clusters).")
          GROUP BY heroid HAVING ".$result["regions_data"][$region]['settings']['limiter_lower']." < mtch ORDER BY value DESC LIMIT $avg_limit;";

# hero damage / minute
$sql .= "SELECT \"hero_damage_per_min\", matchlines.heroid heroid, SUM(matchlines.heroDamage/(matches.duration/60))/SUM(1) value, SUM(1) mtch
          FROM matchlines JOIN matches ON matchlines.matchid = matches.matchid
          WHERE matches.cluster IN (".implode(",", $clusters).")
          GROUP BY heroid HAVING ".$result["regions_data"][$region]['settings']['limiter_lower']." < mtch ORDER BY value DESC LIMIT $avg_limit;";

# tower damage / minute
$sql .= "SELECT \"tower_damage_per_min\", matchlines.heroid heroid, SUM(matchlines.towerDamage/(matches.duration/60))/SUM(1) value, SUM(1) mtch
          FROM matchlines JOIN matches ON matchlines.matchid = matches.matchid
          WHERE matches.cluster IN (".implode(",", $clusters).")
          GROUP BY heroid HAVING ".$result["regions_data"][$region]['settings']['limiter_lower']." < mtch ORDER BY value DESC LIMIT $avg_limit;";

# taken damage / minute
$sql .= "SELECT \"taken_damage_per_min\", adv_matchlines.heroid heroid, SUM(adv_matchlines.damage_taken/(matches.duration/60))/SUM(1) value, SUM(1) mtch
            FROM adv_matchlines JOIN matches ON matchlines.matchid = matches.matchid
            WHERE matches.cluster IN (".implode(",", $clusters).")
            GROUP BY heroid HAVING ".$result["regions_data"][$region]['settings']['limiter_lower']." < mtch ORDER BY value DESC LIMIT $avg_limit;";

# heal / minute
$sql .= "SELECT \"heal_per_min\", matchlines.heroid heroid, SUM(matchlines.heal/(matches.duration/60))/SUM(1) value, SUM(1) mtch
            FROM matchlines JOIN matches ON matchlines.matchid = matches.matchid
            WHERE matches.cluster IN (".implode(",", $clusters).")
            GROUP BY heroid HAVING ".$result["regions_data"][$region]['settings']['limiter_lower']." < mtch ORDER BY value DESC LIMIT $avg_limit;";


$result["regions_data"][$region]["haverages_heroes"] = [];

if ($conn->multi_query($sql) === TRUE);# echo "[S] Requested data for AVERAGE FOR HEROES.\n";
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

do {
  $query_res = $conn->store_result();

  $row = $query_res->fetch_row();
  $result["regions_data"][$region]["haverages_heroes"][$row[0]] = array();

  for ($i=0; $i<$avg_limit && $row != null; $i++, $row = $query_res->fetch_row()) {
    $result["regions_data"][$region]["haverages_heroes"][$row[0]][$i] = [
      "heroid" => $row[1],
      "value"  => $row[2]
    ];
  }

  $query_res->free_result();

} while($conn->next_result());
?>
