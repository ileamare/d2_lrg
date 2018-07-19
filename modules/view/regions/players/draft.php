<?php
include_once("$root/modules/view/generators/draft.php");

function rg_view_generate_regions_players_draft($region, $reg_report) {
  global $meta;
  global $modules;

  $context_pickban = [];
  foreach($reg_report['players_summary'] as $el) {
    $context_pickban[ $el['playerid'] ] = [
      "matches_banned" => 0,
      "winrate_banned" => 0,
      "matches_picked" => $el['matches_s'],
      "matches_total" => $el['matches_s'],
      "winrate_picked" => $el['winrate_s']
    ];
  }

  $res = rg_generator_draft("region$region-players-draft", $context_pickban, $reg_report['players_draft'], $reg_report["main"]["matches"], false);

  return $res;
}


?>
