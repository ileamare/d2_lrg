<?php

function rg_view_generate_teams_summary() {
  global $report;

  $res  = "<div class=\"content-text\">".locale_string("desc_teams_summary")."</div>";

  uasort($report['teams'], function($a, $b) {
    if($a['matches_total'] == $b['matches_total']) return 0;
    else return ($a['matches_total'] < $b['matches_total']) ? 1 : -1;
  });

  $res .= "<table id=\"teams-summary\" class=\"list wide\">";

  $res .= "<tr class=\"thead\">".
            "<th onclick=\"sortTable(0,'teams-sum');\">".locale_string("team_name")."</th>".
            "<th onclick=\"sortTableNum(1,'teams-sum');\">".locale_string("matches_s")."</th>".
            "<th onclick=\"sortTableNum(2,'teams-sum');\">".locale_string("winrate_s")."</th>".
            "<th onclick=\"sortTableNum(3,'teams-sum');\">".locale_string("rad_ratio")."</th>".
            "<th onclick=\"sortTableNum(4,'teams-sum');\">".locale_string("rad_wr_s")."</th>".
            "<th onclick=\"sortTableNum(5,'teams-sum');\">".locale_string("dire_wr_s")."</th>".
            "<th onclick=\"sortTableNum(6,'teams-sum');\">".locale_string("hero_pool")."</th>".
            (compare_ver($report['ana_version'], [1,1,1,-4,1]) < 0 ?
              "" :
              "<th onclick=\"sortTableNum(7,'teams-sum');\">".locale_string("diversity")."</th>"
            ).
            "<th onclick=\"sortTableNum(8,'teams-sum');\">".locale_string("kills")."</th>".
            "<th onclick=\"sortTableNum(9,'teams-sum');\">".locale_string("deaths")."</th>".
            "<th onclick=\"sortTableNum(10,'teams-sum');\">".locale_string("assists")."</th>".
            "<th onclick=\"sortTableNum(11,'teams-sum');\">".locale_string("gpm")."</th>".
            "<th onclick=\"sortTableNum(12,'teams-sum');\">".locale_string("xpm")."</th>".
            "<th onclick=\"sortTableNum(13,'teams-sum');\">".locale_string("wards_placed_s")."</th>".
            "<th onclick=\"sortTableNum(14,'teams-sum');\">".locale_string("sentries_placed_s")."</th>".
            "<th onclick=\"sortTableNum(15,'teams-sum');\">".locale_string("wards_destroyed_s")."</th>".
            "<th onclick=\"sortTableNum(16,'teams-sum');\">".locale_string("duration")."</th>".
      "</tr>";

  foreach($report['teams'] as $team_id => $team) {
    $res .= "<tr>".
              "<td>".team_link($team_id)."</td>".
              "<td>".$team['matches_total']."</td>".
              "<td>".number_format($team['wins']*100/$team['matches_total'],2)."%</td>".
              "<td>".number_format($team['averages']['rad_ratio']*100,2)."%</td>".
                (
                  (compare_ver($report['ana_version'], [1,1,1,-4,0]) < 0) ?
                    "<td>".number_format($team['averages']['rad_wr']*100,2)."%</td>" :
                    "<td>".number_format($team['averages']['radiant_wr']*100,2)."%</td>"
                  ).
              "<td>".number_format($team['averages']['dire_wr']*100,2)."%</td>".
              "<td>".$team['averages']['hero_pool']."</td>".
              (
                (compare_ver($report['ana_version'], [1,1,1,-4,1]) < 0) ?
                  "" :
                  "<td>".number_format($team['averages']['diversity'],2)."</td>"
                ).
              "<td>".number_format($team['averages']['kills'],1)."</td>".
              "<td>".number_format($team['averages']['deaths'],1)."</td>".
              "<td>".number_format($team['averages']['assists'],1)."</td>".
              "<td>".number_format($team['averages']['gpm'],1)."</td>".
              "<td>".number_format($team['averages']['xpm'],1)."</td>".
              "<td>".number_format($team['averages']['wards_placed'],1)."</td>".
              "<td>".number_format($team['averages']['sentries_placed'],1)."</td>".
              "<td>".number_format($team['averages']['wards_destroyed'],1)."</td>".
              (isset($team['averages']['duration']) ?
                "<td>".number_format($team['averages']['duration'],1)."</td>" :
                "<td>".number_format($team['averages']['avg_match_len'],1)."</td>"
              ).
            "</tr>";
  }
  $res .= "</table>";

  return $res;
}

?>
