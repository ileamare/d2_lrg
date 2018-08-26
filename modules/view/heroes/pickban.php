<?php
//echo dirname(__FILE__);
include_once($root."/modules/view/generators/pickban.php");

$modules['heroes']['pickban'] = "";

function rg_view_generate_heroes_pickban() {
  global $report;
  global $meta;

  $res = "";

  $res .= rg_generator_pickban("heroes-pickban", $report['pickban'], $report["random"]["matches_total"]);

  $res .= rg_generator_uncontested($meta["heroes"], $report['pickban']);

  $res .= "<div class=\"content-text\">".locale_string("desc_heroes_pickban")."</div>";

  return $res;
}

?>
