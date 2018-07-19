<?php

include_once($root."/modules/view/generators/haverages.php");

function rg_view_generate_regions_heroes_haverages($region, $reg_report) {
  global $report;

  $res = "<div class=\"content-text\">".
          locale_string("desc_heroes_avg", ["lim" => $reg_report['settings']['limiter_lower']+1 ]).
          "</div>";

  $res .= rg_generator_haverages("region$region-heroes-havgs", $reg_report['haverages_heroes']);

  return $res;
}

?>
