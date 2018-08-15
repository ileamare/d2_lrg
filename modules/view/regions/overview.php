<?php
include_once "$root/modules/view/generators/overview.php";
include_once "$root/modules/functions/migrate_params.php";

function rg_view_generate_regions_overview($region, $reg_report) {
  global $meta;
  global $modules;
  global $report;

  if($report['settings']['regions']['detailed_overview']) {
    migrate_params($reg_report['settings'], $report['settings']['regions']);
    $res = rg_view_generator_overview("regions-region$region", $reg_report);
  } else {
    $res = "<table class=\"list\" id=\"region$region-table\">";
    foreach($reg_report['main'] as $key => $value) {
      $res .= "<tr><td>".locale_string($key)."</td><td>".$value."</td></tr>";
    }
    $res .= "</table>";
  }

  return $res;
}

?>
