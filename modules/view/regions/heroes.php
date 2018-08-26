<?php

$res["region".$region]["heroes"] = [];

if($mod == $modstr."-heroes") $unset_module = true;
$parent_mod = $modstr."-heroes-";

if(isset($reg_report['haverages_heroes'])) {
  $res["region".$region]['heroes']["haverages"] = "";
  include_once("heroes/haverages.php");

  if(check_module($parent_mod."haverages")) {
    $res["region".$region]['heroes']['haverages'] = rg_view_generate_regions_heroes_haverages($region, $reg_report);
  }
}

$res["region".$region]['heroes']["pickban"] = "";
include_once("heroes/pickban.php");

if(check_module($parent_mod."pickban")) {
  $res["region".$region]['heroes']['pickban'] = rg_view_generate_regions_heroes_pickban($region, $reg_report);
}

if(isset($reg_report['draft'])) {
  $res["region".$region]['heroes']["draft"] = "";
  include_once("heroes/draft.php");

  if(check_module($parent_mod."draft")) {
    $res["region".$region]['heroes']['draft'] = rg_view_generate_regions_heroes_draft($region, $reg_report);
  }
}

if(isset($reg_report['heroes_meta_graph'])) {
  $res["region".$region]['heroes']["meta_graph"] = "";
  include_once("heroes/meta_graph.php");

  if(check_module($parent_mod."meta_graph")) {
    $res["region".$region]['heroes']['meta_graph'] = rg_view_generate_regions_heroes_meta_graph($region, $reg_report);
  }
}

if(isset($reg_report['hero_pairs']) ||
    (isset($reg_report['hero_trios']) && !empty($reg_report['hero_trios'])) ||
    (isset($reg_report['hero_lane_combos']) && !empty($reg_report['hero_lane_combos'])) ) {
  $res["region".$region]['heroes']["combos"] = [];
  include_once("heroes/combos.php");

  if(check_module($parent_mod."combos")) {
    $res["region".$region]['heroes']['combos'] = rg_view_generate_regions_heroes_combos($region, $reg_report, $parent_mod);
  }
}

if(isset($reg_report['hero_positions'])) {
  $res["region".$region]['heroes']["positions"] = [];
  include_once("heroes/positions.php");

  if(check_module($parent_mod."positions")) {
    $res["region".$region]['heroes']['positions'] = rg_view_generate_regions_heroes_positions($region, $reg_report, $parent_mod);
  }
}

if(isset($reg_report['hero_summary'])) {
  $res["region".$region]['heroes']["summary"] = "";
  include_once("heroes/summary.php");

  if(check_module($parent_mod."summary")) {
    $res["region".$region]['heroes']['summary'] = rg_view_generate_regions_heroes_summary($region, $reg_report);
  }
}

?>
