<?php

$result["random"] = [];

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

# ********** Medians

# heroes median picks
$sql .= "set @rn := 0; select * from ( select *, @rn := @rn + 1 as rn from (
        select \"heroes_median_picks\", COUNT(DISTINCT matchlines.matchid) value, matchlines.heroid hid from matchlines GROUP BY matchlines.heroid
        ORDER BY `value`  DESC
        ) a order by a.value DESC ) b where b.rn = @rn div 2;";

# heroes median bans
$sql .= "set @rn := 0; select * from ( select *, @rn := @rn + 1 as rn from (
        select \"heroes_median_bans\", SUM(1) value, draft.hero_id hid
        from draft where draft.is_pick = 0 GROUP BY hid ORDER BY `value`  DESC
        ) a order by a.value DESC ) b where b.rn = @rn div 2;";

# heroes median gpm
$sql .= "set @rn := 0; select * from ( select *, @rn := @rn + 1 as rn from (
        select \"heroes_median_gpm\", matchlines.gpm value from matchlines
        ORDER BY `value`  DESC
        ) a order by a.value DESC ) b where b.rn = @rn div 2;";

# heroes median xpm
$sql .= "set @rn := 0; select * from ( select *, @rn := @rn + 1 as rn from (
        select \"heroes_median_xpm\", matchlines.xpm value from matchlines
        ORDER BY `value`  DESC
        ) a order by a.value DESC ) b where b.rn = @rn div 2;";

# matches median duration
$sql .= "set @rn := 0; select * from ( select *, @rn := @rn + 1 as rn from (
        select \"matches_median_duration\", matches.duration/60 as value
        from matches ORDER BY `value` DESC
        ) a order by a.value DESC ) b where b.rn = @rn div 2;";

# **********

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

  if(!is_bool($query_res)) {
    $row = $query_res->fetch_row();
    $result["random"][$row[0]] = $row[1];
    $query_res->free_result();
  }
} while($conn->next_result());

?>
