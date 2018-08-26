<?php
include_once($root."/modules/view/generators/summary.php");

function rg_view_generate_regions_heroes_summary($region, $reg_report) {
  $res = rg_generator_summary("region$region-heroes-summary", $reg_report['hero_summary']);
  $res .= "<div class=\"content-text\">".locale_string("desc_heroes_summary")."</div>";
  return $res;
}

?>
