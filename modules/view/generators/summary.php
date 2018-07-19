<?php
include_once($root."/modules/view/functions/hero_name.php");
include_once($root."/modules/view/functions/player_name.php");

function rg_generator_summary($table_id, $context, $hero_flag = true) {
  foreach($context as $c) {
    $keys = array_keys($c);
    break;
  }
  $id = $hero_flag ? 'heroid' : 'playerid';
  $res = "<table id=\"$table_id\" class=\"list wide\"><tr class=\"thead\">".
          ($hero_flag ? "<th width=\"1%\"></th>" : "").
          "<th onclick=\"sortTable(".(0+$hero_flag).",'$table_id');\">".locale_string($hero_flag ? "hero" : "player")."</th>";

  for($k=1, $end=sizeof($keys); $k < $end; $k++) {
    $res .= "<th onclick=\"sortTableNum(".($k+$hero_flag).",'$table_id');\">".locale_string($keys[$k])."</th>";
  }
  $res .= "</tr>";

  foreach($context as $el) {
    $res .= "<tr><td>".
              ($hero_flag ? hero_portrait($el[$id])."</td><td>".hero_name($el[$id]) : player_name($el[$id])).
            "</td>".
            "<td>".$el['matches_s']."</td>".
            "<td>".number_format($el['winrate_s']*100,1)."%</td>";

    for($k=3, $end=sizeof($keys); $k < $end; $k++) {
      if(is_numeric($el[$keys[$k]])) {
        if ($el[$keys[$k]] > 10)
          $res .= "<td>".number_format($el[$keys[$k]],1)."</td>";
        else if ($el[$keys[$k]] > 1)
          $res .= "<td>".number_format($el[$keys[$k]],2)."</td>";
        else
          $res .= "<td>".number_format($el[$keys[$k]],3)."</td>";
      } else {
        $res .= "<td>".$el[$keys[$k]]."</td>";
      }
    }
    $res .= "</tr>";
  }
  $res .= "</table>";
  unset($keys);

  return $res;
}

?>
