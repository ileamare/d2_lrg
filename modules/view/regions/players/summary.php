<?php
include_once($root."/modules/view/generators/summary.php");

function rg_view_generate_regions_players_summary($region, $reg_report) {
  global $report;

  if (isset($report['players_additional'])) {
    generate_positions_strings();

    foreach($reg_report['players_summary'] as $id => $player) {
      $position = reset($report['players_additional'][$id]['positions']);
      $position = "position_".$position["core"].".".$position["lane"];
      $reg_report['players_summary'][$id]['common_position'] = locale_string($position);
    }
  }

  $res = rg_generator_summary("region$region-players-summary", $reg_report['players_summary'], false);

  $res .= "<div class=\"content-text\">".locale_string("desc_players_summary")."</div>";

  return $res;
}

?>
