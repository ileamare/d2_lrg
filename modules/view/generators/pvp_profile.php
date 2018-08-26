<?php

include_once($root."/modules/view/functions/hero_name.php");
include_once($root."/modules/view/functions/player_name.php");

function rg_generator_pvp_profile($table_id, $pvp_context, $heroes_flag = true) {
  $res = "<table id=\"$table_id\" class=\"list\">";
  $i = 0;

  foreach($pvp_context as $elid_op => $data) {
    if (isset($data['diff'])) $nodiff = false;
    else $nodiff = true;

    break;
  }

  $res .= "<tr class=\"thead\">".
          ($heroes_flag && !$i++ ? "<th width=\"1%\"></th>" : "").
          "<th onclick=\"sortTable(".($i++).",'$table_id');\">".locale_string("opponent")."</th>".
          "<th onclick=\"sortTableNum(".($i++).",'$table_id');\">".locale_string("winrate")."</th>".
          (!$nodiff ? "<th onclick=\"sortTableNum(".($i++).",'$table_id');\">".locale_string("diff")."</th>" : "").
          "<th onclick=\"sortTableNum(".($i++).",'$table_id');\">".locale_string("matches")."</th>".
          "<th onclick=\"sortTableNum(".($i++).",'$table_id');\">".locale_string("won")."</th>".
          "<th onclick=\"sortTableNum(".($i++).",'$table_id');\">".locale_string("lost")."</th></tr>";

  if ($nodiff) {
    uasort($pvp_context, function($a, $b) {
      if($a['winrate'] == $b['winrate']) return 0;
      else return ($a['winrate'] < $b['winrate']) ? 1 : -1;
    });
  } else {
    uasort($pvp_context, function($a, $b) {
      if($a['diff'] == $b['diff']) return 0;
      else return ($a['diff'] < $b['diff']) ? 1 : -1;
    });
  }

  foreach($pvp_context as $elid_op => $data) {
    $res .= "<tr ".(isset($data['matchids']) ?
                      "onclick=\"showModal('".implode($data['matchids'], ", ")."','".locale_string("matches")."')\"" :
                      "").">".
            ($heroes_flag ? "<td>".hero_portrait($elid_op)."</td>" : "").
            "<td>".($heroes_flag ? hero_name($elid_op) : player_name($elid_op))."</th>".
            "<td>".number_format($data['winrate']*100,2)."%</th>".
            (!$nodiff ? "<td>".number_format($data['diff']*100,2)."%</th>" : "").
            "<td>".$data['matches']."</th>".
            "<td>".$data['won']."</th>".
            "<td>".$data['lost']."</th></tr>";
  }

  $res .= "</table>";

  return $res;
}

?>
