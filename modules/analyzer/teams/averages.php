<?php
$sql = "";

# avg kills
$sql .= "SELECT \"kills\", SUM(ans.sum_kills)/SUM(ans.match_count) FROM (
          SELECT SUM(kills) sum_kills, COUNT(DISTINCT matchlines.matchid) match_count
          FROM matchlines JOIN teams_matches
          ON matchlines.matchid = teams_matches.matchid
          AND matchlines.isRadiant = teams_matches.is_radiant
          WHERE teams_matches.teamid = ".$id."
      ) ans;";

# avg deaths
$sql .= "SELECT \"deaths\", SUM(ans.sum_deaths)/SUM(ans.match_count) FROM (
          SELECT SUM(deaths) sum_deaths, COUNT(DISTINCT matchlines.matchid) match_count
          FROM matchlines JOIN teams_matches
          ON matchlines.matchid = teams_matches.matchid
          AND matchlines.isRadiant = teams_matches.is_radiant
          WHERE teams_matches.teamid = ".$id."
      ) ans;";

# avg assists
$sql .= "SELECT \"assists\", SUM(ans.sum_assists)/SUM(ans.match_count) FROM (
          SELECT SUM(assists) sum_assists, COUNT(DISTINCT matchlines.matchid) match_count
          FROM matchlines JOIN teams_matches
          ON matchlines.matchid = teams_matches.matchid
          AND matchlines.isRadiant = teams_matches.is_radiant
          WHERE teams_matches.teamid = ".$id."
      ) ans;";

# avg xpm
$sql .= "SELECT \"xpm\", SUM(ans.sum_xpm)/SUM(ans.match_count) FROM (
          SELECT SUM(xpm) sum_xpm, COUNT(DISTINCT matchlines.matchid) match_count
          FROM matchlines JOIN teams_matches
          ON matchlines.matchid = teams_matches.matchid
          AND matchlines.isRadiant = teams_matches.is_radiant
          WHERE teams_matches.teamid = ".$id."
          GROUP BY matchlines.matchid
      ) ans;";

# avg gpm
$sql .= "SELECT \"gpm\", SUM(ans.sum_gpm)/SUM(ans.match_count) FROM (
          SELECT SUM(gpm) sum_gpm, COUNT(DISTINCT matchlines.matchid) match_count
          FROM matchlines JOIN teams_matches
          ON matchlines.matchid = teams_matches.matchid
          AND matchlines.isRadiant = teams_matches.is_radiant
          WHERE teams_matches.teamid = ".$id."
          GROUP BY matchlines.matchid
      ) ans;";

# avg wards
$sql .= "SELECT \"wards_placed\", SUM(ans.sum_wards)/SUM(ans.match_count) FROM (
          SELECT SUM(adv_matchlines.wards) sum_wards, COUNT(DISTINCT matchlines.matchid) match_count
          FROM adv_matchlines JOIN matchlines
          ON adv_matchlines.matchid = matchlines.matchid
          AND adv_matchlines.playerid = matchlines.playerid
          JOIN teams_matches
          ON adv_matchlines.matchid = teams_matches.matchid
          AND matchlines.isRadiant = teams_matches.is_radiant
          WHERE teams_matches.teamid = ".$id."
          GROUP BY matchlines.matchid
      ) ans;";

# avg sentries
$sql .= "SELECT \"sentries_placed\", SUM(ans.sum_sentries)/SUM(ans.match_count) FROM (
          SELECT SUM(adv_matchlines.sentries) sum_sentries, COUNT(DISTINCT matchlines.matchid) match_count
          FROM adv_matchlines JOIN matchlines
          ON adv_matchlines.matchid = matchlines.matchid
          AND adv_matchlines.playerid = matchlines.playerid
          JOIN teams_matches
          ON adv_matchlines.matchid = teams_matches.matchid
          AND matchlines.isRadiant = teams_matches.is_radiant
          WHERE teams_matches.teamid = ".$id."
          GROUP BY matchlines.matchid
      ) ans;";

# avg wards destroyed
$sql .= "SELECT \"wards_destroyed\", SUM(ans.sum_wards_destroyed)/SUM(ans.match_count) FROM (
          SELECT SUM(adv_matchlines.wards_destroyed) sum_wards_destroyed, COUNT(DISTINCT matchlines.matchid) match_count
          FROM adv_matchlines JOIN matchlines
          ON adv_matchlines.matchid = matchlines.matchid
          AND adv_matchlines.playerid = matchlines.playerid
          JOIN teams_matches
          ON adv_matchlines.matchid = teams_matches.matchid
          AND matchlines.isRadiant = teams_matches.is_radiant
          WHERE teams_matches.teamid = ".$id."
          GROUP BY matchlines.matchid
      ) ans;";

# courier kills
$sql .= "SELECT \"courier_kills\", SUM(adv_matchlines.couriers_killed)/COUNT(DISTINCT matches.matchid) FROM adv_matchlines
          JOIN matchlines ON adv_matchlines.playerid = matchlines.playerid AND adv_matchlines.matchid = matchlines.matchid
          JOIN teams_matches ON teams_matches.matchid = matchlines.matchid AND teams_matches.is_radiant = matchlines.isradiant
          JOIN matches ON teams_matches.matchid = matches.matchid
          WHERE teams_matches.teamid = ".$id.";";

# roshan kills by hero's team
$sql .= "SELECT \"roshan_kills\", SUM(adv_matchlines.roshans_killed)/COUNT(DISTINCT matches.matchid) FROM adv_matchlines
          JOIN matchlines ON adv_matchlines.playerid = matchlines.playerid AND adv_matchlines.matchid = matchlines.matchid
          JOIN teams_matches ON teams_matches.matchid = matchlines.matchid AND teams_matches.is_radiant = matchlines.isradiant
          JOIN matches ON teams_matches.matchid = matches.matchid
          WHERE teams_matches.teamid = ".$id.";";

# hero pool
$sql .= "SELECT \"hero_pool\", COUNT(DISTINCT matchlines.heroid) FROM matchlines JOIN teams_matches
          ON matchlines.matchid = teams_matches.matchid
          AND matchlines.isRadiant = teams_matches.is_radiant
          WHERE teams_matches.teamid = ".$id.";";

# diversity
# (COUNT(DISTINCT heroid)/mhpt.mhp) * (COUNT(DISTINCT heroid)/COUNT(DISTINCT matchid))
$sql .= "SELECT \"diversity\", (COUNT(DISTINCT matchlines.heroid)/mhpt.mhp)*(COUNT(DISTINCT matchlines.heroid)/COUNT(DISTINCT matchlines.matchid))
          FROM matchlines JOIN teams_matches JOIN (
            select max(heropool) mhp from (
              select COUNT(DISTINCT matchlines.heroid) heropool
              FROM matchlines JOIN teams_matches
              ON matchlines.matchid = teams_matches.matchid
              AND matchlines.isRadiant = teams_matches.is_radiant
              GROUP BY teams_matches.teamid
            ) _hp
          ) mhpt
          ON matchlines.matchid = teams_matches.matchid
          AND matchlines.isRadiant = teams_matches.is_radiant
          WHERE teams_matches.teamid = ".$id.";";

# radiant ratio
$sql .= "SELECT \"rad_ratio\", SUM(is_radiant)/COUNT(DISTINCT matchid)
          FROM teams_matches
          WHERE teamid = ".$id.";";

# radiant wr
$sql .= "SELECT \"radiant_wr\", SUM(matches.radiantWin)/COUNT(DISTINCT matches.matchid) FROM matches JOIN teams_matches
          ON matches.matchid = teams_matches.matchid
          AND teams_matches.is_radiant = 1
          WHERE teams_matches.teamid = ".$id.";";

# dire wr
$sql .= "SELECT \"dire_wr\", 1-(SUM(matches.radiantWin)/COUNT(DISTINCT matches.matchid)) FROM matches JOIN teams_matches
          ON matches.matchid = teams_matches.matchid
          AND teams_matches.is_radiant = 0
          WHERE teams_matches.teamid = ".$id.";";

# duration
$sql .= "SELECT \"avg_match_len\", (SUM(matches.duration)/60)/COUNT(DISTINCT matches.matchid) FROM matches JOIN teams_matches
          ON matches.matchid = teams_matches.matchid WHERE teams_matches.teamid = ".$id.";";

if ($conn->multi_query($sql) === TRUE) echo "[S] Requested data for TEAM $id AVERAGES.\n";
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$result['teams'][$id]['averages'] = array();

do {
  $query_res = $conn->store_result();

  $row = $query_res->fetch_row();

  $result['teams'][$id]['averages'][$row[0]] = $row[1];

  $query_res->free_result();
} while($conn->next_result());
?>
