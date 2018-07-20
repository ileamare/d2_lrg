<?php
//echo dirname(__FILE__);
include_once($root."/modules/view/generators/draft.php");

$modules['players']['draft'] = "";

function rg_view_generate_players_draft() {
  global $report;

  $context_pickban = [];
  foreach($report['players_summary'] as $id => $el) {
    $context_pickban[ $id ] = [
      "matches_banned" => 0,
      "winrate_banned" => 0,
      "matches_picked" => $el['matches_s'],
      "matches_total" => $el['matches_s'],
      "winrate_picked" => $el['winrate_s']
    ];
  }

  $res = rg_generator_draft("players-draft", $context_pickban, $report['players_draft'], $report["random"]["matches_total"], false);

  return $res;
}

?>
