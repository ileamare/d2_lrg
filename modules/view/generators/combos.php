<?php

include_once($root."/modules/view/functions/hero_name.php");
include_once($root."/modules/view/functions/player_name.php");

function rg_generator_combos($table_id, $context, $context_matches, $heroes_flag = true) {
  $i = 0;
  $id = $heroes_flag ? "heroid" : "playerid";

  foreach($context as $combo) {
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

      if(isset($combo[$id.'3']))
        $trios = true;
      else
        $trios = false;

      break;
  }

  $res = "<table id=\"$table_id\" class=\"list wide\"><tr class=\"thead\">".
         (($heroes_flag && !$i++) ? "<th width=\"1%\"></th>" : "").
         "<th onclick=\"sortTable(".($i++).",'$table_id');\">".locale_string($heroes_flag ? "hero" : "player")." 1</th>".
         (($heroes_flag && $i++) ? "<th width=\"1%\"></th>" : "").
         "<th onclick=\"sortTable(".($i++).",'$table_id');\">".locale_string($heroes_flag ? "hero" : "player")." 2</th>".
         (
           $trios ?
           (($heroes_flag && $i++) ? "<th width=\"1%\"></th>" : "").
           "<th onclick=\"sortTable(".($i++).",'$table_id');\">".locale_string($heroes_flag ? "hero" : "player")." 3</th>" :
           ""
           ).
         "<th onclick=\"sortTableNum(".($i++).",'$table_id');\">".locale_string("matches")."</th>".
         "<th onclick=\"sortTableNum(".($i++).",'$table_id');\">".locale_string("winrate")."</th>".
         ($expectation ? "<th onclick=\"sortTableNum(".($i++).",'$table_id');\">".locale_string("pair_expectation")."</th>".
                         "<th onclick=\"sortTableNum(".($i++).",'$table_id');\">".locale_string("pair_deviation")."</th>".
                         "<th onclick=\"sortTableNum(".($i++).",'$table_id');\">".locale_string("percentage")."</th>" : "").
         ($lane_rate ? "<th onclick=\"sortTableNum(".($i++).",'$table_id');\">".locale_string("lane_rate")."</th>" : "").
         ($lane ? "<th onclick=\"sortTableNum(".($i++).",'$table_id');\">".locale_string("lane")."</th>" : "").
         ((is_array($context_matches) && !empty($context_matches)) ? "<th>".locale_string("matchlinks")."</th>" : "").
         "</tr>";


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
                ($expectation ? "<td>".number_format($combo['expectation'], 3)."</td>".
                                "<td>".number_format($combo['matches']-$combo['expectation'], 3)."</td>".
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
                      locale_string("matches")."</a></th>" :
                  "").
            "</tr>";
  }
  $res .= "</table>";

  return $res;
}

?>