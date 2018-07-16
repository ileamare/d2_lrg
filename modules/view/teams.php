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

  $res['tvt'] = "";

  $res['profiles'] = [];

  return $res;
}
?>
