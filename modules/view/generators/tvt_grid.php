<?php
include_once("$root/modules/view/functions/team_name.php");

function rg_generator_tvt_grid($table_id, $context) {
  $team_ids = array_keys($context);

  $res = "<table id=\"$table_id\" class=\"pvp wide\">";

  $res .= "<tr class=\"thead\"><th></th>";
  foreach($context as $tid => $data) {
    $res .= "<th><span>".team_tag($tid)."</span></th>";
  }
  $res .= "</tr>";

  foreach($context as $tid => $teamline) {
    $res .= "<tr><td>".team_name($tid)."</td>";
    for($i=0, $end = sizeof($team_ids); $i<$end; $i++) {
      if($tid == $team_ids[$i]) {
        $res .= "<td class=\"transparent\"></td>";
      } else if($teamline[$team_ids[$i]]['matches'] == 0) {
        $res .= "<td>-</td>";
      } else {
        $res .= "<td".
                ($teamline[$team_ids[$i]]['winrate'] > 0.55 ? " class=\"high-wr\"" : (
                      $teamline[$team_ids[$i]]['winrate'] < 0.45 ? " class=\"low-wr\"" : ""
                    )
                  )." onclick=\"showModal('".locale_string("matches").": ".$context[$tid][$team_ids[$i]]['matches']
                        ."<br />".locale_string("winrate").": ".number_format($context[$tid][$team_ids[$i]]['winrate']*100,2)
                        ."%<br />".locale_string("won")." ".$context[$tid][$team_ids[$i]]['won']." - "
                                 .locale_string("lost")." ".$context[$tid][$team_ids[$i]]['lost'].(
                                   isset($context[$tid][$team_ids[$i]]['matchids']) ?
                                    "<br />MatchIDs: ".implode($context[$tid][$team_ids[$i]]['matchids'], ", ")
                                    : "").
                        "','".team_name($tid)." ".locale_string("vs")." ".team_name($team_ids[$i])."')\">".
                        number_format($teamline[$team_ids[$i]]['winrate']*100,0)."</td>";
      }
    }
    $res .= "</tr>";
  }

  $res .= "</table>";

  return $res;
}

?>
