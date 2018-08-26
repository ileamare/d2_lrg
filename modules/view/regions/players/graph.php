<?php

include_once($root."/modules/view/generators/meta_graph.php");

function rg_view_generate_regions_players_party_graph($region, $reg_report) {
  global $report;

  $locale_settings = ["limh" => $reg_report['settings']['limiter_graph']+1,
      "per" => "35%"
  ];

  $res = "<div class=\"content-text\">".locale_string("desc_players_combo_graph", $locale_settings)."</div>";

  $res .= rg_generator_meta_graph("region$region-players-party-graph", $reg_report['players_parties_graph'], $report['players_additional'], false);

  return $res;
}

?>
