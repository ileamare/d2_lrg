<?php
include_once($root."/modules/view/functions/hero_name.php");
include_once($root."/modules/view/functions/player_name.php");

function rg_generator_summary($table_id, $context, $hero_flag = true) {
  if(!sizeof($context)) return "";

  global $report;

  $keys = array_keys( array_values($context)[0] );

  $res = "<table id=\"$table_id\" class=\"list wide sortable\"><thead><tr>".
          ($hero_flag ? "<th class=\"sorter-no-parser\" width=\"1%\"></th>" : "").
          "<th data-sortInitialOrder=\"asc\">".locale_string($hero_flag ? "hero" : "player")."</th>";

  for($k=0, $end=sizeof($keys); $k < $end; $k++) {
    $res .= "<th>".locale_string($keys[$k])."</th>";
  }
  $res .= "</tr></thead><tbody>";

  foreach($context as $id => $el) {
    $res .= "<tr><td>".
              ($hero_flag ? hero_portrait($id)."</td><td>".hero_name($id) : player_name($id)).
            "</td>".
            "<td>".$el['matches_s']."</td>".
            "<td>".number_format($el['winrate_s']*100,1)."%</td>";

    for($k=2, $end=sizeof($keys); $k < $end; $k++) {
      $res .= "<td>";
      if (strpos($keys[$k], "duration") !== FALSE || strpos($keys[$k], "_len") !== FALSE) {
        $res .= floor($el[$keys[$k]]).":".floor(($el[$keys[$k]]-floor($el[$keys[$k]]))*60);
      } else if(is_numeric($el[$keys[$k]])) {
        if ($el[$keys[$k]] > 10)
          $res .= number_format($el[$keys[$k]],1);
        else if ($el[$keys[$k]] > 1)
          $res .= number_format($el[$keys[$k]],2);
        else
          $res .= number_format($el[$keys[$k]],3);
      } else {
        $res .= $el[$keys[$k]];
      }
      $res .= "</td>";
    }
    $res .= "</tr>";
  }
  $res .= "</tbody></table>";
  unset($keys);

  return $res;
}

?>
