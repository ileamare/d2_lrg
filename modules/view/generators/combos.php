<?php

include_once($root."/modules/view/functions/hero_name.php");
include_once($root."/modules/view/functions/player_name.php");

function rg_generator_combos($table_id, $context, $context_matches, $heroes_flag = true) {
  $id = $heroes_flag ? "heroid" : "playerid";

  if(!sizeof($context))
    return "";

  # Figuring out what kind of context we have here

  $combo = array_values($context)[0];

  if(isset($combo['lane_rate']))
    $lane_rate = true;
  else
    $lane_rate = false;

  if(isset($combo['lane']))
    $lane = true;
  else
    $lane = false;

  if(isset($combo['expectation']))
    $expectation = true;
  else
    $expectation = false;

  if(isset($combo['wr_diff']))
    $wr_diff = true;
  else
    $wr_diff = false;

  if(isset($combo[$id.'3']))
    $trios = true;
  else
    $trios = false;

  unset($combo);

  $res = "<table id=\"$table_id\" class=\"list wide sortable\"><thead><tr class=\"thead\">".
         ($heroes_flag ? "<th colspan=\"4\">".locale_string("heroes")."</th>" : "<th colspan=\"2\">".locale_string("players")."</th>").
         (
           $trios ?
           (($heroes_flag) ? "<th width=\"1%\"></th>" : "").
           "<th data-sortInitialOrder=\"asc\">".locale_string($heroes_flag ? "hero" : "player")." 3</th>" :
           ""
           ).
         "<th>".locale_string("matches")."</th>".
         "<th>".locale_string("winrate")."</th>".
         ($wr_diff ? "<th>".locale_string("winrate_diff")."</th>" : "").
         ($expectation ? "<th>".locale_string("pair_expectation")."</th>".
                         "<th>".locale_string("pair_deviation")."</th>".
                         "<th>".locale_string("percentage")."</th>" : "").
         ($lane_rate ? "<th>".locale_string("lane_rate")."</th>" : "").
         ($lane ? "<th>".locale_string("lane")."</th>" : "").
         ((is_array($context_matches) && !empty($context_matches)) ? "<th>".locale_string("matchlinks")."</th>" : "").
         "</tr></thead><tbody>";


  foreach($context as $combo) {
    $res .= "<tr>".
                ($heroes_flag ? "<td>".hero_portrait($combo[$id.'1'])."</td>" : "").
                "<td>".($heroes_flag ? hero_name($combo[$id.'1']) : player_name($combo[$id.'1']))."</td>".
                ($heroes_flag ? "<td>".hero_portrait($combo[$id.'2'])."</td>" : "").
                "<td>".($heroes_flag ? hero_name($combo[$id.'2']) : player_name($combo[$id.'2']))."</td>".
                (
                  $trios ?
                  ($heroes_flag ? "<td>".hero_portrait($combo[$id.'3'])."</td>" : "").
                  "<td>".($heroes_flag ? hero_name($combo[$id.'3']) : player_name($combo[$id.'2']))."</td>" :
                  ""
                  ).
                "<td>".$combo['matches']."</td>".
                "<td>".number_format($combo['winrate']*100,2)."%</td>".
                ($wr_diff ? "<td>".number_format($combo['wr_diff']*100,2)."%</td>" : "").
                ($expectation ? "<td>".number_format($combo['expectation'], 0)."</td>".
                                "<td>".number_format($combo['matches']-$combo['expectation'], 0)."</td>".
                                "<td>".number_format(($combo['matches']-$combo['expectation'])*100/$combo['matches'], 2)."%</td>" : "").
                ($lane_rate ? "<td>".number_format($combo['lane_rate']*100, 2)."%</td>" : "").
                ($lane ? "<td>".locale_string("lane_".$combo['lane'])."</td>" : "").
                ((is_array($context_matches) && !empty($context_matches)) ?
                  "<td><a onclick=\"showModal('".htmlspecialchars(
                      join_matches($context_matches[ $combo[$id.'1'].'-'.$combo[$id.'2'].($trios ? '-'.$combo[$id.'3'] : "") ])).
                      "', '".locale_string("matches")." : ".
                      ($heroes_flag ? hero_name($combo[$id.'1']) : player_name($combo[$id.'1']))." + ".
                      ($heroes_flag ? hero_name($combo[$id.'2']) : player_name($combo[$id.'2'])).
                      ($trios ? " + ".($heroes_flag ? hero_name($combo[$id.'3']) : player_name($combo[$id.'3'])) : "")
                      ."');\">".
                      locale_string("matches")."</a></td>" :
                  "").
            "</tr>";
  }
  $res .= "</tbody></table>";

  return $res;
}

?>
