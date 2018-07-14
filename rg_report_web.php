<?php
include_once("rg_report_out_settings.php");
include_once("modules/functions/versions.php");
$lg_version = array( 1, 4, 0, -4, 1 );

include_once("modules/functions/locale_strings.php");
include_once("modules/functions/get_language_code_iso6391.php");

# FUNCTIONS
include_once("modules/view/functions/modules.php");

include_once("modules/view/functions/player_card.php");
include_once("modules/view/functions/team_card.php");
include_once("modules/view/functions/match_card.php");

include_once("modules/view/functions/join_selectors.php");
include_once("modules/view/functions/links.php");
include_once("modules/view/functions/join_matches.php");

include_once("modules/view/functions/team_name.php");
include_once("modules/view/functions/player_name.php");
include_once("modules/view/functions/hero_name.php");

include_once("modules/view/functions/has_pair.php");

# PRESETS
include_once("modules/view/__preset.php");

/* INITIALISATION */

$root = dirname(__FILE__);

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

    include_once("modules/view/__post_load.php");

    $modules = array();
    # module => array or ""
    include_once("modules/view/overview.php");
    if (isset($report['records']))
      include_once("modules/view/records.php");

    if (isset($report['averages_heroes']) || isset($report['pickban']) || isset($report['draft']) || isset($report['hero_positions']) ||
        isset($report['hero_sides']) || isset($report['hero_pairs']) || isset($report['hero_triplets']))
          include_once("modules/view/heroes.php");

    if (isset($report['averages_players']) || isset($report['pvp']) || isset($report['player_positions']) || isset($report['player_pairs']))
      include_once("modules/view/players.php");

    if (isset($report['teams'])) { $modules['teams'] = array(); $modules['summary_teams'] = ""; }
    if (isset($report['teams'])) $modules['tvt'] = "";

    if (isset($report['matches'])) $modules['matches'] = "";

    if (isset($report['players'])) $modules['participants'] = array();

    if (isset($report['regions_data']))
      include("modules/view/regions.php");

    if(empty($mod)) $unset_module = true;
    else $unset_module = false;

    $h3 = array_rand($report['random']);


  # overview
  if (check_module("overview")) {
    $modules['overview'] .= rg_view_generate_overview();
  }

  # records
  if (isset($modules['records']) && check_module("records")) {
    $modules['records'] .= rg_view_generate_records();
  }

  # heroes
  if (isset($modules['heroes']) && check_module("heroes")) {
    if($mod == "heroes") $unset_module = true;
    $parent = "heroes-";

    if (isset($report['averages_heroes']) ) {
      if (check_module($parent."haverages")) {
        $modules['heroes']['haverages'] = rg_view_generate_heroes_haverages();
      }
    }
    if (isset($report['pickban'])) {
      if (check_module($parent."pickban")) { // FUNCTION SET
        $modules['heroes']['pickban'] = rg_view_generate_heroes_pickban();
      }
    }
    if (isset($report['draft'])) {
      if (check_module($parent."draft")) {
        $modules['heroes']['draft'] = rg_view_generate_heroes_draft();
      }
    }
    if (isset($report['hero_positions'])) {
      if(check_module($parent."positions")) {
        if($mod == $parent."positions") $unset_module = true;

        $modules['heroes']['positions'] = rg_view_generate_heroes_positions();
      }
    }
    if (isset($report['hero_sides'])) {
      if(check_module($parent."sides")) {
        $modules['heroes']['sides'] = rg_view_generate_heroes_sides();
      }
    }
    if (isset($report['hero_combos_graph']) && $report['settings']['heroes_combo_graph']) {
      if (check_module($parent."meta_graph")) {
        $modules['heroes']['meta_graph'] = rg_view_generate_heroes_meta_graph();
      }
    }
    if (isset($report['hero_pairs']) || isset($report['hero_triplets']) || isset($report['hero_lane_combos'])) {
      if (check_module($parent."combos")) {
        if($mod == $parent."combos") $unset_module = true;

        $modules['heroes']['combos'] = rg_view_generate_heroes_combos();
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
      if(check_module($parent."summary")) {
        $modules['heroes']['summary'] = rg_view_generate_heroes_summary();
      }
    }
  }

  # players
  if (isset($modules['players']) && check_module("players")) {
    if($mod == "players") $unset_module = true;
    $parent = "players-";

    if (isset($report['averages_players'])) {
      if (check_module($parent."haverages")) {
        $modules['players']['haverages'] = rg_view_generate_players_haverages();
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
            $draft = [];

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
                  $heroline .= "<td class=\"separator\">".number_format(($stage['pick']*$stage['pick_wr']+$stage['ban']*$stage['ban_wr'])/$report['teams'][$tid]['matches_total']*100, 2)."%</td>";

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

              $draft[$hid] = array ("out" => "", "matches" => $report['teams'][$tid]['pickban'][$hid]['matches_total']);
              $draft[$hid]['out'] .= "<td>".hero_full($hid)."</td>";

              $draft[$hid]['out'] .= "<td>".$report['teams'][$tid]['pickban'][$hid]['matches_total']."</td>";
              $draft[$hid]['out'] .= "<td>".number_format(($report['teams'][$tid]['pickban'][$hid]['wins_picked'] +
                                        $report['teams'][$tid]['pickban'][$hid]['wins_banned'])/
                                        $report['teams'][$tid]['matches_total']*100, 2)."%</td>";

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

            $modules['teams']["team_".$tid."_stats"]['draft'] .= "<table id=\"heroes-draft-team-$tid\" class=\"list wide\"><tr class=\"thead overhead\"><th width=\"15%\"></th><th colspan=\"6\">".locale_string("total")."</th>";
            $heroline = "<tr class=\"thead\">".
                          "<th onclick=\"sortTable(0,'heroes-draft-team-$tid');\">".locale_string("hero")."</th>".
                          "<th onclick=\"sortTableNum(1,'heroes-draft-team-$tid');\">".locale_string("matches_s")."</th>".
                          "<th onclick=\"sortTableNum(2,'heroes-draft-team-$tid');\">".locale_string("outcome_impact_s")."</th>".
                          "<th onclick=\"sortTableNum(3,'heroes-draft-team-$tid');\">".locale_string("picks_s")."</th>".
                          "<th onclick=\"sortTableNum(4,'heroes-draft-team-$tid');\">".locale_string("winrate_s")."</th>".
                          "<th onclick=\"sortTableNum(5,'heroes-draft-team-$tid');\">".locale_string("bans_s")."</th>".
                          "<th onclick=\"sortTableNum(6,'heroes-draft-team-$tid');\">".locale_string("winrate_s")."</th>";

            if($max_stage > 1)
              for($i=1; $i<=$max_stage; $i++) {
                $modules['teams']["team_".$tid."_stats"]['draft'] .= "<th class=\"separator\" colspan=\"5\">".locale_string("stage")." $i</th>";
                $heroline .= "<th onclick=\"sortTableNum(".(1+5*$i+1).",'heroes-draft-team-$tid');\" class=\"separator\">".locale_string("outcome_impact_s")."</th>".
                            "<th onclick=\"sortTableNum(".(1+5*$i+2).",'heroes-draft-team-$tid');\">".locale_string("picks_s")."</th>".
                            "<th onclick=\"sortTableNum(".(1+5*$i+3).",'heroes-draft-team-$tid');\">".locale_string("winrate_s")."</th>".
                            "<th onclick=\"sortTableNum(".(1+5*$i+4).",'heroes-draft-team-$tid');\">".locale_string("bans_s")."</th>".
                            "<th onclick=\"sortTableNum(".(1+5*$i+5).",'heroes-draft-team-$tid');\">".locale_string("winrate_s")."</th>";
              }
            $modules['teams']["team_".$tid."_stats"]['draft'] .= "</tr>".$heroline."</tr>";

            unset($heroline);

            foreach($draft as $hero)
              $modules['teams']["team_".$tid."_stats"]['draft'] .= $hero['out'];

            $modules['teams']["team_".$tid."_stats"]['draft'] .= "</table>";
            unset($draft);
          }
        }
        if (isset($report['teams'][$tid]['draft_vs'])) {
          $modules['teams']["team_".$tid."_stats"]['vsdraft'] = "";

          if(check_module($parent."team_".$tid."_stats-vsdraft")) {
            $draft = [];

            for ($i=0; $i<2; $i++) {
              $type = $i ? "pick" : "ban";
              $max_stage = 1;
              if(!isset($report['teams'][$tid]['draft_vs'][$i])) continue;
              foreach($report['teams'][$tid]['draft_vs'][$i] as $stage_num => $stage) {
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
                  $heroline .= "<td class=\"separator\">".number_format(($stage['pick']*$stage['pick_wr']+$stage['ban']*$stage['ban_wr'])/$report['teams'][$tid]['matches_total']*100, 2)."%</td>";

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

              $draft[$hid] = array ("out" => "", "matches" => $report['teams'][$tid]['pickban_vs'][$hid]['matches_total']);
              $draft[$hid]['out'] .= "<td>".hero_full($hid)."</td>";

              $draft[$hid]['out'] .= "<td>".$report['teams'][$tid]['pickban_vs'][$hid]['matches_total']."</td>";
              $draft[$hid]['out'] .= "<td>".number_format(($report['teams'][$tid]['pickban_vs'][$hid]['wins_picked'] +
                                        $report['teams'][$tid]['pickban_vs'][$hid]['wins_banned'])/
                                        $report['teams'][$tid]['matches_total']*100, 2)."%</td>";

              if(isset($report['teams'][$tid]['pickban_vs'][$hid]['matches_picked']) && $report['teams'][$tid]['pickban_vs'][$hid]['matches_picked'])
                $draft[$hid]['out'] .= "<td>".$report['teams'][$tid]['pickban_vs'][$hid]['matches_picked']."</td><td>".
                  number_format($report['teams'][$tid]['pickban_vs'][$hid]['wins_picked']*100/$report['teams'][$tid]['pickban_vs'][$hid]['matches_picked'], 2)."%</td>";
              else
                $draft[$hid]['out'] .= "<td>-</td><td>-</td>";

              if(isset($report['teams'][$tid]['pickban_vs'][$hid]['matches_banned']) && $report['teams'][$tid]['pickban_vs'][$hid]['matches_banned'])
                $draft[$hid]['out'] .= "<td>".$report['teams'][$tid]['pickban_vs'][$hid]['matches_banned']."</td><td>".
                    number_format($report['teams'][$tid]['pickban_vs'][$hid]['wins_banned']*100/$report['teams'][$tid]['pickban_vs'][$hid]['matches_banned'], 2)."%</td>";
              else
                $draft[$hid]['out'] .= "<td>-</td><td>-</td>";

              $draft[$hid]['out'] .= $heroline."</tr>";
            }


            uasort($draft, function($a, $b) {
              if($a['matches'] == $b['matches']) return 0;
              else return ($a['matches'] < $b['matches']) ? 1 : -1;
            });

            $modules['teams']["team_".$tid."_stats"]['vsdraft'] .= "<table id=\"heroes-vsdraft-team-$tid\" class=\"list wide\"><tr class=\"thead overhead\"><th width=\"15%\"></th><th colspan=\"6\">".locale_string("total")."</th>";
            $heroline = "<tr class=\"thead\">".
                          "<th onclick=\"sortTable(0,'heroes-vsdraft-team-$tid');\">".locale_string("hero")."</th>".
                          "<th onclick=\"sortTableNum(1,'heroes-vsdraft-team-$tid');\">".locale_string("matches_s")."</th>".
                          "<th onclick=\"sortTableNum(2,'heroes-vsdraft-team-$tid');\">".locale_string("outcome_impact_s")."</th>".
                          "<th onclick=\"sortTableNum(3,'heroes-vsdraft-team-$tid');\">".locale_string("picks_s")."</th>".
                          "<th onclick=\"sortTableNum(4,'heroes-vsdraft-team-$tid');\">".locale_string("winrate_s")."</th>".
                          "<th onclick=\"sortTableNum(5,'heroes-vsdraft-team-$tid');\">".locale_string("bans_s")."</th>".
                          "<th onclick=\"sortTableNum(6,'heroes-vsdraft-team-$tid');\">".locale_string("winrate_s")."</th>";

            if($max_stage > 1)
              for($i=1; $i<=$max_stage; $i++) {
                $modules['teams']["team_".$tid."_stats"]['vsdraft'] .= "<th class=\"separator\" colspan=\"5\">".locale_string("stage")." $i</th>";
                $heroline .= "<th onclick=\"sortTableNum(".(1+5*$i+1).",'heroes-vsdraft-team-$tid');\" class=\"separator\">".locale_string("outcome_impact_s")."</th>".
                            "<th onclick=\"sortTableNum(".(1+5*$i+2).",'heroes-vsdraft-team-$tid');\">".locale_string("picks_s")."</th>".
                            "<th onclick=\"sortTableNum(".(1+5*$i+3).",'heroes-vsdraft-team-$tid');\">".locale_string("winrate_s")."</th>".
                            "<th onclick=\"sortTableNum(".(1+5*$i+4).",'heroes-vsdraft-team-$tid');\">".locale_string("bans_s")."</th>".
                            "<th onclick=\"sortTableNum(".(1+5*$i+5).",'heroes-vsdraft-team-$tid');\">".locale_string("winrate_s")."</th>";
              }
            $modules['teams']["team_".$tid."_stats"]['vsdraft'] .= "</tr>".$heroline."</tr>";

            unset($heroline);

            foreach($draft as $hero)
              $modules['teams']["team_".$tid."_stats"]['vsdraft'] .= $hero['out'];

            $modules['teams']["team_".$tid."_stats"]['vsdraft'] .= "</table>";
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
                number_format($wr*100, 1)." ".locale_string("winrate")."'".
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

  if (isset($modules['regions']) && check_module("regions")) {
    if($mod == "regions") $unset_module = true;
    $parent = "regions-";

    rg_view_generate_regions();
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
            if(isset($report['settings']['custom_logo']) && file_exists("res/custom_styles/logos/".$report['settings']['custom_logo'].".css"))
                echo "<link href=\"res/custom_styles/logos/".$report['settings']['custom_logo'].".css\" rel=\"stylesheet\" type=\"text/css\" />";
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
        <?php if(empty($report['settings']['custom_logo'])) {?>
          <h1><?php echo $report['league_name']; ?></h1>
          <h2><?php echo $report['league_desc']; ?></h2>
          <h3><?php echo locale_string($h3).": ".$report['random'][$h3]; ?></h3>
        <?php } else { ?>
          <div id="image-logo"></div>
        <?php } ?>
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

                $head = json_decode("[\"".preg_replace("/{\"league_name\":\"(.+)\"\,\"league_desc\":(.*)/", "$1", $file)."\"]");
                $desc = json_decode("[\"".preg_replace("/{\"league_name\":\"(.+)\"\,\"league_desc\":\"(.+)\",\"league_id\":(.+),\"league_tag\":(.*)/", "$2", $file)."\"]");

                $reports[] = array(
                  "name" => $name,
                  "head" => array_pop($head),
                  "desc" => array_pop($desc),
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
