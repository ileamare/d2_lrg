<?php
$modules['teams'] = [];

function rg_view_generate_teams() {
  global $mod, $parent, $unset_module, $report, $meta, $strings, $root;
  if($mod == "teams") $unset_module = true;
  $parent = "teams-";

  $res = [];

  $res['summary'] = "";
  if (check_module($parent."summary")) {
    include_once("teams/summary.php");
    $res['summary'] = rg_view_generate_teams_summary();
  }

  $res['grid'] = "";
  if (check_module($parent."grid")) {
    include_once("teams/grid.php");
    $res['grid'] = rg_view_generate_teams_grid();
  }

  $res['profiles'] = [];

  return $res;
}
?>
