<?php
function player_name($pid) {
  global $report;
  if($pid)
      return htmlspecialchars($report['players'][$pid]);
  return "null";
}
?>
