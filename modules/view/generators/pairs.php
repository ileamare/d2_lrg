<?php

include_once($root."/modules/view/functions/hero_name.php");
include_once($root."/modules/view/functions/player_name.php");

function rg_generator_pairs($table_id, $context, $context_matches, $heroes_flag = true) {
  $i = 0;
  $id = $heroes_flag ? "heroid" : "playerid";

  foreach($context as $pair) {
      if(isset($pair['lane_rate']))
        $lane_rate = true;
      else
        $lane_rate = false;

      if(isset($pair['lane']))
        $lane = true;
      else
        $lane = false;

      if(isset($pair['expectation']))
        $expectation = true;
      else
        $expectation = false;

      break;
  }

  $res = "<table id=\"$table_id\" class=\"list wide\"><tr class=\"thead\">".
         (($heroes_flag && !$i++) ? "<th width=\"1%\"></th>" : "").
         "<th onclick=\"sortTable(".($i++).",'$table_id');\">".locale_string($heroes_flag ? "hero" : "player")." 1</th>".
         (($heroes_flag && $i++) ? "<th width=\"1%\"></th>" : "").
         "<th onclick=\"sortTable(".($i++).",'$table_id');\">".locale_string($heroes_flag ? "hero" : "player")." 2</th>".
         "<th onclick=\"sortTableNum(".($i++).",'$table_id');\">".locale_string("matches")."</th>".
         "<th onclick=\"sortTableNum(".($i++).",'$table_id');\">".locale_string("winrate")."</th>".
         ($expectation ? "<th onclick=\"sortTableNum(".($i++).",'$table_id');\">".locale_string("pair_expectation")."</th>".
                         "<th onclick=\"sortTableNum(".($i++).",'$table_id');\">".locale_string("pair_deviation")."</th>".
                         "<th onclick=\"sortTableNum(".($i++).",'$table_id');\">".locale_string("percentage")."</th>" : "").
         ($lane_rate ? "<th onclick=\"sortTableNum(".($i++).",'$table_id');\">".locale_string("lane_rate")."</th>" : "").
         ($lane ? "<th onclick=\"sortTableNum(".($i++).",'$table_id');\">".locale_string("lane")."</th>" : "").
         "</tr>";


  foreach($context as $pair) {
    $res .= "<tr".(is_array($context_matches) && !empty($context_matches) ?
            " onclick=\"showModal('".htmlspecialchars(join_matches($context_matches[$pair[$id.'1'].'-'.$pair[$id.'2']])).
                "', '".locale_string("matches")."');\"" : "").">".
                ($heroes_flag ? "<td>".hero_portrait($pair[$id.'1'])."</td>" : "").
                "<td>".($heroes_flag ? hero_name($pair[$id.'1']) : player_name($pair[$id.'1']))."</td>".
                ($heroes_flag ? "<td>".hero_portrait($pair[$id.'2'])."</td>" : "").
                "<td>".($heroes_flag ? hero_name($pair[$id.'2']) : player_name($pair[$id.'2']))."</td>".
                "<td>".$pair['matches']."</td>".
                "<td>".number_format($pair['winrate']*100,2)."%</td>".
                ($expectation ? "<td>".number_format($pair['expectation'], 3)."</td>".
                                "<td>".number_format($pair['matches']-$pair['expectation'], 3)."</td>".
                                "<td>".number_format(($pair['matches']-$pair['expectation'])*100/$pair['matches'], 2)."%</td>" : "").
                ($lane_rate ? "<td>".number_format($pair['lane_rate']*100, 2)."%</td>" : "").
                ($lane ? "<td>".locale_string("lane_".$pair['lane'])."</td>" : "").
            "</tr>";
  }
  $res .= "</table>";

  return $res;
}

?>
