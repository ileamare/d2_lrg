<?php
$modules['records'] = "";

function rg_view_generate_records($report) {
  $res = "<table id=\"records-module-table\" class=\"list\">
                            <tr class=\"thead\">
                              <th onclick=\"sortTable(0,'records-module-table');\">".locale_string("record")."</th>".
                             "<th onclick=\"sortTable(1,'records-module-table');\">".locale_string("match")."</th>
                              <th onclick=\"sortTableNum(2,'records-module-table');\">".locale_string("value")."</th>
                              <th onclick=\"sortTable(3,'records-module-table');\">".locale_string("player")."</th>
                              <th onclick=\"sortTable(4,'records-module-table');\">".locale_string("hero")."</th>
                            </tr>";
  foreach($report['records'] as $key => $record) {
    $res .= "<tr>
                              <td>".locale_string($key)."</td>
                              <td>". ($record['matchid'] ?
                                        "<a href=\"https://opendota.com/matches/".$record['matchid']."\" title=\"".locale_string("match")." ".$record['matchid']." on OpenDota\" target=\"_blank\" rel=\"noopener\">".$record['matchid']."</a>" :
                                        //"<a onclick=\"showModal('".htmlspecialchars(match_card($record['matchid'], $report['matches'][$record['matchid']], $report, $meta))."','');\" alt=\"Match ".$record['matchid']." on OpenDota\" target=\"_blank\">".$record['matchid']."</a>" :
                                   "")."</td>
                              <td>".number_format($record['value'],2)."</td>
                              <td>". ($record['playerid'] ?
                                        (strstr($key, "_team") != FALSE ?
                                          team_link($record['playerid']) :
                                          $report['players'][$record['playerid']]
                                        ) :
                                   "")."</td>
                              <td>".($record['heroid'] ? hero_full($record['heroid']) : "").
                             "</td>
                          </tr>";
  }

  $res .= "</table>";
  $res .= "<div class=\"content-text\">".locale_string("desc_records")."</div>";

  return $res;
}

?>
