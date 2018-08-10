<?php
include_once "$root/modules/view/generators/overview.php";

function rg_view_generate_regions_overview($region, $report) {
  global $meta;
  global $modules;

  $res = rg_view_generator_overview("regions-region$region", $report);

  return $res;
}

?>
