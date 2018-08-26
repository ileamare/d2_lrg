<?php

$modules['heroes'] = [];

if (isset($report['averages_heroes']) )
  include("heroes/haverages.php");

if (isset($report['pickban']))
  include("heroes/pickban.php");

if (isset($report['draft']))
  include("heroes/draft.php");

if (isset($report['hero_positions']))
  include("heroes/positions.php");

if (isset($report['hero_sides']))
  include("heroes/sides.php");

if (isset($report['hero_combos_graph']) && $report['settings']['heroes_combo_graph'])
  include("heroes/meta_graph.php");

if (isset($report['hero_pairs']) || isset($report['hero_triplets']) || isset($report['hero_lane_combos']))
  include("heroes/combos.php");

if (isset($report['hvh']))
  include("heroes/hero_vs_hero.php");

if (isset($report['hero_summary']))
  include("heroes/summary.php");

function rg_view_generate_heroes() {
  global $report, $mod, $parent, $unset_module;

  if($mod == "heroes") $unset_module = true;
  $parent = "heroes-";

  if (isset($report['averages_heroes']) ) {
    if (check_module($parent."haverages")) { // FUNCTION SET
      $res['haverages'] = rg_view_generate_heroes_haverages();
    }
  }
  if (isset($report['pickban'])) {
    if (check_module($parent."pickban")) {
      $res['pickban'] = rg_view_generate_heroes_pickban();
    }
  }
  if (isset($report['draft'])) {
    if (check_module($parent."draft")) {
      $res['draft'] = rg_view_generate_heroes_draft();
    }
  }
  if (isset($report['hero_positions'])) {
    if(check_module($parent."positions")) {
      $res['positions'] = rg_view_generate_heroes_positions();
    }
  }
  if (isset($report['hero_sides'])) {
    if(check_module($parent."sides")) {
      $res['sides'] = rg_view_generate_heroes_sides();
    }
  }
  if (isset($report['hero_combos_graph']) && $report['settings']['heroes_combo_graph']) {
    if (check_module($parent."meta_graph")) {
      $res['meta_graph'] = rg_view_generate_heroes_meta_graph();
    }
  }
  if (isset($report['hero_pairs']) || isset($report['hero_triplets']) || isset($report['hero_lane_combos'])) {
    if (check_module($parent."combos")) {
      $res['combos'] = rg_view_generate_heroes_combos();
    }
  }
  if (isset($report['hvh'])) {
    if (check_module($parent."hvh")) {
      $res['hvh'] = rg_view_generate_heroes_hvh();
    }
  }
  if (isset($report['hero_summary'])) {
    if(check_module($parent."summary")) {
      $res['summary'] = rg_view_generate_heroes_summary();
    }
  }

  return $res;
}

?>
