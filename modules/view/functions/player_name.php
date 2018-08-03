<?php
function player_name($pid) {
  global $report;
  $res = "";
  if (isset($report['players'])) {
    if($pid && isset($report['players'][$pid])) {
      if(isset($report['teams'])) {
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
