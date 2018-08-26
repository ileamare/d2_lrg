<?php
function team_name($tid) {
  global $report;
  if($tid && isset($report['teams'][ $tid ]['name']))
    return htmlspecialchars($report['teams'][ $tid ]['name']);
  return "(no team)";
}

function team_tag($tid) {
  global $report;
  if($tid && isset($report['teams'][ $tid ]['tag']))
    return htmlspecialchars($report['teams'][ $tid ]['tag']);
  return "";
}
?>
