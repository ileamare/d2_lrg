<?php
include_once($root."/modules/view/functions/player_name.php");
include_once($root."/modules/view/functions/team_name.php");
include_once($root."/modules/view/functions/links.php");
include_once($root."/modules/view/functions/hero_name.php");
include_once($root."/modules/view/functions/convert_time.php");

function rg_generator_records($context) {
  if(!sizeof($context)) return "";

  $res = "<table id=\"records-module-table\" class=\"list sortable\"><thead>".
                             "<th>".locale_string("record")."</th>".
                             "<th>".locale_string("match")."</th>".
                             "<th>".locale_string("value")."</th>".
                             "<th>".locale_string("player")."</th>".
                             "<th>".locale_string("hero")."</th></tr></thead>";
  foreach($context as $key => $record) {
    $res .= "<tr><td>".locale_string($key)."</td>
          <td>". ($record['matchid'] ?
                    match_link($record['matchid']) :
               "")."</td>
          <td>".(
            strpos($key, "duration") !== FALSE || strpos($key, "_len") !== FALSE ||
            strpos($key, "shortest") !== FALSE || strpos($key, "longest") !== FALSE ?
              convert_time($record['value']) :
              ( $record['value'] - floor($record['value']) != 0 ? number_format($record['value'], 2) : number_format($record['value'], 0) )
            ).
          "</td><td>". ($record['playerid'] ?
                    (strstr($key, "_team") != FALSE ?
                      team_link($record['playerid']) :
                      player_link($record['playerid'])
                    ) :
               "")."</td>
          <td>".($record['heroid'] ? hero_full($record['heroid']) : "").
         "</td>
      </tr>";
  }

  $res .= "</table>";

  return $res;
}

?>
