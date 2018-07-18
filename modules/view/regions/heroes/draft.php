<?php
include_once("$root/modules/view/generators/draft.php");

function rg_view_generate_regions_heroes_draft($region, $reg_report) {
  global $meta;
  global $modules;

  $res = rg_generator_draft("region$region-heroes-draft", $reg_report['pickban'], $reg_report['draft'], $reg_report["main"]["matches"]);

  return $res;
}


?>
