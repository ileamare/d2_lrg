<?php

function team_card($tid, $full = false) {
  global $report;
  global $meta;
  global $strings;
  global $leaguetag;
  global $linkvars;

  if(!isset($report['teams'])) return null;

  $output = "<div class=\"team-card".($full ? " full" : "")."\"><div class=\"team-name\">".
            "<a href=\"?league=".$leaguetag."&mod=teams-profiles-team".$tid.
            (empty($linkvars) ? "" : "&$linkvars")
            ."\" title=\"".team_name($tid)."\">".team_name($tid)." (".team_tag($tid).")</a></div>";

  if(isset($report['teams'][$tid]['regions'])) {
    asort($report['teams'][$tid]['regions']);
    $region_line = "";
    foreach($report['teams'][$tid]['regions'] as $region => $m) {
      //if(!empty($region_line)) $region_line .= ", ";
      $region_line .= region_link($region);
      # initially I thought that there will be a list of all team's regions
      # but I don't think it's good idea for
      break;
    }
  }

  $output .= "<div class=\"team-info-block\">".
                "<div class=\"section-caption\">".locale_string("summary").":</div>".
                "<div class=\"team-info-line\"><span class=\"caption\">".locale_string("matches").":</span> ".$report['teams'][$tid]['matches_total']."</div>".
                "<div class=\"team-info-line\"><span class=\"caption\">".locale_string("winrate").":</span> ".
                    number_format($report['teams'][$tid]['wins']*100/$report['teams'][$tid]['matches_total'])."%</div>".
                "<div class=\"team-info-line\"><span class=\"caption\">".locale_string("gpm").":</span> ".number_format($report['teams'][$tid]['averages']['gpm'])."</div>".
                "<div class=\"team-info-line\"><span class=\"caption\">".locale_string("xpm").":</span> ".number_format($report['teams'][$tid]['averages']['xpm'])."</div>".
                "<div class=\"team-info-line\"><span class=\"caption\">".locale_string("kda").":</span> ".number_format($report['teams'][$tid]['averages']['kills']).
                  "/".number_format($report['teams'][$tid]['averages']['deaths'])."/".number_format($report['teams'][$tid]['averages']['assists'])."</div>".
                (isset($region_line) ? "<div class=\"team-info-line\"><span class=\"caption\">".locale_string("main_region").":</span> ".$region_line."</div>" : "").
                  "</div>";

  $output .= "<div class=\"team-info-block\">".
                "<div class=\"section-caption\">".locale_string("active_roster").":</div>";
  foreach($report['teams'][$tid]['active_roster'] as $player) {
    if (!isset($report['players'][$player])) continue;
    $position = reset($report['players_additional'][$player]['positions']);
    $output .= "<div class=\"team-info-line\">".player_name($player)." (".($position['core'] ? locale_string("core")." " : locale_string("support")).
                  locale_string( "lane_".$position['lane'] ).")</div>";
  }
  $output .= "</div>";

  if (isset($report['teams'][$tid]['pickban'])) {
    $heroes = $report['teams'][$tid]['pickban'];
    uasort($heroes, function($a, $b) {
      if($a['matches_picked'] == $b['matches_picked']) return 0;
      else return ($a['matches_picked'] < $b['matches_picked']) ? 1 : -1;
    });

    $output .= "<div class=\"team-info-block\">".
                  "<div class=\"section-caption\">".locale_string("top_pick_heroes").":</div>";
    $counter = 0;
    foreach($heroes as $hid => $stats) {
      if($counter > 3) break;
      $output .= "<div class=\"team-info-line\"><span class=\"caption\">".hero_full($hid).":</span> ";
      $output .= $stats['matches_picked']." - ".
                  number_format(
                    (isset($stats['wins_picked']) ?
                    $stats['wins_picked']/$stats['matches_picked'] :
                    $stats['winrate_picked'])*100, 2)."%</div>";
      $counter++;
    }
    $output .= "</div>";
  }

  if (isset($report['teams'][$tid]['hero_pairs'])) {
    $heroes = $report['teams'][$tid]['hero_pairs'];

    $output .= "<div class=\"team-info-block\">".
                  "<div class=\"section-caption\">".locale_string("top_pick_pairs").":</div>";
    $counter = 0;
    foreach($heroes as $stats) {
      if($counter > 2) break;
      $output .= "<div class=\"team-info-line\"><span class=\"caption\">".hero_full($stats['heroid1'])." + ".hero_full($stats['heroid2']).":</span> ";
      $output .= $stats['matches']." - ".number_format($stats['winrate']*100, 2)."%</div>";
      $counter++;
    }
    if (!$counter) $output .= "<div class=\"team-info-line\"><span class=\"caption\">".locale_string("none")."</span></div>";
    $output .= "</div>";

  }

  return $output."</div>";

}

?>
