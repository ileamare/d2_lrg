<?php
require_once("rg_report_out_settings.php");
require_once("modules/functions/versions.php");
require_once("modules/functions/locale_strings.php");
require_once("modules/functions/get_language_code_iso6391.php");

$lg_version = array( 1, 3, 0, 0, 0 );

$visjs_settings = "physics:{
  barnesHut:{
    avoidOverlap:1,
    centralGravity:0.3,
    springLength:95,
    springConstant:0.005,
    gravitationalConstant:-900
  },
  timestep: 0.1,
}, nodes: {
   borderWidth:3,
   shape: 'dot',
   font: {color:'#ccc', background: 'rgba(0,0,0,0.5)',size:12},
   shadow: {
     enabled: true
   },
   scaling:{
     label: {
       min:8, max:20
     }
   }
 }";

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
    if($pid)
        return $report['players'][$pid];
    return "null";
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
                  "<div class=\"player-info-line\"><span class=\"caption\">".locale_string("matches").":</span> ".$pinfo['matches']." (".
                    $pinfo['won']." - ".($pinfo['matches'] - $pinfo['won']).")</div>".
                  "<div class=\"player-info-line\"><span class=\"caption\">".locale_string("winrate").":</span> ".number_format($pinfo['won']*100/$pinfo['matches'], 2)."%</div>".
                  "<div class=\"player-info-line\"><span class=\"caption\">".locale_string("gpm").":</span> ".number_format($pinfo['gpm'],1)."</div>".
                  "<div class=\"player-info-line\"><span class=\"caption\">".locale_string("xpm").":</span> ".number_format($pinfo['xpm'],1)."</div>".
                  "<div class=\"player-info-line\"><span class=\"caption\">".locale_string("hero_pool").":</span> ".$pinfo['hero_pool_size']."</div></div>";

    # heroes
    $output .= "<div class=\"player-heroes\"><div class=\"section-caption\">".locale_string("heroes")."</div><div class=\"section-lines\">";
    foreach($pinfo['heroes'] as $hero) {
      $output .= "<div class=\"player-info-line\"><span class=\"caption\">".hero_full($hero['heroid']).":</span> ";
      $output .= $hero['matches']." - ".number_format($hero['wins']*100/$hero['matches'], 2)."%</div>";
    }
    $output .= "</div></div>";

    # positions
    $output .= "<div class=\"player-positions\"><div class=\"section-caption\">".locale_string("player_positions")."</div><div class=\"section-lines\">";
    foreach($pinfo['positions'] as $position) {
      $output .= "<div class=\"player-info-line\"><span class=\"caption\">".($position['core'] ? locale_string("core")." " : locale_string("support")).
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

  function team_tag($tid) {
    global $report;
    if($tid && isset($report['teams'][ $tid ]['tag']))
      return $report['teams'][ $tid ]['tag'];
    return "";
  }

  function team_link($tid) {
    global $leaguetag;
    global $linkvars;

    return "<a href=\"?league=".$leaguetag."&mod=teams-team_".$tid."_stats".(empty($linkvars) ? "" : "&$linkvars")
      ."\" title=\"".team_name($tid)."\">".team_name($tid)." (".team_tag($tid).")</a>";
  }

  function team_card($tid) {
    global $report;
    global $meta;
    global $strings;
    global $leaguetag;
    global $linkvars;

    if(!isset($report['teams'])) return null;

    $output = "<div class=\"team-card\"><div class=\"team-name\">".
              "<a href=\"?league=".$leaguetag."&mod=teams-team_".$tid."_stats".
              (empty($linkvars) ? "" : "&$linkvars")
              ."\" title=\"".team_name($tid)."\">".team_name($tid)." (".$tid.")</a></div>";

    $output .= "<div class=\"team-info-block\">".
                  "<div class=\"section-caption\">".locale_string("summary").":</div>".
                  "<div class=\"team-info-line\"><span class=\"caption\">".locale_string("matches").":</span> ".$report['teams'][$tid]['matches_total']."</div>".
                  "<div class=\"team-info-line\"><span class=\"caption\">".locale_string("winrate").":</span> ".
                      number_format($report['teams'][$tid]['wins']*100/$report['teams'][$tid]['matches_total'])."%</div>".
                  "<div class=\"team-info-line\"><span class=\"caption\">".locale_string("gpm").":</span> ".number_format($report['teams'][$tid]['averages']['gpm'])."</div>".
                  "<div class=\"team-info-line\"><span class=\"caption\">".locale_string("xpm").":</span> ".number_format($report['teams'][$tid]['averages']['xpm'])."</div>".
                  "<div class=\"team-info-line\"><span class=\"caption\">".locale_string("kda").":</span> ".number_format($report['teams'][$tid]['averages']['kills']).
                    "/".number_format($report['teams'][$tid]['averages']['deaths'])."/".number_format($report['teams'][$tid]['averages']['assists'])."</div></div>";

    $output .= "<div class=\"team-info-block\">".
                  "<div class=\"section-caption\">".locale_string("active_roster").":</div>";
    foreach($report['teams'][$tid]['active_roster'] as $player) {
      if (!isset($report['players'][$player])) continue;
      $position = reset($report['players_additional'][$player]['positions']);
      $output .= "<div class=\"team-info-line\">".player_name($player)." (".($position['core'] ? locale_string("core")." " : locale_string("support")).
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
                    "<div class=\"section-caption\">".locale_string("top_pick_heroes").":</div>";
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

  function match_card($mid) {
    global $report;
    global $meta;
    global $strings;
    global $linkvars;
    global $leaguetag;
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
      if(isset($report['match_participants_teams'][$mid]['radiant']) &&
         isset($report['teams'][ $report['match_participants_teams'][$mid]['radiant'] ]['name']))
        $team_radiant = "<a href=\"?league=".$leaguetag."&mod=teams-team_".$report['match_participants_teams'][$mid]['radiant']."_stats".
          (empty($linkvars) ? "" : "&$linkvars")
          ."\" title=\"".$report['teams'][ $report['match_participants_teams'][$mid]['radiant'] ]['name']."\">".
          $report['teams'][ $report['match_participants_teams'][$mid]['radiant'] ]['name'].
          " (".$report['teams'][ $report['match_participants_teams'][$mid]['radiant'] ]['tag'].")</a>";
      else $team_radiant = "Radiant";
      if(isset($report['match_participants_teams'][$mid]['dire']) &&
         isset($report['teams'][ $report['match_participants_teams'][$mid]['dire'] ]['name']))
        $team_dire = "<a href=\"?league=".$leaguetag."&mod=teams-team_".$report['match_participants_teams'][$mid]['dire']."_stats".
          (empty($linkvars) ? "" : "&$linkvars")
          ."\" title=\"".$report['teams'][ $report['match_participants_teams'][$mid]['dire'] ]['name']."\">".
          $report['teams'][ $report['match_participants_teams'][$mid]['dire'] ]['name'].
          " (".$report['teams'][ $report['match_participants_teams'][$mid]['dire'] ]['tag'].")</a>";
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
                  <div class=\"match-info-line\"><span class=\"caption\">".locale_string("duration").":</span> ".
                    $duration."</div>
                  <div class=\"match-info-line\"><span class=\"caption\">".locale_string("region").":</span> ".
                    $meta['regions'][
                      $meta['clusters'][ $report['matches_additional'][$mid]['cluster'] ]
                    ]."</div>
                  <div class=\"match-info-line\"><span class=\"caption\">".locale_string("game_mode").":</span> ".
                    $meta['modes'][$report['matches_additional'][$mid]['game_mode']]."</div>
                    <div class=\"match-info-line\"><span class=\"caption\">".locale_string("winner").":</span> ".
                      ($report['matches_additional'][$mid]['radiant_win'] ? $team_radiant : $team_dire)."</div>
                    <div class=\"match-info-line\"><span class=\"caption\">".locale_string("date").":</span> ".
                      date(locale_string("time_format")." ".locale_string("date_format"), $report['matches_additional'][$mid]['date'])."</div>
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
            $selectors[] = "<span class=\"selector active\">".locale_string($modname)."</span>";
          else
            $selectors[] = "<span class=\"selector".($unset_selector ? " active" : "").
                              "\"><a href=\"?league=".$leaguetag."&mod=".(empty($parent) ? "" : $parent."-" ).$modname.
                              (empty($linkvars) ? "" : "&".$linkvars).
                              "\">".locale_string($modname)."</a></span>";
        } else {
          $selectors[] = "<span class=\"mod-".$level_codes[$level][1]."-selector selector".
                              ($first ? " active" : "")."\" onclick=\"switchTab(event, 'module-".(empty($parent) ? "" : $parent."-" ).$modname."', 'mod-".$level_codes[$level][1]."');\">".locale_string($modname)."</span>";
        }
      } else {
        if($lrg_use_get && $lrg_get_depth > $level) {
          if (stripos($mod, (empty($parent) ? "" : $parent."-" ).$modname) === 0)
            $selectors[] = "<option selected=\"selected\" value=\"?league=".$leaguetag."&mod=".(empty($parent) ? "" : $parent."-" ).$modname."&".
            (empty($linkvars) ? "" : "&".$linkvars)
            ."\">".locale_string($modname)."</option>";
          else
            $selectors[] = "<option".($unset_selector ? "selected=\"selected\"" : "")." value=\"?league=".$leaguetag."&mod=".(empty($parent) ? "" : $parent."-" ).$modname.(empty($linkvars) ? "" : "&".$linkvars)
            ."\">".locale_string($modname)."</option>";
        } else {
          $selectors[] = "<option value=\"module-".(empty($parent) ? "" : $parent."-" ).$modname."\">".locale_string($modname)."</option>";
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
    $locale = GetLanguageCodeISO6391();

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
  else $locale = "en";

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

    # legacy name for Radiant Winrate
    if (compare_ver($report['ana_version'], array(1,1,1,-4,0)) < 0) {
        $strings[$locale]['rad_wr'] = $strings[$locale]['radiant_wr'];
    }

    $modules = array();
    # module => array or ""
    $modules['overview'] = "";
    if (isset($report['records']))
      require_once("modules/view/mod.view.records.php");
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

    if(isset($report['versions'])) {
        foreach($report['versions'] as $k => $v) {
            $mode = (int)($k/100);
            if(!isset($meta['versions'][$mode])) {
                for($i = $mode; $i > 0; $i--) {
                    if(isset($meta['versions'][$i])) {
                        break;
                    }
                }
                $diff = $mode - $i;
                $parent_patch = explode(".", $meta['versions'][$i]);
                $parent_patch[1] = (int)$parent_patch[1] + $diff;
                if ($parent_patch[1] < 10)
                    $parent_patch[1] = "0".$parent_patch[1];
                $meta['versions'][$mode] = implode(".", $parent_patch);

                unset($diff);
                unset($parent_patch);
            }
        }
    }

  # overview
  if ( check_module("overview") ) {
    $modules['overview'] .= "<div class=\"content-text overview overview-head\">";
    $modules['overview'] .= "<div class=\"content-header\">".locale_string("summary")."</div><div class=\"block-content\">";
    $modules['overview'] .= locale_string("over-pregen-report");
    if ($report['league_id'] == null || $report['league_id'] == "custom")
      $modules['overview'] .= " ".locale_string("over-custom-league")." ".$report['league_name']." — ".$report['league_desc'].".";
    else
      $modules['overview'] .= " ".$report['league_name']." (".$report['league_id'].") — ".$report['league_desc'].".";

    $modules['overview'] .= "</div><div class=\"block-content\">";

    $modules['overview'] .= locale_string("over-matches", ["num" => $report['random']['matches_total'] ] )." ";
    if(isset($report['teams']))
      $modules['overview'] .= locale_string("over-teams", ["num" => $report['random']['teams_on_event'] ] )." ";
    else $modules['overview'] .= locale_string("over-players", ["num" => $report['random']['players_on_event'] ] )." ";

    $modules['overview'] .= "</div><div class=\"block-content\">";

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
        $modules['overview'] .= locale_string("over-one-version", ["ver"=>$ver])." ";
      else $modules['overview'] .= locale_string("over-most-version", ["num" => $mode, "ver" => $ver])." ";

      unset($ver);
    }

    if($report['settings']['overview_modes']) {
      $mode = reset($report['modes']);
      if ($mode/$report['random']['matches_total'] > 0.99)
        $modules['overview'] .= locale_string("over-one-mode", ["gm" => $meta['modes'][ key($report['modes']) ] ])." ";
      else $modules['overview'] .= locale_string("over-most-mode", ["num" => $mode, "gm"=> $meta['modes'][ key($report['modes']) ] ])." ";
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
        $modules['overview'] .= locale_string("over-one-region", [ "server" => key($regions_matches)] )." ";
      else
        $modules['overview'] .= locale_string("over-most-region", ["num"=>$mode, "server"=>key($regions_matches) ] )." ";
    }

    $modules['overview'] .= "</div>";

    if($report['settings']['overview_time_limits']) {
      $modules['overview'] .= "<div class=\"block-content\">";

      $modules['overview'] .= locale_string("over-first-match", ["date"=> date(locale_string("time_format")." ".locale_string("date_format"), $report['first_match']['date']) ])."<br />";
      $modules['overview'] .= locale_string("over-last-match", ["date"=> date(locale_string("time_format")." ".locale_string("date_format"), $report['last_match']['date']) ])."<br />";

      $modules['overview'] .= "</div>";
    }

    if($report['settings']['overview_last_match_winners']) {
      $modules['overview'] .= "<div class=\"block-content\">";

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

      $modules['overview'] .= locale_string("over-last-match-winner", ["team"=>$mode])."</div>";
    }

    $modules['overview'] .= "</div>";


    if($report['settings']['overview_charts']) {
      $use_graphjs = true;

      $modules['overview'] .= "<div class=\"content-text overview overview-graphs\">";

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
                                  labels: [ '".locale_string("radiant")."','".locale_string("dire")."' ],
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
                                  labels: [ '".locale_string("heroes_contested")."','".locale_string("heroes_uncontested")."' ],
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
        $modules['overview'] .= "<h1>".locale_string("matches_per_day")."</h1>".
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
      $modules['overview'] .= "</div>";
    }

    if($report['settings']['overview_random_stats']) {
      $modules['overview'] .= "<div class=\"content-header\">".locale_string("random")."</div>";
      $modules['overview'] .= "<table class=\"list\" id=\"overview-table\">";
      foreach($report['random'] as $key => $value) {
        $modules['overview'] .= "<tr><td>".locale_string($key)."</td><td>".$value."</td></tr>";
      }
      $modules['overview'] .= "</table>";
    }

    if(isset($report['players_additional']) || isset($report["teams"])) {
      $modules['overview'] .= "<div class=\"content-header\">".locale_string("notable_paricipans")."</div>";
      $modules['overview'] .= "<div class=\"content-cards\">";

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
            $modules['overview'] .= "<h1>".locale_string("np_winner")."</h1>";
            $modules['overview'] .= team_card($tid);
        }
        unset($tid);
      }

      $modules['overview'] .= "</div><table class=\"list\">";
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

        $modules['overview'] .= "<tr><td>".locale_string("most_matches")."</td><td>".
            team_link($max_matches)."</td><td>".$report['teams'][$max_matches]['matches_total']."</td></tr>";

        if($max_wr)
          $modules['overview'] .= "<tr><td>".locale_string("highest_winrate")."</td><td>".
            team_link($max_wr)."</td><td>".number_format($report['teams'][$max_wr]['wins']*100/$report['teams'][$max_wr]['matches_total'],2)."%</td></tr>";

        if (isset($report['records'])) {
          $modules['overview'] .= "<tr><td>".locale_string("widest_hero_pool_team")."</td><td>".
              team_link($report['records']['widest_hero_pool_team']['playerid'])."</td><td>".
              $report['records']['widest_hero_pool_team']['value']."</td></tr>";

          $modules['overview'] .= "<tr><td>".locale_string("smallest_hero_pool_team")."</td><td>".
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

        $modules['overview'] .= "<tr><td>".locale_string("most_matches")."</td><td>".
          player_name($max_matches)."</td><td>".$report['players_additional'][$max_matches]['matches']."</td></tr>";

        if($max_wr)
          $modules['overview'] .= "<tr><td>".locale_string("highest_winrate")."</td><td>".
              player_name($max_wr)."</td><td>".
              number_format($report['players_additional'][$max_wr]['won']*100/$report['players_additional'][$max_wr]['matches'],2)."%</td></tr>";
      }
        if (isset($report['records'])) {
          $modules['overview'] .= "<tr><td>".locale_string("widest_hero_pool")."</td><td>".
            player_name($report['records']['widest_hero_pool']['playerid'])."</td><td>".$report['records']['widest_hero_pool']['value']."</td></tr>";
          $modules['overview'] .= "<tr><td>".locale_string("smallest_hero_pool")."</td><td>".
            player_name($report['records']['smallest_hero_pool']['playerid'])."</td><td>".$report['records']['smallest_hero_pool']['value']."</td></tr>";
        }

        if (isset($report['averages_players'])) {
          $modules['overview'] .= "<tr><td>".locale_string("diversity")."</td><td>".
            player_name($report['averages_players']['diversity'][0]['playerid'])."</td><td>".
            number_format($report['averages_players']['diversity'][0]['value']*100,2)."%</td></tr>";
        }

      $modules['overview'] .= "</table>";

      $modules['overview'] .= "<div class=\"content-text\"><a href=\"http://".
          $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."&mod=participants\">".locale_string("full_participants").
          "</a> / <a href=\"http://".
          $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."&mod=records\">".locale_string("full_records").
          "</a></div>";
      $modules['overview'] .= "</div>";
    }

    if (isset($report['records']) && isset($report['settings']['overview_include_records']) && $report['settings']['overview_include_records']) {
      $modules['overview'] .= "<div class=\"content-header\">".locale_string("records")."</div>";
      $modules['overview'] .= rg_view_generate_records($report);
    }

    $modules['overview'] .= "<div class=\"content-header\">".locale_string("draft")."</div>";

    if($report['settings']['overview_top_contested'] && isset($report['pickban'])) {
        $modules['overview'] .=  "<table id=\"over-heroes-pickban\" class=\"list\"><caption>".locale_string("top_contested_heroes")."</caption>
                                              <tr class=\"thead\">
                                                <th>".locale_string("hero")."</th>
                                                <th>".locale_string("matches_total")."</th>
                                                <th>".locale_string("matches_picked")."</th>
                                                <th>".locale_string("winrate_picked")."</th>
                                                <th>".locale_string("matches_banned")."</th>
                                                <th>".locale_string("winrate_banned")."</th>
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

    $modules['overview'] .= "<div class=\"small-list-wrapper\">";
    if($report['settings']['overview_top_picked']) {
        $modules['overview'] .=  "<table id=\"over-heroes-pick\" class=\"list list-small\"><caption>".locale_string("top_picked_heroes")."</caption>
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
          $modules['overview'] .=  "<tr>
                                      <td>".($hid ? hero_full($hid) : "").
                                     "</td>
                                      <td>".$hero['matches_total']."</td>
                                      <td>".$hero['matches_picked']."</td>
                                      <td>".number_format($hero['winrate_picked']*100,2)."%</td>
                                    </tr>";
          $counter--;
        }
        unset($workspace);
        $modules['overview'] .= "</table>";
    }

    if($report['settings']['overview_top_bans']) {
        $modules['overview'] .=  "<table id=\"over-heroes-ban\" class=\"list list-small\"><caption>".locale_string("top_banned_heroes")."</caption>
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
    $modules['overview'] .= "</div>";

    if($report['settings']['overview_top_draft']) {
      $modules['overview'] .= "<div class=\"small-list-wrapper\">";

      for ($i=0; $i<2; $i++) {
        for ($j=1; $j<4; $j++) {
          if($report['settings']["overview_draft_".$i."_".$j] && isset($report['draft']) && !empty($report['draft'][$i][$j])) {

              $modules['overview'] .=  "<table id=\"over-draft-$i-$j\" class=\"list list-small\">
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

      $modules['overview'] .= "</div>";
    }

    if($report['settings']['overview_top_hero_pairs'] && isset($report['hero_pairs']) && !empty($report['hero_pairs'])) {
        $modules['overview'] .= "<table id=\"over-hero-pairs\" class=\"list\">
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
          $modules['overview'] .= "<tr>
                                    <td>".($pair['heroid1'] ? hero_full($pair['heroid1']) : "").
                                   "</td><td>".($pair['heroid2'] ? hero_full($pair['heroid2'])  : "").
                                   "</td>
                                   <td>".$pair['matches']."</td>
                                   <td>".number_format($pair['winrate']*100,2)."%</td>
                                  </tr>";
          $counter--;
        }
        $modules['overview'] .= "</table>";
    }

    $modules['overview'] .= "<div class=\"content-text\"><a href=\"http://".
        $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."&mod=heroes-draft\">".locale_string("full_draft").
        "</a> / <a href=\"http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."&mod=heroes-hero_combo_graph\">".
        locale_string("hero_combo_graph")."</a></div>";

    if(!isset($report['teams']) && $report['settings']['overview_top_player_pairs'] && isset($report['player_pairs']) && !empty($report['player_pairs'])) {
        $modules['overview'] .= "<div class=\"content-header\">".locale_string("top_player_pairs")."</div>";

        $modules['overview'] .= "<table id=\"over-player-pairs\" class=\"list\">
                                  <tr class=\"thead\">
                                    <th>".locale_string("player")." 1</th>
                                    <th>".locale_string("player")." 2</th>
                                    <th>".locale_string("matches")."</th>
                                    <th>".locale_string("winrate")."</th>
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

        $modules['overview'] .= "<div class=\"content-text\"><a href=\"http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."&mod=players-player_combos\">".locale_string("full_player_combos")."</a></div>";
    }


    if($report['settings']['overview_matches']) {
      $modules['overview'] .= "<div class=\"content-header\">".locale_string("notable_matches")."</div>";
      $modules['overview'] .= "<div class=\"content-cards\">";
      if($report['settings']['overview_first_match'])
        $modules['overview'] .= "<h1>".locale_string("first_match")."</h1>".match_card($report['first_match']['mid']);
      if($report['settings']['overview_last_match'])
        $modules['overview'] .= "<h1>".locale_string("last_match")."</h1>".match_card($report['last_match']['mid']);
      if($report['settings']['overview_records_stomp'])
        $modules['overview'] .= "<h1>".locale_string("match_stomp")."</h1>".match_card($report['records']['stomp']['matchid']);
      if($report['settings']['overview_records_comeback'])
        $modules['overview'] .= "<h1>".locale_string("match_comeback")."</h1>".match_card($report['records']['comeback']['matchid']);
      if($report['settings']['overview_records_duration']) {
        if (compare_ver($report['ana_version'], array(1,0,4,-4,1)) < 0)
          $modules['overview'] .= "<h1>".locale_string("longest_match")."</h1>".match_card($report['records']['duration']['matchid']);
        else {
          $modules['overview'] .= "<h1>".locale_string("longest_match")."</h1>".match_card($report['records']['longest_match']['matchid']);
          $modules['overview'] .= "<h1>".locale_string("shortest_match")."</h1>".match_card($report['records']['shortest_match']['matchid']);
        }
      }

      $modules['overview'] .= "<div class=\"content-text\"><a href=\"http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."&mod=matches\">".locale_string("full_matches")."</a></div>";

      $modules['overview'] .= "</div>";
    }

    $modules['overview'] .= "<div class=\"content-text\">".locale_string("desc_overview")."</div>";
    $modules['overview'] .= "<div class=\"content-text small\">".
      locale_string("limiter_h").": ".$report['settings']['limiter']."<br />".
      locale_string("limiter_l").": ".$report['settings']['limiter_triplets']."<br />".
      (compare_ver($report['ana_version'], array(1,1,0,-3,5)) >= 0 ?
        locale_string("limiter_gr").": ".$report['settings']['limiter_combograph']."<br />"
        : "").
      locale_string("ana_version").": ".parse_ver($report['ana_version'])."</div>";
  }

  # records
  # should I include this part into module later on?..
  if (isset($modules['records']) && check_module("records")) {
    $modules['records'] .= rg_view_generate_records($report);
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
                                                      <caption>".locale_string($key)."</caption>
                                                      <tr class=\"thead\">
                                                        <th>".locale_string("hero")."</th>
                                                        <th>".locale_string("value")."</th>
                                                      </tr>";
          foreach($avg as $hero) {
            $modules['heroes']['averages_heroes'] .= "<tr>
                                                        <td>".($hero['heroid'] ? hero_full($hero['heroid']) : "").
                                                       "</td><td>".number_format($hero['value'],2)."</td></tr>";
          }
          $modules['heroes']['averages_heroes'] .= "</table>";
        }
        $modules['heroes']['averages_heroes'] .= "</div>";
        $modules['heroes']['averages_heroes'] .= "<div class=\"content-text\">".locale_string("desc_heroes_avg", ["lim" => $report['settings']['limiter']+1 ])."</div>";
      }
    }
    if (isset($report['pickban'])) {
      $modules['heroes']['pickban'] = "";

      if (check_module($parent."pickban")) {
        $heroes = $meta['heroes'];

        uasort($report['pickban'], function($a, $b) {
          if($a['matches_total'] == $b['matches_total']) return 0;
          else return ($a['matches_total'] < $b['matches_total']) ? 1 : -1;
        });

        $modules['heroes']['pickban'] .=  "<table id=\"heroes-pickban\" class=\"list\">
                                              <tr class=\"thead\">
                                                <th onclick=\"sortTable(0,'heroes-pickban');\">".locale_string("hero")."</th>
                                                <th onclick=\"sortTableNum(1,'heroes-pickban');\">".locale_string("matches_total")."</th>
                                                <th  class=\"separator\"onclick=\"sortTableNum(2,'heroes-pickban');\">".locale_string("contest_rate")."</th>
                                                <th onclick=\"sortTableNum(3,'heroes-pickban');\">".locale_string("outcome_impact")."</th>
                                                <th class=\"separator\" onclick=\"sortTableNum(4,'heroes-pickban');\">".locale_string("matches_picked")."</th>
                                                <th onclick=\"sortTableNum(5,'heroes-pickban');\">".locale_string("winrate")."</th>
                                                <th class=\"separator\" onclick=\"sortTableNum(6,'heroes-pickban');\">".locale_string("matches_banned")."</th>
                                                <th onclick=\"sortTableNum(7,'heroes-pickban');\">".locale_string("winrate")."</th>
                                              </tr>";
        foreach($report['pickban'] as $hid => $hero) {
          unset($heroes[$hid]);
          $oi = ($hero['matches_picked']*$hero['winrate_picked'] + $hero['matches_banned']*$hero['winrate_banned'])/$report["random"]["matches_total"];
          $modules['heroes']['pickban'] .=  "<tr>
                                                <td>".($hid ? hero_full($hid) : "")."</td>
                                                <td>".$hero['matches_total']."</td>
                                                <td class=\"separator\">".number_format($hero['matches_total']/$report["random"]["matches_total"]*100,2)."%</td>
                                                <td>".number_format($oi*100,2)."%</td>
                                                <td class=\"separator\">".$hero['matches_picked']."</td>
                                                <td>".number_format($hero['winrate_picked']*100,2)."%</td>
                                                <td class=\"separator\">".$hero['matches_banned']."</td>
                                                <td>".number_format($hero['winrate_banned']*100,2)."%</td>
                                              </tr>";
        }
        unset($oi);
        $modules['heroes']['pickban'] .= "</table>";

        if(sizeof($heroes)) {
          $modules['heroes']['pickban'] .= "<div class=\"content-text\"><h1>".locale_string("heroes_uncontested").": ".sizeof($heroes)."</h1><div class=\"hero-list\">";

          foreach($heroes as $hero) {
            $modules['heroes']['pickban'] .= "<div class=\"hero\"><img src=\"res/heroes/".$hero['tag'].
                ".png\" alt=\"".$hero['tag']."\" /><span class=\"hero_name\">".
                $hero['name']."</span></div>";
          }
          $modules['heroes']['pickban'] .= "</div></div>";
        }
        $modules['heroes']['pickban'] .= "<div class=\"content-text\">".locale_string("desc_heroes_pickban")."</div>";
      }
    }
    if (isset($report['draft'])) {
      $modules['heroes']['draft'] = "";

      if (check_module($parent."draft")) {
        $draft = array();

        for ($i=0; $i<2; $i++) {
          $type = $i ? "pick" : "ban";
          $max_stage = 1;
          if(!isset($report['draft'][$i])) continue;
          foreach($report['draft'][$i] as $stage_num => $stage) {
            if ($stage_num > $max_stage) $max_stage = $stage_num;
            foreach($stage as $hero) {
              if(!isset($draft[ $hero['heroid'] ])) {
                if($stage_num > 1) {
                  for($j=1; $j<$stage_num; $j++) {
                    $draft[ $hero['heroid'] ][$j] = array ("pick" => 0, "pick_wr" => 0, "ban" => 0, "ban_wr" => 0 );
                  }
                }
              }

              if(!isset($draft[ $hero['heroid'] ][$stage_num]))
                $draft[ $hero['heroid'] ][$stage_num] = array ("pick" => 0, "pick_wr" => 0, "ban" => 0, "ban_wr" => 0 );
              $draft[ $hero['heroid'] ][$stage_num][$type] = $hero['matches'];
              $draft[ $hero['heroid'] ][$stage_num][$type."_wr"] = $hero['winrate'];
            }
          }
        }

        foreach ($draft as $hid => $stages) {
          $heroline = "";

          $stages_passed = 0;
          foreach($stages as $stage) {
            if($max_stage > 1) {
              $heroline .= "<td class=\"separator\">".number_format(($stage['pick']*$stage['pick_wr']+$stage['ban']*$stage['ban_wr'])/$report['random']['matches_total']*100,2)."%</td>";
              if($stage['pick'])
                $heroline .= "<td>".$stage['pick']."</td><td>".number_format($stage['pick_wr']*100, 2)."%</td>";
              else
                $heroline .= "<td>-</td><td>-</td>";

              if($stage['ban'])
                $heroline .= "<td>".$stage['ban']."</td><td>".number_format($stage['ban_wr']*100, 2)."%</td>";
              else
                $heroline .= "<td>-</td><td>-</td>";
            }

            $stages_passed++;
          }

          if($stages_passed < $max_stage) {
            for ($i=$stages_passed; $i<$max_stage; $i++)
              $heroline .= "<td class=\"separator\">-</td><td>-</td><td>-</td><td>-</td><td>-</td>";
          }

          $draft[$hid] = array ("out" => "", "matches" => $report['pickban'][$hid]['matches_total']);
          $draft[$hid]['out'] .= "<td>".hero_full($hid)."</td>";

          $draft[$hid]['out'] .= "<td>".$report['pickban'][$hid]['matches_total']."</td>";
          $draft[$hid]['out'] .= "<td>".number_format(
            ($report['pickban'][$hid]['matches_picked']*$report['pickban'][$hid]['winrate_picked'] +
              $report['pickban'][$hid]['matches_banned']*$report['pickban'][$hid]['winrate_banned'])
              /$report['random']['matches_total']*100, 2)."%</td>";

          if($report['pickban'][$hid]['matches_picked'])
            $draft[$hid]['out'] .= "<td>".$report['pickban'][$hid]['matches_picked']."</td><td>".number_format($report['pickban'][$hid]['winrate_picked']*100, 2)."%</td>";
          else
            $draft[$hid]['out'] .= "<td>-</td><td>-</td>";

          if($report['pickban'][$hid]['matches_banned'])
            $draft[$hid]['out'] .= "<td>".$report['pickban'][$hid]['matches_banned']."</td><td>".number_format($report['pickban'][$hid]['winrate_banned']*100, 2)."%</td>";
          else
            $draft[$hid]['out'] .= "<td>-</td><td>-</td>";

          $draft[$hid]['out'] .= $heroline."</tr>";
        }


        uasort($draft, function($a, $b) {
          if($a['matches'] == $b['matches']) return 0;
          else return ($a['matches'] < $b['matches']) ? 1 : -1;
        });

        $modules['heroes']['draft'] .= "<table id=\"heroes-draft\" class=\"list wide\"><tr class=\"thead overhead\"><th width=\"15%\"></th><th colspan=\"6\">".locale_string("total")."</th>";
        $heroline = "<tr class=\"thead\">".
                      "<th onclick=\"sortTable(0,'heroes-draft');\">".locale_string("hero")."</th>".
                      "<th onclick=\"sortTableNum(1,'heroes-draft');\">".locale_string("matches_s")."</th>".
                      "<th onclick=\"sortTableNum(2,'heroes-draft');\">".locale_string("outcome_impact_s")."</th>".
                      "<th onclick=\"sortTableNum(3,'heroes-draft');\">".locale_string("picks_s")."</th>".
                      "<th onclick=\"sortTableNum(4,'heroes-draft');\">".locale_string("winrate_s")."</th>".
                      "<th onclick=\"sortTableNum(5,'heroes-draft');\">".locale_string("bans_s")."</th>".
                      "<th onclick=\"sortTableNum(6,'heroes-draft');\">".locale_string("winrate_s")."</th>";

        if($max_stage > 1)
          for($i=1; $i<=$max_stage; $i++) {
            $modules['heroes']['draft'] .= "<th class=\"separator\" colspan=\"5\">".locale_string("stage")." $i</th>";
            $heroline .= "<th onclick=\"sortTableNum(".(1+5*$i+1).",'heroes-draft');\" class=\"separator\">".locale_string("outcome_impact_s")."</th>".
                        "<th onclick=\"sortTableNum(".(1+5*$i+2).",'heroes-draft');\">".locale_string("picks_s")."</th>".
                        "<th onclick=\"sortTableNum(".(1+5*$i+3).",'heroes-draft');\">".locale_string("winrate_s")."</th>".
                        "<th onclick=\"sortTableNum(".(1+5*$i+4).",'heroes-draft');\">".locale_string("bans_s")."</th>".
                        "<th onclick=\"sortTableNum(".(1+5*$i+5).",'heroes-draft');\">".locale_string("winrate_s")."</th>";
          }
        $modules['heroes']['draft'] .= "</tr>".$heroline."</tr>";

        unset($heroline);

        foreach($draft as $hero)
          $modules['heroes']['draft'] .= $hero['out'];

        $modules['heroes']['draft'] .= "</table>";
        unset($draft);
      }
    }
    if (isset($report['hero_positions'])) {
      $modules['heroes']['hero_positions'] = array();

      if(check_module($parent."hero_positions")) {
        if($mod == $parent."hero_positions") $unset_module = true;

        $position_overview_template = array("total" => 0);
        for ($i=1; $i>=0 && !isset($keys); $i--) {
          for ($j=1; $j<6 && $j>0; $j++) {
            if (!$i) { $j = 0; }
            if(isset($report['hero_positions'][$i][$j][0])) {
              $keys = array_keys($report['hero_positions'][$i][$j][0]);
              break;
            }
            if (!$i) { break; }
          }
        }

        for ($i=1; $i>=0; $i--) {
          for ($j=1; $j<6 && $j>0; $j++) {
            if (!$i) { $j = 0; }
            if(sizeof($report['hero_positions'][$i][$j]))
              $position_overview_template["$i.$j"] = array("matches" => 0, "wr" => 0);

            if(!isset($strings['en']["positions_$i.$j"]))
              $strings['en']["positions_$i.$j"] = ($i ? locale_string("core") : locale_string("support"))." ".$meta['lanes'][$j];

            if (!$i) { break; }
          }
        }

        $modules['heroes']['hero_positions']['overview'] = "";
        if (check_module($parent."hero_positions-"."overview")) {
          $overview = array();

          for ($i=1; $i>=0; $i--) {
            for ($j=1; $j<6 && $j>0; $j++) {
              if (!$i) { $j = 0; }

              foreach($report['hero_positions'][$i][$j] as $hero) {
                if (!isset($overview[ $hero['heroid'] ])) $overview[ $hero['heroid'] ] = $position_overview_template;

                $overview[ $hero['heroid'] ]["$i.$j"]['matches'] = $hero['matches_s'];
                $overview[ $hero['heroid'] ]["$i.$j"]['wr'] = $hero['winrate_s'];
                $overview[ $hero['heroid'] ]["total"] += $hero['matches_s'];
              }

              if (!$i) { break; }
            }
          }
          ksort($overview);

          $modules['heroes']['hero_positions']['overview'] .= "<table id=\"heroes-positions-overview\" class=\"list wide\"><tr class=\"thead overhead\"><th width=\"20%\" colspan=\"2\"></th>";

          $heroline = "<tr class=\"thead\"><th onclick=\"sortTable(0,'heroes-positions-overview');\">".locale_string("hero")."</th>".
                        "<th onclick=\"sortTableNum(1,'heroes-positions-overview');\">".locale_string("matches_s")."</th>";
          $i = 2;
          foreach($position_overview_template as $k => $v) {
            if ($k == "total") continue;

            $modules['heroes']['hero_positions']['overview'] .= "<th colspan=\"3\" class=\"separator\">".locale_string("positions_$k")."</th>";
            $heroline .= "<th onclick=\"sortTableNum(".($i++).",'heroes-positions-overview');\"  class=\"separator\">".locale_string("matches_s")."</th>".
                          "<th onclick=\"sortTableNum(".($i++).",'heroes-positions-overview');\">".locale_string("ratio")."</th>".
                          "<th onclick=\"sortTableNum(".($i++).",'heroes-positions-overview');\">".locale_string("winrate_s")."</th>";
          }
          $modules['heroes']['hero_positions']['overview'] .= "</tr>".$heroline."</tr>";

          foreach ($overview as $hid => $hero) {
            $modules['heroes']['hero_positions']['overview'] .= "<tr><td>".hero_full($hid)."</td><td>".$hero['total']."</td>";
            foreach($hero as $v) {
              if (!is_array($v)) continue;

              if(!$v['matches']) {
                $modules['heroes']['hero_positions']['overview'] .= "<td class=\"separator\">-</td>".
                              "<td>-</td>".
                              "<td>-</th>";
              } else {
                $modules['heroes']['hero_positions']['overview'] .= "<td class=\"separator\">".$v['matches']."</td>".
                            "<td>".number_format($v['matches']*100/$hero['total'],2)."%</td>".
                            "<td>".number_format($v['wr']*100,2)."%</th>";
              }
            }
            $modules['heroes']['hero_positions']['overview'] .= "</tr>";
          }
          $modules['heroes']['hero_positions']['overview'] .= "</table>";

          unset($overview);
          unset($heroline);
          unset($position_overview_template);
        }

        for ($i=1; $i>=0; $i--) {
          for ($j=1; $j<6 && $j>0; $j++) {
            if (!$i) { $j = 0; }

            if(sizeof($report['hero_positions'][$i][$j])) {
              $modules['heroes']['hero_positions']["positions_$i.$j"]  = "";
              if (!check_module($parent."hero_positions-"."positions_$i.$j")) { if (!$i) { break; } continue; }

              $modules['heroes']['hero_positions']["positions_$i.$j"] .= "<table id=\"heroes-positions-$i-$j\" class=\"list wide\">
                                                <tr class=\"thead\">
                                                  <th onclick=\"sortTable(0,'heroes-positions-$i-$j');\">".locale_string("hero")."</th>";
              for($k=1, $end=sizeof($keys); $k < $end; $k++) {
                $modules['heroes']['hero_positions']["positions_$i.$j"] .= "<th onclick=\"sortTableNum($k,'heroes-positions-$i-$j');\">".locale_string($keys[$k])."</th>";
              }
              $modules['heroes']['hero_positions']["positions_$i.$j"] .= "</tr>";

              uasort($report['hero_positions'][$i][$j], function($a, $b) {
                if($a['matches_s'] == $b['matches_s']) return 0;
                else return ($a['matches_s'] < $b['matches_s']) ? 1 : -1;
              });

              foreach($report['hero_positions'][$i][$j] as $hero) {

                $modules['heroes']['hero_positions']["positions_$i.$j"] .= "<tr".(isset($report['hero_positions_matches']) ?
                                                                  " onclick=\"showModal('".htmlspecialchars(join_matches($report['hero_positions_matches'][$i][$j][$hero['heroid']])).
                                                                          "', '".$meta['heroes'][ $hero['heroid'] ]['name']." - ".
                                                                          locale_string("positions_$i.$j")." - ".locale_string("matches")."');\"" : "").">
                                                    <td>".($hero['heroid'] ? hero_full($hero['heroid']) : "").
                                                   "</td>
                                                    <td>".$hero['matches_s']."</td>
                                                    <td>".number_format($hero['winrate_s']*100,1)."%</td>";
                for($k=3, $end=sizeof($keys); $k < $end; $k++) {
                  $modules['heroes']['hero_positions']["positions_$i.$j"] .= "<td>".number_format($hero[$keys[$k]],1)."</td>";
                }
                $modules['heroes']['hero_positions']["positions_$i.$j"] .= "</tr>";
              }
              $modules['heroes']['hero_positions']["positions_$i.$j"] .= "</table>";

              $modules['heroes']['hero_positions']["positions_$i.$j"] .= "<div class=\"content-text\">".locale_string("desc_heroes_positions")."</div>";
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

          $modules['heroes']['hero_sides']['overview'] .= "<table id=\"hero-sides-overview\" class=\"list\">
                                <tr class=\"thead overhead\">
                                  <th width=\"25%\"></th>
                                  <th width=\"10%\"></th>
                                  <th width=\"15%\"></th>
                                  <th class=\"separator\" colspan=\"2\">".locale_string("radiant")."</th>
                                  <th class=\"separator\" colspan=\"2\">".locale_string("dire")."</th>
                                </tr>
                                <tr class=\"thead\">
                                  <th onclick=\"sortTable(0,'hero-sides-overview');\">".locale_string("hero")."</th>
                                  <th onclick=\"sortTableNum(1,'hero-sides-overview');\">".locale_string("matches")."</th>
                                  <th onclick=\"sortTableNum(2,'hero-sides-overview');\">".locale_string("rad_ratio")."</th>
                                  <th class=\"separator\" onclick=\"sortTableNum(3,'hero-sides-overview');\">".locale_string("matches")."</th>
                                  <th onclick=\"sortTableNum(4,'hero-sides-overview');\">".locale_string("winrate")."</th>
                                  <th class=\"separator\" onclick=\"sortTableNum(5,'hero-sides-overview');\">".locale_string("matches")."</th>
                                  <th onclick=\"sortTableNum(6,'hero-sides-overview');\">".locale_string("winrate")."</th>
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
                                                "<td class=\"separator\">".$hero["side0matches"]."</td>".
                                                "<td>".number_format($hero["side0winrate"]*100,2)."%</td>".
                                                "<td class=\"separator\">".$hero["side1matches"]."</td>".
                                                "<td>".number_format($hero["side1winrate"]*100,2)."%</td>".
                                              "</tr>";
          }
          $modules['heroes']['hero_sides']['overview'] .= "</table>";

          $modules['heroes']['hero_sides']["overview"] .= "<div class=\"content-text\">".locale_string("desc_heroes_sides")."</div>";
          unset($heroes);
        }

        for ($side = 0; $side < 2; $side++) {
          $modules['heroes']['hero_sides'][$side ? 'dire' : 'radiant'] = "";
          if(!check_module($parent."hero_sides-".($side ? 'dire' : 'radiant'))) continue;

          $modules['heroes']['hero_sides'][$side ? 'dire' : 'radiant'] .= "<table id=\"hero-sides-".$side."\" class=\"list\">
                                        <tr class=\"thead\">
                                          <th onclick=\"sortTable(0,'hero-sides-$side');\">".locale_string("hero")."</th>";
          for($k=1, $end=sizeof($keys); $k < $end; $k++) {
            $modules['heroes']['hero_sides'][$side ? 'dire' : 'radiant'] .= "<th onclick=\"sortTableNum($k,'hero-sides-$side');\">".locale_string($keys[$k])."</th>";
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

          $modules['heroes']['hero_sides'][$side ? 'dire' : 'radiant'] .= "<div class=\"content-text\">".locale_string("desc_heroes_sides")."</div>";
        }
        unset($keys);
      }
    }
    if (isset($report['hero_combos_graph']) && $report['settings']['heroes_combo_graph']) {
      $modules['heroes']['hero_combo_graph'] = "";

      if (check_module($parent."hero_combo_graph") && isset($report['pickban'])) {
        $locale_settings = ["lim" =>
            (compare_ver($report['ana_version'], array(1,1,0,-3,5)) >= 0 ?
                $report['settings']['limiter_combograph']+1
                : $report['settings']['limiter']+1),
            "per" => "35%"
        ];

        $modules['heroes']['hero_combo_graph'] .= "<div class=\"content-text\">".locale_string("desc_meta_graph", $locale_settings)."</div>";

        $modules['heroes']['hero_combo_graph'] .= "<div class=\"content-text\">".
          locale_string("desc_meta_graph_add", $locale_settings)."</div>";

        unset($locale_settings);

        if(isset($report['hero_combos_graph'])) {
          $use_visjs = true;

          $modules['heroes']['hero_combo_graph'] .= "<div id=\"hero-combos-graph\" class=\"graph\"></div><script type=\"text/javascript\">";

          $nodes = "";

          $counter = 0; $endp = sizeof($report['pickban'])*0.35;

          uasort($report['pickban'], function($a, $b) {
            if($a['matches_total'] == $b['matches_total']) return 0;
            else return ($a['matches_total'] < $b['matches_total']) ? 1 : -1;
          });

          foreach($report['pickban'] as $hid => $hero) {
            if($counter++ >= $endp && !has_pair($hid, $report['hero_combos_graph'])) {
              //if($counter < $endp) $counter++;
              //else
              //if($hero['matches_picked'] < $report['settings']['limiter_combograph']*2)
                continue;
            }
            //if(!isset($report['pickban'][$hid])) continue;
            $nodes .= "{id: $hid, value: ".$hero['matches_total'].
              ", label: '".addslashes($meta['heroes'][$hid]['name'])."'".
              ", title: '".addslashes($meta['heroes'][$hid]['name']).", ".
              $hero['matches_total']." ".locale_string("total").", ".
              $hero['matches_picked']." ".locale_string("matches_picked").", ".
              number_format($hero['winrate_picked']*100, 1)." ".locale_string("winrate_picked")."'".
              ", shape:'circularImage', image: 'res/heroes/".$meta['heroes'][$hid]['tag'].".png'".
              ", color:{ border:'rgba(".number_format(255-255*$hero['winrate_picked'], 0).",124,".
              number_format(255*$hero['winrate_picked'], 0).")' }},";
          }
          $modules['heroes']['hero_combo_graph'] .= "var nodes = [".$nodes."];";

          $nodes = "";
          foreach($report['hero_combos_graph'] as $combo) {
            $nodes .= "{from: ".$combo['heroid1'].", to: ".$combo['heroid2'].", value:".$combo['matches'].", title:\"".$combo['matches']."\", color:{color:'rgba(".
              number_format(255-255*$combo['wins']/$combo['matches'], 0).",124,".
              number_format(255*$combo['wins']/$combo['matches'],0).",1)'}},";
          }

          $modules['heroes']['hero_combo_graph'] .= "var edges = [".$nodes."];";

          $modules['heroes']['hero_combo_graph'] .= "var container = document.getElementById('hero-combos-graph');\n".
                                                      "var data = { nodes: nodes, edges: edges};\n".
                                                      "var options={
                                                        $visjs_settings
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
                                                <caption>".locale_string("hero_pairs")."</caption>
                                                <tr class=\"thead\">
                                                  <th onclick=\"sortTable(0,'hero-pairs');\">".locale_string("hero")." 1</th>
                                                  <th onclick=\"sortTable(1,'hero-pairs');\">".locale_string("hero")." 2</th>
                                                  <th onclick=\"sortTableNum(2,'hero-pairs');\">".locale_string("matches")."</th>
                                                  <th onclick=\"sortTableNum(3,'hero-pairs');\">".locale_string("winrate")."</th>".
                                                  /*<th onclick=\"sortTableNum(4,'hero-pairs');\">".locale_string("pair_percentage")."</th>
                                                  <th onclick=\"sortTableNum(5,'hero-pairs');\">".locale_string("expectation")."</th>*/
                                                "</tr>";
          foreach($report['hero_pairs'] as $pair) {
            $modules['heroes']['hero_combos'] .= "<tr".(isset($report['hero_pairs_matches']) ?
                                                " onclick=\"showModal('".htmlspecialchars(join_matches($report['hero_pairs_matches'][$pair['heroid1'].'-'.$pair['heroid2']])).
                                                                      "', '".locale_string("matches")."');\"" : "").">
                                                  <td>".($pair['heroid1'] ? hero_full($pair['heroid1']) : "").
                                                 "</td><td>".($pair['heroid2'] ? hero_full($pair['heroid2'])  : "").
                                                 "</td>
                                                 <td>".$pair['matches']."</td>
                                                 <td>".number_format($pair['winrate']*100,2)."%</td>".
                                                 /*<td>".$pair['percentage']."</td>
                                                 <td>".$pair['expectation']."</td>*/
                                                "</tr>";
          }
          $modules['heroes']['hero_combos'] .= "</table>";
        }

        if (!empty($report['hero_triplets'])) {

          $modules['heroes']['hero_combos'] .= "<table id=\"hero-triplets\" class=\"list\">
                                                <caption>".locale_string("hero_triplets")."</caption>
                                                <tr class=\"thead\">
                                                  <th onclick=\"sortTable(0,'hero-triplets');\">".locale_string("hero")." 1</th>
                                                  <th onclick=\"sortTable(1,'hero-triplets');\">".locale_string("hero")." 2</th>
                                                  <th onclick=\"sortTable(2,'hero-triplets');\">".locale_string("hero")." 3</th>
                                                  <th onclick=\"sortTableNum(3,'hero-triplets');\">".locale_string("matches")."</th>
                                                  <th onclick=\"sortTableNum(4,'hero-triplets');\">".locale_string("winrate")."</th>
                                                </tr>";
          foreach($report['hero_triplets'] as $pair) {
            $modules['heroes']['hero_combos'] .= "<tr".(isset($report['hero_pairs_matches']) ?
                                                " onclick=\"showModal('".
                                                implode($report['hero_pairs_matches'][$pair['heroid1'].'-'.$pair['heroid2'].'-'.$pair['heroid3']], ", ").
                                                                      "', '".locale_string("matches")."');\"" : "").">
                                                  <td>".($pair['heroid1'] ? hero_full($pair['heroid1']) : "").
                                                 "</td><td>".($pair['heroid2'] ? hero_full($pair['heroid2']) : "").
                                                 "</td><td>".($pair['heroid3'] ? hero_full($pair['heroid3']) : "").
                                                 "</td>
                                                 <td>".$pair['matches']."</td>
                                                 <td>".number_format($pair['winrate']*100,2)."</td>
                                                </tr>";
          }
          $modules['heroes']['hero_combos'] .= "</table>";

          $modules['heroes']['hero_combos'] .= "<div class=\"content-text\">".locale_string("desc_heroes_combos", [ "limh"=>$report['settings']['limiter']+1, "liml"=>$report['settings']['limiter_triplets']+1 ] )."</div>";
        }
      }
    }
    if (isset($report['hvh'])) {
      $pvp = array();
      $modules['heroes']['hvh'] = array();

      if (check_module($parent."hvh")) {
        if($mod == $parent."hvh") $unset_module = true;

        $hvh = array();

        foreach($report['hvh'] as $line) {
          if( !isset($hvh[ $line['heroid1'] ]) )
            $hvh[ $line['heroid1'] ] = array();
          if( !isset($hvh[ $line['heroid2'] ]) )
            $hvh[ $line['heroid2'] ] = array();

          $hvh[ $line['heroid1'] ][ $line['heroid2'] ] = array(
            "winrate" => $line['h1winrate'],
            "matches" => $line['matches'],
            "won" => $line['h1won'],
            "lost" => $line['matches']-$line['h1won']
          );

          $hvh[ $line['heroid2'] ][ $line['heroid1'] ] = array(
            "winrate" => 1-$line['h1winrate'],
            "matches" => $line['matches'],
            "won" => $line['matches']-$line['h1won'],
            "lost" => $line['h1won']
          );
        }

        foreach($hvh as $hid => $opponents) {
          $strings['en']['hid'.$hid."id"] = $meta['heroes'][$hid]['name'];

          $modules['heroes']['hvh']['hid'.$hid."id"] = "";
          if(!check_module($parent."hvh-hid".$hid."id")) continue;

          $modules['heroes']['hvh']['hid'.$hid."id"] .= "<div class=\"content-text\">".locale_string("desc_heroes_hvh")."</div>";

          $modules['heroes']['hvh']['hid'.$hid."id"] = "<table id=\"hero-hvh-$hid\" class=\"list\">";

          $modules['heroes']['hvh']['hid'.$hid."id"] .= "<tr class=\"thead\">
                                                        <th onclick=\"sortTable(0,'hero-hvh-$hid');\">".locale_string("opponent")."</th>
                                                        <th onclick=\"sortTableNum(1,'hero-hvh-$hid');\">".locale_string("winrate")."</th>
                                                        <th onclick=\"sortTableNum(2,'hero-hvh-$hid');\">".locale_string("matches")."</th>
                                                        <th onclick=\"sortTableNum(3,'hero-hvh-$hid');\">".locale_string("won")."</th>
                                                        <th onclick=\"sortTableNum(4,'hero-hvh-$hid');\">".locale_string("lost")."</th>
                                                     </tr>";

          uasort($opponents, function($a, $b) {
            if($a['matches'] == $b['matches']) return 0;
            else return ($a['matches'] < $b['matches']) ? 1 : -1;
          });

          foreach($opponents as $hid_op => $data) {
            $modules['heroes']['hvh']['hid'.$hid."id"] .= "<tr ".(isset($data['matchids']) ?
                                                              "onclick=\"showModal('".implode($data['matchids'], ", ")."','".locale_string("matches")."')\"" :
                                                              "").">
                                                          <td>".hero_full($hid_op)."</th>
                                                          <td>".number_format($data['winrate']*100,2)."%</th>
                                                          <td>".$data['matches']."</th>
                                                          <td>".$data['won']."</th>
                                                          <td>".$data['lost']."</th>
                                                       </tr>";
          }

          $modules['heroes']['hvh']['hid'.$hid."id"] .= "</table>";
        }

        unset($hvh);
      }
    }
    if (isset($report['hero_summary'])) {
      $modules['heroes']['summary']  = "";
      if(check_module($parent."summary")) {
        $keys = array_keys($report['hero_summary'][0]);
        $modules['heroes']['summary'] .= "<table id=\"heroes-summary\" class=\"list wide\">
                                          <tr class=\"thead\">
                                            <th onclick=\"sortTable(0,'heroes-summary');\">".locale_string("hero")."</th>";
        for($k=1, $end=sizeof($keys); $k < $end; $k++) {
          $modules['heroes']['summary'] .= "<th onclick=\"sortTableNum($k,'heroes-summary');\">".locale_string($keys[$k])."</th>";
        }
        $modules['heroes']['summary'] .= "</tr>";

        foreach($report['hero_summary'] as $hero) {

          $modules['heroes']['summary'] .= "<tr>
                                              <td>".hero_full($hero['heroid'])."</td>
                                              <td>".$hero['matches_s']."</td>
                                              <td>".number_format($hero['winrate_s']*100,1)."%</td>";
          for($k=3, $end=sizeof($keys); $k < $end; $k++) {
            if ($hero[$keys[$k]] > 1)
              $modules['heroes']['summary'] .= "<td>".number_format($hero[$keys[$k]],1)."</td>";
            else $modules['heroes']['summary'] .= "<td>".number_format($hero[$keys[$k]],3)."</td>";
          }
          $modules['heroes']['summary'] .= "</tr>";
        }
        $modules['heroes']['summary'] .= "</table>";

        $modules['heroes']['summary'] .= "<div class=\"content-text\">".locale_string("desc_heroes_summary")."</div>";
        unset($keys);
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
                                                      <caption>".locale_string($key)."</caption>
                                                      <tr class=\"thead\">
                                                        <th>".locale_string("player")."</th>
                                                        <th>".locale_string("value")."</th>
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
        $modules['players']['averages_players'] .= "<div class=\"content-text\">".locale_string("desc_players_avg")."</div>";
      }
    }
    if (isset($report['players_summary'])) {

      $modules['players']['summary']  = "";
      if(check_module($parent."summary")) {
        $keys = array_keys($report['players_summary'][0]);
        $modules['players']['summary'] .= "<table id=\"players-summary\" class=\"list wide\">
                                          <tr class=\"thead\">
                                            <th onclick=\"sortTable(0,'players-summary');\">".locale_string("player")."</th>";
        for($k=1, $end=sizeof($keys); $k < $end; $k++) {
          $modules['players']['summary'] .= "<th onclick=\"sortTableNum($k,'players-summary');\">".locale_string($keys[$k])."</th>";
        }
        $modules['players']['summary'] .= "<th onclick=\"sortTableNum($k,'players-summary');\">".locale_string("common_position")."</th>";
        $modules['players']['summary'] .= "</tr>";

        foreach($report['players_summary'] as $player) {

          $modules['players']['summary'] .= "<tr>
                                              <td>".player_name($player['playerid'])."</td>
                                              <td>".$player['matches_s']."</td>
                                              <td>".number_format($player['winrate_s']*100,1)."%</td>";
          for($k=3, $end=sizeof($keys); $k < $end; $k++) {
            if ($player[$keys[$k]] > 1)
              $modules['players']['summary'] .= "<td>".number_format($player[$keys[$k]],1)."</td>";
            else $modules['players']['summary'] .= "<td>".number_format($player[$keys[$k]],3)."</td>";
          }
          $position = reset($report['players_additional'][$player['playerid']]['positions']);
          $modules['players']['summary'] .= "<td>".($position['core'] ? locale_string("core")." " : locale_string("support")).
                        $meta['lanes'][ $position['lane'] ]."</td>";
          $modules['players']['summary'] .= "</tr>";
        }
        $modules['players']['summary'] .= "</table>";

        $modules['players']['summary'] .= "<div class=\"content-text\">".locale_string("desc_players_summary")."</div>";
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
                          )." onclick=\"showModal('".locale_string("matches").": ".$pvp[$pid][$player_ids[$i]]['matches']
                                ."<br />".locale_string("winrate").": ".number_format($pvp[$pid][$player_ids[$i]]['winrate']*100,2)
                                ."%<br />".locale_string("won")." ".$pvp[$pid][$player_ids[$i]]['won']." - "
                                         .locale_string("lost")." ".$pvp[$pid][$player_ids[$i]]['lost'].(
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

          $modules['players']['pvp']['grid'] .= "<div class=\"content-text\">".locale_string("desc_players_pvp_grid")."</div>";
        }



        foreach($pvp as $pid => $playerline) {
          $strings['en']['pid'.$pid."id"] = $report['players'][$pid];

          $modules['players']['pvp']['pid'.$pid."id"] = "";
          if(!check_module($parent."pvp-pid".$pid."id")) continue;

          $modules['players']['pvp']['pid'.$pid."id"] = "<table id=\"player-pvp-$pid\" class=\"list\">";

          $modules['players']['pvp']['pid'.$pid."id"] .= "<tr class=\"thead\">
                                                        <th onclick=\"sortTable(0,'player-pvp-$pid');\">".locale_string("opponent")."</th>
                                                        <th onclick=\"sortTableNum(1,'player-pvp-$pid');\">".locale_string("winrate")."</th>
                                                        <th onclick=\"sortTableNum(2,'player-pvp-$pid');\">".locale_string("matches")."</th>
                                                        <th onclick=\"sortTableNum(3,'player-pvp-$pid');\">".locale_string("won")."</th>
                                                        <th onclick=\"sortTableNum(4,'player-pvp-$pid');\">".locale_string("lost")."</th>
                                                     </tr>";
          for($i=0, $end = sizeof($player_ids); $i<$end; $i++) {
            if($player_ids[$i] == $pid || $pvp[$pid][$player_ids[$i]]['matches'] == 0) {
              continue;
            } else {
              $modules['players']['pvp']['pid'.$pid."id"] .= "<tr ".(isset($pvp[$pid][$player_ids[$i]]['matchids']) ?
                                                                "onclick=\"showModal('".implode($pvp[$pid][$player_ids[$i]]['matchids'], ", ")."','".locale_string("matches")."')\"" :
                                                                "").">
                                                            <td>".$report['players'][$player_ids[$i]]."</th>
                                                            <td>".number_format($pvp[$pid][$player_ids[$i]]['winrate']*100,2)."</th>
                                                            <td>".$pvp[$pid][$player_ids[$i]]['matches']."</th>
                                                            <td>".$pvp[$pid][$player_ids[$i]]['won']."</th>
                                                            <td>".$pvp[$pid][$player_ids[$i]]['lost']."</th>
                                                         </tr>";
            }
          }
          $modules['players']['pvp']['pid'.$pid."id"] .= "</table>";

          $modules['players']['pvp']['pid'.$pid."id"] .= "<div class=\"content-text\">".locale_string("desc_players_pvp")."</div>";
        }
        unset($pvp);
      }
    }
    if (isset($report['players_combo_graph']) && $report['settings']['players_combo_graph'] && isset($report)) {
      $modules['players']['players_combo_graph'] = "";

      if (check_module($parent."players_combo_graph")) {
        $modules['players']['players_combo_graph'] .= "<div class=\"content-text\">".locale_string("desc_players_combo_graph")."</div>";
        if(isset($report['players_combo_graph'])) {
          $use_visjs = true;

          $modules['players']['players_combo_graph'] .= "<div id=\"players-combos-graph\" class=\"graph\"></div><script type=\"text/javascript\">";

          $nodes = "";
          foreach($report['players'] as $pid => $player) {
            if (!has_pair($pid, $report['players_combo_graph'])) continue;
            $wr = $report['players_additional'][$pid]['won'] / $report['players_additional'][$pid]['matches'];
            $wr = 0.5;
            $nodes .= "{id: $pid, value: ".$report['players_additional'][$pid]['matches'].", label: '".addslashes($player)."'".
              ", color:{ border:'rgba(".number_format(255-255*$wr, 0).",124,".
              number_format(255*$wr, 0).")' }},";
          }
          $modules['players']['players_combo_graph'] .= "var nodes = [".$nodes."];";

          $nodes = "";
          foreach($report['players_combo_graph'] as $combo) {
            $nodes .= "{from: ".$combo['playerid1'].", to: ".$combo['playerid2'].", value:".$combo['matches'].", title:\"".$combo['matches']."\", color:{color:'rgba(".
              number_format(255-255*$combo['wins']/$combo['matches'], 0).",124,".
              number_format(255*$combo['wins']/$combo['matches'],0).",1)'}},";
          }

          $modules['players']['players_combo_graph'] .= "var edges = [".$nodes."];";

          $modules['players']['players_combo_graph'] .= "var container = document.getElementById('players-combos-graph');\n".
                                                      "var data = { nodes: nodes, edges: edges};\n".
                                                      "var data = { nodes: nodes, edges: edges};\n".
                                                      "var options={
                                                        $visjs_settings
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
                                                <caption>".locale_string("player_pairs")."</caption>
                                                <tr class=\"thead\">
                                                  <th onclick=\"sortTable(0,'player-pairs');\">".locale_string("player")." 1</th>
                                                  <th onclick=\"sortTable(1,'player-pairs');\">".locale_string("player")." 2</th>
                                                  <th onclick=\"sortTableNum(2,'player-pairs');\">".locale_string("matches")."</th>
                                                  <th onclick=\"sortTableNum(3,'player-pairs');\">".locale_string("winrate")."</th>
                                                </tr>";
          foreach($report['player_pairs'] as $pair) {
            $modules['players']['player_combos'] .= "<tr".(isset($report['player_pairs_matches']) ?
                            " onclick=\"showModal('".implode($report['player_pairs_matches'][$pair['playerid1'].'-'.$pair['playerid2']], ", ").
                                  "', '".locale_string("matches")."');\"" : "").">
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
                                                <caption>".locale_string("player_triplets")."</caption>
                                                <tr class=\"thead\">
                                                  <th onclick=\"sortTable(0,'player-triplets');\">".locale_string("player")." 1</th>
                                                  <th onclick=\"sortTable(1,'player-triplets');\">".locale_string("player")." 2</th>
                                                  <th onclick=\"sortTable(2,'player-triplets');\">".locale_string("player")." 3</th>
                                                  <th onclick=\"sortTableNum(3,'player-triplets');\">".locale_string("matches")."</th>
                                                  <th onclick=\"sortTableNum(4,'player-triplets');\">".locale_string("winrate")."</th>
                                                </tr>";
          foreach($report['player_triplets'] as $pair) {
            $modules['players']['player_combos'] .= "<tr".(isset($report['player_triplets_matches']) ?
                            " onclick=\"showModal('".implode($report['player_triplets_matches'][$pair['playerid1'].'-'.$pair['playerid2'].'-'.$pair['playerid3']], ", ").
                                  "', '".locale_string("matches")."');\"" : "").">
                                                  <td>".$report['players'][ $pair['playerid1'] ]."</td>
                                                  <td>".$report['players'][ $pair['playerid2'] ]."</td>
                                                  <td>".$report['players'][ $pair['playerid3'] ]."</td>
                                                 <td>".$pair['matches']."</td>
                                                 <td>".number_format($pair['winrate']*100,2)."</td>
                                                </tr>";
          }
          $modules['players']['player_combos'] .= "</table>";

          $modules['players']['player_combos'] .= "<div class=\"content-text\">".locale_string("desc_players_combos")."</div>";
        }
      }
    }
    if (isset($report['player_positions'])) {
      $modules['players']['player_positions'] = array();

      if(check_module($parent."player_positions")) {
        if($mod == $parent."player_positions") $unset_module = true;

        $position_overview_template = array("total" => 0);
        for ($i=1; $i>=0 && !isset($keys); $i--) {
          for ($j=1; $j<6 && $j>0; $j++) {
            if (!$i) { $j = 0; }
            if(isset($report['player_positions'][$i][$j][0])) {
              $keys = array_keys($report['player_positions'][$i][$j][0]);
              break;
            }
            if (!$i) { break; }
          }
        }

        for ($i=1; $i>=0; $i--) {
          for ($j=1; $j<6 && $j>0; $j++) {
            if (!$i) { $j = 0; }
            if(sizeof($report['player_positions'][$i][$j]))
              $position_overview_template["$i.$j"] = array("matches" => 0, "wr" => 0);

            if(!isset($strings['en']["positions_$i.$j"]))
              $strings['en']["positions_$i.$j"] = ($i ? locale_string("core") : locale_string("support"))." ".$meta['lanes'][$j];

            if (!$i) { break; }
          }
        }

        $modules['players']['player_positions']['overview'] = "";
        if (check_module($parent."player_positions-overview")) {
          $overview = array();

          for ($i=1; $i>=0; $i--) {
            for ($j=1; $j<6 && $j>0; $j++) {
              if (!$i) { $j = 0; }

              foreach($report['player_positions'][$i][$j] as $player) {
                if (!isset($overview[ $player['playerid'] ])) $overview[ $player['playerid'] ] = $position_overview_template;

                $overview[ $player['playerid'] ]["$i.$j"]['matches'] = $player['matches_s'];
                $overview[ $player['playerid'] ]["$i.$j"]['wr'] = $player['winrate_s'];
                $overview[ $player['playerid'] ]["total"] += $player['matches_s'];
              }

              if (!$i) { break; }
            }
          }
          ksort($overview);

          $modules['players']['player_positions']['overview'] .= "<table id=\"players-positions-overview\" class=\"list wide\"><tr class=\"thead overhead\"><th width=\"20%\" colspan=\"2\"></th>";

          $heroline = "<tr class=\"thead\"><th onclick=\"sortTable(0,'players-positions-overview');\">".locale_string("player")."</th>".
                        "<th onclick=\"sortTableNum(1,'players-positions-overview');\">".locale_string("matches_s")."</th>";
          $i = 2;
          foreach($position_overview_template as $k => $v) {
            if ($k == "total") continue;

            $modules['players']['player_positions']['overview'] .= "<th colspan=\"3\" class=\"separator\">".locale_string("positions_$k")."</th>";
            $heroline .= "<th onclick=\"sortTableNum(".($i++).",'players-positions-overview');\"  class=\"separator\">".locale_string("matches_s")."</th>".
                          "<th onclick=\"sortTableNum(".($i++).",'players-positions-overview');\">".locale_string("ratio")."</th>".
                          "<th onclick=\"sortTableNum(".($i++).",'players-positions-overview');\">".locale_string("winrate_s")."</th>";
          }
          $modules['players']['player_positions']['overview'] .= "</tr>".$heroline."</tr>";

          foreach ($overview as $pid => $player) {
            $modules['players']['player_positions']['overview'] .= "<tr><td>".player_name($pid)."</td><td>".$player['total']."</td>";
            foreach($player as $v) {
              if (!is_array($v)) continue;

              if(!$v['matches']) {
                $modules['players']['player_positions']['overview'] .= "<td class=\"separator\">-</td>".
                              "<td>-</td>".
                              "<td>-</th>";
              } else {
                $modules['players']['player_positions']['overview'] .= "<td class=\"separator\">".$v['matches']."</td>".
                            "<td>".number_format($v['matches']*100/$player['total'],2)."%</td>".
                            "<td>".number_format($v['wr']*100,2)."%</th>";
              }
            }
            $modules['players']['player_positions']['overview'] .= "</tr>";
          }
          $modules['players']['player_positions']['overview'] .= "</table>";

          unset($overview);
          unset($position_overview_template);
          unset($heroline);
        }

        for ($i=1; $i>=0; $i--) {
          for ($j=1; $j<6 && $j>0; $j++) {
            if (!$i) { $j = 0; }

            if(!isset($strings['en']["positions_$i.$j"]))
              $strings['en']["positions_$i.$j"] = ($i ? locale_string("core") : locale_string("support"))." ".$meta['lanes'][$j];

            if(sizeof($report['player_positions'][$i][$j])) {
              $modules['players']['player_positions']["positions_$i.$j"]  = "";
              if (!check_module($parent."player_positions-"."positions_$i.$j")) { if (!$i) { break; } continue; }

              $modules['players']['player_positions']["positions_$i.$j"] .= "<table id=\"players-positions-$i-$j\" class=\"list wide\">
                                                <tr class=\"thead\">
                                                  <th onclick=\"sortTable(0,'players-positions-$i-$j');\">".locale_string("player")."</th>";
              for($k=1, $end=sizeof($keys); $k < $end; $k++) {
                $modules['players']['player_positions']["positions_$i.$j"] .= "<th onclick=\"sortTableNum($k,'players-positions-$i-$j');\">".locale_string($keys[$k])."</th>";
              }
              $modules['players']['player_positions']["positions_$i.$j"] .= "</tr>";


              foreach($report['player_positions'][$i][$j] as $player) {

                $modules['players']['player_positions']["positions_$i.$j"] .= "<tr".(isset($report['player_positions_matches']) ?
                                                    " onclick=\"showModal('".htmlspecialchars(join_matches($report['player_positions_matches'][$i][$j][$player['playerid']])).
                                                                          "', '".$report['players'][$player['playerid']]." - ".
                                                                          locale_string("positions_$i.$j")." - ".locale_string("matches")."');\"" : "").">
                                                    <td>".$report['players'][$player['playerid']]."</td>
                                                    <td>".$player['matches_s']."</td>
                                                    <td>".number_format($player['winrate_s']*100,1)."%</td>";
                for($k=3, $end=sizeof($keys); $k < $end; $k++) {
                  if ($player[$keys[$k]] > 1)
                    $modules['players']['player_positions']["positions_$i.$j"] .= "<td>".number_format($player[$keys[$k]],1)."</td>";
                  else $modules['players']['player_positions']["positions_$i.$j"] .= "<td>".number_format($player[$keys[$k]],2)."</td>";
                }
                $modules['players']['player_positions']["positions_$i.$j"] .= "</tr>";
              }
              $modules['players']['player_positions']["positions_$i.$j"] .= "</table>";

              $modules['players']['player_positions']["positions_$i.$j"] .= "<div class=\"content-text\">".locale_string("desc_players_positions")."</div>";
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
      $strings['en']["team_".$tid."_stats"] = $team['name'];

      if(check_module($parent."team_".$tid."_stats")) {
        if($mod == $parent."team_".$tid."_stats") $unset_module = true;

        if (isset($report['teams'][$tid]['averages'])) {
          $modules['teams']["team_".$tid."_stats"]['overview'] = "";

          if(check_module($parent."team_".$tid."_stats-overview")) {
            $modules['teams']["team_".$tid."_stats"]['overview'] .= "<div class=\"content-cards\">".team_card($tid)."</div>";
            $modules['teams']["team_".$tid."_stats"]['overview'] .= "<table id=\"teams-$tid-avg-table\" class=\"list\"> ";

            foreach ($report['teams'][$tid]['averages'] as $key => $value) {
              $modules['teams']["team_".$tid."_stats"]['overview'] .= "<tr><td>".locale_string( $key )."</td><td>".number_format($value, 2)."</td></tr>";
            }

            $modules['teams']["team_".$tid."_stats"]['overview'] .= "</table>";

            $modules['teams']["team_".$tid."_stats"]['overview'] .= "<div class=\"content-text\">".locale_string("desc_teams")."</div>";
          }
        }
        if (isset($report['teams'][$tid]['draft'])) {
          $modules['teams']["team_".$tid."_stats"]['draft'] = "";

          if(check_module($parent."team_".$tid."_stats-draft")) {
            $draft = array();

            for ($i=0; $i<2; $i++) {
              $type = $i ? "pick" : "ban";
              $max_stage = 1;
              if(!isset($report['teams'][$tid]['draft'][$i])) continue;
              foreach($report['teams'][$tid]['draft'][$i] as $stage_num => $stage) {
                if ($stage_num > $max_stage) $max_stage = $stage_num;
                foreach($stage as $hero) {
                  if(!isset($draft[ $hero['heroid'] ])) {
                    if($stage_num > 1) {
                      for($j=1; $j<$stage_num; $j++) {
                        $draft[ $hero['heroid'] ][$j] = array ("pick" => 0, "pick_wr" => 0, "ban" => 0, "ban_wr" => 0 );
                      }
                    }
                  }

                  if(!isset($draft[ $hero['heroid'] ][$stage_num]))
                    $draft[ $hero['heroid'] ][$stage_num] = array ("pick" => 0, "pick_wr" => 0, "ban" => 0, "ban_wr" => 0 );
                  $draft[ $hero['heroid'] ][$stage_num][$type] = $hero['matches'];
                  $draft[ $hero['heroid'] ][$stage_num][$type."_wr"] = $hero['winrate'];
                }
              }
            }

            foreach ($draft as $hid => $stages) {
              $heroline = "";

              $stages_passed = 0;
              foreach($stages as $stage) {
                if($max_stage > 1) {
                  if($stage['pick'])
                    $heroline .= "<td class=\"separator\">".$stage['pick']."</td><td>".number_format($stage['pick_wr']*100, 2)."%</td>";
                  else
                    $heroline .= "<td class=\"separator\">-</td><td>-</td>";

                  if($stage['ban'])
                    $heroline .= "<td>".$stage['ban']."</td><td>".number_format($stage['ban_wr']*100, 2)."%</td>";
                  else
                    $heroline .= "<td>-</td><td>-</td>";
                }

                $stages_passed++;
              }

              if($stages_passed < $max_stage) {
                for ($i=$stages_passed; $i<$max_stage; $i++)
                  $heroline .= "<td class=\"separator\">-</td><td>-</td><td>-</td><td>-</td>";
              }

              $draft[$hid] = array ("out" => "", "matches" => $report['teams'][$tid]['pickban'][$hid]['matches_total']);
              $draft[$hid]['out'] .= "<td>".hero_full($hid)."</td>";

              $draft[$hid]['out'] .= "<td>".$report['teams'][$tid]['pickban'][$hid]['matches_total']."</td>";

              if(isset($report['teams'][$tid]['pickban'][$hid]['matches_picked']) && $report['teams'][$tid]['pickban'][$hid]['matches_picked'])
                $draft[$hid]['out'] .= "<td>".$report['teams'][$tid]['pickban'][$hid]['matches_picked']."</td><td>".
                  number_format($report['teams'][$tid]['pickban'][$hid]['wins_picked']*100/$report['teams'][$tid]['pickban'][$hid]['matches_picked'], 2)."%</td>";
              else
                $draft[$hid]['out'] .= "<td>-</td><td>-</td>";

              if(isset($report['teams'][$tid]['pickban'][$hid]['matches_banned']) && $report['teams'][$tid]['pickban'][$hid]['matches_banned'])
                $draft[$hid]['out'] .= "<td>".$report['teams'][$tid]['pickban'][$hid]['matches_banned']."</td><td>".
                    number_format($report['teams'][$tid]['pickban'][$hid]['wins_banned']*100/$report['teams'][$tid]['pickban'][$hid]['matches_banned'], 2)."%</td>";
              else
                $draft[$hid]['out'] .= "<td>-</td><td>-</td>";

              $draft[$hid]['out'] .= $heroline."</tr>";
            }


            uasort($draft, function($a, $b) {
              if($a['matches'] == $b['matches']) return 0;
              else return ($a['matches'] < $b['matches']) ? 1 : -1;
            });

            $modules['teams']["team_".$tid."_stats"]['draft'] .= "<table id=\"heroes-draft-team-$tid\" class=\"list wide\"><tr class=\"thead overhead\"><th width=\"15%\"></th><th colspan=\"5\">".locale_string("total")."</th>";
            $heroline = "<tr class=\"thead\">".
                          "<th onclick=\"sortTable(0,'heroes-draft-team-$tid');\">".locale_string("hero")."</th>".
                          "<th onclick=\"sortTableNum(1,'heroes-draft-team-$tid');\">".locale_string("matches")."</th>".
                          "<th onclick=\"sortTableNum(2,'heroes-draft-team-$tid');\">".locale_string("picks")."</th>".
                          "<th onclick=\"sortTableNum(3,'heroes-draft-team-$tid');\">".locale_string("winrate")."</th>".
                          "<th onclick=\"sortTableNum(4,'heroes-draft-team-$tid');\">".locale_string("bans")."</th>".
                          "<th onclick=\"sortTableNum(5,'heroes-draft-team-$tid');\">".locale_string("winrate")."</th>";

            if($max_stage > 1)
              for($i=1; $i<=$max_stage; $i++) {
                $modules['teams']["team_".$tid."_stats"]['draft'] .= "<th class=\"separator\" colspan=\"4\">".locale_string("stage")." $i</th>";
                $heroline .= "<th onclick=\"sortTableNum(".(1+4*$i+1).",'heroes-draft-team-$tid');\" class=\"separator\">".locale_string("picks")."</th>".
                            "<th onclick=\"sortTableNum(".(1+4*$i+2).",'heroes-draft-team-$tid');\">".locale_string("winrate")."</th>".
                            "<th onclick=\"sortTableNum(".(1+4*$i+3).",'heroes-draft-team-$tid');\">".locale_string("bans")."</th>".
                            "<th onclick=\"sortTableNum(".(1+4*$i+4).",'heroes-draft-team-$tid');\">".locale_string("winrate")."</th>";
              }
            $modules['teams']["team_".$tid."_stats"]['draft'] .= "</tr>".$heroline."</tr>";

            unset($heroline);

            foreach($draft as $hero)
              $modules['teams']["team_".$tid."_stats"]['draft'] .= $hero['out'];

            $modules['teams']["team_".$tid."_stats"]['draft'] .= "</table>";
            unset($draft);
          }
        }
        if (isset($report['teams'][$tid]['hero_positions'])) {
          $modules['teams']["team_".$tid."_stats"]['hero_positions'] = "";

          if (check_module($parent."team_".$tid."_stats-hero_positions")) {
            if($mod == $parent."team_".$tid."_stats-hero_positions") $unset_module = true;
            for ($i=1; $i>=0 && !isset($keys); $i--) {
              for ($j=1; $j<6 && $j>0; $j++) {
                if (!$i) { $j = 0; }
                if(isset($report['teams'][$tid]['hero_positions'][$i][$j][0])) {
                  $keys = array_keys($report['teams'][$tid]['hero_positions'][$i][$j][0]);
                  break;
                }
                if (!$i) { break; }
              }
            }

            for ($i=1; $i>=1; $i--) {
              for ($j=1; $j<6 && $j>0; $j++) {
                if (!$i) { $j = 0; }

                if(!isset($strings['en']["positions_$i.$j"]))
                  $strings['en']["positions_$i.$j"] = ($i ? locale_string("core") : locale_string("support"))." ".$meta['lanes'][$j];

                if(sizeof($report['teams'][$tid]['hero_positions'][$i][$j])) {
                  $modules['teams']["team_".$tid."_stats"]['hero_positions']["positions_$i.$j"]  = "";

                  if (check_module($parent."team_".$tid."_stats-hero_positions-positions_$i.$j")) {
                    $modules['teams']["team_".$tid."_stats"]['hero_positions']["positions_$i.$j"] .= "<table id=\"heroes-positions-$i-$j\" class=\"list wide\">
                                                      <tr class=\"thead\">
                                                        <th onclick=\"sortTable(0,'heroes-positions-$i-$j');\">".locale_string("hero")."</th>";
                    for($k=1, $end=sizeof($keys); $k < $end; $k++) {
                      $modules['teams']["team_".$tid."_stats"]['hero_positions']["positions_$i.$j"] .= "<th onclick=\"sortTableNum($k,'heroes-positions-$i-$j');\">".locale_string($keys[$k])."</th>";
                    }
                    $modules['teams']["team_".$tid."_stats"]['hero_positions']["positions_$i.$j"] .= "</tr>";

                    uasort($report['teams'][$tid]['hero_positions'][$i][$j], function($a, $b) {
                      if($a['matches_s'] == $b['matches_s']) return 0;
                      else return ($a['matches_s'] < $b['matches_s']) ? 1 : -1;
                    });

                    foreach($report['teams'][$tid]['hero_positions'][$i][$j] as $hero) {

                      $modules['teams']["team_".$tid."_stats"]['hero_positions']["positions_$i.$j"] .= "<tr".(isset($report['teams'][$tid]['hero_positions_matches']) ?
                                                                        " onclick=\"showModal('".htmlspecialchars(join_matches($report['teams'][$tid]['hero_positions_matches'][$i][$j][$hero['heroid']])).
                                                                                "', '".$meta['heroes'][ $hero['heroid'] ]['name']." - ".
                                                                                locale_string("positions_$i.$j")." - ".locale_string("matches")."');\"" : "").">
                                                          <td>".($hero['heroid'] ? hero_full($hero['heroid']) : "").
                                                         "</td>
                                                          <td>".$hero['matches_s']."</td>
                                                          <td>".number_format($hero['winrate_s']*100,1)."%</td>";
                      for($k=3, $end=sizeof($keys); $k < $end; $k++) {
                        $modules['teams']["team_".$tid."_stats"]['hero_positions']["positions_$i.$j"] .= "<td>".number_format($hero[$keys[$k]],1)."</td>";
                      }
                      $modules['teams']["team_".$tid."_stats"]['hero_positions']["positions_$i.$j"] .= "</tr>";
                    }
                    $modules['teams']["team_".$tid."_stats"]['hero_positions']["positions_$i.$j"] .= "</table>";

                    $modules['teams']["team_".$tid."_stats"]['hero_positions']["positions_$i.$j"] .= "<div class=\"content-text\">".locale_string("desc_heroes_positions")."</div>";
                  }
                }


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
              if(!isset($report['teams'][$tid]['pickban'][$hid])) continue;
              if($report['teams'][$tid]['pickban'][$hid]['matches_picked'])
                $wr = $report['teams'][$tid]['pickban'][$hid]['wins_picked'] / $report['teams'][$tid]['pickban'][$hid]['matches_picked'];
              else
                $wr = 0;
              $nodes .= "{id: $hid, value: ".$report['teams'][$tid]['pickban'][$hid]['matches_total'].
                ", label: '".addslashes($hero['name'])."'".
                ", title: '".addslashes($hero['name']).", ".
                $report['teams'][$tid]['pickban'][$hid]['matches_total']." ".locale_string("total").", ".
                $report['teams'][$tid]['pickban'][$hid]['matches_picked']." ".locale_string("matches_picked").", ".
                number_format($wr*100, 1)." ".locale_string("winrate_picked")."'".
                ", shape:'circularImage', image: 'res/heroes/".$meta['heroes'][$hid]['tag'].".png'".
                ", color:{ border:'rgba(".number_format(255-255*$wr, 0).",124,".
                number_format(255*$wr, 0).")' }},";
            }
            $modules['teams']["team_".$tid."_stats"]['hero_combo_graph'] .= "var nodes = [".$nodes."];";

            $nodes = "";
            foreach($report['teams'][$tid]['hero_graph'] as $combo) {
              $nodes .= "{from: ".$combo['heroid1'].", to: ".$combo['heroid2'].", value:".$combo['matches'].", title:\"".$combo['matches']."\", color:{color:'rgba(".
                number_format(255*(1-$combo['winrate']), 0).",124,".
                number_format(255*$combo['winrate'],0).",1)'}},";
            }

            $modules['teams']["team_".$tid."_stats"]['hero_combo_graph'] .= "var edges = [".$nodes."];";

            $modules['teams']["team_".$tid."_stats"]['hero_combo_graph'] .= "var container = document.getElementById('team$tid-combos-graph');\n".
                                                        "var data = { nodes: nodes, edges: edges};\n".
                                                        "var options={
                                                          $visjs_settings
                                                         };\n".
                                                        "var network = new vis.Network(container, data, options);\n".
                                                        "</script>";

              $modules['teams']["team_".$tid."_stats"]['hero_combo_graph'] .= "<div class=\"content-text\">".locale_string("desc_heroes_combo_graph", ["lim" => $report['settings']['limiter_triplets']+1 ])."</div>";
          }
        }
        if (isset($report['teams'][$tid]['hero_pairs']) || isset($report['teams'][$tid]['hero_triplets'])) {
          $modules['teams']["team_".$tid."_stats"]['hero_combos'] = "";

          if (check_module($parent."team_".$tid."_stats-hero_combos")) {
            $modules['teams']["team_".$tid."_stats"]['hero_combos'] .= "<table id=\"team$tid-pairs\" class=\"list\">
                                                  <caption>".locale_string("hero_pairs")."</caption>
                                                  <tr class=\"thead\">
                                                    <th onclick=\"sortTable(0,'hero-pairs');\">".locale_string("hero")." 1</th>
                                                    <th onclick=\"sortTable(1,'hero-pairs');\">".locale_string("hero")." 2</th>
                                                    <th onclick=\"sortTableNum(2,'hero-pairs');\">".locale_string("matches")."</th>
                                                    <th onclick=\"sortTableNum(3,'hero-pairs');\">".locale_string("winrate")."</th>
                                                  </tr>";
            foreach($report['teams'][$tid]['hero_pairs'] as $pair) {
              $modules['teams']["team_".$tid."_stats"]['hero_combos'] .= "<tr".(isset($report['teams'][$tid]['hero_pairs_matches']) ?
                                                  " onclick=\"showModal('".
                                                            htmlspecialchars(join_matches($report['teams'][$tid]['hero_pairs_matches'][$pair['heroid1'].'-'.$pair['heroid2']])).
                                                                        "', '".locale_string("matches")."');\"" : "").">
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
                                                    <caption>".locale_string("hero_triplets")."</caption>
                                                    <tr class=\"thead\">
                                                      <th onclick=\"sortTable(0,'hero-triplets');\">".locale_string("hero")." 1</th>
                                                      <th onclick=\"sortTable(1,'hero-triplets');\">".locale_string("hero")." 2</th>
                                                      <th onclick=\"sortTable(2,'hero-triplets');\">".locale_string("hero")." 3</th>
                                                      <th onclick=\"sortTableNum(3,'hero-triplets');\">".locale_string("matches")."</th>
                                                      <th onclick=\"sortTableNum(4,'hero-triplets');\">".locale_string("winrate")."</th>
                                                    </tr>";
              foreach($report['teams'][$tid]['hero_triplets'] as $pair) {
                $modules['teams']["team_".$tid."_stats"]['hero_combos'] .= "<tr".(isset($report['teams'][$tid]['hero_pairs_matches']) ?
                                                    " onclick=\"showModal('".
                                                    implode($report['hero_pairs_matches'][$pair['heroid1'].'-'.$pair['heroid2'].'-'.$pair['heroid3']], ", ").
                                                                          "', '".locale_string("matches")."');\"" : "").">
                                                      <td>".($pair['heroid1'] ? hero_full($pair['heroid1']) : "").
                                                     "</td><td>".($pair['heroid2'] ? hero_full($pair['heroid2']) : "").
                                                     "</td><td>".($pair['heroid3'] ? hero_full($pair['heroid3']) : "").
                                                     "</td>
                                                     <td>".$pair['matches']."</td>
                                                     <td>".number_format($pair['winrate']*100,2)."</td>
                                                    </tr>";
              }
              $modules['teams']["team_".$tid."_stats"]['hero_combos'] .= "</table>";
            }

            $modules['teams']["team_".$tid."_stats"]['hero_combos'] .= "<div class=\"content-text\">".locale_string("desc_heroes_combos", [ "limh"=>$report['settings']['limiter']+1, "liml"=>$report['settings']['limiter_triplets']+1 ] )."</div>";
          }
        }
        if (isset($report['teams'][$tid]['matches']) && isset($report['matches'])) {
          $modules['teams']["team_".$tid."_stats"]['matches'] = "";

          if(check_module($parent."team_".$tid."_stats-matches")) {
            $modules['teams']["team_".$tid."_stats"]['matches'] = "<div class=\"content-text\">".locale_string("desc_matches")."</div>";
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
            $modules['teams']["team_".$tid."_stats"]['roster'] = "<div class=\"content-text\">".locale_string("desc_roster")."</div>";
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
                    "<th onclick=\"sortTable(0,'teams-sum');\">".locale_string("team_name")."</th>".
                    "<th onclick=\"sortTableNum(1,'teams-sum');\">".locale_string("matches_s")."</th>".
                    "<th onclick=\"sortTableNum(2,'teams-sum');\">".locale_string("winrate_s")."</th>".
                    "<th onclick=\"sortTableNum(3,'teams-sum');\">".locale_string("rad_ratio")."</th>".
                    "<th onclick=\"sortTableNum(4,'teams-sum');\">".locale_string("rad_wr_s")."</th>".
                    "<th onclick=\"sortTableNum(5,'teams-sum');\">".locale_string("dire_wr_s")."</th>".
                    "<th onclick=\"sortTableNum(6,'teams-sum');\">".locale_string("hero_pool")."</th>".
                    (compare_ver($report['ana_version'], array(1,1,1,-4,1)) < 0 ?
                      "" :
                      "<th onclick=\"sortTableNum(7,'teams-sum');\">".locale_string("diversity")."</th>"
                    ).
                    "<th onclick=\"sortTableNum(8,'teams-sum');\">".locale_string("kills")."</th>".
                    "<th onclick=\"sortTableNum(9,'teams-sum');\">".locale_string("deaths")."</th>".
                    "<th onclick=\"sortTableNum(10,'teams-sum');\">".locale_string("assists")."</th>".
                    "<th onclick=\"sortTableNum(11,'teams-sum');\">".locale_string("gpm")."</th>".
                    "<th onclick=\"sortTableNum(12,'teams-sum');\">".locale_string("xpm")."</th>".
                    "<th onclick=\"sortTableNum(13,'teams-sum');\">".locale_string("wards_placed_s")."</th>".
                    "<th onclick=\"sortTableNum(14,'teams-sum');\">".locale_string("sentries_placed_s")."</th>".
                    "<th onclick=\"sortTableNum(15,'teams-sum');\">".locale_string("wards_destroyed_s")."</th>".
                    "<th onclick=\"sortTableNum(16,'teams-sum');\">".locale_string("duration")."</th>".
              "</tr>";

    foreach($report['teams'] as $team_id => $team) {
      $modules['summary_teams'] .= "<tr>".
                    "<td>".team_link($team_id)."</td>".
                    "<td>".$team['matches_total']."</td>".
                    "<td>".number_format($team['wins']*100/$team['matches_total'],2)."%</td>".
                    "<td>".number_format($team['averages']['rad_ratio']*100,2)."%</td>".
                      (
                        (compare_ver($report['ana_version'], array(1,1,1,-4,0)) < 0) ?
                          "<td>".number_format($team['averages']['rad_wr']*100,2)."%</td>" :
                          "<td>".number_format($team['averages']['radiant_wr']*100,2)."%</td>"
                        ).
                    "<td>".number_format($team['averages']['dire_wr']*100,2)."%</td>".
                    "<td>".$team['averages']['hero_pool']."</td>".
                    (
                      (compare_ver($report['ana_version'], array(1,1,1,-4,1)) < 0) ?
                        "" :
                        "<td>".number_format($team['averages']['diversity'],2)."</td>"
                      ).
                    "<td>".number_format($team['averages']['kills'],1)."</td>".
                    "<td>".number_format($team['averages']['deaths'],1)."</td>".
                    "<td>".number_format($team['averages']['assists'],1)."</td>".
                    "<td>".number_format($team['averages']['gpm'],1)."</td>".
                    "<td>".number_format($team['averages']['xpm'],1)."</td>".
                    "<td>".number_format($team['averages']['wards_placed'],1)."</td>".
                    "<td>".number_format($team['averages']['sentries_placed'],1)."</td>".
                    "<td>".number_format($team['averages']['wards_destroyed'],1)."</td>".
                    (
                      (compare_ver($report['ana_version'], array(1,1,1,-4,1)) < 0) ?
                        "<td>".number_format($team['averages']['duration'],1)."</td>" :
                        "<td>".number_format($team['averages']['avg_match_len'],1)."</td>"
                      ).

              "</tr>";
    }
    $modules['summary_teams'] .= "</table>";

    $modules['summary_teams'] .= "<div class=\"content-text\">".locale_string("desc_teams_summary")."</div>";
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
                      )." onclick=\"showModal('".locale_string("matches").": ".$tvt[$tid][$team_ids[$i]]['matches']
                            ."<br />".locale_string("winrate").": ".number_format($tvt[$tid][$team_ids[$i]]['winrate']*100,2)
                            ."%<br />".locale_string("won")." ".$tvt[$tid][$team_ids[$i]]['won']." - "
                                     .locale_string("lost")." ".$tvt[$tid][$team_ids[$i]]['lost'].(
                                       isset($tvt[$tid][$team_ids[$i]]['matchids']) ?
                                        "<br />MatchIDs: ".implode($tvt[$tid][$team_ids[$i]]['matchids'], ", ")
                                        : "").
                            "','".$report['teams'][$tid]['name']." vs ".$report['teams'][$team_ids[$i]]['name']."')\">".
                            number_format($teamline[$team_ids[$i]]['winrate']*100,0)."</td>";
          }
        }
        $modules['tvt'] .= "</tr>";
      }

      $modules['tvt'] .= "</table>";

      $modules['tvt'] .= "<div class=\"content-text\">".locale_string("desc_tvt")."</div>";

      unset($tvt);
  }

  # matches
  if (isset($modules['matches']) && check_module("matches")) {
    $modules['matches'] = "<div class=\"content-text\">".locale_string("desc_matches")."</div>";
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
        $modules['participants']['teams'] .= "<div class=\"content-text\">".locale_string("desc_participants")."</div>";
        $modules['participants']['teams'] .= "<div class=\"content-cards\">";
        foreach($report['teams'] as $team_id => $team) {
          $modules['participants']['teams'] .= team_card($team_id);
        }
        $modules['participants']['teams'] .= "</div>";
      }
    }

    $modules['participants']['players'] = "";
    if(check_module($parent."players")) {
      $modules['participants']['players'] .= "<div class=\"content-text\">".locale_string("desc_participants")."</div>";
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
      <?php
         if(file_exists("favicon.ico")) echo "<link rel=\"shortcut icon\" href=\"favicon.ico\" />";
      ?>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      <title><?php
        echo $instance_title;
        if (!empty($leaguetag))
            echo " - ".$report['league_name'];
        ?></title>
      <link href="res/valve_mimic.css" rel="stylesheet" type="text/css" />
      <link href="res/reports.css" rel="stylesheet" type="text/css" />
      <?php
            if(isset($override_style) && file_exists("res/custom_styles/".$override_style.".css"))
                echo "<link href=\"res/custom_styles/".$override_style.".css\" rel=\"stylesheet\" type=\"text/css\" />";
            else if(isset($report['settings']['custom_style']) && file_exists("res/custom_styles/".$report['settings']['custom_style'].".css"))
                echo "<link href=\"res/custom_styles/".$report['settings']['custom_style'].".css\" rel=\"stylesheet\" type=\"text/css\" />";
            else {

              if(empty($leaguetag) && !empty($noleague_style))
                echo "<link href=\"res/custom_styles/".$noleague_style.".css\" rel=\"stylesheet\" type=\"text/css\" />";
              else if(!empty($default_style))
                echo "<link href=\"res/custom_styles/".$default_style.".css\" rel=\"stylesheet\" type=\"text/css\" />";
            }
            if($use_graphjs) {
              echo "<script type=\"text/javascript\" src=\"res/dependencies/Chart.bundle.min.js\"></script>";
            }
            if($use_visjs) {
              echo "<script type=\"text/javascript\" src=\"res/dependencies/vis.min.js\"></script>";
              echo "<script type=\"text/javascript\" src=\"res/dependencies/vis-network.min.js\"></script>";
              echo "<link href=\"res/dependencies/vis.min.css\" rel=\"stylesheet\" type=\"text/css\" />";
              echo "<link href=\"res/dependencies/vis-network.min.css\" rel=\"stylesheet\" type=\"text/css\" />";
            }

       if (!empty($custom_head)) echo $custom_head; ?>
    </head>
    <body>
      <?php if (!empty($custom_body)) echo $custom_body; ?>
      <header class="navBar">
        <!-- these shouldn't be spans, but I was mimicking Valve pro circuit style in everything, so I copied that too. -->
        <span class="navItem dotalogo"><a href="<?php echo $main_path; ?>"></a></span>
        <span class="navItem"><a href=".<?php if(!empty($linkvars)) echo "?".$linkvars; ?>" title="Dota 2 League Reports"><?php echo locale_string("leag_reports")?></a></span>
        <?php
          foreach($title_links as $link) {
            echo "<span class=\"navItem\"><a href=\"".$link['link']."\" target=\"_blank\" rel=\"noopener\" title=\"".$link['title']."\">".$link['text']."</a></span>";
          }
         ?>
        <div class="share-links">
          <?php
            echo '<div class="share-link reddit"><a href="https://www.reddit.com/submit?url='.htmlspecialchars('https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].
              (empty($_SERVER['QUERY_STRING']) ? "" : '?'.$_SERVER['QUERY_STRING'])
            ).'" target="_blank" rel="noopener">Share on Reddit</a></div>';
            echo '<div class="share-link twitter"><a href="https://twitter.com/share?text=League+Report:+'.$leaguetag.'+'.htmlspecialchars('https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].
              (empty($_SERVER['QUERY_STRING']) ? "" : '?'.$_SERVER['QUERY_STRING'])
            ).'" target="_blank" rel="noopener">Share on Twitter</a></div>';
            echo '<div class="share-link vk"><a href="https://vk.com/share.php?url='.htmlspecialchars('https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].
              (empty($_SERVER['QUERY_STRING']) ? "" : '?'.$_SERVER['QUERY_STRING'])
            ).'" target="_blank" rel="noopener">Share on VK</a></div>';
            echo '<div class="share-link fb"><a href="https://www.facebook.com/sharer/sharer.php?u='.htmlspecialchars('https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].
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
          <h3><?php echo locale_string($h3).": ".$report['random'][$h3]; ?></h3>
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
          <h1><?php echo  $instance_name; ?></h1>
        </div>
        <div id="main-section" class="content-section">
          <?php
          $dir = scandir("reports");

          if (sizeof($dir) < 3) {
            echo "<div id=\"content-top\">".
              "<div class=\"content-header\">".locale_string("empty_instance_cap")."</div>".
              "<div class=\"content-text\">".locale_string("empty_instance_desc").".</div>".
            "</div>";
          } else {
            echo "<div id=\"content-top\">".
              "<div class=\"content-header\">".locale_string("noleague_cap")."</div>".
              "<div class=\"content-text\">".locale_string("noleague_desc").":</div>".
            "</div>";

            echo "<table id=\"leagues-list\" class=\"list wide\"><tr class=\"thead\">
              <th onclick=\"sortTable(0,'leagues-list');\">".locale_string("league_name")."</th>
              <th onclick=\"sortTableNum(1,'leagues-list');\">".locale_string("league_id")."</th>
              <th>".locale_string("league_desc")."</th>
              <th onclick=\"sortTableNum(3,'leagues-list');\">".locale_string("matches_total")."</th>
              <th onclick=\"sortTableValue(4,'leagues-list');\">".locale_string("start_date")."</th>
              <th onclick=\"sortTableValue(5,'leagues-list');\">".locale_string("end_date")."</th></tr>";

            $reports = array();

            foreach($dir as $report) {
                if($report[0] == ".")
                    continue;
                $name = str_replace("report_", "", $report);
                $name = str_replace(".json", "", $name);

                $f = fopen("reports/report_".$name.".json","r");
                $file = fread($f, 400);

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
              if($a['end'] == $b['end']) {
                if($a['std'] == $b['std']) return 0;
                else return ($a['std'] < $b['std']) ? 1 : -1;
              } else return ($a['end'] < $b['end']) ? 1 : -1;
            });

            foreach($reports as $report) {
              echo "<tr><td><a href=\"?league=".$report['name'].(empty($linkvars) ? "" : "&".$linkvars)."\">".$report['head']."</a></td>".
                "<td>".($report['id'] == "null" ? "-" : $report['id'])."</td>".
                "<td>".$report['desc']."</td>".
                "<td>".$report['total']."</td>".
                "<td value=\"".$report['std']."\">".date(locale_string("date_format"), $report['std'])."</td>".
                "<td value=\"".$report['end']."\">".date(locale_string("date_format"), $report['end'])."</td></tr>";
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
          Graphs are made with <a href="https://visjs.org" target="_blank" rel="noopener">vis.js</a> and <a href="http://www.chartjs.org/" target="_blank" rel="noopener">chart.js</a>.<br />
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
