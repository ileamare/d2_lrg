<?php
function player_name($pid, $tt = true) {
  global $report;
  $res = "";
  if (isset($report['players'])) {
    if($pid && isset($report['players'][$pid])) {
      if($tt && isset($report['teams']) && isset($report['players_additional'][$pid]['team'])) {
        $res .= team_tag($report['players_additional'][$pid]['team']).".";
      }
      $res .= htmlspecialchars($report['players'][$pid]);
    } else $res = "null";
  } else {
    $res = "PID $pid";
  }
  
  return $res;
}

function player_link($pid, $tt = true) {
  global $link_provider;
  return "<a target=\"_blank\" href=\"https://$link_provider/players/$pid\">".player_name($pid, $tt)."</a>";
}

?>
