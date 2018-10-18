<?php
include_once("$root/modules/view/functions/player_card.php");

function rg_generator_participants_players($context) {
  if(!sizeof($context)) return "";
  
  $res = "<div class=\"content-cards\">";
  foreach($context as $player_id => $player) {
    $res .= player_card($player_id);
  }
  $res .= "</div>";

  return $res;
}

?>
