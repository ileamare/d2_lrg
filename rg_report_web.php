<?php
require_once("rg_report_out_settings.php");
require_once("modules/mod_versions.php");

$lg_version = array( 1, 0, 3, 0 );

/* FUNCTIONS */  {
  function has_pair($hid, $pairs) {
    foreach($pairs as $pair) {
      if(!isset($keys)) $keys = array_keys($pair);
      if ($pair[$keys[0]] == $hid) return true;
      if ($pair[$keys[1]] == $hid) return true;
    }
    return false;
  }

  function check_module($module) {
    global $lrg_get_depth;
    global $lrg_use_get;
    global $mod;

    if(unset_module()) {
      $mod = $module;
    }

    return ($lrg_use_get && stripos($mod, $module) === 0) || !$lrg_use_get || !$lrg_get_depth || $lrg_get_depth <= substr_count($module, "-");
  }

  function unset_module() {
    global $unset_module;

    if($unset_module) {
      $unset_module = false;
      return true;
    }
    return false;
  }

  function hero_portrait($hid) {
    global $meta;
    return "<img class=\"hero_portrait\" src=\"res/heroes/".$meta['heroes'][ $hid ]['tag'].
      ".png\" alt=\"".$meta['heroes'][ $hid ]['tag']."\" />";
  }

  function hero_full($hid) {
    global $meta;
    return hero_portrait($hid)." ".$meta['heroes'][ $hid ]['name'];
  }

  function player_name($pid) {
    global $report;
    return $report['players'][$pid];
  }

  function player_card_link() {

  }

  function player_card($player_id) {
    global $report;
    global $meta;
    global $strings;
    $pname = $report['players'][$player_id];
    $pinfo = $report['players_additional'][$player_id];

    $output = "<div class=\"player-card\"><div class=\"player-name\"><a href=\"http://opendota.com/players/$player_id\" target=\"_blank\" rel=\"noopener\">".$pname." (".$player_id.")</a></div>";
    if(isset($report['teams']))
      $output .= "<div class=\"player-team\">".team_name($pinfo['team'])." (".$pinfo['team'].")</div>";
    $output .= "<div class=\"player-add-info\">".
                  "<div class=\"player-info-line\"><span class=\"caption\">".$strings['matches'].":</span> ".$pinfo['matches']." (".
                    $pinfo['won']." - ".($pinfo['matches'] - $pinfo['won']).")</div>".
                  "<div class=\"player-info-line\"><span class=\"caption\">".$strings['winrate'].":</span> ".number_format($pinfo['won']*100/$pinfo['matches'], 2)."%</div>".
                  "<div class=\"player-info-line\"><span class=\"caption\">".$strings['gpm'].":</span> ".number_format($pinfo['gpm'],1)."</div>".
                  "<div class=\"player-info-line\"><span class=\"caption\">".$strings['xpm'].":</span> ".number_format($pinfo['xpm'],1)."</div>".
                  "<div class=\"player-info-line\"><span class=\"caption\">".$strings['hero_pool'].":</span> ".$pinfo['hero_pool_size']."</div></div>";

    # heroes
    $output .= "<div class=\"player-heroes\"><div class=\"section-caption\">".$strings['heroes']."</div><div class=\"section-lines\">";
    foreach($pinfo['heroes'] as $hero) {
      $output .= "<div class=\"player-info-line\"><span class=\"caption\">".hero_full($hero['heroid']).":</span> ";
      $output .= $hero['matches']." - ".number_format($hero['wins']*100/$hero['matches'], 2)."%</div>";
    }
    $output .= "</div></div>";

    # positions
    $output .= "<div class=\"player-positions\"><div class=\"section-caption\">".$strings['player_positions']."</div><div class=\"section-lines\">";
    foreach($pinfo['positions'] as $position) {
      $output .= "<div class=\"player-info-line\"><span class=\"caption\">".($position['core'] ? $strings['core']." " : $strings['support']).
                    $meta['lanes'][ $position['lane'] ].":</span> ";
      $output .= $position['matches']." - ".number_format($position['wins']*100/$position['matches'], 2)."%</div>";
    }
    $output .= "</div></div>";


    return $output."</div>";
  }

  function team_name($tid) {
    global $report;
    if($tid && isset($report['teams'][ $tid ]['name']))
      return $report['teams'][ $tid ]['name'];
    return "(no team)";
  }

  function team_card($tid) {
    global $report;
    global $meta;
    global $strings;

    $output = "<div class=\"team-card\"><div class=\"team-name\">".team_name($tid)." (".$tid.")</div>";

    $output .= "<div class=\"team-info-block\">".
                  "<div class=\"section-caption\">".$strings['summary'].":</div>".
                  "<div class=\"team-info-line\"><span class=\"caption\">".$strings['matches'].":</span> ".$report['teams'][$tid]['matches_total']."</div>".
                  "<div class=\"team-info-line\"><span class=\"caption\">".$strings['winrate'].":</span> ".
                      number_format($report['teams'][$tid]['wins']*100/$report['teams'][$tid]['matches_total'])."%</div>".
                  "<div class=\"team-info-line\"><span class=\"caption\">".$strings['gpm'].":</span> ".number_format($report['teams'][$tid]['averages']['gpm'])."</div>".
                  "<div class=\"team-info-line\"><span class=\"caption\">".$strings['xpm'].":</span> ".number_format($report['teams'][$tid]['averages']['xpm'])."</div>".
                  "<div class=\"team-info-line\"><span class=\"caption\">".$strings['kda'].":</span> ".number_format($report['teams'][$tid]['averages']['kills']).
                    "/".number_format($report['teams'][$tid]['averages']['deaths'])."/".number_format($report['teams'][$tid]['averages']['assists'])."</div></div>";

    $output .= "<div class=\"team-info-block\">".
                  "<div class=\"section-caption\">".$strings['active_roster'].":</div>";
    foreach($report['teams'][$tid]['active_roster'] as $player) {
      if (!isset($report['players'][$player])) continue;
      $position = reset($report['players_additional'][$player]['positions']);
      $output .= "<div class=\"team-info-line\">".player_name($player)." (".($position['core'] ? $strings['core']." " : $strings['support']).
                    $meta['lanes'][ $position['lane'] ].")</div>";
    }
    $output .= "</div>";

    if (isset($report['teams'][$tid]['pickban'])) {
      $heroes = $report['teams'][$tid]['pickban'];
      uasort($heroes, function($a, $b) {
        if($a['matches_picked'] == $b['matches_picked']) return 0;
        else return ($a['matches_picked'] < $b['matches_picked']) ? 1 : -1;
      });

      $output .= "<div class=\"team-info-block\">".
                    "<div class=\"section-caption\">".$strings['top_pick_heroes'].":</div>";
      $counter = 0;
      foreach($heroes as $hid => $stats) {
        if($counter > 3) break;
        $output .= "<div class=\"team-info-line\"><span class=\"caption\">".hero_full($hid).":</span> ";
        $output .= $stats['matches_picked']." - ".number_format($stats['wins_picked']*100/$stats['matches_picked'], 2)."%</div>";
        $counter++;
      }
      $output .= "</div>";
    }

    if (isset($report['teams'][$tid]['hero_pairs'])) {
      $heroes = $report['teams'][$tid]['hero_pairs'];

      $output .= "<div class=\"team-info-block\">".
                    "<div class=\"section-caption\">".$strings['top_pick_pairs'].":</div>";
      $counter = 0;
      foreach($heroes as $stats) {
        if($counter > 2) break;
        $output .= "<div class=\"team-info-line\"><span class=\"caption\">".hero_full($stats['heroid1'])." + ".hero_full($stats['heroid2']).":</span> ";
        $output .= $stats['matches']." - ".number_format($stats['winrate']*100, 2)."%</div>";
        $counter++;
      }
      $output .= "</div>";

    }

    return $output."</div>";

  }

  function match_card($mid) {
    global $report;
    global $meta;
    global $strings;
    $output = "<div class=\"match-card\"><div class=\"match-id\">".match_link($mid)."</div>";
    $radiant = "<div class=\"match-team radiant\">";
    $dire = "<div class=\"match-team dire\">";

    $players_radi = ""; $players_dire = "";
    $heroes_radi = "";  $heroes_dire = "";

    for($i=0; $i<10; $i++) {
      if($report['matches'][$mid][$i]['radiant']) {
        $players_radi .= "<div class=\"match-player\">".$report['players'][ $report['matches'][$mid][$i]['player'] ]."</div>";
        $heroes_radi .= "<div class=\"match-hero\">".hero_portrait($report['matches'][$mid][$i]['hero'])."</div>";
      } else {
        $players_dire .= "<div class=\"match-player\">".$report['players'][ $report['matches'][$mid][$i]['player'] ]."</div>";
        $heroes_dire .= "<div class=\"match-hero\">".hero_portrait($report['matches'][$mid][$i]['hero'])."</div>";
      }

    }
    if(isset($report['teams']) && isset($report['match_participants_teams'][$mid])) {
      if(isset($report['teams'][ $report['match_participants_teams'][$mid]['radiant'] ]['name']))
        $team_radiant = $report['teams'][ $report['match_participants_teams'][$mid]['radiant'] ]['name'].
          " (".$report['teams'][ $report['match_participants_teams'][$mid]['radiant'] ]['tag'].")";
      else $team_radiant = "Radiant";
      if(isset($report['teams'][ $report['match_participants_teams'][$mid]['dire'] ]['name']))
        $team_dire = $report['teams'][ $report['match_participants_teams'][$mid]['dire'] ]['name'].
          " (".$report['teams'][ $report['match_participants_teams'][$mid]['dire'] ]['tag'].")";
      else $team_dire = "Dire";
    } else {
      $team_radiant = "Radiant";
      $team_dire = "Dire";
    }
    $radiant .= "<div class=\"match-team-score\">".$report['matches_additional'][$mid]['radiant_score']."</div>".
                "<div class=\"match-team-name".($report['matches_additional'][$mid]['radiant_win'] ? " winner" : "")."\">".$team_radiant."</div>";
    $dire .= "<div class=\"match-team-score\">".$report['matches_additional'][$mid]['dire_score']."</div>".
             "<div class=\"match-team-name".($report['matches_additional'][$mid]['radiant_win'] ? "" : " winner")."\">".$team_dire."</div>";

    $radiant .= "<div class=\"match-players\">".$players_radi."</div><div class=\"match-heroes\">".$heroes_radi."</div>".
                "<div class=\"match-team-nw\">".$report['matches_additional'][$mid]['radiant_nw']."</div></div>";
    $dire .= "<div class=\"match-players\">".$players_dire."</div><div class=\"match-heroes\">".$heroes_dire."</div>".
            "<div class=\"match-team-nw\">".$report['matches_additional'][$mid]['dire_nw']."</div></div>";


    $output .= $radiant.$dire;

    $duration = (int)($report['matches_additional'][$mid]['duration']/3600);

    $duration = $duration ? $duration.":".(
          (int)($report['matches_additional'][$mid]['duration']%3600/60) < 10 ?
          "0".(int)($report['matches_additional'][$mid]['duration']%3600/60) :
          (int)($report['matches_additional'][$mid]['duration']%3600/60)
        ) : ((int)($report['matches_additional'][$mid]['duration']%3600/60));

    $duration = $duration.":".(
      (int)($report['matches_additional'][$mid]['duration']%60) < 10 ?
      "0".(int)($report['matches_additional'][$mid]['duration']%60) :
      (int)($report['matches_additional'][$mid]['duration']%60)
    );

    $output .= "<div class=\"match-add-info\">
                  <div class=\"match-info-line\"><span class=\"caption\">".$strings['duration'].":</span> ".
                    $duration."</div>
                  <div class=\"match-info-line\"><span class=\"caption\">".$strings['region'].":</span> ".
                    $meta['regions'][
                      $meta['clusters'][ $report['matches_additional'][$mid]['cluster'] ]
                    ]."</div>
                  <div class=\"match-info-line\"><span class=\"caption\">".$strings['game_mode'].":</span> ".
                    $meta['modes'][$report['matches_additional'][$mid]['game_mode']]."</div>
                    <div class=\"match-info-line\"><span class=\"caption\">".$strings['winner'].":</span> ".
                      ($report['matches_additional'][$mid]['radiant_win'] ? $team_radiant : $team_dire)."</div>
                    <div class=\"match-info-line\"><span class=\"caption\">".$strings['date'].":</span> ".
                      date($strings['time_format']." ".$strings['date_format'], $report['matches_additional'][$mid]['date'])."</div>
                </div>";

    return $output."</div>";
  }

  function join_matches($matches) {
    $output = array();
    foreach($matches as $match) {
      $output[] = match_link($match);
    }
    return implode($output, ", ");

  }

  function match_link($mid) {
    return "<a href=\"https://opendota.com/matches/$mid\" target=\"_blank\" rel=\"noopener\">$mid</a>";
  }

  function join_selectors($modules, $level, $parent="") {
    global $lrg_use_get;
    global $lrg_get_depth;
    global $level_codes;
    global $mod;
    global $strings;
    global $leaguetag;
    global $max_tabs;
    global $linkvars;

    $out = "";
    $first = true;
    $unset_selector = false;

    if(empty($parent)) {
      $selectors = explode("-", $mod);
      if(!isset($modules[$selectors[0]])) $unset_selector = true;
    } else {
      $selectors = explode("-", str_replace($parent."-", "", $mod));
      if(!isset($modules[$selectors[0]])) $unset_selector = true;
    }
    # reusing assets Kappa
    $selectors = array();
    $selectors_num = sizeof($modules);

    foreach($modules as $modname => $module) {
      if ($selectors_num < $max_tabs) {
        if($lrg_use_get && $lrg_get_depth > $level) {
          if (stripos($mod, (empty($parent) ? "" : $parent."-" ).$modname) === 0)
            $selectors[] = "<span class=\"selector active\">".$strings[$modname]."</span>";
          else
            $selectors[] = "<span class=\"selector".($unset_selector ? " active" : "").
                              "\"><a href=\"?league=".$leaguetag."&mod=".(empty($parent) ? "" : $parent."-" ).$modname.
                              (empty($linkvars) ? "" : "&".$linkvars).
                              "\">".$strings[$modname]."</a></span>";
        } else {
          $selectors[] = "<span class=\"mod-".$level_codes[$level][1]."-selector selector".
                              ($first ? " active" : "")."\" onclick=\"switchTab(event, 'module-".(empty($parent) ? "" : $parent."-" ).$modname."', 'mod-".$level_codes[$level][1]."');\">".$strings[$modname]."</span>";
        }
      } else {
        if($lrg_use_get && $lrg_get_depth > $level) {
          if (stripos($mod, (empty($parent) ? "" : $parent."-" ).$modname) === 0)
            $selectors[] = "<option selected=\"selected\" value=\"?league=".$leaguetag."&mod=".(empty($parent) ? "" : $parent."-" ).$modname."&".
            (empty($linkvars) ? "" : "&".$linkvars)
            ."\">".$strings[$modname]."</option>";
          else
            $selectors[] = "<option".($unset_selector ? "selected=\"selected\"" : "")." value=\"?league=".$leaguetag."&mod=".(empty($parent) ? "" : $parent."-" ).$modname.(empty($linkvars) ? "" : "&".$linkvars)
            ."\">".$strings[$modname]."</option>";
        } else {
          $selectors[] = "<option value=\"module-".(empty($parent) ? "" : $parent."-" ).$modname."\">".$strings[$modname]."</option>";
        }
      }
      if(($lrg_use_get && stripos($mod, (empty($parent) ? "" : $parent."-" ).$modname) === 0) || !$lrg_use_get || $lrg_get_depth < $level+1 || $unset_selector) {
        if(is_array($module)) {
          $module = join_selectors($module, $level+1, (empty($parent) ? "" : $parent."-" ).$modname);
        }
        $out .= "<div id=\"module-".(empty($parent) ? "" : $parent."-" ).$modname."\" class=\"selector-module mod-".$level_codes[$level][1].($first ? " active" : "")."\">".$module."</div>";
        $first = false;
        $unset_selector = false;
      }
    }
    if ($selectors_num < $max_tabs)
      return "<div class=\"selector-modules".(empty($level_codes[$level][0]) ? "" : "-".$level_codes[$level][0])."\">".implode($selectors, " | ")."</div>".$out;
    else
    if($lrg_use_get && $lrg_get_depth > $level)
      return "<div class=\"selector-modules".(empty($level_codes[$level][0]) ? "" : "-".$level_codes[$level][0])."\">".
          "<select onchange=\"select_modules_link(this);\" class=\"select-selectors select-selectors".(empty($level_codes[$level][0]) ? "" : "-".$level_codes[$level][0])."\">".
          implode($selectors, "")."</select></div>".$out;
      else
      return "<div class=\"selector-modules".(empty($level_codes[$level][0]) ? "" : "-".$level_codes[$level][0])."\">".
          "<select onchange=\"switchTab(event, this.value, 'mod-".$level_codes[$level][1]."');\" class=\"select-selectors select-selectors".
          (empty($level_codes[$level][0]) ? "" : "-".$level_codes[$level][0])."\">".
          implode($selectors, "")."</select></div>".$out;
  }
}
$level_codes = array(
  # level => array( class-postfix, class-level )
  0 => array ( "", "higher-level" ),
  1 => array ( "sublevel", "lower-level" ),
  2 => array ( "level-3", "level-3" ),
  3 => array ( "level-4", "level-4" )
);
$charts_colors = array( "#6af","#f66","#fa6","#6f6","#66f","#6fa","#a6f","#62f","#2f6","#6f2","#f22","#ff6","#6ff","#f6f","#666" );

/* INITIALISATION */

  $linkvars = array();

  if(isset($argv)) {

    $options = getopt("l:m:d:f:S");

    if(isset($options['l'])) {
      $leaguetag = $options['l'];
    }
    if(isset($options['m'])) {
      $lrg_use_get = true;
      $mod = $options['m'];
    }
    if(isset($options['f'])) {
      $lrg_get_depth = 0;
    }
    if(isset($options['d'])) {
      $lrg_get_depth = (int)$options['d'];
    }
    if(isset($options['S'])) {
      $override_style = $options['S'];
      $linkvars[] = array("stow", $options['S']);
    }
  } else if ($lrg_use_get) {
    if(isset($_GET['league']) && !empty($_GET['league'])) {
      if(file_exists("reports/report_".$_GET['league'].".json")) {
        $leaguetag = $_GET['league'];
        if($lrg_get_depth > 0) {
          if(isset($_GET['mod'])) $mod = $_GET['mod'];
          else $mod = "";
        }
      } else $leaguetag = "";
    } else $leaguetag = "";

    if(isset($_GET['loc']) && !empty($_GET['loc'])) {
      $locale = $_GET['loc'];
      $linkvars[] = array("loc", $_GET['loc']);
    }
    if(isset($_GET['stow']) && !empty($_GET['stow'])) {
      $override_style = $_GET['stow'];
      $linkvars[] = array("stow", $_GET['stow']);
    }
  }

  require_once('locales/en.php');
  if(strtolower($locale) != "en" && file_exists('locales/'.$locale.'.php'))
    require_once('locales/'.$locale.'.php');

  $use_visjs = false;
  $use_graphjs = false;

  for($i=0,$sz=sizeof($linkvars); $i<$sz; $i++)
    $linkvars[$i] = implode($linkvars[$i], "=");
  $linkvars = implode($linkvars, "&");

  if (!empty($leaguetag)) {
    $output = "";

    $report = file_get_contents("reports/report_".$leaguetag.".json") or die("[F] Can't open $teaguetag, probably no such report\n");
    $report = json_decode($report, true);

    $meta = file_get_contents("res/metadata.json") or die("[F] Can't open metadata\n");
    $meta = json_decode($meta, true);

    $modules = array();
    # module => array or ""
    $modules['overview'] = "";
    if (isset($report['records'])) $modules['records'] = "";
    if (isset($report['averages_heroes']) || isset($report['pickban']) || isset($report['draft']) || isset($report['hero_positions']) ||
        isset($report['hero_sides']) || isset($report['hero_pairs']) || isset($report['hero_triplets']))
          $modules['heroes'] = array();

    if (isset($report['averages_players']) || isset($report['pvp']) || isset($report['player_positions']) || isset($report['player_pairs']))
      $modules['players'] = array();

    if (isset($report['teams'])) { $modules['teams'] = array(); $modules['summary_teams'] = ""; }
    if (isset($report['teams'])) $modules['tvt'] = "";

    if (isset($report['matches'])) $modules['matches'] = "";

    if (isset($report['players'])) $modules['participants'] = array();

    if(empty($mod)) $unset_module = true;
    else $unset_module = false;

    $h3 = array_rand($report['random']);

  # overview
  if ( check_module("overview") ) {
    $modules['overview'] .= "<div class=\"content-text overview overview-head\">";
    $modules['overview'] .= "<div class=\"content-header\">".$strings['summary']."</div><div class=\"block-content\">";
    $modules['overview'] .= $strings['over-pregen-report'];
    if ($report['league_id'] == null || $report['league_id'] == "custom")
      $modules['overview'] .= " ".$strings['over-custom-league']." ".$report['league_name']." — ".$report['league_desc'].".";
    else
      $modules['overview'] .= " ".$report['league_name']." (".$report['league_id'].") — ".$report['league_desc'].".";

    $modules['overview'] .= "</div><div class=\"block-content\">";

    $modules['overview'] .= $strings['over-matches-left'].$report['random']['matches_total'].$strings['over-matches-right']." ";
    if(isset($report['teams']))
      $modules['overview'] .= $strings['over-teams-left'].$report['random']['teams_on_event'].$strings['over-teams-right']." ";
    else $modules['overview'] .= $strings['over-players-left'].$report['random']['players_on_event'].$strings['over-players-right']." ";

    $modules['overview'] .= "</div><div class=\"block-content\">";

    if($report['settings']['overview_versions']) {
      $mode = reset($report['versions']);
      if ($mode/$report['random']['matches_total'] > 0.99)
        $modules['overview'] .= $strings['over-one-version-left'].$meta['versions'][ key($report['versions']) ].$strings['over-one-version-right']." ";
      else $modules['overview'] .= $mode.$strings['over-most-version-left'].$meta['versions'][ key($report['versions']) ].$strings['over-most-version-right']." ";
    }

    if($report['settings']['overview_modes']) {
      $mode = reset($report['modes']);
      if ($mode/$report['random']['matches_total'] > 0.99)
        $modules['overview'] .= $strings['over-one-mode-left'].$meta['modes'][ key($report['modes']) ].$strings['over-one-mode-right']." ";
      else $modules['overview'] .= $mode.$strings['over-most-mode-left'].$meta['modes'][ key($report['modes']) ].$strings['over-most-mode-right']." ";
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
        $modules['overview'] .= $strings['over-one-region-left'].key($regions_matches).$strings['over-one-region-right']." ";
      else
        $modules['overview'] .= $mode.$strings['over-most-region-left'].key($regions_matches).$strings['over-most-region-right']." ";
    }

    $modules['overview'] .= "</div>";

    if($report['settings']['overview_time_limits']) {
      $modules['overview'] .= "<div class=\"block-content\">";

      $modules['overview'] .= $strings['over-first-match']." ".date($strings['time_format']." ".$strings['date_format'], $report['first_match']['date'])."<br />";
      $modules['overview'] .= $strings['over-last-match']." ".date($strings['time_format']." ".$strings['date_format'], $report['last_match']['date'])."<br />";

      $modules['overview'] .= "</div>";
    }

    if($report['settings']['overview_last_match_winners']) {
      $modules['overview'] .= "<div class=\"block-content\">";

      if( $report['matches_additional'][ $report['last_match']['mid'] ]['radiant_win'] ) {
        if(isset($report['teams']) && isset($report['teams'][ $report['match_participants_teams'][ $report['last_match']['mid'] ]['radiant'] ]['name']))
          $modules['overview'] .= $report['teams'][ $report['match_participants_teams'][ $report['last_match']['mid'] ]['radiant'] ]['name'];
        else $modules['overview'] .= $strings['radiant'];
      } else {
        if(isset($report['teams']) && isset($report['teams'][ $report['match_participants_teams'][ $report['last_match']['mid'] ]['dire'] ]['name']))
          $modules['overview'] .= $report['teams'][ $report['match_participants_teams'][ $report['last_match']['mid'] ]['dire'] ]['name'];
        else $modules['overview'] .= $strings['dire'];
      }

      $modules['overview'] .= $strings['over-last-match-winner']."</div>";
    }

    $modules['overview'] .= "</div>";


    if($report['settings']['overview_charts']) {
      $use_graphjs = true;

      $modules['overview'] .= "<div class=\"content-text overview overview-graphs\">";

      $mode = reset($report['versions']);
      if ($report['settings']['overview_versions'] && $mode/$report['random']['matches_total'] < 0.99) {
        $converted_modes = array();
        foreach ($report['versions'] as $mode => $data) {
          $converted_modes[] = $meta['versions'][$mode];
        }
        $colors = array_slice($charts_colors, 0, sizeof($converted_modes));
        $modules['overview'] .= "<div class=\"chart-pie\"><canvas id=\"overview-patches\" width=\"undefined\" height=\"undefined\"></canvas><script>".
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
        $modules['overview'] .= "<div class=\"chart-pie\"><canvas id=\"overview-modes\" width=\"undefined\" height=\"undefined\"></canvas><script>".
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
        $modules['overview'] .= "<div class=\"chart-pie\"><canvas id=\"overview-regions\" width=\"undefined\" height=\"undefined\"></canvas><script>".
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
        $modules['overview'] .= "<div class=\"chart-pie\"><canvas id=\"overview-sides\" width=\"undefined\" height=\"undefined\"></canvas><script>".
                              "var modes_chart_el = document.getElementById('overview-sides'); ".
                              "var modes_chart = new Chart(modes_chart_el, {
                                type: 'pie',
                                data: {
                                  labels: [ '".$strings['radiant']."','".$strings['dire']."' ],
                                  datasets: [{data: [ ".$report['random']['radiant_wr'].",".$report['random']['dire_wr']." ],
                                  borderWidth: 0,
                                  backgroundColor:['#6af','#f66']}]
                                }
                              });</script></div>";
      }

      if ($report['settings']['overview_heroes_contested_graph']) {
        $modules['overview'] .= "<div class=\"chart-pie\"><canvas id=\"overview-heroes\" width=\"undefined\" height=\"undefined\"></canvas><script>".
                              "var modes_chart_el = document.getElementById('overview-heroes'); ".
                              "var modes_chart = new Chart(modes_chart_el, {
                                type: 'pie',
                                data: {
                                  labels: [ '".$strings['heroes_contested']."','".$strings['heroes_uncontested']."' ],
                                  datasets: [{data: [ ".$report['random']['heroes_contested'].",".(sizeof($meta['heroes'])-$report['random']['heroes_contested'])." ],
                                  borderWidth: 0,
                                  backgroundColor:['#6af','#f66']}]
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
        $modules['overview'] .= "<h1>".$strings['matches_per_day']."</h1>".
                              "<div class=\"chart-bars\"><canvas id=\"overview-days\" width=\"undefined\" height=\"undefined\"></canvas><script>".
                              "var modes_chart_el = document.getElementById('overview-days'); ".
                              "var modes_chart = new Chart(modes_chart_el, {
                                type: 'horizontalBar',
                                data: {
                                  labels: [ '','".implode($converted_modes,"','")."' ],
                                  datasets: [{label:'".$strings['matches_per_day']."',data: [ 0,".implode($matchcount,",")." ],
                                  backgroundColor:'#999'}]
                                }
                              });</script></div>";

        }
      $modules['overview'] .= "</div>";
    }

    $modules['overview'] .= "<div class=\"content-header\">".$strings['notable_paricipans']."</div>";
    $modules['overview'] .= "<div class=\"content-cards\">";
    if (isset($report['teams'])) {
      $modules['overview'] .= "<h1>".$strings["np_winner"]."</h1>";
      if($report['matches_additional'][ $report['last_match']['mid'] ]['radiant_win']) {
        $tid = $report['match_participants_teams'][ $report['last_match']['mid'] ]['radiant'];
      } else {
        $tid = $report['match_participants_teams'][ $report['last_match']['mid'] ]['dire'];
      }
      $modules['overview'] .= team_card($tid);
      unset($tid);

      if (isset($report['records'])) {
        $modules['overview'] .= "<h1>".$strings["widest_hero_pool_team"]."</h1>";
        $modules['overview'] .= team_card($report['records']['widest_hero_pool_team']['playerid']);
      }

      $max_wr = 0;
      $max_matches = 0;
      foreach ($report['teams'] as $team_id => $team) {
          if(!$max_matches || $report['teams'][$max_matches]['matches_total'] < $team['matches_total'] )
            $max_matches = $team_id;
          if(!$max_wr || $report['teams'][$max_wr]['wins']/$report['teams'][$max_wr]['matches_total'] < $team['wins']/$team['matches_total'] )
            $max_wr = $team_id;
      }

      $modules['overview'] .= "<h1>".$strings["most_matches"]."</h1>";
      $modules['overview'] .= team_card($max_matches);

      $modules['overview'] .= "<h1>".$strings["highest_winrate"]."</h1>";
      $modules['overview'] .= team_card($max_wr);
    } else {
      if (isset($report['records'])) {
        $modules['overview'] .= "<h1>".$strings["widest_hero_pool"]."</h1>";
        $modules['overview'] .= player_card($report['records']['widest_hero_pool']['playerid']);

        $max_wr = 0;
        $max_matches = 0;
        foreach ($report['players_additional'] as $pid => $player) {
            if(!$max_matches || $report['players_additional'][$max_wr]['matches'] < $player['matches'] )
              $max_matches = $pid;
            if(!$max_wr || $report['players_additional'][$max_wr]['won']/$report['players_additional'][$max_wr]['matches'] < $player['won']/$player['matches'] )
              $max_wr = $pid;
        }

        $modules['overview'] .= "<h1>".$strings["most_matches"]."</h1>";
        $modules['overview'] .= player_card($max_matches);

        $modules['overview'] .= "<h1>".$strings["highest_winrate"]."</h1>";
        $modules['overview'] .= player_card($max_wr);
      }


    }
    $modules['overview'] .= "<div class=\"content-text\"><a href=\"http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."?league".$leaguetag.
      (empty($linkvars) ? "" : "&".$linkvars)."&mod=participants\">".$strings['full_participants']."</a></div>";
    $modules['overview'] .= "</div>";

    $modules['overview'] .= "<div class=\"content-header\">".$strings['heroes']."</div>";

    if($report['settings']['overview_top_contested'] && isset($report['pickban'])) {
        $modules['overview'] .=  "<table id=\"over-heroes-pickban\" class=\"list\"><caption>".$strings['top_contested_heroes']."</caption>
                                              <tr class=\"thead\">
                                                <th>".$strings['hero']."</th>
                                                <th>".$strings['matches_total']."</th>
                                                <th>".$strings['matches_picked']."</th>
                                                <th>".$strings['winrate_picked']."</th>
                                                <th>".$strings['matches_banned']."</th>
                                                <th>".$strings['winrate_banned']."</th>
                                              </tr>";

        $workspace = $report['pickban'];
        uasort($workspace, function($a, $b) {
          if($a['matches_total'] == $b['matches_total']) return 0;
          else return ($a['matches_total'] < $b['matches_total']) ? 1 : -1;
        });

        $counter = $report['settings']['overview_top_contested_count'];
        foreach($workspace as $hid => $hero) {
          if($counter == 0) break;
          $modules['overview'] .=  "<tr>
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
        $modules['overview'] .= "</table>";
    }

    if($report['settings']['overview_top_picked'] && isset($report['pickban'])) {
        $modules['overview'] .=  "<table id=\"over-heroes-pick\" class=\"list\"><caption>".$strings['top_picked_heroes']."</caption>
                                              <tr class=\"thead\">
                                                <th>".$strings['hero']."</th>
                                                <th>".$strings['matches_total']."</th>
                                                <th>".$strings['matches_picked']."</th>
                                                <th>".$strings['winrate_picked']."</th>
                                              </tr>";

        $workspace = $report['pickban'];
        uasort($workspace, function($a, $b) {
          if($a['matches_picked'] == $b['matches_picked']) return 0;
          else return ($a['matches_picked'] < $b['matches_picked']) ? 1 : -1;
        });

        $counter = $report['settings']['overview_top_picked_count'];
        foreach($workspace as $hid => $hero) {
          if($counter == 0) break;
          $modules['overview'] .=  "<tr>
                                      <td>".($hid ? hero_full($hid) : "").
                                     "</td>
                                      <td>".$hero['matches_total']."</td>
                                      <td>".$hero['matches_banned']."</td>
                                      <td>".number_format($hero['winrate_banned']*100,2)."%</td>
                                    </tr>";
          $counter--;
        }
        unset($workspace);
        $modules['overview'] .= "</table>";
    }

    if($report['settings']['overview_top_bans'] && isset($report['pickban'])) {
        $modules['overview'] .=  "<table id=\"over-heroes-ban\" class=\"list\"><caption>".$strings['top_banned_heroes']."</caption>
                                              <tr class=\"thead\">
                                                <th>".$strings['hero']."</th>
                                                <th>".$strings['matches_total']."</th>
                                                <th>".$strings['matches_banned']."</th>
                                                <th>".$strings['winrate_banned']."</th>
                                              </tr>";

        $workspace = $report['pickban'];
        uasort($workspace, function($a, $b) {
          if($a['matches_banned'] == $b['matches_banned']) return 0;
          else return ($a['matches_banned'] < $b['matches_banned']) ? 1 : -1;
        });

        $counter = $report['settings']['overview_top_bans_count'];
        foreach($workspace as $hid => $hero) {
          if($counter == 0) break;
          $modules['overview'] .=  "<tr>
                                      <td>".($hid ? hero_full($hid) : "").
                                     "</td>
                                      <td>".$hero['matches_total']."</td>
                                      <td>".$hero['matches_banned']."</td>
                                      <td>".number_format($hero['winrate_banned']*100,2)."%</td>
                                    </tr>";
          $counter--;
        }
        unset($workspace);
        $modules['overview'] .= "</table>";
    }

    $modules['overview'] .= "<div class=\"content-text\"><a href=\"http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."?league".$leaguetag.
      (empty($linkvars) ? "" : "&".$linkvars)."&mod=heroes-pickban\">".$strings['full_pickban']."</a></div>";

    if($report['settings']['overview_top_draft']) {
      $modules['overview'] .= "<div class=\"content-header\">".$strings['draft']."</div>";

      for ($i=0; $i<2; $i++) {
        for ($j=1; $j<4; $j++) {
          if($report['settings']["overview_draft_".$i."_".$j] && isset($report['draft']) && !empty($report['draft'][$i][$j])) {


              $modules['overview'] .=  "<table id=\"over-draft-$i-$j\" class=\"list\">
                                          <caption>".$strings['stage_num_1']." $j ".$strings['stage_num_2']." ".($i ? $strings['picks'] : $strings['bans'])."</caption>
                                                    <tr class=\"thead\">
                                                      <th>".$strings['hero']."</th>
                                                      <th>".$strings['matches']."</th>
                                                      <th>".$strings['winrate']."</th>
                                                    </tr>";

              $counter = $report['settings']["overview_draft_".$i."_".$j."_count"];

              uasort($report['draft'][$i][$j], function($a, $b) {
                if($a['matches'] == $b['matches']) return 0;
                else return ($a['matches'] < $b['matches']) ? 1 : -1;
              });
              foreach($report['draft'][$i][$j] as $hero) {
                if($counter == 0) break;
                $modules['overview'] .=  "<tr>
                                            <td>".($hid ? hero_full($hero['heroid']) : "").
                                           "</td>
                                            <td>".$hero['matches']."</td>
                                            <td>".number_format($hero['winrate']*100,2)."%</td>
                                          </tr>";
                $counter--;
              }
              $modules['overview'] .= "</table>";
          }
        }
      }
      $modules['overview'] .= "<div class=\"content-text\"><a href=\"http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."?league".$leaguetag.
        (empty($linkvars) ? "" : "&".$linkvars)."&mod=heroes-draft\">".$strings['full_draft']."</a></div>";
    }

    if($report['settings']['overview_top_hero_pairs'] && isset($report['hero_pairs']) && !empty($report['hero_pairs'])) {
        $modules['overview'] .= "<div class=\"content-header\">".$strings['top_pick_pairs']."</div>";

        $modules['overview'] .= "<table id=\"over-hero-pairs\" class=\"list\">
                                  <tr class=\"thead\">
                                    <th>".$strings['hero']." 1</th>
                                    <th>".$strings['hero']." 2</th>
                                    <th>".$strings['matches']."</th>
                                    <th>".$strings['winrate']."</th>
                                  </tr>";
        $counter = $report['settings']['overview_top_hero_pairs_count'];
        foreach($report['hero_pairs'] as $pair) {
          if($counter == 0) break;
          $modules['overview'] .= "<tr>
                                    <td>".($pair['heroid1'] ? hero_full($pair['heroid1']) : "").
                                   "</td><td>".($pair['heroid2'] ? hero_full($pair['heroid2'])  : "").
                                   "</td>
                                   <td>".$pair['matches']."</td>
                                   <td>".number_format($pair['winrate']*100,2)."</td>
                                  </tr>";
          $counter--;
        }
        $modules['overview'] .= "</table>";

        $modules['overview'] .= "<div class=\"content-text\"><a href=\"http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."?league".$leaguetag.
          (empty($linkvars) ? "" : "&".$linkvars)."&mod=heroes-hero_combos\">".$strings['full_hero_combos']."</a></div>";
    }

    if(!isset($report['teams']) && $report['settings']['overview_top_player_pairs'] && isset($report['player_pairs']) && !empty($report['player_pairs'])) {
        $modules['overview'] .= "<div class=\"content-header\">".$strings['top_player_pairs']."</div>";

        $modules['overview'] .= "<table id=\"over-player-pairs\" class=\"list\">
                                  <tr class=\"thead\">
                                    <th>".$strings['player']." 1</th>
                                    <th>".$strings['player']." 2</th>
                                    <th>".$strings['matches']."</th>
                                    <th>".$strings['winrate']."</th>
                                  </tr>";
        $counter = $report['settings']['overview_top_player_pairs_count'];
        foreach($report['player_pairs'] as $pair) {
          if($counter == 0) break;
          $modules['overview'] .= "<tr>
                                    <td>".$report['players'][ $pair['playerid1'] ].
                                   "</td><td>".$report['players'][ $pair['playerid2'] ].
                                   "</td>
                                   <td>".$pair['matches']."</td>
                                   <td>".number_format($pair['winrate']*100,2)."</td>
                                  </tr>";
          $counter--;
        }
        $modules['overview'] .= "</table>";

        $modules['overview'] .= "<div class=\"content-text\"><a href=\"http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."?league".$leaguetag.
          (empty($linkvars) ? "" : "&".$linkvars)."&mod=players-player_combos\">".$strings['full_player_combos']."</a></div>";
    }


    if($report['settings']['overview_matches']) {
      $modules['overview'] .= "<div class=\"content-header\">".$strings['notable_matches']."</div>";
      $modules['overview'] .= "<div class=\"content-cards\">";
      if($report['settings']['overview_first_match'])
        $modules['overview'] .= "<h1>".$strings['first_match']."</h1>".match_card($report['first_match']['mid']);
      if($report['settings']['overview_last_match'])
        $modules['overview'] .= "<h1>".$strings['last_match']."</h1>".match_card($report['last_match']['mid']);
      if($report['settings']['overview_records_stomp'])
        $modules['overview'] .= "<h1>".$strings['match_stomp']."</h1>".match_card($report['records']['stomp']['matchid']);
      if($report['settings']['overview_records_comeback'])
        $modules['overview'] .= "<h1>".$strings['match_comeback']."</h1>".match_card($report['records']['comeback']['matchid']);
      if($report['settings']['overview_records_duration'])
        $modules['overview'] .= "<h1>".$strings['longest_match']."</h1>".match_card($report['records']['duration']['matchid']);

      $modules['overview'] .= "<div class=\"content-text\"><a href=\"http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."?league".$leaguetag.
        (empty($linkvars) ? "" : "&".$linkvars)."&mod=matches\">".$strings['full_matches']."</a></div>";

      $modules['overview'] .= "</div>";
    }

    if($report['settings']['overview_random_stats']) {
      $modules['overview'] .= "<div class=\"content-header\">".$strings['random']."</div>";
      $modules['overview'] .= "<table class=\"list\" id=\"overview-table\">";
      foreach($report['random'] as $key => $value) {
        $modules['overview'] .= "<tr><td>".$strings[$key]."</td><td>".$value."</td></tr>";
      }
      $modules['overview'] .= "</table>";

      $modules['overview'] .= "<div class=\"content-text\">".$strings['desc_overview']."</div>";
      $modules['overview'] .= "<div class=\"content-text\">".$strings['ana_version'].": ".parse_ver($report['ana_version'])."</div>";
      $modules['overview'] .= "<div class=\"content-text\">".
        $strings['limiter_h'].": ".$report['settings']['limiter']."<br />".
        $strings['limiter_l'].": ".$report['settings']['limiter_triplets']."</div>";
    }
  }

  # records
  if (isset($modules['records']) && check_module("records")) {
    $modules['records'] .= "<table id=\"records-module-table\" class=\"list\">
                              <tr class=\"thead\">
                                <th onclick=\"sortTable(0,'records-module-table');\">".$strings['record']."</th>".
                               "<th onclick=\"sortTable(1,'records-module-table');\">".$strings['match']."</th>
                                <th onclick=\"sortTableNum(2,'records-module-table');\">".$strings['value']."</th>
                                <th onclick=\"sortTable(3,'records-module-table');\">".$strings['player']."</th>
                                <th onclick=\"sortTable(4,'records-module-table');\">".$strings['hero']."</th>
                              </tr>";
    foreach($report['records'] as $key => $record) {
      $modules['records'] .= "<tr>
                                <td>".$strings[$key]."</td>
                                <td>". ($record['matchid'] ?
                                          "<a href=\"https://opendota.com/matches/".$record['matchid']."\" title=\"".$strings['match']." ".$record['matchid']." on OpenDota\" target=\"_blank\" rel=\"noopener\">".$record['matchid']."</a>" :
                                          //"<a onclick=\"showModal('".htmlspecialchars(match_card($record['matchid'], $report['matches'][$record['matchid']], $report, $meta))."','');\" alt=\"Match ".$record['matchid']." on OpenDota\" target=\"_blank\">".$record['matchid']."</a>" :
                                     "")."</td>
                                <td>".number_format($record['value'],2)."</td>
                                <td>". ($record['playerid'] ?
                                          (strstr($key, "_team") != FALSE ?
                                            $report['teams'][ $record['playerid'] ]['name']." ( ".$report['teams'][ $record['playerid'] ]['tag']." )" :
                                            $report['players'][$record['playerid']]
                                          ) :
                                     "")."</td>
                                <td>".($record['heroid'] ? hero_full($record['heroid']) : "").
                               "</td>
                            </tr>";
    }

    $modules['records'] .= "</table>";
    $modules['records'] .= "<div class=\"content-text\">".$strings['desc_records']."</div>";
  }

  # heroes
  if (isset($modules['heroes']) && check_module("heroes")) {
    if($mod == "heroes") $unset_module = true;
    $parent = "heroes-";

    if (isset($report['averages_heroes']) ) {
      $modules['heroes']['averages_heroes'] = "";

      if (check_module($parent."averages_heroes")) {
        $modules['heroes']['averages_heroes'] .= "<div class=\"small-list-wrapper\">";
        foreach($report['averages_heroes'] as $key => $avg) {
          $modules['heroes']['averages_heroes'] .= "<table id=\"avgs-heroes-".$key."\" class=\"list list-fixed list-small\">
                                                      <caption>".$strings[$key]."</caption>
                                                      <tr class=\"thead\">
                                                        <th>".$strings['hero']."</th>
                                                        <th>".$strings['value']."</th>
                                                      </tr>";
          foreach($avg as $hero) {
            $modules['heroes']['averages_heroes'] .= "<tr>
                                                        <td>".($hero['heroid'] ? hero_full($hero['heroid']) : "").
                                                       "</td><td>".number_format($hero['value'],2)."</td></tr>";
          }
          $modules['heroes']['averages_heroes'] .= "</table>";
        }
        $modules['heroes']['averages_heroes'] .= "</div>";
        $modules['heroes']['averages_heroes'] .= "<div class=\"content-text\">".$strings['desc_heroes_avg']."</div>";
      }
    }
    if (isset($report['pickban'])) {
      $modules['heroes']['pickban'] = "";

      if (check_module($parent."pickban")) {
        $heroes = $meta['heroes'];

          $modules['heroes']['pickban'] .=  "<table id=\"heroes-pickban\" class=\"list\">
                                                <tr class=\"thead\">
                                                  <th onclick=\"sortTable(0,'heroes-pickban');\">".$strings['hero']."</th>
                                                  <th onclick=\"sortTableNum(1,'heroes-pickban');\">".$strings['matches_total']."</th>
                                                  <th onclick=\"sortTableNum(2,'heroes-pickban');\">".$strings['matches_picked']."</th>
                                                  <th onclick=\"sortTableNum(3,'heroes-pickban');\">".$strings['winrate_picked']."</th>
                                                  <th onclick=\"sortTableNum(4,'heroes-pickban');\">".$strings['matches_banned']."</th>
                                                  <th onclick=\"sortTableNum(5,'heroes-pickban');\">".$strings['winrate_banned']."</th>
                                                </tr>";
          foreach($report['pickban'] as $hid => $hero) {
            unset($heroes[$hid]);
            $modules['heroes']['pickban'] .=  "<tr>
                                                  <td>".($hid ? hero_full($hid) : "").
                                                 "</td>
                                                  <td>".$hero['matches_total']."</td>
                                                  <td>".$hero['matches_picked']."</td>
                                                  <td>".number_format($hero['winrate_picked']*100,2)."%</td>
                                                  <td>".$hero['matches_banned']."</td>
                                                  <td>".number_format($hero['winrate_banned']*100,2)."%</td>
                                                </tr>";
          }
          $modules['heroes']['pickban'] .= "</table>";

          $modules['heroes']['pickban'] .= "<div class=\"content-text\"><h1>".$strings['heroes_uncontested'].": ".sizeof($heroes)."</h1><div class=\"hero-list\">";

          foreach($heroes as $hero) {
            $modules['heroes']['pickban'] .= "<div class=\"hero\"><img src=\"res/heroes/".$hero['tag'].
                ".png\" alt=\"".$hero['tag']."\" /><span class=\"hero_name\">".
                $hero['name']."</span></div>";
          }
          $modules['heroes']['pickban'] .= "</div></div>";
          $modules['heroes']['pickban'] .= "<div class=\"content-text\">".$strings['desc_heroes_pickban']."</div>";
        }
    }
    if (isset($report['draft'])) {
      $modules['heroes']['draft'] = array();

      if (check_module($parent."draft")) {
        if($mod == $parent."draft") $unset_module = true;

        for ($i=0; $i<2; $i++) {
          $modules['heroes']['draft'][($i ? "pick" : "ban")."_stages"] = "";
          if(!check_module($parent."draft-".($i ? "pick" : "ban")."_stages")) continue;


          for ($j=1; $j<4; $j++) {
            if(empty($report['draft'][$i][$j])) continue;
            uasort($report['draft'][$i][$j], function($a, $b) {
              if($a['matches'] == $b['matches']) return 0;
              else return ($a['matches'] < $b['matches']) ? 1 : -1;
            });

            $modules['heroes']['draft'][($i ? "pick" : "ban")."_stages"] .= "<table id=\"heroes-draft-$i-$j\" class=\"list list-small\">
                                              <caption>".$strings['stage_num_1']." $j ".$strings['stage_num_2']." ".($i ? $strings['picks'] : $strings['bans'])."</caption>
                                              <tr class=\"thead\">
                                                <th onclick=\"sortTable(0,'heroes-draft-$i-$j');\">".$strings['hero']."</th>
                                                <th onclick=\"sortTableNum(1,'heroes-draft-$i-$j');\">".$strings['matches']."</th>
                                                <th onclick=\"sortTableNum(2,'heroes-draft-$i-$j');\">".$strings['winrate']."</th>
                                              </tr>";

            foreach($report['draft'][$i][$j] as $hero) {
              $modules['heroes']['draft'][($i ? "pick" : "ban")."_stages"] .= "<tr>
                                                  <td>".($hero['heroid'] ? hero_full($hero['heroid']) : "").
                                                 "</td>
                                                  <td>".$hero['matches']."</td>
                                                  <td>".number_format($hero['winrate']*100,2)."%</td>
                                                </tr>";
            }
            $modules['heroes']['draft'][($i ? "pick" : "ban")."_stages"] .= "</table>";
          }
          if(empty($modules['heroes']['draft'][($i ? "pick" : "ban")."_stages"]))
            unset($modules['heroes']['draft'][($i ? "pick" : "ban")."_stages"]);


          $modules['heroes']['draft'][($i ? "pick" : "ban")."_stages"] .= "<div class=\"content-text\">".$strings['desc_heroes_draft']."</div>";
        }
      }
    }
    if (isset($report['hero_positions'])) {
      $modules['heroes']['hero_positions'] = array();

      if(check_module($parent."hero_positions")) {
        if($mod == $parent."hero_positions") $unset_module = true;

        for ($i=0; $i<2 && !isset($keys); $i++) {
          for ($j=1; $j<6 && $j>0; $j++) {
            if (!$i) { $j = 0; }
            if(isset($report['hero_positions'][$i][$j][0])) {
              $keys = array_keys($report['hero_positions'][$i][$j][0]);
              break;
            }
            if (!$i) { break; }
          }
        }

        for ($i=0; $i<2; $i++) {
          for ($j=1; $j<6 && $j>0; $j++) {
            if (!$i) { $j = 0; }

            if(!isset($strings["positions_$i"."_$j"]))
              $strings["positions_$i"."_$j"] = ($i ? $strings['core'] : $strings['support'])." ".$meta['lanes'][$j];

            if(sizeof($report['hero_positions'][$i][$j])) {
              $modules['heroes']['hero_positions']["positions_$i"."_$j"]  = "";
              if (!check_module($parent."hero_positions-"."positions_$i"."_$j")) { if (!$i) { break; } continue; }

              $modules['heroes']['hero_positions']["positions_$i"."_$j"] .= "<table id=\"heroes-positions-$i-$j\" class=\"list wide\">
                                                <tr class=\"thead\">
                                                  <th onclick=\"sortTable(0,'heroes-positions-$i-$j');\">".$strings['hero']."</th>";
              for($k=1, $end=sizeof($keys); $k < $end; $k++) {
                $modules['heroes']['hero_positions']["positions_$i"."_$j"] .= "<th onclick=\"sortTableNum($k,'heroes-positions-$i-$j');\">".$strings[$keys[$k]]."</th>";
              }
              $modules['heroes']['hero_positions']["positions_$i"."_$j"] .= "</tr>";

              uasort($report['hero_positions'][$i][$j], function($a, $b) {
                if($a['matches_s'] == $b['matches_s']) return 0;
                else return ($a['matches_s'] < $b['matches_s']) ? 1 : -1;
              });

              foreach($report['hero_positions'][$i][$j] as $hero) {

                $modules['heroes']['hero_positions']["positions_$i"."_$j"] .= "<tr".(isset($report['hero_positions_matches']) ?
                                                                  " onclick=\"showModal('".htmlspecialchars(join_matches($report['hero_positions_matches'][$i][$j][$hero['heroid']])).
                                                                          "', '".$meta['heroes'][ $hero['heroid'] ]['name']." - ".
                                                                          $strings["positions_$i"."_$j"]." - ".$strings['matches']."');\"" : "").">
                                                    <td>".($hero['heroid'] ? hero_full($hero['heroid']) : "").
                                                   "</td>
                                                    <td>".$hero['matches_s']."</td>
                                                    <td>".number_format($hero['winrate_s']*100,1)."%</td>";
                for($k=3, $end=sizeof($keys); $k < $end; $k++) {
                  $modules['heroes']['hero_positions']["positions_$i"."_$j"] .= "<td>".number_format($hero[$keys[$k]],1)."</td>";
                }
                $modules['heroes']['hero_positions']["positions_$i"."_$j"] .= "</tr>";
              }
              $modules['heroes']['hero_positions']["positions_$i"."_$j"] .= "</table>";

              $modules['heroes']['hero_positions']["positions_$i"."_$j"] .= "<div class=\"content-text\">".$strings['desc_heroes_positions']."</div>";
            }
            if (!$i) { break; }
          }
        }
        unset($keys);
      }
    }
    if (isset($report['hero_sides'])) {
      $modules['heroes']['hero_sides'] = array();

      if(check_module($parent."hero_sides")) {
        if($mod == $parent."hero_sides") $unset_module = true;

        for ($i=0; $i<2 && !isset($keys); $i++) {
            if(isset($report['hero_sides'][$i][0])) {
              $keys = array_keys($report['hero_sides'][$i][0]);
              break;
            }
        }

        $modules['heroes']['hero_sides']['overview'] = "";
        if(check_module($parent."hero_sides-overview")) {
          $heroes = array();

          for ($side = 0; $side < 2; $side++) {
            foreach($report['hero_sides'][$side] as $hero) {
              if (!isset($heroes[$hero['heroid']])) {
                $heroes[$hero['heroid']] = array(
                  "matches" => $hero['matches'],
                  "side".$side."matches" => $hero['matches'],
                  "side".$side."winrate" => $hero['winrate']
                );
              } else {
                $heroes[$hero['heroid']]["matches"] += $hero['matches'];
                $heroes[$hero['heroid']]["side".$side."matches"] = $hero['matches'];
                $heroes[$hero['heroid']]["side".$side."winrate"] = $hero['winrate'];
              }
            }
          }

          uasort($heroes, function($a, $b) {
            if($a['matches'] == $b['matches']) return 0;
            else return ($a['matches'] < $b['matches']) ? 1 : -1;
          });

          $modules['heroes']['hero_sides']['overview'] .= "<table id=\"hero-sides-overiew\" class=\"list\">
                                        <tr class=\"thead\">
                                          <th onclick=\"sortTable(0,'hero-sides-overview');\">".$strings['hero']."</th>
                                          <th onclick=\"sortTableNum(1,'hero-sides-overview');\">".$strings['matches']."</th>
                                          <th onclick=\"sortTableNum(2,'hero-sides-overview');\">".$strings['rad_ratio']."</th>
                                          <th onclick=\"sortTableNum(3,'hero-sides-overview');\">".$strings['radiant']." ".$strings['matches']."</th>
                                          <th onclick=\"sortTableNum(4,'hero-sides-overview');\">".$strings['radiant']." ".$strings['winrate']."</th>
                                          <th onclick=\"sortTableNum(5,'hero-sides-overview');\">".$strings['dire']." ".$strings['matches']."</th>
                                          <th onclick=\"sortTableNum(6,'hero-sides-overview');\">".$strings['dire']." ".$strings['winrate']."</th>
                                        </tr>";
          foreach ($heroes as $hid => $hero) {
            if(!isset($hero["side0matches"])) {
              $hero["side0matches"] = 0;
              $hero["side0winrate"] = 0;
            }
            if(!isset($hero["side1matches"])) {
              $hero["side1matches"] = 0;
              $hero["side1winrate"] = 0;
            }

            $modules['heroes']['hero_sides']['overview'] .= "<tr>
                                                <td>".($hid ? hero_full($hid) : "")."</td>".
                                                "<td>".$hero['matches']."</td>".
                                                "<td>".number_format($hero["side0matches"]*100/$hero["matches"],2)."%</td>".
                                                "<td>".$hero["side0matches"]."</td>".
                                                "<td>".number_format($hero["side0winrate"]*100,2)."%</td>".
                                                "<td>".$hero["side1matches"]."</td>".
                                                "<td>".number_format($hero["side1winrate"]*100,2)."%</td>".
                                              "</tr>";
          }
          $modules['heroes']['hero_sides']['overview'] .= "</table>";

          $modules['heroes']['hero_sides']["overview"] .= "<div class=\"content-text\">".$strings['desc_heroes_sides']."</div>";
          unset($heroes);
        }

        for ($side = 0; $side < 2; $side++) {
          $modules['heroes']['hero_sides'][$side ? 'dire' : 'radiant'] = "";
          if(!check_module($parent."hero_sides-".($side ? 'dire' : 'radiant'))) continue;

          $modules['heroes']['hero_sides'][$side ? 'dire' : 'radiant'] .= "<table id=\"hero-sides-".$side."\" class=\"list\">
                                        <tr class=\"thead\">
                                          <th onclick=\"sortTable(0,'hero-sides-$side');\">".$strings['hero']."</th>";
          for($k=1, $end=sizeof($keys); $k < $end; $k++) {
            $modules['heroes']['hero_sides'][$side ? 'dire' : 'radiant'] .= "<th onclick=\"sortTableNum($k,'hero-sides-$side');\">".$strings[$keys[$k]]."</th>";
          }
          $modules['heroes']['hero_sides'][$side ? 'dire' : 'radiant'] .= "</tr>";

          foreach($report['hero_sides'][$side] as $hero) {
            $modules['heroes']['hero_sides'][$side ? 'dire' : 'radiant'] .= "<tr>
                                                <td>".($hero['heroid'] ? hero_full($hero['heroid']) : "").
                                               "</td>".
                                               "<td>".$hero['matches']."</td>".
                                               "<td>".number_format($hero['winrate']*100,2)."%</td>";
            for($k=3, $end=sizeof($keys); $k < $end; $k++) {
              $modules['heroes']['hero_sides'][$side ? 'dire' : 'radiant'] .= "<td>".number_format($hero[$keys[$k]],2)."</td>";
            }
            $modules['heroes']['hero_sides'][$side ? 'dire' : 'radiant'] .= "</tr>";
          }
          $modules['heroes']['hero_sides'][$side ? 'dire' : 'radiant'] .= "</table>";

          $modules['heroes']['hero_sides'][$side ? 'dire' : 'radiant'] .= "<div class=\"content-text\">".$strings['desc_heroes_sides']."</div>";
        }
        unset($keys);
      }
    }
    if (isset($report['hero_combos_graph']) &&
    $report['settings']['heroes_combo_graph']) {
      $modules['heroes']['hero_combo_graph'] = "";

      if (check_module($parent."hero_combo_graph") && isset($report['pickban'])) {
        $modules['heroes']['hero_combo_graph'] .= "<div class=\"content-text\">".$strings['desc_heroes_combo_graph']."</div>";
        if(isset($report['hero_combos_graph'])) {
          $use_visjs = true;

          $modules['heroes']['hero_combo_graph'] .= "<div id=\"hero-combos-graph\" class=\"graph\"></div><script type=\"text/javascript\">";

          $nodes = "";
          foreach($meta['heroes'] as $hid => $hero) {
            if(!has_pair($hid, $report['hero_combos_graph'])) continue;
            $nodes .= "{id: $hid, value: ".$report['pickban'][$hid]['matches_picked'].
              ", label: '".addslashes($hero['name'])."'},";
          }
          $modules['heroes']['hero_combo_graph'] .= "var nodes = [".$nodes."];";

          $nodes = "";
          foreach($report['hero_combos_graph'] as $combo) {
            $nodes .= "{from: ".$combo['heroid1'].", to: ".$combo['heroid2'].", value:".$combo['matches']."},";
          }

          $modules['heroes']['hero_combo_graph'] .= "var edges = [".$nodes."];";

          $modules['heroes']['hero_combo_graph'] .= "var container = document.getElementById('hero-combos-graph');\n".
                                                      "var data = { nodes: nodes, edges: edges};\n".
                                                      "var options={
                                                        physics:{
                                                          barnesHut:{
                                                            avoidOverlap:0.7,
                                                            centralGravity:0.05,
                                                            springLength:90,
                                                            springConstant:0.005,
                                                            gravitationalConstant:-200
                                                          },
                                                          timestep: 0.05,
                                                        }, nodes: {
                                                           shape: 'dot',
                                                           font: {color:'#ccc',size:14},
                                                           scaling:{
                                                             label: {
                                                               min:8, max:20
                                                             }
                                                           }
                                                         }
                                                       };\n".
                                                      "var network = new vis.Network(container, data, options);\n".
                                                      "</script>";
        }
      }
    }
    if (isset($report['hero_pairs']) || isset($report['hero_pairs'])) {
      $modules['heroes']['hero_combos'] = "";

        if (isset($report['hero_pairs'])) {{
          $modules['heroes']['hero_combos'] .= "<table id=\"hero-pairs\" class=\"list\">
                                                <caption>".$strings['hero_pairs']."</caption>
                                                <tr class=\"thead\">
                                                  <th onclick=\"sortTable(0,'hero-pairs');\">".$strings['hero']." 1</th>
                                                  <th onclick=\"sortTable(1,'hero-pairs');\">".$strings['hero']." 2</th>
                                                  <th onclick=\"sortTableNum(2,'hero-pairs');\">".$strings['matches']."</th>
                                                  <th onclick=\"sortTableNum(3,'hero-pairs');\">".$strings['winrate']."</th>
                                                </tr>";
          foreach($report['hero_pairs'] as $pair) {
            $modules['heroes']['hero_combos'] .= "<tr".(isset($report['hero_pairs_matches']) ?
                                                " onclick=\"showModal('".htmlspecialchars(join_matches($report['hero_pairs_matches'][$pair['heroid1'].'-'.$pair['heroid2']])).
                                                                      "', '".$strings['matches']."');\"" : "").">
                                                  <td>".($pair['heroid1'] ? hero_full($pair['heroid1']) : "").
                                                 "</td><td>".($pair['heroid2'] ? hero_full($pair['heroid2'])  : "").
                                                 "</td>
                                                 <td>".$pair['matches']."</td>
                                                 <td>".number_format($pair['winrate']*100,2)."</td>
                                                </tr>";
          }
          $modules['heroes']['hero_combos'] .= "</table>";
        }

        if (!empty($report['hero_triplets'])) {

          $modules['heroes']['hero_combos'] .= "<table id=\"hero-triplets\" class=\"list\">
                                                <caption>".$strings['hero_triplets']."</caption>
                                                <tr class=\"thead\">
                                                  <th onclick=\"sortTable(0,'hero-triplets');\">".$strings['hero']." 1</th>
                                                  <th onclick=\"sortTable(1,'hero-triplets');\">".$strings['hero']." 2</th>
                                                  <th onclick=\"sortTable(2,'hero-triplets');\">".$strings['hero']." 3</th>
                                                  <th onclick=\"sortTableNum(3,'hero-triplets');\">".$strings['matches']."</th>
                                                  <th onclick=\"sortTableNum(4,'hero-triplets');\">".$strings['winrate']."</th>
                                                </tr>";
          foreach($report['hero_triplets'] as $pair) {
            $modules['heroes']['hero_combos'] .= "<tr".(isset($report['hero_pairs_matches']) ?
                                                " onclick=\"showModal('".
                                                implode($report['hero_pairs_matches'][$pair['heroid1'].'-'.$pair['heroid2'].'-'.$pair['heroid3']], ", ").
                                                                      "', '".$strings['matches']."');\"" : "").">
                                                  <td>".($pair['heroid1'] ? hero_full($pair['heroid1']) : "").
                                                 "</td><td>".($pair['heroid2'] ? hero_full($pair['heroid2']) : "").
                                                 "</td><td>".($pair['heroid3'] ? hero_full($pair['heroid3']) : "").
                                                 "</td>
                                                 <td>".$pair['matches']."</td>
                                                 <td>".number_format($pair['winrate']*100,2)."</td>
                                                </tr>";
          }
          $modules['heroes']['hero_combos'] .= "</table>";

          $modules['heroes']['hero_combos'] .= "<div class=\"content-text\">".$strings['desc_heroes_combos']."</div>";
        }
      }
    }
  }

  # players
  if (isset($modules['players']) && check_module("players")) {
    if($mod == "players") $unset_module = true;
    $parent = "players-";

    if (isset($report['averages_players'])) {
      $modules['players']['averages_players'] = "";

      if(check_module($parent."averages_players")) {
        $modules['players']['averages_players'] .= "<div class=\"small-list-wrapper\">";
        foreach($report['averages_players'] as $key => $avg) {
          $modules['players']['averages_players'] .= "<table id=\"avgs-players-".$key."\" class=\"list list-fixed list-small\">
                                                      <caption>".$strings[$key]."</caption>
                                                      <tr class=\"thead\">
                                                        <th>".$strings['player']."</th>
                                                        <th>".$strings['value']."</th>
                                                      </tr>";
          foreach($avg as $player) {
            if(strrpos($key, "by_team") === FALSE) {
              $modules['players']['averages_players'] .= "<tr><td>".player_name($player['playerid']);
            } else {
              $modules['players']['averages_players'] .= "<tr><td>".team_name($player['playerid']);
            }
            $modules['players']['averages_players'] .= "</td><td>".number_format($player['value'],2)."</td></tr>";
          }
          $modules['players']['averages_players'] .= "</table>";
        }
        $modules['players']['averages_players'] .= "</div>";
        $modules['players']['averages_players'] .= "<div class=\"content-text\">".$strings['desc_players_avg']."</div>";
      }
    }
    if (isset($report['players_summary'])) {

      $modules['players']['summary']  = "";
      if(check_module($parent."summary")) {
        $keys = array_keys($report['players_summary'][0]);
        $modules['players']['summary'] .= "<table id=\"players-summary\" class=\"list wide\">
                                          <tr class=\"thead\">
                                            <th onclick=\"sortTable(0,'players-summary');\">".$strings['hero']."</th>";
        for($k=1, $end=sizeof($keys); $k < $end; $k++) {
          $modules['players']['summary'] .= "<th onclick=\"sortTableNum($k,'players-summary');\">".$strings[$keys[$k]]."</th>";
        }
        $modules['players']['summary'] .= "<th onclick=\"sortTableNum($k,'players-summary');\">".$strings['common_position']."</th>";
        $modules['players']['summary'] .= "</tr>";

        foreach($report['players_summary'] as $player) {

          $modules['players']['summary'] .= "<tr>
                                              <td>".player_name($player['playerid'])."</td>
                                              <td>".$player['matches_s']."</td>
                                              <td>".number_format($player['winrate_s']*100,1)."%</td>";
          for($k=3, $end=sizeof($keys); $k < $end; $k++) {
            if ($player[$keys[$k]] > 1)
              $modules['players']['summary'] .= "<td>".number_format($player[$keys[$k]],1)."</td>";
            else $modules['players']['summary'] .= "<td>".number_format($player[$keys[$k]],2)."</td>";
          }
          $position = reset($report['players_additional'][$player['playerid']]['positions']);
          $modules['players']['summary'] .= "<td>".($position['core'] ? $strings['core']." " : $strings['support']).
                        $meta['lanes'][ $position['lane'] ]."</td>";
          $modules['players']['summary'] .= "</tr>";
        }
        $modules['players']['summary'] .= "</table>";

        $modules['players']['summary'] .= "<div class=\"content-text\">".$strings['desc_players_summary']."</div>";
        unset($keys);
      }
    }
    if (isset($report['pvp'])) {
      $pvp = array();
      $modules['players']['pvp'] = array();

      if (check_module($parent."pvp")) {
        if($mod == $parent."pvp") $unset_module = true;

        foreach($report['players'] as $pid => $pname) {
          $pvp[$pid] = array();
        }
        $player_ids = array_keys($report['players']);

        if($report['settings']['pvp_grid']) {
          $modules['players']['pvp']['grid'] = "";
        }

        foreach($pvp as $player_id => $playerline) {
          foreach($player_ids as $pid) {
            $pvp[$player_id][$pid] = array(
              "winrate" => 0,
              "matches" => 0,
              "won" => 0,
              "lost" => 0
            );
          }
        }

        foreach($player_ids as $pid) {
          for($i=0, $end = sizeof($report['pvp']); $i<$end; $i++) {
            if($report['pvp'][$i]['playerid1'] == $pid) {
              $pvp[$pid][$report['pvp'][$i]['playerid2']] = array(
                "winrate" => $report['pvp'][$i]['p1winrate'],
                "matches" => $report['pvp'][$i]['matches'],
                "won" => $report['pvp'][$i]['p1won'],
                "lost" => $report['pvp'][$i]['matches'] - $report['pvp'][$i]['p1won'],
                "matchids" => $report['pvp'][$i]['matchids']
              );
            }
            if($report['pvp'][$i]['playerid2'] == $pid) {
              $pvp[$pid][$report['pvp'][$i]['playerid1']] = array(
                "winrate" => 1-$report['pvp'][$i]['p1winrate'],
                "matches" => $report['pvp'][$i]['matches'],
                "won" => $report['pvp'][$i]['matches'] - $report['pvp'][$i]['p1won'],
                "lost" => $report['pvp'][$i]['p1won']
              );
            }
            if(isset($report['pvp'][$i]['matchids'])) {
              if($report['pvp'][$i]['playerid1'] == $pid)
                $pvp[$pid][$report['pvp'][$i]['playerid2']]['matchids'] = $report['pvp'][$i]['matchids'];
              if($report['pvp'][$i]['playerid2'] == $pid)
                $pvp[$pid][$report['pvp'][$i]['playerid1']]['matchids'] = $report['pvp'][$i]['matchids'];
            }
          }
        }

        if($report['settings']['pvp_grid'] && check_module($parent."pvp-grid")) {
          $modules['players']['pvp']['grid'] .= "<table  class=\"pvp wide\">";

          $modules['players']['pvp']['grid'] .= "<tr class=\"thead\"><th></th>";
          foreach($report['players'] as $pid => $pname) {
            $modules['players']['pvp']['grid'] .= "<th><span>".$pname."</span></th>";
          }
          $modules['players']['pvp']['grid'] .= "</tr>";


          foreach($pvp as $pid => $playerline) {
            $modules['players']['pvp']['grid'] .= "<tr><td>".$report['players'][$pid]."</td>";
            for($i=0, $end = sizeof($player_ids); $i<$end; $i++) {
              if($pid == $player_ids[$i]) {
                $modules['players']['pvp']['grid'] .= "<td class=\"transparent\"></td>";
              } else if($playerline[$player_ids[$i]]['matches'] == 0) {
                $modules['players']['pvp']['grid'] .= "<td>-</td>";
              } else {
                $modules['players']['pvp']['grid'] .= "<td".
                        ($playerline[$player_ids[$i]]['winrate'] > 0.55 ? " class=\"high-wr\"" : (
                              $playerline[$player_ids[$i]]['winrate'] < 0.45 ? " class=\"low-wr\"" : ""
                            )
                          )." onclick=\"showModal('".$strings['matches'].": ".$pvp[$pid][$player_ids[$i]]['matches']
                                ."<br />".$strings['winrate'].": ".number_format($pvp[$pid][$player_ids[$i]]['winrate']*100,2)
                                ."%<br />".$strings['won']." ".$pvp[$pid][$player_ids[$i]]['won']." - "
                                         .$strings['lost']." ".$pvp[$pid][$player_ids[$i]]['lost'].(
                                           isset($pvp[$pid][$player_ids[$i]]['matchids']) ?
                                            "<br />MatchIDs: ".implode($pvp[$pid][$player_ids[$i]]['matchids'], ", ")
                                            : "").
                                "','".$report['players'][$pid]." vs ".$report['players'][$player_ids[$i]]."')\">".
                            number_format($playerline[$player_ids[$i]]['winrate']*100,0)."</td>";
              }
            }
            $modules['players']['pvp']['grid'] .= "</tr>";
          }

          $modules['players']['pvp']['grid'] .= "</table>";

          $modules['players']['pvp']['grid'] .= "<div class=\"content-text\">".$strings['desc_players_pvp_grid']."</div>";
        }



        foreach($pvp as $pid => $playerline) {
          $strings['pid'.$pid] = $report['players'][$pid];

          $modules['players']['pvp']['pid'.$pid] = "";
          if(!check_module($parent."pvp-pid".$pid)) continue;

          $modules['players']['pvp']['pid'.$pid] = "<table id=\"player-pvp-$pid\" class=\"list\">";

          $modules['players']['pvp']['pid'.$pid] .= "<tr class=\"thead\">
                                                        <th onclick=\"sortTable(0,'player-pvp-$pid');\">".$strings['opponent']."</th>
                                                        <th onclick=\"sortTableNum(1,'player-pvp-$pid');\">".$strings['winrate']."</th>
                                                        <th onclick=\"sortTableNum(2,'player-pvp-$pid');\">".$strings['matches']."</th>
                                                        <th onclick=\"sortTableNum(3,'player-pvp-$pid');\">".$strings['won']."</th>
                                                        <th onclick=\"sortTableNum(4,'player-pvp-$pid');\">".$strings['lost']."</th>
                                                     </tr>";
          for($i=0, $end = sizeof($player_ids); $i<$end; $i++) {
            if($player_ids[$i] == $pid || $pvp[$pid][$player_ids[$i]]['matches'] == 0) {
              continue;
            } else {
              $modules['players']['pvp']['pid'.$pid] .= "<tr ".(isset($pvp[$pid][$player_ids[$i]]['matchids']) ?
                                                                "onclick=\"showModal('".implode($pvp[$pid][$player_ids[$i]]['matchids'], ", ")."','".$strings['matches']."')\"" :
                                                                "").">
                                                            <td>".$report['players'][$player_ids[$i]]."</th>
                                                            <td>".number_format($pvp[$pid][$player_ids[$i]]['winrate']*100,2)."</th>
                                                            <td>".$pvp[$pid][$player_ids[$i]]['matches']."</th>
                                                            <td>".$pvp[$pid][$player_ids[$i]]['won']."</th>
                                                            <td>".$pvp[$pid][$player_ids[$i]]['lost']."</th>
                                                         </tr>";
            }
          }
          $modules['players']['pvp']['pid'.$pid] .= "</table>";

          $modules['players']['pvp']['pid'.$pid] .= "<div class=\"content-text\">".$strings['desc_players_pvp']."</div>";
        }
        unset($pvp);
      }
    }
    if (isset($report['players_combo_graph']) && $report['settings']['players_combo_graph']) {
      $modules['players']['players_combo_graph'] = "";

      if (check_module($parent."players_combo_graph")) {
        $modules['players']['players_combo_graph'] .= "<div class=\"content-text\">".$strings['desc_players_combo_graph']."</div>";
        if(isset($report['players_combo_graph'])) {
          $use_visjs = true;

          $modules['players']['players_combo_graph'] .= "<div id=\"players-combos-graph\" class=\"graph\"></div><script type=\"text/javascript\">";

          $nodes = "";
          foreach($report['players'] as $pid => $player) {
            if (!has_pair($pid, $report['players_combo_graph'])) continue;
            $nodes .= "{id: $pid, value: ".$report['players_additional'][$pid]['matches'].", label: '".addslashes($player)."'},";
          }
          $modules['players']['players_combo_graph'] .= "var nodes = [".$nodes."];";

          $nodes = "";
          foreach($report['players_combo_graph'] as $combo) {
            $nodes .= "{from: ".$combo['playerid1'].", to: ".$combo['playerid2'].", value:".$combo['wins']."},";
          }

          $modules['players']['players_combo_graph'] .= "var edges = [".$nodes."];";

          $modules['players']['players_combo_graph'] .= "var container = document.getElementById('players-combos-graph');\n".
                                                      "var data = { nodes: nodes, edges: edges};\n".
                                                      "var options={
                                                        physics:{
                                                          barnesHut:{
                                                            avoidOverlap:0.8,
                                                            centralGravity:0.05,
                                                          },
                                                          timestep: 0.01
                                                        }, nodes: {
                                                           shape: 'dot',
                                                           font: {color:'#ccc',size:14},
                                                           scaling:{
                                                             label: {
                                                               min:8, max:20
                                                             }
                                                           }
                                                         }
                                                       };\n".
                                                      "var network = new vis.Network(container, data, options);\n".
                                                      "</script>";
        }
      }
    }
    if (isset($report['player_pairs']) || isset($report['player_triplets'])) {
      $modules['players']['player_combos'] = "";

      if(check_module($parent."player_combos")) {
        if(isset($report['player_pairs'])) {
          $modules['players']['player_combos'] .= "<table id=\"player-pairs\" class=\"list\">
                                                <caption>".$strings['player_pairs']."</caption>
                                                <tr class=\"thead\">
                                                  <th onclick=\"sortTable(0,'player-pairs');\">".$strings['player']." 1</th>
                                                  <th onclick=\"sortTable(1,'player-pairs');\">".$strings['player']." 2</th>
                                                  <th onclick=\"sortTableNum(2,'player-pairs');\">".$strings['matches']."</th>
                                                  <th onclick=\"sortTableNum(3,'player-pairs');\">".$strings['winrate']."</th>
                                                </tr>";
          foreach($report['player_pairs'] as $pair) {
            $modules['players']['player_combos'] .= "<tr".(isset($report['player_pairs_matches']) ?
                            " onclick=\"showModal('".implode($report['player_pairs_matches'][$pair['playerid1'].'-'.$pair['playerid2']], ", ").
                                  "', '".$strings['matches']."');\"" : "").">
                                                  <td>".$report['players'][ $pair['playerid1'] ]."</td>
                                                  <td>".$report['players'][ $pair['playerid2'] ]."</td>
                                                 <td>".$pair['matches']."</td>
                                                 <td>".number_format($pair['winrate']*100,2)."</td>
                                                </tr>";
          }
          $modules['players']['player_combos'] .= "</table>";
        }

        if (isset($report['player_triplets'])) {
          $modules['players']['player_combos'] .= "<table id=\"player-triplets\" class=\"list\">
                                                <caption>".$strings['player_triplets']."</caption>
                                                <tr class=\"thead\">
                                                  <th onclick=\"sortTable(0,'player-triplets');\">".$strings['player']." 1</th>
                                                  <th onclick=\"sortTable(1,'player-triplets');\">".$strings['player']." 2</th>
                                                  <th onclick=\"sortTable(2,'player-triplets');\">".$strings['player']." 3</th>
                                                  <th onclick=\"sortTableNum(3,'player-triplets');\">".$strings['matches']."</th>
                                                  <th onclick=\"sortTableNum(4,'player-triplets');\">".$strings['winrate']."</th>
                                                </tr>";
          foreach($report['player_triplets'] as $pair) {
            $modules['players']['player_combos'] .= "<tr".(isset($report['player_triplets_matches']) ?
                            " onclick=\"showModal('".implode($report['player_triplets_matches'][$pair['playerid1'].'-'.$pair['playerid2'].'-'.$pair['playerid3']], ", ").
                                  "', '".$strings['matches']."');\"" : "").">
                                                  <td>".$report['players'][ $pair['playerid1'] ]."</td>
                                                  <td>".$report['players'][ $pair['playerid2'] ]."</td>
                                                  <td>".$report['players'][ $pair['playerid3'] ]."</td>
                                                 <td>".$pair['matches']."</td>
                                                 <td>".number_format($pair['winrate']*100,2)."</td>
                                                </tr>";
          }
          $modules['players']['player_combos'] .= "</table>";

          $modules['players']['player_combos'] .= "<div class=\"content-text\">".$strings['desc_players_combos']."</div>";
        }
      }
    }
    if (isset($report['player_positions'])) {
      $modules['players']['player_positions'] = array();

      if(check_module($parent."player_positions")) {
        if($mod == $parent."player_positions") $unset_module = true;

        for ($i=0; $i<2 && !isset($keys); $i++) {
          for ($j=1; $j<6 && $j>0; $j++) {
            if (!$i) { $j = 0; }
            if(isset($report['player_positions'][$i][$j][0])) {
              $keys = array_keys($report['player_positions'][$i][$j][0]);
              break;
            }
            if (!$i) { break; }
          }
        }

        for ($i=0; $i<2; $i++) {
          for ($j=1; $j<6 && $j>0; $j++) {
            if (!$i) { $j = 0; }

            if(!isset($strings["positions_$i"."_$j"]))
              $strings["positions_$i"."_$j"] = ($i ? $strings['core'] : $strings['support'])." ".$meta['lanes'][$j];

            if(sizeof($report['player_positions'][$i][$j])) {
              $modules['players']['player_positions']["positions_$i"."_$j"]  = "";
              if (!check_module($parent."player_positions-"."positions_$i"."_$j")) { if (!$i) { break; } continue; }

              $modules['players']['player_positions']["positions_$i"."_$j"] .= "<table id=\"players-positions-$i-$j\" class=\"list wide\">
                                                <tr class=\"thead\">
                                                  <th onclick=\"sortTable(0,'players-positions-$i-$j');\">".$strings['player']."</th>";
              for($k=1, $end=sizeof($keys); $k < $end; $k++) {
                $modules['players']['player_positions']["positions_$i"."_$j"] .= "<th onclick=\"sortTableNum($k,'players-positions-$i-$j');\">".$strings[$keys[$k]]."</th>";
              }
              $modules['players']['player_positions']["positions_$i"."_$j"] .= "</tr>";


              foreach($report['player_positions'][$i][$j] as $player) {

                $modules['players']['player_positions']["positions_$i"."_$j"] .= "<tr".(isset($report['player_positions_matches']) ?
                                                    " onclick=\"showModal('".htmlspecialchars(join_matches($report['player_positions_matches'][$i][$j][$player['playerid']])).
                                                                          "', '".$report['players'][$player['playerid']]." - ".
                                                                          $strings["positions_$i"."_$j"]." - ".$strings['matches']."');\"" : "").">
                                                    <td>".$report['players'][$player['playerid']]."</td>
                                                    <td>".$player['matches_s']."</td>
                                                    <td>".number_format($player['winrate_s']*100,1)."%</td>";
                for($k=3, $end=sizeof($keys); $k < $end; $k++) {
                  if ($player[$keys[$k]] > 1)
                    $modules['players']['player_positions']["positions_$i"."_$j"] .= "<td>".number_format($player[$keys[$k]],1)."</td>";
                  else $modules['players']['player_positions']["positions_$i"."_$j"] .= "<td>".number_format($player[$keys[$k]],2)."</td>";
                }
                $modules['players']['player_positions']["positions_$i"."_$j"] .= "</tr>";
              }
              $modules['players']['player_positions']["positions_$i"."_$j"] .= "</table>";

              $modules['players']['player_positions']["positions_$i"."_$j"] .= "<div class=\"content-text\">".$strings['desc_players_positions']."</div>";
            }
            if (!$i) { break; }
          }
        }
        unset($keys);
      }
    }
  }

  # teams
  if (isset($modules['teams']) && check_module("teams")) {
    if($mod == "teams") $unset_module = true;
    $parent = "teams-";

    foreach ($report['teams'] as $tid => $team) {
      $modules['teams']["team_".$tid."_stats"] = array();
      $strings["team_".$tid."_stats"] = $team['name'];

      if(check_module($parent."team_".$tid."_stats")) {
        if($mod == $parent."team_".$tid."_stats") $unset_module = true;

        if (isset($report['teams'][$tid]['averages'])) {
          $modules['teams']["team_".$tid."_stats"]['overview'] = "";

          if(check_module($parent."team_".$tid."_stats-overview")) {
            $modules['teams']["team_".$tid."_stats"]['overview'] .= "<div class=\"content-cards\">".team_card($tid)."</div>";
            $modules['teams']["team_".$tid."_stats"]['overview'] .= "<table id=\"teams-$tid-avg-table\" class=\"list\"> ";

            foreach ($report['teams'][$tid]['averages'] as $key => $value) {
              $modules['teams']["team_".$tid."_stats"]['overview'] .= "<tr><td>".$strings[ $key ]."</td><td>".number_format($value, 2)."</td></tr>";
            }

            $modules['teams']["team_".$tid."_stats"]['overview'] .= "</table>";

            $modules['teams']["team_".$tid."_stats"]['overview'] .= "<div class=\"content-text\">".$strings['desc_teams']."</div>";
          }
        }
        if (isset($report['teams'][$tid]['pickban'])) {
          $modules['teams']["team_".$tid."_stats"]['pickban'] = "";

          if(check_module($parent."team_".$tid."_stats-pickban")) {
            $heroes = $meta['heroes'];

            $modules['teams']["team_".$tid."_stats"]['pickban'] .=  "<table id=\"pickban-team$tid\" class=\"list\">
                                                  <tr class=\"thead\">
                                                    <th onclick=\"sortTable(0,'pickban-team$tid');\">".$strings['hero']."</th>
                                                    <th onclick=\"sortTableNum(1,'pickban-team$tid');\">".$strings['matches_total']."</th>
                                                    <th onclick=\"sortTableNum(2,'pickban-team$tid');\">".$strings['matches_picked']."</th>
                                                    <th onclick=\"sortTableNum(3,'pickban-team$tid');\">".$strings['winrate_picked']."</th>
                                                    <th onclick=\"sortTableNum(4,'pickban-team$tid');\">".$strings['matches_banned']."</th>
                                                    <th onclick=\"sortTableNum(5,'pickban-team$tid');\">".$strings['winrate_banned']."</th>
                                                  </tr>";
            foreach($report['teams'][$tid]['pickban'] as $hid => $hero) {
              unset($heroes[$hid]);
              $modules['teams']["team_".$tid."_stats"]['pickban'] .=  "<tr>
                                                    <td>".($hid ? hero_full($hid) : "").
                                                   "</td>
                                                    <td>".$hero['matches_total']."</td>
                                                    <td>".$hero['matches_picked']."</td>
                                                    <td>".($hero['matches_picked'] ? number_format($hero['wins_picked']*100/$hero['matches_picked'],2) : 0)."%</td>
                                                    <td>".$hero['matches_banned']."</td>
                                                    <td>".($hero['matches_banned'] ? number_format($hero['wins_banned']*100/$hero['matches_banned'],2) : 0)."%</td>
                                                  </tr>";
            }
            $modules['teams']["team_".$tid."_stats"]['pickban'] .= "</table>";

            $modules['teams']["team_".$tid."_stats"]['pickban'] .= "<div class=\"content-text\"><h1>".$strings['heroes_uncontested'].": ".sizeof($heroes)."</h1><div class=\"hero-list\">";

            foreach($heroes as $hero) {
              $modules['teams']["team_".$tid."_stats"]['pickban'] .= "<div class=\"hero\"><img src=\"res/heroes/".$hero['tag'].
                  ".png\" alt=\"".$hero['tag']."\" /><span class=\"hero_name\">".
                  $hero['name']."</span></div>";
            }
            $modules['teams']["team_".$tid."_stats"]['pickban'] .= "</div></div>";

            $modules['teams']["team_".$tid."_stats"]['pickban'] .= "<div class=\"content-text\">".$strings['desc_heroes_pickban']."</div>";
          }
        }
        if (isset($report['teams'][$tid]['draft'])) {
          $modules['teams']["team_".$tid."_stats"]['draft'] = "";

          if(check_module($parent."team_".$tid."_stats-draft")) {
            for ($i=0; $i<2; $i++) {
              for ($j=1; $j<4; $j++, isset($report['teams'][$tid]['draft'])) {
                uasort($report['teams'][$tid]['draft'][$i][$j], function($a, $b) {
                  if($a['matches'] == $b['matches']) return 0;
                  else return ($a['matches'] < $b['matches']) ? 1 : -1;
                });

                $modules['teams']["team_".$tid."_stats"]['draft'] .= "<table id=\"team$tid-draft-$i-$j\" class=\"list list-small\">
                                                  <caption> Stage $j of ".($i ? $strings['picks'] : $strings['bans'])."</caption>
                                                  <tr class=\"thead\">
                                                    <th onclick=\"sortTable(0,'team$tid-draft-$i-$j');\">".$strings['hero']."</th>
                                                    <th onclick=\"sortTableNum(1,'team$tid-draft-$i-$j');\">".$strings['matches']."</th>
                                                    <th onclick=\"sortTableNum(2,'team$tid-draft-$i-$j');\">".$strings['winrate']."</th>
                                                  </tr>";

                foreach($report['teams'][$tid]['draft'][$i][$j] as $hero) {
                  $modules['teams']["team_".$tid."_stats"]['draft'] .= "<tr>
                                                      <td>".($hero['heroid'] ? hero_full($hero['heroid']) : "").
                                                     "</td>
                                                      <td>".$hero['matches']."</td>
                                                      <td>".number_format($hero['winrate']*100,2)."%</td>
                                                    </tr>";
                }
                $modules['teams']["team_".$tid."_stats"]['draft'] .= "</table>";

                $modules['teams']["team_".$tid."_stats"]['draft'] .= "<div class=\"content-text\">".$strings['desc_heroes_draft']."</div>";
              }
            }
          }
        }
        if (isset($report['teams'][$tid]['hero_positions'])) {
          $modules['teams']["team_".$tid."_stats"]['hero_positions'] = "";

          if (check_module($parent."team_".$tid."_stats-hero_positions")) {
            if($mod == $parent."team_".$tid."_stats-hero_positions") $unset_module = true;
            for ($i=0; $i<2 && !isset($keys); $i++) {
              for ($j=1; $j<6 && $j>0; $j++) {
                if (!$i) { $j = 0; }
                if(isset($report['teams'][$tid]['hero_positions'][$i][$j][0])) {
                  $keys = array_keys($report['teams'][$tid]['hero_positions'][$i][$j][0]);
                  break;
                }
                if (!$i) { break; }
              }
            }

            for ($i=0; $i<2; $i++) {
              for ($j=1; $j<6 && $j>0; $j++) {
                if (!$i) { $j = 0; }

                if(!isset($strings["positions_$i"."_$j"]))
                  $strings["positions_$i"."_$j"] = ($i ? $strings['core'] : $strings['support'])." ".$meta['lanes'][$j];

                if(sizeof($report['teams'][$tid]['hero_positions'][$i][$j])) {
                  $modules['teams']["team_".$tid."_stats"]['hero_positions']["positions_$i"."_$j"]  = "";

                  if (check_module($parent."team_".$tid."_stats-hero_positions-positions_$i"."_$j")) {
                    $modules['teams']["team_".$tid."_stats"]['hero_positions']["positions_$i"."_$j"] .= "<table id=\"heroes-positions-$i-$j\" class=\"list wide\">
                                                      <tr class=\"thead\">
                                                        <th onclick=\"sortTable(0,'heroes-positions-$i-$j');\">".$strings['hero']."</th>";
                    for($k=1, $end=sizeof($keys); $k < $end; $k++) {
                      $modules['teams']["team_".$tid."_stats"]['hero_positions']["positions_$i"."_$j"] .= "<th onclick=\"sortTableNum($k,'heroes-positions-$i-$j');\">".$strings[$keys[$k]]."</th>";
                    }
                    $modules['teams']["team_".$tid."_stats"]['hero_positions']["positions_$i"."_$j"] .= "</tr>";

                    uasort($report['teams'][$tid]['hero_positions'][$i][$j], function($a, $b) {
                      if($a['matches_s'] == $b['matches_s']) return 0;
                      else return ($a['matches_s'] < $b['matches_s']) ? 1 : -1;
                    });

                    foreach($report['teams'][$tid]['hero_positions'][$i][$j] as $hero) {

                      $modules['teams']["team_".$tid."_stats"]['hero_positions']["positions_$i"."_$j"] .= "<tr".(isset($report['teams'][$tid]['hero_positions_matches']) ?
                                                                        " onclick=\"showModal('".htmlspecialchars(join_matches($report['teams'][$tid]['hero_positions_matches'][$i][$j][$hero['heroid']])).
                                                                                "', '".$meta['heroes'][ $hero['heroid'] ]['name']." - ".
                                                                                $strings["positions_$i"."_$j"]." - ".$strings['matches']."');\"" : "").">
                                                          <td>".($hero['heroid'] ? hero_full($hero['heroid']) : "").
                                                         "</td>
                                                          <td>".$hero['matches_s']."</td>
                                                          <td>".number_format($hero['winrate_s']*100,1)."%</td>";
                      for($k=3, $end=sizeof($keys); $k < $end; $k++) {
                        $modules['teams']["team_".$tid."_stats"]['hero_positions']["positions_$i"."_$j"] .= "<td>".number_format($hero[$keys[$k]],1)."</td>";
                      }
                      $modules['teams']["team_".$tid."_stats"]['hero_positions']["positions_$i"."_$j"] .= "</tr>";
                    }
                    $modules['teams']["team_".$tid."_stats"]['hero_positions']["positions_$i"."_$j"] .= "</table>";
                  }
                }
                $modules['teams']["team_".$tid."_stats"]['hero_positions']["positions_$i"."_$j"] .= "<div class=\"content-text\">".$strings['desc_heroes_positions']."</div>";

                if (!$i) { break; }
              }
            }
            unset($keys);
          }
        }
        if (isset($report['teams'][$tid]['hero_graph']) && $report['settings']['heroes_combo_graph'] && isset($report['teams'][$tid]['pickban'])) {
          $modules['teams']["team_".$tid."_stats"]['hero_combo_graph'] = "";

          if (check_module($parent."team_".$tid."_stats-hero_combo_graph") && isset($report['teams'][$tid]['pickban'])) {
            $use_visjs = true;

            $modules['teams']["team_".$tid."_stats"]['hero_combo_graph'] .= "<div id=\"team$tid-combos-graph\" class=\"graph\"></div><script type=\"text/javascript\">";

            $nodes = "";
            foreach($meta['heroes'] as $hid => $hero) {
              if(!has_pair($hid, $report['teams'][$tid]['hero_graph'])) continue;
              $nodes .= "{id: $hid, value: ".$report['pickban'][$hid]['matches_picked'].
                ", label: '".addslashes($hero['name'])."'},";
            }
            $modules['teams']["team_".$tid."_stats"]['hero_combo_graph'] .= "var nodes = [".$nodes."];";

            $nodes = "";
            foreach($report['teams'][$tid]['hero_graph'] as $combo) {
              $nodes .= "{from: ".$combo['heroid1'].", to: ".$combo['heroid2'].", value:".$combo['matches']."},";
            }

            $modules['teams']["team_".$tid."_stats"]['hero_combo_graph'] .= "var edges = [".$nodes."];";

            $modules['teams']["team_".$tid."_stats"]['hero_combo_graph'] .= "var container = document.getElementById('team$tid-combos-graph');\n".
                                                        "var data = { nodes: nodes, edges: edges};\n".
                                                        "var options={
                                                          physics:{
                                                            barnesHut:{
                                                              avoidOverlap:0.8,
                                                              centralGravity:0.05,
                                                              springLength:90,
                                                              springConstant:0.001,
                                                              gravitationalConstant:-500
                                                            },
                                                            timestep: 0.01
                                                          }, nodes: {
                                                             shape: 'dot',
                                                             font: {color:'#ccc',size:14},
                                                             scaling:{
                                                               label: {
                                                                 min:8, max:20
                                                               }
                                                             }
                                                           }
                                                         };\n".
                                                        "var network = new vis.Network(container, data, options);\n".
                                                        "</script>";

              $modules['teams']["team_".$tid."_stats"]['hero_combo_graph'] .= "<div class=\"content-text\">".$strings['desc_heroes_combo_graph']."</div>";
          }
        }
        if (isset($report['teams'][$tid]['hero_pairs']) || isset($report['teams'][$tid]['hero_triplets'])) {
          $modules['teams']["team_".$tid."_stats"]['hero_combos'] = "";

          if (check_module($parent."team_".$tid."_stats-hero_combos")) {
            $modules['teams']["team_".$tid."_stats"]['hero_combos'] .= "<table id=\"team$tid-pairs\" class=\"list\">
                                                  <caption>".$strings['hero_pairs']."</caption>
                                                  <tr class=\"thead\">
                                                    <th onclick=\"sortTable(0,'hero-pairs');\">".$strings['hero']." 1</th>
                                                    <th onclick=\"sortTable(1,'hero-pairs');\">".$strings['hero']." 2</th>
                                                    <th onclick=\"sortTableNum(2,'hero-pairs');\">".$strings['matches']."</th>
                                                    <th onclick=\"sortTableNum(3,'hero-pairs');\">".$strings['winrate']."</th>
                                                  </tr>";
            foreach($report['teams'][$tid]['hero_pairs'] as $pair) {
              $modules['teams']["team_".$tid."_stats"]['hero_combos'] .= "<tr".(isset($report['teams'][$tid]['hero_pairs_matches']) ?
                                                  " onclick=\"showModal('".
                                                            htmlspecialchars(join_matches($report['teams'][$tid]['hero_pairs_matches'][$pair['heroid1'].'-'.$pair['heroid2']])).
                                                                        "', '".$strings['matches']."');\"" : "").">
                                                    <td>".($pair['heroid1'] ? hero_full($pair['heroid1']) : "").
                                                   "</td><td>".($pair['heroid2'] ? hero_full($pair['heroid2']) : "").
                                                   "</td>
                                                   <td>".$pair['matches']."</td>
                                                   <td>".number_format($pair['winrate']*100,2)."</td>
                                                  </tr>";
            }
            $modules['teams']["team_".$tid."_stats"]['hero_combos'] .= "</table>";

            if (!empty($report['teams'][$tid]['hero_triplets'])) {
              $modules['teams']["team_".$tid."_stats"]['hero_combos'] .= "<table id=\"hero-triplets\" class=\"list\">
                                                    <caption>".$strings['hero_triplets']."</caption>
                                                    <tr class=\"thead\">
                                                      <th onclick=\"sortTable(0,'hero-triplets');\">".$strings['hero']." 1</th>
                                                      <th onclick=\"sortTable(1,'hero-triplets');\">".$strings['hero']." 2</th>
                                                      <th onclick=\"sortTable(2,'hero-triplets');\">".$strings['hero']." 3</th>
                                                      <th onclick=\"sortTableNum(3,'hero-triplets');\">".$strings['matches']."</th>
                                                      <th onclick=\"sortTableNum(4,'hero-triplets');\">".$strings['winrate']."</th>
                                                    </tr>";
              foreach($report['teams'][$tid]['hero_triplets'] as $pair) {
                $modules['teams']["team_".$tid."_stats"]['hero_combos'] .= "<tr".(isset($report['teams'][$tid]['hero_pairs_matches']) ?
                                                    " onclick=\"showModal('".
                                                    implode($report['hero_pairs_matches'][$pair['heroid1'].'-'.$pair['heroid2'].'-'.$pair['heroid3']], ", ").
                                                                          "', '".$strings['matches']."');\"" : "").">
                                                      <td>".($pair['heroid1'] ? hero_full($pair['heroid1']) : "").
                                                     "</td><td>".($pair['heroid2'] ? hero_full($pair['heroid2']) : "").
                                                     "</td><td>".($pair['heroid3'] ? hero_full($pair['heroid3']) : "").
                                                     "</td>
                                                     <td>".$pair['matches']."</td>
                                                     <td>".number_format($pair['winrate']*100,2)."</td>
                                                    </tr>";
              }
              $modules['teams']["team_".$tid."_stats"]['hero_combos'] .= "</table>";

              $modules['teams']["team_".$tid."_stats"]['hero_combos'] .= "<div class=\"content-text\">".$strings['desc_heroes_combos']."</div>";
            }
          }
        }
        if (isset($report['teams'][$tid]['matches']) && isset($report['matches'])) {
          $modules['teams']["team_".$tid."_stats"]['matches'] = "";

          if(check_module($parent."team_".$tid."_stats-matches")) {
            $modules['teams']["team_".$tid."_stats"]['matches'] = "<div class=\"content-text\">".$strings['desc_matches']."</div>";
            $modules['teams']["team_".$tid."_stats"]['matches'] .= "<div class=\"content-cards\">";
            foreach($report['teams'][$tid]['matches'] as $matchid => $match) {
              $modules['teams']["team_".$tid."_stats"]['matches'] .= match_card($matchid);
            }
            $modules['teams']["team_".$tid."_stats"]['matches'] .= "</div>";
          }
        }
        if (isset($modules['participants'])) {
          $modules['teams']["team_".$tid."_stats"]['roster'] = "";

          if(check_module($parent."team_".$tid."_stats-roster")) {
            $modules['teams']["team_".$tid."_stats"]['roster'] = "<div class=\"content-text\">".$strings['desc_roster']."</div>";
            $modules['teams']["team_".$tid."_stats"]['roster'] .= "<div class=\"content-cards\">";
            foreach($report['teams'][$tid]['active_roster'] as $player) {
              $modules['teams']["team_".$tid."_stats"]['roster'] .= player_card($player);
            }
            $modules['teams']["team_".$tid."_stats"]['roster'] .= "</div>";
          }
        }
      }
    }
  }

  if (isset($modules['summary_teams']) && check_module("summary_teams")) {
    $modules['summary_teams'] .= "<table id=\"teams-sum\" class=\"list wide\">";

    $modules['summary_teams'] .= "<tr class=\"thead\">".
                    "<th onclick=\"sortTable(0,'teams-sum');\">".$strings['team_name']."</th>".
                    "<th onclick=\"sortTableNum(1,'teams-sum');\">".$strings['matches_s']."</th>".
                    "<th onclick=\"sortTableNum(2,'teams-sum');\">".$strings['winrate_s']."</th>".
                    "<th onclick=\"sortTableNum(2,'teams-sum');\">".$strings['rad_ratio']."</th>".
                    "<th onclick=\"sortTableNum(2,'teams-sum');\">".$strings['rad_wr']."</th>".
                    "<th onclick=\"sortTableNum(2,'teams-sum');\">".$strings['dire_wr']."</th>".
                    "<th onclick=\"sortTableNum(3,'teams-sum');\">".$strings['hero_pool']."</th>".
                    "<th onclick=\"sortTableNum(4,'teams-sum');\">".$strings['kills']."</th>".
                    "<th onclick=\"sortTableNum(5,'teams-sum');\">".$strings['deaths']."</th>".
                    "<th onclick=\"sortTableNum(6,'teams-sum');\">".$strings['assists']."</th>".
                    "<th onclick=\"sortTableNum(7,'teams-sum');\">".$strings['gpm']."</th>".
                    "<th onclick=\"sortTableNum(8,'teams-sum');\">".$strings['xpm']."</th>".
                    "<th onclick=\"sortTableNum(9,'teams-sum');\">".$strings['wards_placed_s']."</th>".
                    "<th onclick=\"sortTableNum(10,'teams-sum');\">".$strings['sentries_placed_s']."</th>".
                    "<th onclick=\"sortTableNum(11,'teams-sum');\">".$strings['wards_destroyed_s']."</th>".
                    "<th onclick=\"sortTableNum(11,'teams-sum');\">".$strings['duration']."</th>".
              "</tr>";

    foreach($report['teams'] as $team_id => $team) {
      $modules['summary_teams'] .= "<tr>".
                    "<td>".team_name($team_id)."</td>".
                    "<td>".$team['matches_total']."</td>".
                    "<td>".number_format($team['wins']*100/$team['matches_total'],2)."%</td>".
                    "<td>".number_format($team['averages']['rad_ratio']*100,2)."%</td>".
                    "<td>".number_format($team['averages']['rad_wr']*100,2)."%</td>".
                    "<td>".number_format($team['averages']['dire_wr']*100,2)."%</td>".
                    "<td>".$team['averages']['hero_pool']."</td>".
                    "<td>".number_format($team['averages']['kills'],1)."</td>".
                    "<td>".number_format($team['averages']['deaths'],1)."</td>".
                    "<td>".number_format($team['averages']['assists'],1)."</td>".
                    "<td>".number_format($team['averages']['gpm'],1)."</td>".
                    "<td>".number_format($team['averages']['xpm'],1)."</td>".
                    "<td>".number_format($team['averages']['wards_placed'],1)."</td>".
                    "<td>".number_format($team['averages']['sentries_placed'],1)."</td>".
                    "<td>".number_format($team['averages']['wards_destroyed'],1)."</td>".
                    "<td>".number_format($team['averages']['duration'],1)."</td>".
              "</tr>";
    }
    $modules['summary_teams'] .= "</table>";

    $modules['summary_teams'] .= "<div class=\"content-text\">".$strings['desc_teams_summary']."</div>";
  }

  if (isset($modules['tvt']) && check_module("tvt")) {
    $tvt = array();

      foreach($report['teams'] as $tid => $team) {
        $tvt[$tid] = array();
      }
      $team_ids = array_keys($report['teams']);

        $modules['tvt'] = "";

        $modules['tvt'] .= "<table  class=\"pvp wide\">";

        $modules['tvt'] .= "<tr class=\"thead\"><th></th>";
        foreach($report['teams'] as $tid => $team) {
          $modules['tvt'] .= "<th><span>".$team['tag']."</span></th>";
        }
        $modules['tvt'] .= "</tr>";

      foreach($tvt as $team_id => $team) {
        foreach($team_ids as $tid) {
          $tvt[$team_id][$tid] = array(
            "winrate" => 0,
            "matches" => 0,
            "won" => 0,
            "lost" => 0
          );
        }
      }

      foreach($team_ids as $tid) {
        for($i=0, $end = sizeof($report['tvt']); $i<$end; $i++) {
          if($report['tvt'][$i]['teamid1'] == $tid) {
            $tvt[$tid][$report['tvt'][$i]['teamid2']] = array(
              "winrate" => ($report['tvt'][$i]['t1won']/$report['tvt'][$i]['matches'] ),
              "matches" => $report['tvt'][$i]['matches'],
              "won" => $report['tvt'][$i]['t1won'],
              "lost" => $report['tvt'][$i]['matches'] - $report['tvt'][$i]['t1won'],
            );
          }
          if($report['tvt'][$i]['teamid2'] == $tid) {
            $tvt[$tid][$report['tvt'][$i]['teamid1']] = array(
              "winrate" => ($report['tvt'][$i]['matches']-$report['tvt'][$i]['t1won'])/$report['tvt'][$i]['matches'],
              "matches" => $report['tvt'][$i]['matches'],
              "won" => $report['tvt'][$i]['matches'] - $report['tvt'][$i]['t1won'],
              "lost" => $report['tvt'][$i]['t1won']
            );
          }
        }
      }

      foreach($tvt as $tid => $teamline) {
        $modules['tvt'] .= "<tr><td>".$report['teams'][$tid]['name']."</td>";
        for($i=0, $end = sizeof($team_ids); $i<$end; $i++) {
          if($tid == $team_ids[$i]) {
            $modules['tvt'] .= "<td class=\"transparent\"></td>";
          } else if($teamline[$team_ids[$i]]['matches'] == 0) {
            $modules['tvt'] .= "<td>-</td>";
          } else {
            $modules['tvt'] .= "<td".
                    ($teamline[$team_ids[$i]]['winrate'] > 0.55 ? " class=\"high-wr\"" : (
                          $teamline[$team_ids[$i]]['winrate'] < 0.45 ? " class=\"low-wr\"" : ""
                        )
                      ).">".number_format($teamline[$team_ids[$i]]['winrate']*100,0)."</td>";
          }
        }
        $modules['tvt'] .= "</tr>";
      }

      $modules['tvt'] .= "</table>";

      $modules['tvt'] .= "<div class=\"content-text\">".$strings['desc_tvt']."</div>";

      unset($tvt);
  }

  # matches
  if (isset($modules['matches']) && check_module("matches")) {
    $modules['matches'] = "<div class=\"content-text\">".$strings['desc_matches']."</div>";
    $modules['matches'] .= "<div class=\"content-cards\">";
    foreach($report['matches'] as $matchid => $match) {
      $modules['matches'] .= match_card($matchid);
    }
    $modules['matches'] .= "</div>";
  }

  # participants
  if(isset($modules['participants']) && check_module("participants")) {
    if($mod == "participants") $unset_module = true;
    $parent = "participants-";

    if(isset($report['teams'])) {
      $modules['participants']['teams'] = "";
      if(check_module($parent."teams")) {
        $modules['participants']['teams'] .= "<div class=\"content-text\">".$strings['desc_participants']."</div>";
        $modules['participants']['teams'] .= "<div class=\"content-cards\">";
        foreach($report['teams'] as $team_id => $team) {
          $modules['participants']['teams'] .= team_card($team_id);
        }
        $modules['participants']['teams'] .= "</div>";
      }
    }

    $modules['participants']['players'] = "";
    if(check_module($parent."players")) {
      $modules['participants']['players'] .= "<div class=\"content-text\">".$strings['desc_participants']."</div>";
      $modules['participants']['players'] .= "<div class=\"content-cards\">";
      foreach($report['players'] as $player_id => $player) {
        $modules['participants']['players'] .= player_card($player_id);
      }
      $modules['participants']['players'] .= "</div>";
    }
  }
}
  ?>
  <!DOCTYPE html>
  <html>
    <head>
      <!--
        League Report Generator
        Spectral Alliance
        leamare/d2_lrg on github
       -->
      <link rel="shortcut icon" href="/favicon.ico" />
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      <title>LRG<?php if (!empty($leaguetag)) echo " - ".$report['league_name']; ?></title>
      <link href="res/valve_mimic.css" rel="stylesheet" type="text/css" />
      <link href="res/reports.css" rel="stylesheet" type="text/css" />
      <?php
            if(isset($override_style) && file_exists("res/custom_styles/".$override_style.".css"))
                echo "<link href=\"res/custom_styles/".$override_style.".css\" rel=\"stylesheet\" type=\"text/css\" />";
            else if(isset($report['settings']['custom_style']) && file_exists("res/custom_styles/".$report['settings']['custom_style'].".css"))
                echo "<link href=\"res/custom_styles/".$report['settings']['custom_style'].".css\" rel=\"stylesheet\" type=\"text/css\" />";
            if($use_graphjs) {
              echo "<script type=\"text/javascript\" src=\"res/dependencies/Chart.bundle.min.js\"></script>";
            }
            if($use_visjs) {
              echo "<script type=\"text/javascript\" src=\"res/dependencies/vis.js\"></script>";
              echo "<link href=\"res/dependencies/vis-network.min.css\" rel=\"stylesheet\" type=\"text/css\" />";
            }

       if (!empty($custom_head)) echo $custom_head; ?>
    </head>
    <body>
      <?php if (!empty($custom_body)) echo $custom_body; ?>
      <header class="navBar">
        <!-- these shouldn't be spans, but I was mimicking Valve pro circuit style in everything, so I copied that too. -->
        <span class="navItem dotalogo"><a href="<?php echo $main_path; ?>"></a></span>
        <span class="navItem"><a href=".<?php if(!empty($linkvars)) echo "?".$linkvars; ?>" title="Dota 2 League Reports"><?php echo $strings['leag_reports']?></a></span>
        <?php
          foreach($title_links as $link) {
            echo "<span class=\"navItem\"><a href=\"".$link['link']."\" target=\"_blank\" rel=\"noopener\" title=\"".$link['title']."\">".$link['text']."</a></span>";
          }
         ?>
        <div class="share-links">
          <?php
            echo '<div class="share-link reddit"><a href="http://www.reddit.com/submit?url='.htmlspecialchars('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].
              (empty($_SERVER['QUERY_STRING']) ? "" : '?'.$_SERVER['QUERY_STRING'])
            ).'" target="_blank" rel="noopener">Share on Reddit</a></div>';
            echo '<div class="share-link twitter"><a href="http://twitter.com/share?text=League+Report:+'.$leaguetag.'" target="_blank" rel="noopener">Share on Twitter</a></div>';
            echo '<div class="share-link vk"><a href="https://vk.com/share.php?url='.htmlspecialchars('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].
              (empty($_SERVER['QUERY_STRING']) ? "" : '?'.$_SERVER['QUERY_STRING'])
            ).'" target="_blank" rel="noopener">Share on VK</a></div>';
            echo '<div class="share-link fb"><a href="https://www.facebook.com/sharer/sharer.php?u='.htmlspecialchars('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].
              (empty($_SERVER['QUERY_STRING']) ? "" : '?'.$_SERVER['QUERY_STRING'])
            ).'" target="_blank" rel="noopener">Share on Facebook</a></div>';
          ?>
        </div>
      </header>
      <div id="content-wrapper">
      <?php if (!empty($leaguetag)) { ?>
        <div id="header-image" class="section-header">
          <h1><?php echo $report['league_name']; ?></h1>
          <h2><?php echo $report['league_desc']; ?></h2>
          <h3><?php echo $strings[$h3].": ".$report['random'][$h3]; ?></h3>
        </div>
        <div id="main-section" class="content-section">
<?php

if (!empty($custom_content)) echo "<br />".$custom_content;

$output = join_selectors($modules, 0);

echo $output;

?>
          </div>
      <?php } else { ?>
        <div id="header-image" class="section-header">
          <h1>League Report Generator</h1>
        </div>
        <div id="main-section" class="content-section">
          <?php
          $dir = scandir("reports");

          if (sizeof($dir) < 3) {
            echo "<div id=\"content-top\">".
              "<div class=\"content-header\">".$strings['empty_instance_cap']."</div>".
              "<div class=\"content-text\">".$strings['empty_instance_desc'].".</div>".
            "</div>";
          } else {
            echo "<div id=\"content-top\">".
              "<div class=\"content-header\">".$strings['noleague_cap']."</div>".
              "<div class=\"content-text\">".$strings['noleague_desc'].":</div>".
            "</div>";

            echo "<table id=\"leagues-list\" class=\"list wide\"><tr class=\"thead\">
              <th onclick=\"sortTable(0,'leagues-list');\">".$strings['league_name']."</th>
              <th onclick=\"sortTableNum(1,'leagues-list');\">".$strings['league_id']."</th>
              <th>".$strings['league_desc']."</th>
              <th onclick=\"sortTableNum(3,'leagues-list');\">".$strings['matches_total']."</th>
              <th onclick=\"sortTableValue(4,'leagues-list');\">".$strings['start_date']."</th>
              <th onclick=\"sortTableValue(5,'leagues-list');\">".$strings['end_date']."</th></tr>";

            $reports = array();

            foreach($dir as $report) {
                if($report[0] == ".")
                    continue;
                $name = str_replace("report_", "", $report);
                $name = str_replace(".json", "", $name);

                $f = fopen("reports/report_".$name.".json","r");
                $file = fread($f, 700);

                $reports[] = array(
                  "name" => $name,
                  "head" => array_pop(json_decode("[\"".preg_replace("/{\"league_name\":\"(.+)\"\,\"league_desc\":(.*)/", "$1", $file)."\"]")),
                  "desc" => array_pop(json_decode("[\"".preg_replace("/{\"league_name\":\"(.+)\"\,\"league_desc\":\"(.+)\",\"league_id\":(.+),\"league_tag\":(.*)/", "$2", $file)."\"]")),
                  "id" => preg_replace("/{\"league_name\":\"(.+)\"\,\"league_desc\":\"(.+)\",\"league_id\":(.+),\"league_tag\":(.*)/", "$3", $file),
                  "std" => (int)preg_replace("/(.*)\"first_match\":\{(.*)\"date\":\"(\d+)\"\},\"last_match\"(.*)/i", "$3 ", $file),
                  "end" => (int)preg_replace("/(.*)\"last_match\":\{(.*)\"date\":\"(\d+)\"\},\"random\"(.*)/i", "$3 ", $file),
                  "total" => (int)preg_replace("/(.*)\"random\":\{(.*)\"matches_total\":\"(\d+)\",\"(.*)/i", "$3 ", $file)
                );
            }

            uasort($reports, function($a, $b) {
              if($a['std'] == $b['std']) return 0;
              else return ($a['std'] < $b['std']) ? 1 : -1;
            });

            foreach($reports as $report) {
              echo "<tr><td><a href=\"?league=".$report['name'].(empty($linkvars) ? "" : "&".$linkvars)."\">".$report['head']."</a></td>".
                "<td>".($report['id'] == "null" ? " - " : $report['id'])."</td>".
                "<td>".$report['desc']."</td>".
                "<td>".$report['total']."</td>".
                "<td value=\"".$report['std']."\">".date($strings['date_format'], $report['std'])."</td>".
                "<td value=\"".$report['end']."\">".date($strings['date_format'], $report['end'])."</td></tr>";
            }

            echo "</table>";
          }
          ?>
        </div>
      <?php } ?>
      </div>
        <footer>
          <a href="https://dota2.com" target="_blank" rel="noopener">Dota 2</a> is a registered trademark of <a href="https://valvesoftware.com" target="_blank" rel="noopener">Valve Corporation.</a>
          Match replay data analyzed by <a href="https://opendota.com" target="_blank" rel="noopener">OpenDota</a>.<br />
          Graphs are made with <a href="https://visjs.org" target="_blank" rel="noopener">vis.js</a> and <a href="https://chartjs.org" target="_blank" rel="noopener">chart.js</a>.<br />
          Made by <a href="https://spectralalliance.ru" target="_blank" rel="noopener">Spectral Alliance</a>
          with support of <a href="https://vk.com/thecybersport" target="_blank" rel="noopener">TheCyberSport</a>. Klozi is a registered trademark of Grafensky.<br />
          <?php if (!empty($custom_footer)) echo $custom_footer."<br />";
            echo "LRG web version: <a>".parse_ver($lg_version)."</a>. ";
          ?>
          All changes can be discussed on Spectral Alliance discord channel and on <a href="https://github.com/leamare/d2_lrg" target="_blank" rel="noopener">github</a>.
        </footer>
        <div class="modal" id="modal-box">
          <div class="modal-content">
            <div class="modal-header"></div>
            <div id="modal-text" class="modal-text"></div>
            <div id="modal-sublevel" class="modal-sublevel"></div>
          </div>
        </div>
        <script type="text/javascript" src="res/reports.js"></script>
      </body>
    </html>
