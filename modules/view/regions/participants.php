<?php

$res["region".$region]["participants"] = [];

if($mod == $modstr."-participants") $unset_module = true;
$parent_mod = $modstr."-participants-";
generate_positions_strings();

$res["region".$region]['participants']["players"] = "";

if(check_module($parent_mod."players")) {
  include_once("$root/modules/view/generators/participants_players.php");
  $res["region".$region]['participants']["players"] .= "<div class=\"content-text\">".locale_string("desc_participants")."</div>";
  $res["region".$region]['participants']["players"] .= rg_generator_participants_players($reg_report['players_summary']);
}

if(isset($report['teams'])) {
  $res["region".$region]['participants']["teams"] = "";
  if(check_module($parent_mod."teams")) {
    include_once("$root/modules/view/generators/participants_teams.php");
    $res["region".$region]['participants']["teams"] .= "<div class=\"content-text\">".locale_string("desc_participants")."</div>";
    $res["region".$region]['participants']["teams"] .= rg_generator_participants_teams($reg_report['teams']);
  }
}

?>
