<?php

$res["region".$region]["players"] = [];

if($mod == $modstr."-players") $unset_module = true;
$parent_mod = $modstr."-players-";

if(isset($reg_report['haverages_players'])) {
  $res["region".$region]['players']["haverages"] = "";
  include_once("players/haverages.php");

  if(check_module($parent_mod."haverages")) {
    $res["region".$region]['players']['haverages'] = rg_view_generate_regions_players_haverages($region, $reg_report);
  }
}

if(isset($reg_report['players_draft'])) {
  $res["region".$region]['players']["draft"] = "";
  include_once("players/draft.php");

  if(check_module($parent_mod."draft")) {
    $res["region".$region]['players']['draft'] = rg_view_generate_regions_players_draft($region, $reg_report);
  }
}

if(isset($reg_report['player_pairs']) ||
    (isset($reg_report['player_trios']) && !empty($reg_report['player_trios'])) ||
    (isset($reg_report['player_lane_combos']) && !empty($reg_report['player_lane_combos'])) ) {
  $res["region".$region]['players']["combos"] = [];
  include_once("players/combos.php");

  if(check_module($parent_mod."combos")) {
    $res["region".$region]['players']['combos'] = rg_view_generate_regions_players_combos($region, $reg_report, $parent_mod);
  }
}

if(isset($reg_report['players_parties_graph'])) {
  $res["region".$region]['players']["party_graph"] = "";
  include_once("players/graph.php");

  if(check_module($parent_mod."party_graph")) {
    $res["region".$region]['players']['party_graph'] = rg_view_generate_regions_players_party_graph($region, $reg_report);
  }
}

if(isset($reg_report['player_positions'])) {
  $res["region".$region]['players']["positions"] = [];
  include_once("players/positions.php");

  if(check_module($parent_mod."positions")) {
    $res["region".$region]['players']['positions'] = rg_view_generate_regions_players_positions($region, $reg_report, $parent_mod);
  }
}

$res["region".$region]['players']["summary"] = "";
include_once("players/summary.php");

if(check_module($parent_mod."summary")) {
  $res["region".$region]['players']['summary'] = rg_view_generate_regions_players_summary($region, $reg_report);
}

?>
