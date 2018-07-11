<?php

function rg_generator_haverages($table_id, $context, $hero_flag = true) {
  $res = "<div class=\"small-list-wrapper\">";
  $id = $hero_flag ? "heroid" : "playerid";
  foreach($context as $key => $avg) {
    $res .= "<table id=\"$table_id-".$key."\" class=\"list list-fixed list-small\">".
            "<caption>".locale_string($key)."</caption><tr class=\"thead\">".
            ($hero_flag ? "<th width=\"13%\"></th>" : "").
            "<th width=\"".($hero_flag ? 47 : 60)."%\">".locale_string($hero_flag ? "hero" : "player")."</th>".
            "<th>".locale_string("value")."</th></tr>";
    foreach($avg as $el) {
      $res .= "<tr>".($hero_flag ? "<td>".hero_portrait($el[$id])."</td>" : "").
              "<td>".($el['heroid'] ? hero_name($el[$id]) : "").
              "</td><td>".number_format($el['value'],2)."</td></tr>";
    }
    $res .= "</table>";
  }
  $res .= "</div>";

  return $res;
}

?>
