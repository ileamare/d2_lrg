<?php

$result["random"] = array();

# matches total
$sql  = "SELECT \"matches_total\", COUNT(matchid) FROM matches;";
# players on event
$sql .= "SELECT \"players_on_event\", COUNT(playerID) FROM players;";
if($lg_settings['main']['teams']) # teams on event
  $sql .= "SELECT \"teams_on_event\", COUNT(DISTINCT teamid) FROM teams_matches;";

# heroes contested
$sql .= "SELECT \"heroes_contested\", count(distinct hero_id) FROM draft;";
# heroes picked
$sql .= "SELECT \"heroes_picked\", count(distinct hero_id) FROM draft WHERE is_pick = 1;";
# heroes banned
$sql .= "SELECT \"heroes_banned\", count(distinct hero_id) FROM draft WHERE is_pick = 0;";
# Radiant winrate
$sql .= "SELECT \"radiant_wr\", SUM(radiantWin)*100/SUM(1) FROM matches;";
# Dire winrate
$sql .= "SELECT \"dire_wr\", (1-(SUM(radiantWin)/SUM(1)))*100 FROM matches;";
# total creeps killed (lh+dn)
$sql .= "SELECT \"creeps_killed\", SUM(lasthits+denies) FROM matchlines;";
# total wards placed
$sql .= "SELECT \"obs_total\", SUM(wards) FROM adv_matchlines;";
# total wards destroyed
$sql .= "SELECT \"obs_killed_total\", SUM(wards_destroyed) FROM adv_matchlines;";
# couriers killed
$sql .= "SELECT \"couriers_killed_total\", SUM(couriers_killed) FROM adv_matchlines;";
# roshans killed
$sql .= "SELECT \"roshans_killed_total\", SUM(roshans_killed) FROM adv_matchlines;";
# buybacks total
$sql .= "SELECT \"buybacks_total\", SUM(buybacks) FROM adv_matchlines;";
# average match length
$sql .= "SELECT \"avg_match_len\", SUM(duration)/(60*COUNT(DISTINCT matchid)) FROM matches;";


if ($conn->multi_query($sql) === TRUE) echo "[S] Requested data for RANDOM STATS.\n";
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

do {
  $query_res = $conn->store_result();

  $row = $query_res->fetch_row();

  $result["random"][$row[0]] = $row[1];

  $query_res->free_result();
} while($conn->next_result());

?>
