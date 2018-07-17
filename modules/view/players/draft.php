<?php
//echo dirname(__FILE__);
include_once($root."/modules/view/generators/draft.php");

$modules['players']['draft'] = "";

function rg_view_generate_players_draft() {
  global $report;

  $res = rg_generator_draft("players-draft", $report['players_draft_pickban'], $report['players_draft'], $report["random"]["matches_total"], false);

  return $res;
}

?>
