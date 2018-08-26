<?php

include_once($root."/modules/view/functions/hero_name.php");
include_once($root."/modules/view/functions/player_name.php");

function rg_generator_pvp_grid($table_id, $contesters, $context) {
  $res = "<table  class=\"pvp wide\">";

  $res .= "<tr class=\"thead\"><th></th>";
  foreach($contesters as $elid => $name) {
    $res .= "<th><span>".$name."</span></th>";
  }
  $res .= "</tr>";

  $player_ids = array_keys($contesters);

  foreach($context as $elid => $playerline) {
    $res .= "<tr><td>".$contesters[$elid]."</td>";
    for($i=0, $end = sizeof($player_ids); $i<$end; $i++) {
      if($elid == $player_ids[$i]) {
        $res .= "<td class=\"transparent\"></td>";
      } else if(!isset($playerline[$player_ids[$i]]) || $playerline[$player_ids[$i]]['matches'] == 0) {
        $res .= "<td>-</td>";
      } else {
        $res .= "<td".
                ($playerline[$player_ids[$i]]['winrate'] > 0.55 ? " class=\"high-wr\"" : (
                      $playerline[$player_ids[$i]]['winrate'] < 0.45 ? " class=\"low-wr\"" : ""
                    )
                  )." onclick=\"showModal('".locale_string("matches").": ".$context[$elid][$player_ids[$i]]['matches']
                        ."<br />".locale_string("winrate").": ".number_format($context[$elid][$player_ids[$i]]['winrate']*100,2)
                        ."%<br />".locale_string("won")." ".$context[$elid][$player_ids[$i]]['won']." - "
                                 .locale_string("lost")." ".$context[$elid][$player_ids[$i]]['lost'].(
                                   isset($context[$elid][$player_ids[$i]]['matchids']) ?
                                    "<br />MatchIDs: ".implode($context[$elid][$player_ids[$i]]['matchids'], ", ")
                                    : "").
                        "','".$contesters[$elid]." vs ".$contesters[$player_ids[$i]]."')\">".
                    number_format($playerline[$player_ids[$i]]['winrate']*100,0)."</td>";
      }
    }
    $res .= "</tr>";
  }

  $res .= "</table>";

  return $res;
}

?>
