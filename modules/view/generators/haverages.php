<?php
include_once($root."/modules/view/functions/hero_name.php");
include_once($root."/modules/view/functions/player_name.php");

function rg_generator_haverages($table_id, $context, $hero_flag = true) {
  $res = "<div class=\"small-list-wrapper\">";
  $id = $hero_flag ? "heroid" : "playerid";
  foreach($context as $key => $avg) {
    $res .= "<table id=\"$table_id-".$key."\" class=\"list list-fixed list-small\">".
            "<caption>".locale_string($key)."</caption><thead><tr>".
            ($hero_flag ? "<th width=\"13%\"></th>" : "").
            "<th width=\"".($hero_flag ? 47 : 60)."%\">".locale_string($hero_flag ? "hero" : "player")."</th>".
            "<th>".locale_string("value")."</th></tr></thead>";
    foreach($avg as $el) {
      $res .= "<tr>".($hero_flag ? "<td>".hero_portrait($el[$id])."</td>" : "").
              "<td>".($hero_flag ?
                      hero_name($el[$id]) :
                      ( stripos($key, "team") !== FALSE ?
                        team_name($el[$id]) :
                        player_name($el[$id])
                      )
                    ).
              "</td><td>".number_format($el['value'],2)."</td></tr>";
    }
    $res .= "</table>";
  }
  $res .= "</div>";

  return $res;
}

?>
