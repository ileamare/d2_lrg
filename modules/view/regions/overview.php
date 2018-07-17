<?php

function rg_view_generate_regions_overview($region, $report) {
  global $meta;
  global $modules;

  $res = "<table class=\"list\" id=\"region$region-overview-table\">";
  foreach($report['main'] as $key => $value) {
    $res .= "<tr><td>".locale_string($key)."</td><td>".$value."</td></tr>";
  }
  $res .= "</table>";

  return $res;
}

?>
