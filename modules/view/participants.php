<?php

$modules['participants'] = [];

function rg_view_generate_participants() {
  global $report, $unset_module, $mod, $parent, $root;

  if($mod == "participants") $unset_module = true;
  $parent = "participants-";
  generate_positions_strings();

  if(isset($report['teams'])) {
    $res['teams'] = "";
    if(check_module($parent."teams")) {
      include_once("$root/modules/view/generators/participants_teams.php");
      $res['teams'] .= "<div class=\"content-text\">".locale_string("desc_participants")."</div>";
      $res['teams'] .= rg_generator_participants_teams($report['teams']);
    }
  }

  $res['players'] = "";
  if(check_module($parent."players")) {
    include_once("$root/modules/view/generators/participants_players.php");
    $res['players'] .= "<div class=\"content-text\">".locale_string("desc_participants")."</div>";
    $res['players'] .= rg_generator_participants_players($report['players']);
  }

  return $res;
}

?>
