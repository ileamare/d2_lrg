<?php

include_once($root."/modules/view/functions/hero_name.php");
include_once($root."/modules/view/functions/player_name.php");

function rg_generator_pvp_profile($table_id, $pvp_context, $heroes_flag = true) {
  $res = "<table id=\"$table_id\" class=\"list sortable\">";
  $i = 0;

  if(!sizeof($pvp_context)) return "";

  if (isset( array_values($pvp_context)[0]['diff'] ))
    $nodiff = false;
  else
    $nodiff = true;

  $res .= "<thead><tr>".
          ($heroes_flag && !$i++ ? "<th width=\"1%\"></th>" : "").
          "<th data-sortInitialOrder=\"asc\">".locale_string("opponent")."</th>".
          "<th>".locale_string("winrate")."</th>".
          (!$nodiff ? "<th>".locale_string("diff")."</th>" : "").
          "<th>".locale_string("matches")."</th>".
          "<th>".locale_string("won")."</th>".
          "<th>".locale_string("lost")."</th></tr></thead>";

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
