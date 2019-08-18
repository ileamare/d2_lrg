<?php
include_once("$root/modules/view/functions/team_card.php");

function rg_generator_participants_teams($context) {
  global $report;
  if(!sizeof($context)) return "";

  $res = "<div class=\"content-cards\">";
  foreach($context as $team_id => $team) {
    if (isset($report['teams_interest']) && !in_array($team_id, $report['teams_interest'])) continue;
    $res .= team_card($team_id);
  }
  $res .= "</div>";

  return $res;
}

?>
