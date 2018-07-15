<?php

function match_link($mid) {
  return "<a href=\"https://opendota.com/matches/$mid\" target=\"_blank\" rel=\"noopener\">$mid</a>";
}

function team_link($tid) {
  global $leaguetag;
  global $linkvars;

  return "<a href=\"?league=".$leaguetag."&mod=teams-team_".$tid."_stats".(empty($linkvars) ? "" : "&$linkvars")
    ."\" title=\"".team_name($tid)."\">".team_name($tid)." (".team_tag($tid).")</a>";
}

?>