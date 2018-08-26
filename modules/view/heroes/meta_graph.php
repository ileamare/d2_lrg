<?php

include_once($root."/modules/view/generators/meta_graph.php");

$modules['heroes']['meta_graph'] = "";

function rg_view_generate_heroes_meta_graph() {
  global $report;

  $locale_settings = ["lim" => $report['settings']['limiter_combograph']+1,
      "per" => "35%"
  ];

  $res = "<div class=\"content-text\">".locale_string("desc_meta_graph", $locale_settings)."</div>";

  $res .= rg_generator_meta_graph("heroes-meta-graph", $report['hero_combos_graph'], $report['pickban']);

  $res .= "<div class=\"content-text\">".locale_string("desc_meta_graph_add", $locale_settings)."</div>";

  return $res;
}

?>
