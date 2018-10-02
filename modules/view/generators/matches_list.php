<?php

include_once($root."/modules/view/functions/hero_name.php");
include_once($root."/modules/view/functions/player_name.php");

function rg_generator_matches_list($table_id, $context) {
  global $report;
  $matches = array_keys($context);

  $i = 0;
  $res = "<table id=\"$table_id\" class=\"list sortable\"><thead><tr>".
          "<th>".locale_string("match")."</th>".
          "<th>".locale_string("radiant")."</th>".
          "<th>".locale_string("dire")."</th>".
          "<th>".locale_string("duration")."</th>".
          "<th>".locale_string("kills_combined")."</th>".
          "<th>".locale_string("date")."</th>".
        "</tr></thead><tbody>";
  foreach($matches as $mid) {
    if(isset($report['teams']) && isset($report['match_participants_teams'][$mid])) {
      if(isset($report['match_participants_teams'][$mid]['radiant']) &&
         isset($report['teams'][ $report['match_participants_teams'][$mid]['radiant'] ]['name']))
        $team_radiant = team_link($report['match_participants_teams'][$mid]['radiant']);
      else $team_radiant = "Radiant";
      if(isset($report['match_participants_teams'][$mid]['dire']) &&
         isset($report['teams'][ $report['match_participants_teams'][$mid]['dire'] ]['name']))
        $team_dire = team_link($report['match_participants_teams'][$mid]['dire']);
      else $team_dire = "Dire";
    } else {
      $team_radiant = locale_string("radiant");
      $team_dire = locale_string("dire");
    }

    $duration = (int)($report['matches_additional'][$mid]['duration']/3600);

    $duration = $duration ? $duration.":".(
          (int)($report['matches_additional'][$mid]['duration']%3600/60) < 10 ?
          "0".(int)($report['matches_additional'][$mid]['duration']%3600/60) :
          (int)($report['matches_additional'][$mid]['duration']%3600/60)
        ) : ((int)($report['matches_additional'][$mid]['duration']%3600/60));

    $duration = $duration.":".(
      (int)($report['matches_additional'][$mid]['duration']%60) < 10 ?
      "0".(int)($report['matches_additional'][$mid]['duration']%60) :
      (int)($report['matches_additional'][$mid]['duration']%60)
    );

    $res .= "<tr>".
            "<td>".$mid."</td>".
            "<td value=\"".(isset($report['match_participants_teams'][$mid]['radiant']) ? $report['match_participants_teams'][$mid]['radiant'] : 0)."\">".
              $team_radiant."</td>".
            "<td value=\"".(isset($report['match_participants_teams'][$mid]['radiant']) ? $report['match_participants_teams'][$mid]['dire'] : 0)."\">".
              $team_dire."</td>".
            "<td value=\"".$report['matches_additional'][$mid]['duration']."\">".$duration."</td>".
            "<td>".($report['matches_additional'][$mid]['radiant_score']+$report['matches_additional'][$mid]['dire_score'])."</td>".
            "<td value=\"".$report['matches_additional'][$mid]['date']."\">".
              date(locale_string("time_format")." ".locale_string("date_format"), $report['matches_additional'][$mid]['date'])."</td>".
            "</tr>";
  }
  $res .= "</tbody></table>";

  return $res;
}

?>
