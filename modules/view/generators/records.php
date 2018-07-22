<?php
include_once($root."/modules/view/functions/player_name.php");
include_once($root."/modules/view/functions/team_name.php");
include_once($root."/modules/view/functions/links.php");
include_once($root."/modules/view/functions/hero_name.php");

function rg_generator_records($context) {
  $res = "<table id=\"records-module-table\" class=\"list\">
                            <tr class=\"thead\">
                              <th onclick=\"sortTable(0,'records-module-table');\">".locale_string("record")."</th>".
                             "<th onclick=\"sortTable(1,'records-module-table');\">".locale_string("match")."</th>
                              <th onclick=\"sortTableNum(2,'records-module-table');\">".locale_string("value")."</th>
                              <th onclick=\"sortTable(3,'records-module-table');\">".locale_string("player")."</th>
                              <th onclick=\"sortTable(4,'records-module-table');\">".locale_string("hero")."</th>
                            </tr>";
  foreach($context as $key => $record) {
    $res .= "<tr>
                              <td>".locale_string($key)."</td>
                              <td>". ($record['matchid'] ?
                                        match_link($record['matchid']) :
                                   "")."</td>
                              <td>".number_format($record['value'],2)."</td>
                              <td>". ($record['playerid'] ?
                                        (strstr($key, "_team") != FALSE ?
                                          team_link($record['playerid']) :
                                          player_name($record['playerid'])
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
