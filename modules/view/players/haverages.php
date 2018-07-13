<?php

include_once($root."/modules/view/generators/haverages.php");

$modules['players']['haverages'] = "";

function rg_view_generate_players_haverages() {
  global $report;

  $res = "<div class=\"content-text\">".
          locale_string("desc_players_avg", ["lim" => $report['settings']['limiter_triplets']+1 ]).
          "</div>";

  $res .= rg_generator_haverages("players-havgs", $report['averages_players'], false);

  return $res;
}

?>
