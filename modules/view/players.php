<?php

$modules['players'] = [];

if (isset($report['averages_players']) )
  include("players/haverages.php");

if (isset($report['players_draft']))
  include("players/draft.php");

if (isset($report['player_positions']) )
  include("players/positions.php");

if (isset($report['pvp']) )
  include("players/pvp.php");

if (isset($report['player_pairs']) || isset($report['player_triplets']) || isset($report['player_lane_combos']))
  include("players/combos.php");

if (isset($report['players_combo_graph']) && $report['settings']['players_combo_graph'] && isset($report['players_additional']))
  include("players/party_graph.php");

if (isset($report['players_summary']) )
  include("players/summary.php");


  function rg_view_generate_players() {
    global $report, $mod, $parent, $unset_module;

    if($mod == "players") $unset_module = true;
    $parent = "players-";
    $res = [];

    if (isset($report['averages_players'])) {
      if (check_module($parent."haverages")) {
        $res['haverages'] = rg_view_generate_players_haverages();
      }
    }
    if (isset($report['players_draft'])) {
      if (check_module($parent."draft")) {
        $res['draft'] = rg_view_generate_players_draft();
      }
    }
    if (isset($report['player_positions'])) {
      if(check_module($parent."positions")) {
        $res['positions'] = rg_view_generate_players_positions();
      }
    }
    if (isset($report['pvp'])) {
      if (check_module($parent."pvp")) {
        $res['pvp'] = rg_view_generate_players_pvp();
      }
    }
    if (isset($report['player_pairs']) || isset($report['player_triplets']) || isset($report['player_lane_combos'])) {
      if (check_module($parent."combos")) {
        $res['combos'] = rg_view_generate_players_combos();
      }
    }
    if (isset($report['players_combo_graph']) && $report['settings']['players_combo_graph'] && isset($report['players_additional'])) {
      if (check_module($parent."party_graph")) {
        $res['party_graph'] = rg_view_generate_players_party_graph();
      }
    }
    if (isset($report['players_summary'])) {
      if(check_module($parent."summary")) {
        $res['summary'] = rg_view_generate_players_summary();
      }
    }

    return $res;
  }

?>
