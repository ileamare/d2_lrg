<?php

include_once($root."/modules/view/functions/hero_name.php");
include_once($root."/modules/view/functions/player_name.php");

function rg_generator_positions_overview($table_id, $context, $hero_flag = true) {
  $id = $hero_flag ? "heroid" : "playerid";

  $position_overview_template = array("total" => 0);
  for ($i=1; $i>=0 && !isset($keys); $i--) {
    for ($j=1; $j<6 && $j>0; $j++) {
      if (!$i) { $j = 0; }
      if(isset($context[$i][$j][0])) {
        $keys = array_keys($context[$i][$j][0]);
        break;
      }
      if (!$i) { break; }
    }
  }

  for ($i=1; $i>=0; $i--) {
    for ($j=1; $j<6 && $j>0; $j++) {
      if (!$i) { $j = 0; }
      if(sizeof($context[$i][$j]))
        $position_overview_template["$i.$j"] = array("matches" => 0, "wr" => 0);
      if (!$i) { break; }
    }
  }

  $overview = [];

  for ($i=1; $i>=0; $i--) {
    for ($j=1; $j<6 && $j>0; $j++) {
      if (!$i) { $j = 0; }

      foreach($context[$i][$j] as $el) {
        if (!isset($overview[ $el[$id] ])) $overview[ $el[$id] ] = $position_overview_template;

        $overview[ $el[$id] ]["$i.$j"]['matches'] = $el['matches_s'];
        $overview[ $el[$id] ]["$i.$j"]['wr'] = $el['winrate_s'];
        $overview[ $el[$id] ]["total"] += $el['matches_s'];
      }

      if (!$i) { break; }
    }
  }
  uasort($overview, function($a, $b) {
    if($a['total'] == $b['total']) return 0;
    else return ($a['total'] < $b['total']) ? 1 : -1;
  });

  $res = "<table id=\"$table_id\" class=\"list wide\"><tr class=\"thead overhead\"><th width=\"20%\" colspan=\"".(2+$hero_flag)."\"></th>";

  $heroline = "<tr class=\"thead\">".
                ($hero_flag ?
                  "<th width=\"1%\"></th><th onclick=\"sortTable(1,'$table_id');\">".locale_string("hero")."</th>" :
                  "<th onclick=\"sortTable(0,'$table_id');\">".locale_string("player")."</th>"
                ).
                "<th onclick=\"sortTableNum(1,'$table_id');\">".locale_string("matches_s")."</th>";
  $i = 2;
  foreach($position_overview_template as $k => $v) {
    if ($k == "total") continue;

    $res .= "<th colspan=\"3\" class=\"separator\">".locale_string("position_$k")."</th>";
    $heroline .= "<th onclick=\"sortTableNum(".($hero_flag+$i++).",'$table_id');\"  class=\"separator\">".locale_string("matches_s")."</th>".
                  "<th onclick=\"sortTableNum(".($hero_flag+$i++).",'$table_id');\">".locale_string("ratio")."</th>".
                  "<th onclick=\"sortTableNum(".($hero_flag+$i++).",'$table_id');\">".locale_string("winrate_s")."</th>";
  }
  $res .= "</tr>".$heroline."</tr>";

  foreach ($overview as $elid => $el) {
    $res .= "<tr><td>".
        ($hero_flag ? hero_portrait($elid)."</td><td>".hero_name($elid) : player_name($elid)).
        "</td><td>".$el['total']."</td>";
    foreach($el as $v) {
      if (!is_array($v)) continue;

      if(!$v['matches']) {
        $res .= "<td class=\"separator\">-</td>".
                      "<td>-</td>".
                      "<td>-</th>";
      } else {
        $res .= "<td class=\"separator\">".$v['matches']."</td>".
                    "<td>".number_format($v['matches']*100/$el['total'],2)."%</td>".
                    "<td>".number_format($v['wr']*100,2)."%</th>";
      }
    }
    $res .= "</tr>";
  }
  $res .= "</table>";

  return $res;
}

?>
