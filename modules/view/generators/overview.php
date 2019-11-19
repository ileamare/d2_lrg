<?php
include "overview_sections.php";
include_once("$root/modules/view/generators/teams_summary.php");
include_once("$root/modules/view/functions/convert_time.php");

function rg_view_generator_overview($modlink, $context, $foreword = "") {
  if(!sizeof($context)) return "";

  global $report;
  global $meta;
  global $charts_colors;
  global $linkvars;
  global $leaguetag;

  if (!isset($context['main'])) $context['main'] = $context['random'];

  if (empty($modlink)) {
    $prefix = "overview-";
  } else {
    $prefix = $modlink."-";
    $modlink .= "-";
  }


  if (!isset($context['settings']['limiter_higher'])) $context['settings']['limiter_higher'] = $context['settings']['limiter'];
  if (!isset($context['settings']['limiter_lower'])) $context['settings']['limiter_lower'] = $context['settings']['limiter_triplets'];
  if (!isset($context['settings']['limiter_graph'])) $context['settings']['limiter_graph'] = $context['settings']['limiter_combograph'];

  if (!isset($context['settings']['overview_last_match_winners']))
    $context['settings']['overview_last_match_winners'] = $report['settings']['overview_last_match_winners'];

  $context_total_matches = isset($context['main']['matches_total']) ? $context['main']['matches_total'] : $context['main']['matches'];

  if (isset($context['overview'])) {
    foreach($context['overview'] as $k => $v) {
      $context[$k] = $v;
    }
  }

  $res = "<div class=\"content-text overview overview-head\">";
  $res .= $foreword;
  $res .= "<div class=\"block-content\">";

  $res .= locale_string("over-matches", ["num" => $context_total_matches ] )." ";
  if(isset($report['teams']))
    $res .= locale_string("over-teams", ["num" => $context['main']['teams_on_event'] ] )." ";
  else
    $res .= locale_string("over-players", ["num" => $context['main']['players_on_event'] ] )." ";

  $res .= "</div><div class=\"block-content\">";


  if($report['settings']['overview_versions'] && isset($context['versions'])) {
    $mode = reset($context['versions']);

    $ver = $meta['versions'][ (int) (key($context['versions'])/100) ].(
        key($context['versions']) % 100 ?
        chr( ord('a') + key($context['versions']) % 100 ) :
        ""
      );

    if ($mode/$context_total_matches > 0.99)
      $res .= locale_string("over-one-version", ["ver"=>$ver])." ";
    else $res .= locale_string("over-most-version", ["num" => $mode, "ver" => $ver])." ";

    unset($ver);
  }

  if($report['settings']['overview_modes'] && isset($context['modes'])) {
    $mode = reset($context['modes']);
    if ($mode/$context_total_matches > 0.99)
      $res .= locale_string("over-one-mode", ["gm" => $meta['modes'][ key($context['modes']) ] ])." ";
    else $res .= locale_string("over-most-mode", ["num" => $mode, "gm"=> $meta['modes'][ key($context['modes']) ] ])." ";
  }

  if($report['settings']['overview_regions'] && isset($context['regions'])) {
    $regions_matches = [];
    foreach ($context['regions'] as $mode => $data) {
      $region = $meta['regions'][ $meta['clusters'][$mode] ];
      if(isset($regions_matches[$region])) $regions_matches[$region] += $data;
      else $regions_matches[$region] = $data;
    }
    arsort($regions_matches);
    $mode = reset($regions_matches);
    if ($mode/$context_total_matches > 0.99)
      $res .= locale_string("over-one-region", [ "server" => key($regions_matches)] )." ";
    else
      $res .= locale_string("over-most-region", ["num"=>$mode, "server"=>key($regions_matches) ] )." ";
  }

  $res .= "</div>";

  if($report['settings']['overview_time_limits']) {
    $res .= "<div class=\"block-content\">";

    if(isset($context['first_match']))
      $res .= locale_string("over-first-match", ["date"=> date(locale_string("time_format")." ".locale_string("date_format"), $context['first_match']['date']) ])."<br />";
    if(isset($context['last_match']))
      $res .= locale_string("over-last-match", ["date"=> date(locale_string("time_format")." ".locale_string("date_format"), $context['last_match']['date']) ])."<br />";

    $res .= "</div>";
  }

  if(isset($report['matches_additional']) && ($context['settings']['overview_last_match_winners'] || (isset($context['last_match']['mid']) && !isset($context['settings']))) ) {
    $res .= "<div class=\"block-content\">";

    if( $report['matches_additional'][ $context['last_match']['mid'] ]['radiant_win'] ) {
      if(isset($report['teams']) &&
         isset($report['match_participants_teams'][ $context['last_match']['mid'] ]['radiant']) &&
         isset($report['teams'][ $report['match_participants_teams'][ $context['last_match']['mid'] ]['radiant'] ]['name']))
        $mode = $report['teams'][ $report['match_participants_teams'][ $context['last_match']['mid'] ]['radiant'] ]['name'];
      else $mode = locale_string("radiant");
    } else {
      if(isset($report['teams']) &&
         isset($report['match_participants_teams'][ $context['last_match']['mid'] ]['dire']) &&
         isset($report['teams'][ $report['match_participants_teams'][ $context['last_match']['mid'] ]['dire'] ]['name']))
        $mode = $report['teams'][ $report['match_participants_teams'][ $context['last_match']['mid'] ]['dire'] ]['name'];
      else $mode = locale_string("dire");
    }

    $res .= locale_string("over-last-match-winner", ["team"=>$mode])."</div>";
  }

  $res .= "</div>";


  if($report['settings']['overview_charts']) {
    global $use_graphjs;
    $use_graphjs = true;

    $res .= "<div class=\"content-text overview overview-charts\">";

    if(isset($context['versions']) && $report['settings']['overview_versions_chart']) {
      $chart_context_max = reset($context['versions']);
      if ($report['settings']['overview_versions'] && $chart_context_max/$context_total_matches < 0.99) {
        $chart_context = [];
        foreach ($context['versions'] as $el => $data) {
          $chart_context[] = $meta['versions'][ (int) ($el/100) ].( $el % 100 ? chr( ord('a') + $el % 100 ) : "" );
        }
        $res .= rg_generator_overview_chart("$prefix-patches", $chart_context, $context['versions']);
      }
    }

    if(isset($report['modes'])) {
      $chart_context_max = reset($report['modes']);
      if ($report['settings']['overview_modes'] && $chart_context_max/$context_total_matches < 0.99) {
        $chart_context = [];
        foreach ($context['modes'] as $mode => $data) {
          $chart_context[] = $meta['modes'][$mode];
        }
        $res .= rg_generator_overview_chart("$prefix-modes", $chart_context, $context['modes']);
      }
    }

    if (isset($context['regions'])) {
      $chart_context_max = reset($regions_matches);
      if ($report['settings']['overview_regions'] && $chart_context_max/$context_total_matches < 0.99) {
        $region_names = array_keys($regions_matches);
        $res .= rg_generator_overview_chart("$prefix-regions", $region_names, $regions_matches);
        $colors = array_slice($charts_colors, 0, sizeof($region_names));
        unset($region_names);
      }
      unset($regions_matches);
    }

    if ($report['settings']['overview_sides_graph']) {
      $res .= rg_generator_overview_chart( "$prefix-sides",
        [ locale_string("radiant"), locale_string("dire") ],
        [ $context['main']['radiant_wr'].",".$context['main']['dire_wr'] ]);
    }

    if ($report['settings']['overview_heroes_contested_graph']) {
      $res .= rg_generator_overview_chart( "$prefix-heroes",
        [ locale_string("heroes_pickbanned"), locale_string("heroes_picked"), locale_string("heroes_banned"), locale_string("heroes_uncontested") ],
        [ ($context['main']['heroes_banned']-$context['main']['heroes_contested']+$context['main']['heroes_picked']),
          ($context['main']['heroes_contested']-$context['main']['heroes_banned']),
          ($context['main']['heroes_contested']-$context['main']['heroes_picked']),
          (sizeof($meta['heroes'])-$context['main']['heroes_contested'])
        ]);
    }

    if ($report['settings']['overview_days_graph'] && isset($context['days'])) {
      $converted_modes = array();
      $matchcount = array();
      foreach($context['days'] as $dn => $day) {
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
    foreach($context['main'] as $key => $value) {
      $res .= "<tr><td>".locale_string($key)."</td><td>".
          (
            strpos($key, "duration") !== FALSE || strpos($key, "_len") !== FALSE ?
            convert_time($value) :
            $value
            ).
        "</td></tr>";
    }
    $res .= "</table>";
  }

  if(isset($report['players_additional']) || isset($report["teams"])) {
    $res .= "<div class=\"content-header\">".locale_string("notable_paricipans")."</div>";
    $res .= "<div class=\"content-cards\">";

    if (isset($report['teams']) && $context['settings']['overview_last_match_winners']) {
      if($report['matches_additional'][ $context['last_match']['mid'] ]['radiant_win']) {
          if (isset( $report['match_participants_teams'][ $context['last_match']['mid'] ]['radiant'] ))
              $tid = $report['match_participants_teams'][ $context['last_match']['mid'] ]['radiant'];
          else $tid = 0;
      } else {
          if (isset($report['match_participants_teams'][ $context['last_match']['mid'] ]['dire']) )
              $tid = $report['match_participants_teams'][ $context['last_match']['mid'] ]['dire'];
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
      foreach ($context['teams'] as $team_id => $team) {
        if(!isset($report['teams'][$team_id]['matches_total'])) continue; //FIXME
        if(!$max_matches || $report['teams'][$max_matches]['matches_total'] < $report['teams'][$team_id]['matches_total'] )
          $max_matches = $team_id;
        if($report['teams'][$team_id]['matches_total'] <= $context['settings']['limiter_higher']) continue;

        if($max_wr == 0) $max_wr = $team_id;
        else if(!$max_wr ||
                $report['teams'][$max_wr]['wins']/$report['teams'][$max_wr]['matches_total'] <
                  $report['teams'][$team_id]['wins']/$report['teams'][$team_id]['matches_total'] )
          $max_wr = $team_id;
      }

      $res .= "<tr><td>".locale_string("most_matches")."</td><td>".
          team_link($max_matches)."</td><td>".$report['teams'][$max_matches]['matches_total']."</td></tr>";

      if($max_wr)
        $res .= "<tr><td>".locale_string("highest_winrate")."</td><td>".
          team_link($max_wr)."</td><td>".number_format($report['teams'][$max_wr]['wins']*100/$report['teams'][$max_wr]['matches_total'],2)."%</td></tr>";

      if (isset($context['records'])) {
        $res .= "<tr><td>".locale_string("widest_hero_pool_team")."</td><td>".
            team_link($report['records']['widest_hero_pool_team']['playerid'])."</td><td>".
            $context['records']['widest_hero_pool_team']['value']."</td></tr>";

        $res .= "<tr><td>".locale_string("smallest_hero_pool_team")."</td><td>".
            team_link($report['records']['smallest_hero_pool_team']['playerid'])."</td><td>".
            $context['records']['smallest_hero_pool_team']['value']."</td></tr>";
      }

    } else if (isset($report['players_additional']) && isset($context['players_summary'])) {
      $max_wr = 0;
      $max_matches = 0;
      foreach ($context['players_summary'] as $pid => $player) {
          if(!$max_matches || $report['players_additional'][$max_matches]['matches'] < $report['players_additional'][$pid]['matches'] )
            $max_matches = $pid;
          if($report['players_additional'][$pid]['matches'] <= $context['settings']['limiter_higher']) continue;
          if(!$max_wr || (
              $report['players_additional'][$max_wr]['won']/$report['players_additional'][$max_wr]['matches'] <
                $report['players_additional'][$pid]['won']/$report['players_additional'][$pid]['matches']) )
            $max_wr = $pid;
      }

      $res .= "<tr><td>".locale_string("most_matches")."</td><td>".
        player_name($max_matches)."</td><td>".$report['players_additional'][$max_matches]['matches']."</td></tr>";

      if($max_wr)
        $res .= "<tr><td>".locale_string("highest_winrate")."</td><td>".
            player_name($max_wr)."</td><td>".
            number_format($report['players_additional'][$max_wr]['won']*100/$report['players_additional'][$max_wr]['matches'],2)."%</td></tr>";
    }
      if (isset($context['records'])) {
        $res .= "<tr><td>".locale_string("widest_hero_pool")."</td><td>".
          player_name($context['records']['widest_hero_pool']['playerid'])."</td><td>".$context['records']['widest_hero_pool']['value']."</td></tr>";
        $res .= "<tr><td>".locale_string("smallest_hero_pool")."</td><td>".
          player_name($context['records']['smallest_hero_pool']['playerid'])."</td><td>".$context['records']['smallest_hero_pool']['value']."</td></tr>";
      }

      if (isset($context['averages_players']) && isset($context['averages_players']['diversity'])) {
        $res .= "<tr><td>".locale_string("diversity")."</td><td>".
          player_name($context['averages_players']['diversity'][0]['playerid'])."</td><td>".
          number_format($context['averages_players']['diversity'][0]['value']*100,2)."%</td></tr>";
      }

    $res .= "</table>";

    $res .= "<div class=\"content-text\"><a href=\"?league=$leaguetag&mod=$modlink".
            "participants".(empty($linkvars) ? "" : "&".$linkvars)."\">".locale_string("full_participants").
        "</a> / <a href=\"?league=$leaguetag&mod=records".
        (empty($linkvars) ? "" : "&".$linkvars)."\">".locale_string("full_records").
        "</a></div>";
    $res .= "</div>";
  }

  if (isset($context['records']) && isset($report['settings']['overview_include_records']) && $report['settings']['overview_include_records']) {
    $res .= "<div class=\"content-header\">".locale_string("records")."</div>";
    $res .= rg_view_generate_records($context);
  }

  if(isset($context['teams']) && $report['settings']['overview_teams_summary_short']) {
    $res .= "<div class=\"content-header\">".locale_string("teams")."</div>";
    $res .= rg_view_generator_teams_summary($context['teams'], true);
    $res .= "<div class=\"content-text\"><a href=\"?league=$leaguetag&mod=teams".
        (empty($linkvars) ? "" : "&".$linkvars)."\">".locale_string("full_teams_info").
        "</a></div>";
  }

  $res .= "<div class=\"content-header\">".locale_string("draft")."</div>";

  $res .= rg_draft_accuracy_test($context['pickban'], $context['draft']);

  if($report['settings']['overview_top_contested']) {
    $res .= rg_generator_pickban_overview($prefix."-pickban", $context['pickban'], $context_total_matches, $report['settings']['overview_top_contested_count']);
  }

  $res .= "<div class=\"small-list-wrapper\">";
  if($report['settings']['overview_top_picked']) {
      $res .=  "<table id=\"over-heroes-pick\" class=\"list list-small\"><caption>".locale_string("top_picked_heroes")."</caption>
                <thead><tr>
                  <th>".locale_string("hero")."</th>
                  <th>".locale_string("matches_s")."</th>
                  <th>".locale_string("matches_picked")."</th>
                  <th>".locale_string("winrate_s")."</th>
                </tr></thead>";

      $workspace = $context['pickban'];
      uasort($workspace, function($a, $b) {
        if($a['matches_picked'] == $b['matches_picked']) {
          if($a['matches_total'] == $b['matches_total']) return 0;
          else return ($a['matches_total'] < $b['matches_total']) ? 1 : -1;
        } else return ($a['matches_picked'] < $b['matches_picked']) ? 1 : -1;
      });

      $counter = $report['settings']['overview_top_picked_count'];
      foreach($workspace as $hid => $hero) {
        if($counter == 0) break;
        $res .=  "<tr><td>".($hid ? hero_full($hid) : "")."</td>".
                  "<td>".$hero['matches_total']."</td>".
                  "<td>".$hero['matches_picked']."</td>".
                  "<td>".number_format($hero['winrate_picked']*100,2)."%</td></tr>";
        $counter--;
      }
      unset($workspace);
      $res .= "</table>";
  }

  if($report['settings']['overview_top_bans']) {
      $res .=  "<table id=\"over-heroes-ban\" class=\"list list-small\"><caption>".locale_string("top_banned_heroes")."</caption>
                <thead><tr>
                  <th>".locale_string("hero")."</th>
                  <th>".locale_string("matches_s")."</th>
                  <th>".locale_string("matches_banned")."</th>
                  <th>".locale_string("winrate_s")."</th>
                </tr></thead>";

      $workspace = $context['pickban'];
      uasort($workspace, function($a, $b) {
        if($a['matches_banned'] == $b['matches_banned']) {
          if($a['matches_total'] == $b['matches_total']) return 0;
          else return ($a['matches_total'] < $b['matches_total']) ? 1 : -1;
        } else return ($a['matches_banned'] < $b['matches_banned']) ? 1 : -1;
      });

      $counter = $report['settings']['overview_top_bans_count'];
      foreach($workspace as $hid => $hero) {
        if($counter == 0) break;
        $res .=  "<tr><td>".($hid ? hero_full($hid) : "")."</td>
                    <td>".$hero['matches_total']."</td>
                    <td>".$hero['matches_banned']."</td>
                    <td>".number_format($hero['winrate_banned']*100,2)."%</td>
                  </tr>";
        $counter--;
      }
      unset($workspace);
      $res .= "</table>";
  }

  $res .= rg_generator_uncontested($meta['heroes'], $context['pickban'], true);

  $res .= "</div>";

  if($report['settings']['overview_top_draft']) {
    $res .= "<div class=\"small-list-wrapper\">";

    for ($i=1; $i>=0; $i--) {
      for ($j=1; $j<4; $j++) {
        if($report['settings']["overview_draft_".$i."_".$j] && isset($context['draft']) && !empty($context['draft'][$i][$j])) {

            $res .=  "<table id=\"over-draft-$i-$j\" class=\"list list-small\">
                      <caption>".locale_string("stage_num_1")." $j ".locale_string("stage_num_2")." ".($i ? locale_string("picks") : locale_string("bans"))."</caption>
                        <thead><tr>
                          <th>".locale_string("hero")."</th>
                          <th>".locale_string("matches")."</th>
                          <th>".locale_string("winrate_s")."</th>
                        </tr></thead>";

            $counter = $report['settings']["overview_draft_".$i."_".$j."_count"];

            uasort($context['draft'][$i][$j], function($a, $b) {
              if($a['matches'] == $b['matches']) return 0;
              else return ($a['matches'] < $b['matches']) ? 1 : -1;
            });
            foreach($context['draft'][$i][$j] as $hero) {
              if($counter == 0) break;
              $res .=  "<tr><td>".($hid ? hero_full($hero['heroid']) : "").
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

  if($report['settings']['overview_positions'] ?? true) {
    generate_positions_strings();
    $res .= "<div class=\"content-text\"><h1>".locale_string("positions")."</h1></div>";
    $res .= rg_generator_overview_positions_section($prefix, 
      $context['hero_positions'], 
      $context['pickban'],
      $report['settings']['overview_positions_count'] ?? $report['settings']['overview_top_picked_count'] ?? 5,
      $report['settings']['overview_positions_sort'] ?? "matches"
    );
  }

  if($report['settings']['overview_top_hero_pairs'] && isset($context['hero_pairs']) && !empty($context['hero_pairs'])) {
    $res .= rg_generator_overview_combos($prefix."hero-pairs",
              locale_string("top_pick_pairs")." (".locale_string("deviation").")",
              $context['hero_pairs'],
              $report['settings']['overview_top_hero_pairs_count']
          );
  }

  $res .= "<div class=\"content-text\">".
            "<a href=\"?league=$leaguetag&mod=".$modlink.
            "heroes-pickban".(empty($linkvars) ? "" : "&".$linkvars)."\">".locale_string("pickban").
            "</a> / ".
            "<a href=\"?league=$leaguetag&mod=".$modlink.
            "heroes-draft".(empty($linkvars) ? "" : "&".$linkvars)."\">".locale_string("full_draft").
            "</a> / ".
            "<a href=\"?league=$leaguetag&mod=".$modlink."heroes-meta_graph".(empty($linkvars) ? "" : "&".$linkvars)."\">".
            locale_string("meta_graph")."</a> / ".
            "<a href=\"?league=$leaguetag&mod=".$modlink."heroes-positions".(empty($linkvars) ? "" : "&".$linkvars)."\">".
            locale_string("positions")."</a> / ".
            "<a href=\"?league=$leaguetag&mod=".$modlink."heroes-combos".(empty($linkvars) ? "" : "&".$linkvars)."\">".
            locale_string("combos")."</a>".
          "</div>";

  if(!isset($report['teams']) && $report['settings']['overview_top_player_pairs'] && isset($context['player_pairs']) && !empty($context['player_pairs'])) {
    $res .= rg_generator_overview_combos($prefix."player-pairs",
              locale_string("top_player_pairs")." (".locale_string("deviation").")",
              $context['player_pairs'],
              $report['settings']['overview_top_player_pairs_count'],
              false
          );

      $res .= "<div class=\"content-text\"><a href=\"?league=$leaguetag&mod=".
              $modlink."players-combos".(empty($linkvars) ? "" : "&".$linkvars)."\">".locale_string("full_player_combos")."</a></div>";
  }


  if($report['settings']['overview_matches']) {
    $res .= "<div class=\"content-header\">".locale_string("notable_matches")."</div>";
    $res .= "<div class=\"content-cards\">";

    if(($context['settings']['overview_first_match'] ?? true) && isset($context['first_match']))
      $res .= "<h1>".locale_string("first_match")."</h1>".match_card($context['first_match']['mid']);
    if(($context['settings']['overview_last_match'] ?? true) && isset($context['last_match']))
      $res .= "<h1>".locale_string("last_match")."</h1>".match_card($context['last_match']['mid']);

    if(isset($context['records'])) {
      if($report['settings']['overview_records_stomp'])
        $res .= "<h1>".locale_string("match_stomp")."</h1>".match_card($context['records']['stomp']['matchid']);
      if($report['settings']['overview_records_comeback'])
        $res .= "<h1>".locale_string("match_comeback")."</h1>".match_card($context['records']['comeback']['matchid']);
      if($report['settings']['overview_records_duration']) {
        $res .= "<h1>".locale_string("longest_match")."</h1>".match_card($context['records']['longest_match']['matchid']);
        $res .= "<h1>".locale_string("shortest_match")."</h1>".match_card($context['records']['shortest_match']['matchid']);
      }
    }

    $res .= "<div class=\"content-text\"><a href=\"?league=$leaguetag&mod=".
            $modlink."matches".(empty($linkvars) ? "" : "&".$linkvars)."\">".locale_string("full_matches")."</a></div>";

    $res .= "</div>";
  }

  $res .= "<div class=\"content-text\">".locale_string("desc_overview")."</div>";
  $res .= "<div class=\"content-text small\">".
    locale_string("limiter_h").": ".$context['settings']['limiter_higher']."<br />".
    locale_string("limiter_l").": ".$context['settings']['limiter_lower']."<br />".
    locale_string("limiter_gr").": ".$context['settings']['limiter_graph']."<br />".
    locale_string("ana_version").": ".parse_ver($report['ana_version'])."</div>";

  return $res;
}

?>
