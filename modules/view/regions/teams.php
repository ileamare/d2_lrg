<?php
$res["region".$region]['teams'] = [];

if($mod == $modstr."-teams") $unset_module = true;
$parent_mod = $modstr."-teams-";

$res["region".$region]['teams']['summary'] = "";
if (check_module($parent_mod."summary")) {
  include_once("$root/modules/view/generators/teams_summary.php");
  if($reg_report['settings']['teams_summary_softgen'] ?? false)
    $res["region".$region]['teams']['summary']  = "<div class=\"content-text\">".locale_string("desc_teams_summary_softgen")."</div>";
  $res["region".$region]['teams']['summary'] .= rg_view_generator_teams_summary($reg_report['teams']);
}

if(isset($reg_report['settings']['tvt_grid']) && $reg_report['settings']['tvt_grid']) {
  $res["region".$region]['teams']['grid'] = "";
  if (check_module($parent_mod."grid")) {
    include_once("$root/modules/view/generators/tvt_unwrap_data.php");
    include_once("$root/modules/view/generators/tvt_grid.php");

    $team_ids = array_keys($reg_report['teams']);
    $tvt = rg_generator_tvt_unwrap_data($report['tvt'], $reg_report['teams']);

    if($reg_report['settings']['teams_summary_softgen'])
      $res["region".$region]['teams']['grid']  = "<div class=\"content-text\">".locale_string("desc_teams_summary_softgen")."</div>";
    $res["region".$region]['teams']['grid'] .= "<div class=\"content-text\">".locale_string("desc_tvt")."</div>";

    $res["region".$region]['teams']['grid'] .= rg_generator_tvt_grid("region$region-teams-tvt", $tvt);
  }
}

$res["region".$region]['teams']['profiles'] = [];
if (check_module($parent_mod."profiles")) {
  include_once("$root/modules/view/teams/profiles.php");
  $res["region".$region]['teams']['profiles'] = rg_view_generate_teams_profiles(
    $reg_report['teams'],
    $parent_mod."profiles-",
    $reg_report['settings']['teams_summary_softgen'] ? "<div class=\"content-text\">".locale_string("desc_teams_summary_softgen")."</div>" : ""
  );
}

$res["region".$region]['teams']['cards'] = "";
if (check_module($parent_mod."cards")) {
  include_once("$root/modules/view/generators/participants_teams.php");
  if($reg_report['settings']['teams_summary_softgen'])
    $res["region".$region]['teams']['cards']  = "<div class=\"content-text\">".locale_string("desc_teams_summary_softgen")."</div>";
  $res["region".$region]['teams']['cards'] .= "<div class=\"content-text\">".locale_string("desc_participants")."</div>";
  $res["region".$region]['teams']['cards'] .= rg_generator_participants_teams($reg_report['teams']);
}

?>
