<?php
include_once("$root/modules/view/functions/links.php");

function rg_view_generator_teams_summary($context = null, $short_flag = false) {
  global $report;

  if($context == null) $context = array_keys($report['teams']);
  else $context = array_keys($context);

  if ($short_flag)
    $res = "";
  else
    $res  = "<div class=\"content-text\">".locale_string("desc_teams_summary")."</div>";

  uasort($context, function($a, $b) use ($report) {
    if($report['teams'][$a]['matches_total'] == $report['teams'][$b]['matches_total']) return 0;
    else return ($report['teams'][$a]['matches_total'] < $report['teams'][$b]['matches_total']) ? 1 : -1;
  });

  $percentages = [
    "rad_ratio",
    "radiant_wr",
    "dire_wr"
  ];

  $aliases = [
    "wards_placed" => "wards_placed_s",
    "sentries_placed" => "sentries_placed_s",
    "wards_destroyed" => "wards_destroyed_s",
    "radiant_wr" => "rad_wr_s",
    "dire_wr" => "dire_wr_s",
    "avg_match_len" => "duration"
  ];

  $short = [
    "kills",
    "deaths",
    "assists",
    "gpm",
    "xpm",
    "hero_pool",
    "avg_match_len"
  ];

  $res .= "<table id=\"teams-summary\" class=\"list ".($short_flag ? "" : "wide")."\">";

  foreach($report['teams'] as $team_id => $team) {
    $keys = array_keys($team['averages']);
    break;
  }

  $table_id = "teams-summary";
  $i = 0;

  $res .= "<tr class=\"thead\">".
            "<th onclick=\"sortTable(".($i++).",'$table_id');\">".locale_string("team_name")."</th>".
            "<th onclick=\"sortTableNum(".($i++).",'$table_id');\">".locale_string("matches_s")."</th>".
            "<th onclick=\"sortTableNum(".($i++).",'$table_id');\">".locale_string("winrate")."</th>";
  foreach($keys as $k) {
    if($short_flag) {
      if(!in_array($k, $short)) continue;
    }
    if (isset($aliases[$k])) $k = $aliases[$k];
      $res .= "<th onclick=\"sortTableNum(".($i++).",'$table_id');\">".locale_string($k)."</th>";
  }

  $res .= "</tr>";

  foreach($context as $team_id) {
    $res .= "<tr>".
              "<td>".team_link($team_id)."</td>".
              "<td>".$report['teams'][$team_id]['matches_total']."</td>".
              "<td>".number_format($report['teams'][$team_id]['wins']*100/$report['teams'][$team_id]['matches_total'],2)."%</td>";

    foreach($report['teams'][$team_id]['averages'] as $k => $v) {
      if($short_flag) {
        if(!in_array($k, $short)) continue;
      }
      $res .= "<td>".
              number_format($v*(in_array($k, $percentages) ? 100 : 1),
                ($v > 1000) ? 0 : (
                    ($v > 100) ? 1 : 2
                  )
                ).
              (in_array($k, $percentages) ? "%" : "")."</td>";
    }
    $res .= "</tr>";
  }
  $res .= "</table>";

  return $res;
}

?>
