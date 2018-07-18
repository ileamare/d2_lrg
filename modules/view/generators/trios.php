<?php

include_once($root."/modules/view/functions/hero_name.php");
include_once($root."/modules/view/functions/player_name.php");

function rg_generator_trios($table_id, $context, $context_matches, $heroes_flag = true) {
  $i = 0;
  $id = $heroes_flag ? "heroid" : "playerid";

  foreach($context as $trio) {
      if(isset($trio['expectation']))
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
          (($heroes_flag && $i++) ? "<th width=\"1%\"></th>" : "").
          "<th onclick=\"sortTable(".($i++).",'$table_id');\">".locale_string($heroes_flag ? "hero" : "player")." 3</th>".
          "<th onclick=\"sortTableNum(".($i++).",'$table_id');\">".locale_string("matches")."</th>".
          "<th onclick=\"sortTableNum(".($i++).",'$table_id');\">".locale_string("winrate")."</th>".
          ($expectation ? "<th onclick=\"sortTableNum(".($i++).",'$table_id');\">".locale_string("pair_expectation")."</th>" : "").
          ($expectation ? "<th onclick=\"sortTableNum(".($i++).",'$table_id');\">".locale_string("pair_deviation")."</th>" : "").
          "</tr>";

  foreach($context as $trio) {
    $res .= "<tr".(isset($report['hero_pairs_matches']) ?
            " onclick=\"showModal('".implode($report['hero_pairs_matches'][$trio[$id.'1'].'-'.$trio[$id.'2'].'-'.$trio[$id.'3']], ", ").
                                  "', '".locale_string("matches")."');\"" : "").">".
             ($heroes_flag ? "<td>".hero_portrait($trio[$id.'1'])."</td>" : "").
             "<td>".($heroes_flag ? hero_name($trio[$id.'1']) : player_name($trio[$id.'1']))."</td>".
             ($heroes_flag ? "<td>".hero_portrait($trio[$id.'2'])."</td>" : "").
             "<td>".($heroes_flag ? hero_name($trio[$id.'2']) : player_name($trio[$id.'2']))."</td>".
             ($heroes_flag ? "<td>".hero_portrait($trio[$id.'3'])."</td>" : "").
             "<td>".($heroes_flag ? hero_name($trio[$id.'3']) : player_name($trio[$id.'3']))."</td>".
             "<td>".$trio['matches']."</td>".
             "<td>".number_format($trio['winrate']*100,2)."%</td>".
             ($expectation ? "<td>".number_format($trio['expectation'], 3)."</td>" : "").
             ($expectation ? "<td>".number_format($trio['matches']-$trio['expectation'], 3)."</td>" : "").
             "</tr>";
  }
  $res .= "</table>";

  return $res;
}

?>
