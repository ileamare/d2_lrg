<?php
function player_name($pid, $tt = true) {
  global $report;
  $res = "";
  if (isset($report['players'])) {
    if($pid && isset($report['players'][$pid])) {
      if($tt && isset($report['teams']) && isset($report['players_additional'][$pid]['team'])) {
        $res .= team_tag($report['players_additional'][$pid]['team']).".";
      }
      return $res.htmlspecialchars($report['players'][$pid]);
    }
    return "null";
  } else {
    return "PID $pid";
  }
}
?>
