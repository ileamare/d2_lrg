<?php

$modules['overview'] = "";

//if ( check_module("overview") ) {
function rg_view_generate_overview() {
  global $report;
  global $meta;
  global $charts_colors;

  $res = "<div class=\"content-text overview overview-head\">";
  $res .= "<div class=\"content-header\">".locale_string("summary")."</div><div class=\"block-content\">";
  $res .= locale_string("over-pregen-report");
  if ($report['league_id'] == null || $report['league_id'] == "custom")
    $res .= " ".locale_string("over-custom-league")." ".$report['league_name']." — ".$report['league_desc'].".";
  else
    $res .= " ".$report['league_name']." (".$report['league_id'].") — ".$report['league_desc'].".";

  $res .= "</div><div class=\"block-content\">";

  $res .= locale_string("over-matches", ["num" => $report['random']['matches_total'] ] )." ";
  if(isset($report['teams']))
    $res .= locale_string("over-teams", ["num" => $report['random']['teams_on_event'] ] )." ";
  else $res .= locale_string("over-players", ["num" => $report['random']['players_on_event'] ] )." ";

  $res .= "</div><div class=\"block-content\">";

  if($report['settings']['overview_versions']) {
    $mode = reset($report['versions']);

    if (compare_ver($report['ana_version'], array(1,1,0,-4,1)) < 0) {
      $ver = $meta['versions'][ key($report['versions']) ];
    } else {
      $ver = $meta['versions'][ (int) (key($report['versions'])/100) ].(
          key($report['versions']) % 100 ?
          chr( ord('a') + key($report['versions']) % 100 ) :
          ""
        );
    }

    if ($mode/$report['random']['matches_total'] > 0.99)
      $res .= locale_string("over-one-version", ["ver"=>$ver])." ";
    else $res .= locale_string("over-most-version", ["num" => $mode, "ver" => $ver])." ";

    unset($ver);
  }

  if($report['settings']['overview_modes']) {
    $mode = reset($report['modes']);
    if ($mode/$report['random']['matches_total'] > 0.99)
      $res .= locale_string("over-one-mode", ["gm" => $meta['modes'][ key($report['modes']) ] ])." ";
    else $res .= locale_string("over-most-mode", ["num" => $mode, "gm"=> $meta['modes'][ key($report['modes']) ] ])." ";
  }

  if($report['settings']['overview_regions']) {
    $regions_matches = array();
    foreach ($report['regions'] as $mode => $data) {
      $region = $meta['regions'][ $meta['clusters'][$mode] ];
      if(isset($regions_matches[$region])) $regions_matches[$region] += $data;
      else $regions_matches[$region] = $data;
    }
    arsort($regions_matches);
    $mode = reset($regions_matches);
    if ($mode/$report['random']['matches_total'] > 0.99)
      $res .= locale_string("over-one-region", [ "server" => key($regions_matches)] )." ";
    else
      $res .= locale_string("over-most-region", ["num"=>$mode, "server"=>key($regions_matches) ] )." ";
  }

  $res .= "</div>";

  if($report['settings']['overview_time_limits']) {
    $res .= "<div class=\"block-content\">";

    $res .= locale_string("over-first-match", ["date"=> date(locale_string("time_format")." ".locale_string("date_format"), $report['first_match']['date']) ])."<br />";
    $res .= locale_string("over-last-match", ["date"=> date(locale_string("time_format")." ".locale_string("date_format"), $report['last_match']['date']) ])."<br />";

    $res .= "</div>";
  }

  if($report['settings']['overview_last_match_winners']) {
    $res .= "<div class=\"block-content\">";

    if( $report['matches_additional'][ $report['last_match']['mid'] ]['radiant_win'] ) {
      if(isset($report['teams']) &&
         isset($report['match_participants_teams'][ $report['last_match']['mid'] ]['radiant']) &&
         isset($report['teams'][ $report['match_participants_teams'][ $report['last_match']['mid'] ]['radiant'] ]['name']))
        $mode = $report['teams'][ $report['match_participants_teams'][ $report['last_match']['mid'] ]['radiant'] ]['name'];
      else $mode = locale_string("radiant");
    } else {
      if(isset($report['teams']) &&
         isset($report['match_participants_teams'][ $report['last_match']['mid'] ]['dire']) &&
         isset($report['teams'][ $report['match_participants_teams'][ $report['last_match']['mid'] ]['dire'] ]['name']))
        $mode = $report['teams'][ $report['match_participants_teams'][ $report['last_match']['mid'] ]['dire'] ]['name'];
      else $mode = locale_string("dire");
    }

    $res .= locale_string("over-last-match-winner", ["team"=>$mode])."</div>";
  }

  $res .= "</div>";


  if($report['settings']['overview_charts']) {
    global $use_graphjs;
    $use_graphjs = true;

    $res .= "<div class=\"content-text overview overview-charts\">";

    $mode = reset($report['versions']);
    if ($report['settings']['overview_versions'] && $mode/$report['random']['matches_total'] < 0.99) {
      $converted_modes = array();
      foreach ($report['versions'] as $mode => $data) {
        if (compare_ver($report['ana_version'], array(1,1,0,-4,1)) < 0) {
          $converted_modes[] = $meta['versions'][$mode];
        } else {
          $converted_modes[] = $meta['versions'][ (int) ($mode/100) ].(
              $mode % 100 ?
              chr( ord('a') + $mode % 100 ) :
              ""
            );
        }
      }
      $colors = array_slice($charts_colors, 0, sizeof($converted_modes));
      $res .= "<div class=\"chart-pie\"><canvas id=\"overview-patches\" width=\"undefined\" height=\"undefined\"></canvas><script>".
                            "var modes_chart_el = document.getElementById('overview-patches'); ".
                            "var modes_chart = new Chart(modes_chart_el, {
                              type: 'pie',
                              data: {
                                labels: [ '".implode($converted_modes,"','")."' ],
                                datasets: [{data: [ ".implode($report['versions'],",")." ],
                                borderWidth: 0,
                                backgroundColor:['".implode($colors,"','")."']}]
                              }
                            });</script></div>";
    }

    $mode = reset($report['modes']);
    if ($report['settings']['overview_modes'] && $mode/$report['random']['matches_total'] < 0.99) {
      $converted_modes = array();
      foreach ($report['modes'] as $mode => $data) {
        $converted_modes[] = $meta['modes'][$mode];
      }
      $colors = array_slice($charts_colors, 0, sizeof($converted_modes));
      $res .= "<div class=\"chart-pie\"><canvas id=\"overview-modes\" width=\"undefined\" height=\"undefined\"></canvas><script>".
                            "var modes_chart_el = document.getElementById('overview-modes'); ".
                            "var modes_chart = new Chart(modes_chart_el, {
                              type: 'pie',
                              data: {
                                labels: [ '".implode($converted_modes,"','")."' ],
                                datasets: [{data: [ ".implode($report['modes'],",")." ],
                                borderWidth: 0,
                                backgroundColor:['".implode($colors,"','")."']}]
                              }
                            });</script></div>";
    }

    $mode = reset($regions_matches);
    if ($report['settings']['overview_regions'] && $mode/$report['random']['matches_total'] < 0.99) {
      $region_names = array_keys($regions_matches);
      $colors = array_slice($charts_colors, 0, sizeof($region_names));
      $res .= "<div class=\"chart-pie\"><canvas id=\"overview-regions\" width=\"undefined\" height=\"undefined\"></canvas><script>".
                            "var modes_chart_el = document.getElementById('overview-regions'); ".
                            "var modes_chart = new Chart(modes_chart_el, {
                              type: 'pie',
                              data: {
                                labels: [ '".implode($region_names,"','")."' ],
                                datasets: [{data: [ ".implode($regions_matches,",")." ],
                                borderWidth: 0,
                                backgroundColor:['".implode($colors,"','")."']}]
                              }
                            });</script></div>";
      unset($region_names);
    }
    unset($regions_matches);

    if ($report['settings']['overview_sides_graph']) {
      $colors = array_slice($charts_colors, 0, 2);
      $res .= "<div class=\"chart-pie\"><canvas id=\"overview-sides\" width=\"undefined\" height=\"undefined\"></canvas><script>".
                            "var modes_chart_el = document.getElementById('overview-sides'); ".
                            "var modes_chart = new Chart(modes_chart_el, {
                              type: 'pie',
                              data: {
                                labels: [ '".locale_string("radiant")."','".locale_string("dire")."' ],
                                datasets: [{data: [ ".$report['random']['radiant_wr'].",".$report['random']['dire_wr']." ],
                                borderWidth: 0,
                                backgroundColor:['".implode($colors,"','")."']}]
                              }
                            });</script></div>";
    }

    if ($report['settings']['overview_heroes_contested_graph']) {
      $colors = array_slice($charts_colors, 0, 4);
      // TODO
      if(isset($report['random']['heroes_banned']))
        $res .= "<div class=\"chart-pie\"><canvas id=\"overview-heroes\" width=\"undefined\" height=\"undefined\"></canvas><script>".
                            "var modes_chart_el = document.getElementById('overview-heroes'); ".
                            "var modes_chart = new Chart(modes_chart_el, {
                              type: 'pie',
                              data: {
                                labels: [ '".locale_string("heroes_pickbanned")."','".
                                             locale_string("heroes_picked")."', '".
                                             locale_string("heroes_banned")."','".
                                             locale_string("heroes_uncontested")."' ],
                                datasets: [{data: [ ".($report['random']['heroes_banned']-$report['random']['heroes_contested']+$report['random']['heroes_picked']).",".
                                                      ($report['random']['heroes_contested']-$report['random']['heroes_banned']).", ".
                                                      ($report['random']['heroes_contested']-$report['random']['heroes_picked']).",".
                                                      (sizeof($meta['heroes'])-$report['random']['heroes_contested'])." ],
                                borderWidth: 0,
                                backgroundColor:['".implode($colors,"','")."']}]
                              }
                            });</script></div>";
      else
        $res .= "<div class=\"chart-pie\"><canvas id=\"overview-heroes\" width=\"undefined\" height=\"undefined\"></canvas><script>".
                            "var modes_chart_el = document.getElementById('overview-heroes'); ".
                            "var modes_chart = new Chart(modes_chart_el, {
                              type: 'pie',
                              data: {
                                labels: [ '".locale_string("heroes_contested")."','".
                                             locale_string("heroes_uncontested")."' ],
                                datasets: [{data: [ ".$report['random']['heroes_contested'].", ".
                                                      (sizeof($meta['heroes'])-$report['random']['heroes_contested'])." ],
                                borderWidth: 0,
                                backgroundColor:['".implode($colors,"','")."']}]
                              }
                            });</script></div>";
    }

    if ($report['settings']['overview_days_graph']) {
      $converted_modes = array();
      $matchcount = array();
      foreach($report['days'] as $dn => $day) {
        $converted_modes[] = date("j M Y", $day['timestamp'])." (".($dn+1).")";
        $matchcount[] = sizeof($day['matches']);
      }
      $colors = array_slice($charts_colors, 0, sizeof($converted_modes));
      $res .= "<h1>".locale_string("matches_per_day")."</h1>".
                            "<div class=\"chart-bars\"><canvas id=\"overview-days\" width=\"undefined\" height=\"".
                            (35+sizeof($converted_modes)*3)."px\"></canvas><script>".
                            "var modes_chart_el = document.getElementById('overview-days'); ".
                            "var modes_chart = new Chart(modes_chart_el, {
                              type: 'horizontalBar',
                              data: {
                                labels: [ '','".implode($converted_modes,"','")."' ],
                                datasets: [{label:'".locale_string("matches_per_day")."',data: [ 0,".implode($matchcount,",")." ],
                                backgroundColor:'#ccc'}]
                              }
                            });</script></div>";

      }
    $res .= "</div>";
  }

  if($report['settings']['overview_random_stats']) {
    $res .= "<div class=\"content-header\">".locale_string("random")."</div>";
    $res .= "<table class=\"list\" id=\"overview-table\">";
    foreach($report['random'] as $key => $value) {
      $res .= "<tr><td>".locale_string($key)."</td><td>".$value."</td></tr>";
    }
    $res .= "</table>";
  }

  if(isset($report['players_additional']) || isset($report["teams"])) {
    $res .= "<div class=\"content-header\">".locale_string("notable_paricipans")."</div>";
    $res .= "<div class=\"content-cards\">";

    if (isset($report['teams']) && $report['settings']['overview_last_match_winners']) {
      if($report['matches_additional'][ $report['last_match']['mid'] ]['radiant_win']) {
          if (isset( $report['match_participants_teams'][ $report['last_match']['mid'] ]['radiant'] ))
              $tid = $report['match_participants_teams'][ $report['last_match']['mid'] ]['radiant'];
          else $tid = 0;
      } else {
          if (isset($report['match_participants_teams'][ $report['last_match']['mid'] ]['dire']) )
              $tid = $report['match_participants_teams'][ $report['last_match']['mid'] ]['dire'];
          else $tid = 0;
      }
      if ($tid) {
          $res .= "<h1>".locale_string("np_winner")."</h1>";
          $res .= team_card($tid);
      }
      unset($tid);
    }

    $res .= "</div><table class=\"list\">";
    if (isset($report['teams'])) {
      $max_wr = 0;
      $max_matches = 0;
      foreach ($report['teams'] as $team_id => $team) {
        if(!$max_matches || $report['teams'][$max_matches]['matches_total'] < $team['matches_total'] )
          $max_matches = $team_id;
        if($team['matches_total'] <= $report['settings']['limiter']) continue;

        if($max_wr == 0) $max_wr = $team_id;
        else if(!$max_wr || $report['teams'][$max_wr]['wins']/$report['teams'][$max_wr]['matches_total'] < $team['wins']/$team['matches_total'] )
          $max_wr = $team_id;
      }

      $res .= "<tr><td>".locale_string("most_matches")."</td><td>".
          team_link($max_matches)."</td><td>".$report['teams'][$max_matches]['matches_total']."</td></tr>";

      if($max_wr)
        $res .= "<tr><td>".locale_string("highest_winrate")."</td><td>".
          team_link($max_wr)."</td><td>".number_format($report['teams'][$max_wr]['wins']*100/$report['teams'][$max_wr]['matches_total'],2)."%</td></tr>";

      if (isset($report['records'])) {
        $res .= "<tr><td>".locale_string("widest_hero_pool_team")."</td><td>".
            team_link($report['records']['widest_hero_pool_team']['playerid'])."</td><td>".
            $report['records']['widest_hero_pool_team']['value']."</td></tr>";

        $res .= "<tr><td>".locale_string("smallest_hero_pool_team")."</td><td>".
            team_link($report['records']['smallest_hero_pool_team']['playerid'])."</td><td>".
            $report['records']['smallest_hero_pool_team']['value']."</td></tr>";
      }

    } else if (isset($report['players_additional'])) {
      $max_wr = 0;
      $max_matches = 0;
      foreach ($report['players_additional'] as $pid => $player) {
          if(!$max_matches || $report['players_additional'][$max_matches]['matches'] < $player['matches'] )
            $max_matches = $pid;
          if($player['matches'] <= $report['settings']['limiter']) continue;
          if(!$max_wr || ( $report['players_additional'][$max_wr]['won']/$report['players_additional'][$max_wr]['matches'] < $player['won']/$player['matches']) )
            $max_wr = $pid;
      }

      $res .= "<tr><td>".locale_string("most_matches")."</td><td>".
        player_name($max_matches)."</td><td>".$report['players_additional'][$max_matches]['matches']."</td></tr>";

      if($max_wr)
        $res .= "<tr><td>".locale_string("highest_winrate")."</td><td>".
            player_name($max_wr)."</td><td>".
            number_format($report['players_additional'][$max_wr]['won']*100/$report['players_additional'][$max_wr]['matches'],2)."%</td></tr>";
    }
      if (isset($report['records'])) {
        $res .= "<tr><td>".locale_string("widest_hero_pool")."</td><td>".
          player_name($report['records']['widest_hero_pool']['playerid'])."</td><td>".$report['records']['widest_hero_pool']['value']."</td></tr>";
        $res .= "<tr><td>".locale_string("smallest_hero_pool")."</td><td>".
          player_name($report['records']['smallest_hero_pool']['playerid'])."</td><td>".$report['records']['smallest_hero_pool']['value']."</td></tr>";
      }

      if (isset($report['averages_players'])) {
        $res .= "<tr><td>".locale_string("diversity")."</td><td>".
          player_name($report['averages_players']['diversity'][0]['playerid'])."</td><td>".
          number_format($report['averages_players']['diversity'][0]['value']*100,2)."%</td></tr>";
      }

    $res .= "</table>";

    $res .= "<div class=\"content-text\"><a href=\"http://".
        $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."&mod=participants\">".locale_string("full_participants").
        "</a> / <a href=\"http://".
        $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."&mod=records\">".locale_string("full_records").
        "</a></div>";
    $res .= "</div>";
  }

  if (isset($report['records']) && isset($report['settings']['overview_include_records']) && $report['settings']['overview_include_records']) {
    $res .= "<div class=\"content-header\">".locale_string("records")."</div>";
    $res .= rg_view_generate_records($report);
  }

  $res .= "<div class=\"content-header\">".locale_string("draft")."</div>";

  if($report['settings']['overview_top_contested'] && isset($report['pickban'])) {
      $res .=  "<table id=\"over-heroes-pickban\" class=\"list\"><caption>".locale_string("top_contested_heroes")."</caption>
                                            <tr class=\"thead\">
                                              <th>".locale_string("hero")."</th>
                                              <th>".locale_string("matches_total")."</th>
                                              <th>".locale_string("matches_picked")."</th>
                                              <th>".locale_string("winrate")."</th>
                                              <th>".locale_string("matches_banned")."</th>
                                              <th>".locale_string("winrate")."</th>
                                            </tr>";

      $workspace = $report['pickban'];
      uasort($workspace, function($a, $b) {
        if($a['matches_total'] == $b['matches_total']) return 0;
        else return ($a['matches_total'] < $b['matches_total']) ? 1 : -1;
      });

      $counter = $report['settings']['overview_top_contested_count'];
      foreach($workspace as $hid => $hero) {
        if($counter == 0) break;
        $res .=  "<tr>
                                    <td>".($hid ? hero_full($hid) : "").
                                   "</td>
                                    <td>".$hero['matches_total']."</td>
                                    <td>".$hero['matches_picked']."</td>
                                    <td>".number_format($hero['winrate_picked']*100,2)."%</td>
                                    <td>".$hero['matches_banned']."</td>
                                    <td>".number_format($hero['winrate_banned']*100,2)."%</td>
                                  </tr>";
        $counter--;
      }
      unset($workspace);
      $res .= "</table>";
  }

  $res .= "<div class=\"small-list-wrapper\">";
  if($report['settings']['overview_top_picked']) {
      $res .=  "<table id=\"over-heroes-pick\" class=\"list list-small\"><caption>".locale_string("top_picked_heroes")."</caption>
                                            <tr class=\"thead\">
                                              <th>".locale_string("hero")."</th>
                                              <th>".locale_string("matches_s")."</th>
                                              <th>".locale_string("matches_picked")."</th>
                                              <th>".locale_string("winrate_s")."</th>
                                            </tr>";

      $workspace = $report['pickban'];
      uasort($workspace, function($a, $b) {
        if($a['matches_picked'] == $b['matches_picked']) {
          if($a['matches_total'] == $b['matches_total']) return 0;
          else return ($a['matches_total'] < $b['matches_total']) ? 1 : -1;
        } else return ($a['matches_picked'] < $b['matches_picked']) ? 1 : -1;
      });

      $counter = $report['settings']['overview_top_picked_count'];
      foreach($workspace as $hid => $hero) {
        if($counter == 0) break;
        $res .=  "<tr>
                                    <td>".($hid ? hero_full($hid) : "").
                                   "</td>
                                    <td>".$hero['matches_total']."</td>
                                    <td>".$hero['matches_picked']."</td>
                                    <td>".number_format($hero['winrate_picked']*100,2)."%</td>
                                  </tr>";
        $counter--;
      }
      unset($workspace);
      $res .= "</table>";
  }

  if($report['settings']['overview_top_bans']) {
      $res .=  "<table id=\"over-heroes-ban\" class=\"list list-small\"><caption>".locale_string("top_banned_heroes")."</caption>
                                            <tr class=\"thead\">
                                              <th>".locale_string("hero")."</th>
                                              <th>".locale_string("matches_s")."</th>
                                              <th>".locale_string("matches_banned")."</th>
                                              <th>".locale_string("winrate_s")."</th>
                                            </tr>";

      $workspace = $report['pickban'];
      uasort($workspace, function($a, $b) {
        if($a['matches_banned'] == $b['matches_banned']) {
          if($a['matches_total'] == $b['matches_total']) return 0;
          else return ($a['matches_total'] < $b['matches_total']) ? 1 : -1;
        } else return ($a['matches_banned'] < $b['matches_banned']) ? 1 : -1;
      });

      $counter = $report['settings']['overview_top_bans_count'];
      foreach($workspace as $hid => $hero) {
        if($counter == 0) break;
        $res .=  "<tr>
                                    <td>".($hid ? hero_full($hid) : "").
                                   "</td>
                                    <td>".$hero['matches_total']."</td>
                                    <td>".$hero['matches_banned']."</td>
                                    <td>".number_format($hero['winrate_banned']*100,2)."%</td>
                                  </tr>";
        $counter--;
      }
      unset($workspace);
      $res .= "</table>";
  }
  $res .= "</div>";

  if($report['settings']['overview_top_draft']) {
    $res .= "<div class=\"small-list-wrapper\">";

    for ($i=0; $i<2; $i++) {
      for ($j=1; $j<4; $j++) {
        if($report['settings']["overview_draft_".$i."_".$j] && isset($report['draft']) && !empty($report['draft'][$i][$j])) {

            $res .=  "<table id=\"over-draft-$i-$j\" class=\"list list-small\">
                                        <caption>".locale_string("stage_num_1")." $j ".locale_string("stage_num_2")." ".($i ? locale_string("picks") : locale_string("bans"))."</caption>
                                                  <tr class=\"thead\">
                                                    <th>".locale_string("hero")."</th>
                                                    <th>".locale_string("matches")."</th>
                                                    <th>".locale_string("winrate_s")."</th>
                                                  </tr>";

            $counter = $report['settings']["overview_draft_".$i."_".$j."_count"];

            uasort($report['draft'][$i][$j], function($a, $b) {
              if($a['matches'] == $b['matches']) return 0;
              else return ($a['matches'] < $b['matches']) ? 1 : -1;
            });
            foreach($report['draft'][$i][$j] as $hero) {
              if($counter == 0) break;
              $res .=  "<tr>
                                          <td>".($hid ? hero_full($hero['heroid']) : "").
                                         "</td>
                                          <td>".$hero['matches']."</td>
                                          <td>".number_format($hero['winrate']*100,2)."%</td>
                                        </tr>";
              $counter--;
            }
            $res .= "</table>";
        }
      }
    }

    $res .= "</div>";
  }

  if($report['settings']['overview_top_hero_pairs'] && isset($report['hero_pairs']) && !empty($report['hero_pairs'])) {
      $res .= "<table id=\"over-hero-pairs\" class=\"list\">
                                <caption>".locale_string("top_pick_pairs")."</caption>
                                <tr class=\"thead\">
                                  <th>".locale_string("hero")." 1</th>
                                  <th>".locale_string("hero")." 2</th>
                                  <th>".locale_string("matches")."</th>
                                  <th>".locale_string("winrate")."</th>
                                </tr>";
      $counter = $report['settings']['overview_top_hero_pairs_count'];
      foreach($report['hero_pairs'] as $pair) {
        if($counter == 0) break;
        $res .= "<tr>
                                  <td>".($pair['heroid1'] ? hero_full($pair['heroid1']) : "").
                                 "</td><td>".($pair['heroid2'] ? hero_full($pair['heroid2'])  : "").
                                 "</td>
                                 <td>".$pair['matches']."</td>
                                 <td>".number_format($pair['winrate']*100,2)."%</td>
                                </tr>";
        $counter--;
      }
      $res .= "</table>";
  }

  $res .= "<div class=\"content-text\"><a href=\"http://".
      $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."&mod=heroes-draft\">".locale_string("full_draft").
      "</a> / <a href=\"http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."&mod=heroes-hero_combo_graph\">".
      locale_string("hero_combo_graph")."</a></div>";

  if(!isset($report['teams']) && $report['settings']['overview_top_player_pairs'] && isset($report['player_pairs']) && !empty($report['player_pairs'])) {
      $res .= "<div class=\"content-header\">".locale_string("top_player_pairs")."</div>";

      $res .= "<table id=\"over-player-pairs\" class=\"list\">
                                <tr class=\"thead\">
                                  <th>".locale_string("player")." 1</th>
                                  <th>".locale_string("player")." 2</th>
                                  <th>".locale_string("matches")."</th>
                                  <th>".locale_string("winrate")."</th>
                                </tr>";
      $counter = $report['settings']['overview_top_player_pairs_count'];
      foreach($report['player_pairs'] as $pair) {
        if($counter == 0) break;
        $res .= "<tr>
                                  <td>".$report['players'][ $pair['playerid1'] ].
                                 "</td><td>".$report['players'][ $pair['playerid2'] ].
                                 "</td>
                                 <td>".$pair['matches']."</td>
                                 <td>".number_format($pair['winrate']*100,2)."</td>
                                </tr>";
        $counter--;
      }
      $res .= "</table>";

      $res .= "<div class=\"content-text\"><a href=\"http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."&mod=players-player_combos\">".locale_string("full_player_combos")."</a></div>";
  }


  if($report['settings']['overview_matches']) {
    $res .= "<div class=\"content-header\">".locale_string("notable_matches")."</div>";
    $res .= "<div class=\"content-cards\">";
    if($report['settings']['overview_first_match'])
      $res .= "<h1>".locale_string("first_match")."</h1>".match_card($report['first_match']['mid']);
    if($report['settings']['overview_last_match'])
      $res .= "<h1>".locale_string("last_match")."</h1>".match_card($report['last_match']['mid']);
    if($report['settings']['overview_records_stomp'])
      $res .= "<h1>".locale_string("match_stomp")."</h1>".match_card($report['records']['stomp']['matchid']);
    if($report['settings']['overview_records_comeback'])
      $res .= "<h1>".locale_string("match_comeback")."</h1>".match_card($report['records']['comeback']['matchid']);
    if($report['settings']['overview_records_duration']) {
      if (compare_ver($report['ana_version'], array(1,0,4,-4,1)) < 0)
        $res .= "<h1>".locale_string("longest_match")."</h1>".match_card($report['records']['duration']['matchid']);
      else {
        $res .= "<h1>".locale_string("longest_match")."</h1>".match_card($report['records']['longest_match']['matchid']);
        $res .= "<h1>".locale_string("shortest_match")."</h1>".match_card($report['records']['shortest_match']['matchid']);
      }
    }

    $res .= "<div class=\"content-text\"><a href=\"http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."&mod=matches\">".locale_string("full_matches")."</a></div>";

    $res .= "</div>";
  }

  $res .= "<div class=\"content-text\">".locale_string("desc_overview")."</div>";
  $res .= "<div class=\"content-text small\">".
    locale_string("limiter_h").": ".$report['settings']['limiter']."<br />".
    locale_string("limiter_l").": ".$report['settings']['limiter_triplets']."<br />".
    (compare_ver($report['ana_version'], array(1,1,0,-3,5)) >= 0 ?
      locale_string("limiter_gr").": ".$report['settings']['limiter_combograph']."<br />"
      : "").
    locale_string("ana_version").": ".parse_ver($report['ana_version'])."</div>";

  return $res;
}

?>
