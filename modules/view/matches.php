<?php

$modules['matches'] = [];

include_once($root."/modules/view/generators/matches_list.php");

function rg_view_generate_matches() {
  global $report, $unset_module, $mod;
  if($mod == "matches") $unset_module = true;
  $res = [];
  $parent = "matches-";

  $res['list'] = "";
  if (check_module($parent."list")) {
    $res['list'] = rg_generator_matches_list("matches-list", $report['matches']);
  }

  $res['cards'] = "";
  if (check_module($parent."cards")) {
    krsort($report['matches']);
    $res['cards'] = "<div class=\"content-text\">".locale_string("desc_matches")."</div>";
    $res['cards'] .= "<div class=\"content-cards\">";
    foreach($report['matches'] as $matchid => $match) {
      $res['cards'] .= match_card($matchid);
    }
    $res['cards'] .= "</div>";
  }

  return $res;
}

?>
