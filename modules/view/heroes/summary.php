<?php
include_once($root."/modules/view/generators/summary.php");

$modules['heroes']['summary'] = "";

function rg_view_generate_heroes_summary() {
  global $report;

  $res = rg_generator_summary("heroes-summary", $report['hero_summary']);

  $res .= "<div class=\"content-text\">".locale_string("desc_heroes_summary")."</div>";

  return $res;
}

?>
