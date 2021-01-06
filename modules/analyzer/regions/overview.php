<?php
# main stats

# matches total
$sql  = "SELECT \"matches_total\", COUNT(matchid) FROM matches WHERE matches.cluster IN (".implode(",", $clusters).");";

if ($conn->multi_query($sql) === FALSE)
die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();
$row = $query_res->fetch_row();
$query_res->free_result();
if (!$row[1] || ( ($lg_settings['ana']['regions']['use_limiter'] ?? false) && $row[1] < $limiter_median ) ) {
  // using median number of matches as a limiter for regions
  // Regular limiter is too small and the median number of picks value fits just right
  return 1;
} else {
  $result["regions_data"][$region] = [];
  $result["regions_data"][$region]["main"] = [];
  $result["regions_data"][$region]["main"]["matches"] = $row[1];

  # players on event
  $sql = "SELECT \"players_on_event\", COUNT(DISTINCT players.playerID)
          FROM players JOIN (
            SELECT matchlines.playerid, matches.cluster FROM matchlines
            JOIN matches ON matchlines.matchid = matches.matchid
            WHERE matches.cluster IN (".implode(",", $clusters).")
            GROUP BY matchlines.playerid) clp
          ON clp.playerid = players.playerID;";
  if($lg_settings['main']['teams']) # teams on event
    $sql .= "SELECT \"teams_on_event\", COUNT(DISTINCT teams.teamid) FROM teams JOIN (
      SELECT teams_matches.teamid, matches.cluster FROM matches JOIN teams_matches
      ON matches.matchid = teams_matches.matchid
      WHERE matches.cluster IN (".implode(",", $clusters).")
      GROUP BY teams_matches.teamid) clt
    ON clt.teamid = teams.teamid;";



  # heroes contested
  $sql .= "SELECT \"heroes_contested\", count(distinct hero_id) FROM draft JOIN matches on draft.matchid = matches.matchid WHERE matches.cluster IN (".implode(",", $clusters).");";
  # heroes picked
  $sql .= "SELECT \"heroes_picked\", count(distinct hero_id) FROM draft JOIN matches on draft.matchid = matches.matchid WHERE matches.cluster IN (".implode(",", $clusters).") AND is_pick = 1;";
  # heroes banned
  $sql .= "SELECT \"heroes_banned\", count(distinct hero_id) FROM draft JOIN matches on draft.matchid = matches.matchid WHERE matches.cluster IN (".implode(",", $clusters).") AND is_pick = 0;";

  # ********** Medians

  # heroes median picks
  $sql .= "set @rn := 0; select * from ( select *, @rn := @rn + 1 as rn from (
    select \"heroes_median_picks\", COUNT(DISTINCT matchlines.matchid) value, matchlines.heroid hid 
    from matchlines JOIN matches on matchlines.matchid = matches.matchid
    WHERE matches.cluster IN (".implode(",", $clusters).")
    GROUP BY matchlines.heroid
    ORDER BY `value`  DESC
    ) a order by a.value DESC ) b where b.rn = @rn div 2;";

  # heroes median bans
  $sql .= "set @rn := 0; select * from ( select *, @rn := @rn + 1 as rn from (
    select \"heroes_median_bans\", SUM(1) value, draft.hero_id hid
    from draft join matches on matches.matchid = draft.matchid
    where draft.is_pick = 0 and matches.cluster IN (".implode(",", $clusters).") GROUP BY hid ORDER BY `value`  DESC
    ) a order by a.value DESC ) b where b.rn = @rn div 2;";

  # heroes median gpm
  $sql .= "set @rn := 0; select * from ( select *, @rn := @rn + 1 as rn from (
    select \"heroes_median_gpm\", matchlines.gpm value 
    from matchlines JOIN matches on matchlines.matchid = matches.matchid
    WHERE matches.cluster IN (".implode(",", $clusters).")
    ORDER BY `value`  DESC
    ) a order by a.value DESC ) b where b.rn = @rn div 2;";

  # heroes median xpm
  $sql .= "set @rn := 0; select * from ( select *, @rn := @rn + 1 as rn from (
    select \"heroes_median_xpm\", matchlines.xpm value
    from matchlines JOIN matches on matchlines.matchid = matches.matchid
    WHERE matches.cluster IN (".implode(",", $clusters).")
    ORDER BY `value`  DESC
    ) a order by a.value DESC ) b where b.rn = @rn div 2;";

  # matches median duration
  $sql .= "set @rn := 0; select * from ( select *, @rn := @rn + 1 as rn from (
    select \"matches_median_duration\", matches.duration/60 as value
    from matches 
    WHERE matches.cluster IN (".implode(",", $clusters).")
    ORDER BY `value` DESC
    ) a order by a.value DESC ) b where b.rn = @rn div 2;";

  # **********

  # Radiant winrate
  $sql .= "SELECT \"radiant_wr\", SUM(radiantWin)*100/SUM(1) FROM matches WHERE matches.cluster IN (".implode(",", $clusters).");";
  # Dire winrate
  $sql .= "SELECT \"dire_wr\", (1-(SUM(radiantWin)/SUM(1)))*100 FROM matches WHERE matches.cluster IN (".implode(",", $clusters).");";
  # average match length
  $sql .= "SELECT \"avg_match_len\", SUM(duration)/(60*COUNT(DISTINCT matchid)) FROM matches WHERE matches.cluster IN (".implode(",", $clusters).");";
  # rampages total
  $sql .= "SELECT \"rampages_total\", SUM(am.multi_kill > 4) FROM matches JOIN adv_matchlines am ON matches.matchid = am.matchid WHERE matches.cluster IN (".implode(",", $clusters).");";


  if ($conn->multi_query($sql) === TRUE);# echo "[S] Requested data for REGION STATS.\n";
  else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

  do {
    $query_res = $conn->store_result();

    if(!is_bool($query_res)) {
      $row = $query_res->fetch_row();

      $result["regions_data"][$region]["main"][$row[0]] = $row[1];

      $query_res->free_result();
    }
  } while($conn->next_result());

  require("overview/firstlast.php");
  require("overview/days.php");
  require("overview/modes.php");
  require("overview/versions.php");

  return 0;
}
?>
