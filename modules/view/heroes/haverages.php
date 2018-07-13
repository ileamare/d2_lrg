<?php
//echo dirname(__FILE__);
include_once($root."/modules/view/generators/haverages.php");

$modules['heroes']['haverages'] = "";

function rg_view_generate_heroes_haverages() {
  global $report;

  $res = "<div class=\"content-text\">".
          locale_string("desc_heroes_avg", ["lim" => $report['settings']['limiter_triplets']+1 ]).
          "</div>";

  $res .= rg_generator_haverages("heroes-havgs", $report['averages_heroes']);

  return $res;
}

?>
