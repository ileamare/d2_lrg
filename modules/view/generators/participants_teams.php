<?php
include_once("$root/modules/view/functions/team_card.php");

function rg_generator_participants_teams($context) {
  if(!sizeof($context)) return "";

  $res = "<div class=\"content-cards\">";
  foreach($context as $team_id => $team) {
    $res .= team_card($team_id);
  }
  $res .= "</div>";

  return $res;
}

?>
