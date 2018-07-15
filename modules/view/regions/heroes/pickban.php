<?php
include_once("$root/modules/view/generators/pickban.php");

function rg_view_generate_regions_heroes_pickban($region, $reg_report) {
  global $meta;
  global $modules;

  $res = rg_generator_pickban("region$region-heroes-pickban", $reg_report['pickban'], $reg_report['main']['matches']);
  $res .= rg_generator_uncontested($meta["heroes"], $reg_report['pickban']);
  $res .= "<div class=\"content-text\">".locale_string("desc_heroes_pickban")."</div>";

  return $res;
}


?>
