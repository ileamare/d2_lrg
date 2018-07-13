<?php

include_once($root."/modules/view/generators/sides.php");

$modules['heroes']['sides'] = "";

function rg_view_generate_heroes_sides() {
  global $report;
  global $meta;

  $res = "";

  $res .= rg_generator_sides("heroes-sides", $report['hero_sides']);

  $res .= "<div class=\"content-text\">".locale_string("desc_heroes_sides")."</div>";

  return $res;
}

?>
