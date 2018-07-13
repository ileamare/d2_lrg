<?php
include_once($root."/modules/view/functions/hero_name.php");
include_once($root."/modules/view/functions/player_name.php");

function rg_generator_sides($table_id, $context, $heroes_flag = true) {
  $elements = [];
  $id = $heroes_flag ? "heroid" : "playerid";

  for ($i=0; $i<2 && !isset($keys); $i++) {
      if(isset($context[$i][0])) {
        $keys = array_keys($context[$i][0]);
        break;
      }
  }

  for ($side = 0; $side < 2; $side++) {
    foreach($context[$side] as $el) {
      if (!isset($elements[$el[$id]])) {
        $elements[$el[$id]] = [
          -1 => [
            "matches" => $el['matches']
          ],
          0 => [],
          1 => []
        ];
      } else {
        $elements[$el[$id]][-1]["matches"] += $el['matches'];
      }
      $elements[$el[$id]][$side] = $el;
    }
  }

  foreach($elements as $elid => $el) {
    $elements[$elid][-1]["diff"] = $elements[$elid][1]["winrate"] - $elements[$elid][0]["winrate"];
  }

  uasort($elements, function($a, $b) {
    if($a[-1]['diff'] == $b[-1]['diff']) return 0;
    else return ($a[-1]['diff'] < $b[-1]['diff']) ? 1 : -1;
  });

  $res = "<table id=\"$table_id\" class=\"list wide\">".
            "<tr class=\"thead overhead\"><th colspan=\"".(2+$heroes_flag)."\"></th>".
            "<th class=\"separator\" colspan=\"2\">".locale_string("rad_view")."</th>".
            "<th class=\"separator\" colspan=\"".(sizeof($keys)-1)."\">".locale_string("radiant")."</th>".
            "<th class=\"separator\" colspan=\"".(sizeof($keys)-1)."\">".locale_string("dire")."</th></tr>";

  $i = $heroes_flag ? 1 : 0;
  $res .= "<tr class=\"thead\">".
            ($heroes_flag ? "<th width=\"1%\"></th>" : "").
            "<th onclick=\"sortTable(".($i++).",'$table_id');\">".locale_string($heroes_flag ? "hero" : "player")."</th>".
            "<th onclick=\"sortTableNum(".($i++).",'$table_id');\">".locale_string("matches")."</th>".
            "<th onclick=\"sortTableNum(".($i++).",'$table_id');\" class=\"separator\">".locale_string("rad_ratio")."</th>".
            "<th onclick=\"sortTableNum(".($i++).",'$table_id');\">".locale_string("rad_diff")."</th>";

  for ($side = 1; $side >= 0; $side--) {
    for($k=1, $end=sizeof($keys); $k < $end; $k++) {
      $res .= "<th ".($k==1 ? "class=\"separator\"" : "")." onclick=\"sortTableNum(".($i++).",'$table_id');\">".locale_string($keys[$k])."</th>";
    }
  }
  $res .= "</tr>";
  foreach ($elements as $elid => $el) {
    if(empty($el[0])) {
      $el[0]["matches"] = 0;
      $el[0]["winrate"] = 0;
    }
    if(empty($el[1])) {
      $el[1]["matches"] = 0;
      $el[1]["winrate"] = 0;
    }

    $res .= "<tr><td>".($heroes_flag ? hero_portrait($elid)."</td><td>".hero_name($elid) : player_name($elid))."</td>".
            "<td>".$el[-1]['matches']."</td>".
            "<td class=\"separator\">".number_format($el[1]["matches"]*100/$el[-1]["matches"],2)."%</td>".
            "<td>".number_format($el[-1]["diff"]*100,2)."%</td>";

    for ($side = 1; $side >= 0; $side--) {
      $res .= "<td class=\"separator\">".number_format($el[$side][ "matches" ])."</th>";
      $res .= "<td>".number_format($el[$side][ "winrate" ]*100, 2)."%</th>";
      for($k=3, $end=sizeof($keys); $k < $end; $k++) {
        $res .= "<td>".number_format($el[$side][ $keys[$k] ], 2)."</th>";
      }
    }
    $res .= "</tr>";
  }
  $res .= "</table>";

  return $res;
}
?>
