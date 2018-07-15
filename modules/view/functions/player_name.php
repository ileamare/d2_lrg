<?php
function player_name($pid) {
  global $report;
  if($pid && isset($report['players'][$pid]))
      return htmlspecialchars($report['players'][$pid]);
  return "null";
}
?>
