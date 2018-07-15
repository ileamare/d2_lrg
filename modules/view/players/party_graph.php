<?php

include_once($root."/modules/view/generators/meta_graph.php");

$modules['players']['party_graph'] = "";

function rg_view_generate_players_party_graph() {
  global $report;

  $res = "<div class=\"content-text\">".locale_string("desc_players_combo_graph", [ "limh" => $report['settings']['limiter_combograph']+1 ])."</div>";

  $res .= rg_generator_meta_graph("players-party-graph", $report['players_combo_graph'], $report['players_additional'], false);

  return $res;
}

?>
