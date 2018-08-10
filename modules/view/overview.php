<?php
include_once "modules/view/generators/overview.php";

$modules['overview'] = "";

# It will be updated later to support team-centric reports
function rg_view_generate_overview() {
  global $report;

  $res = "<div class=\"content-header\">".locale_string("summary")."</div><div class=\"block-content\">";
  $res .= locale_string("over-pregen-report");
  if ($report['league_id'] == null || $report['league_id'] == "custom")
    $res .= " ".locale_string("over-custom-league")." ".$report['league_name']." — ".$report['league_desc'].".";
  else
    $res .= " ".$report['league_name']." (".$report['league_id'].") — ".$report['league_desc'].".";

  $res .= "</div>";

  return rg_view_generator_overview("", $report, $res);
}

?>
