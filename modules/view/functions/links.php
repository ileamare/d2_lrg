<?php

function match_link($mid) {
  global $link_provider;
  return "<a href=\"https://$link_provider/matches/$mid\" target=\"_blank\" rel=\"noopener\">$mid</a>";
}

function team_link($tid) {
  global $leaguetag;
  global $linkvars;

  return "<a href=\"?league=".$leaguetag."&mod=teams-profiles-team".$tid.(empty($linkvars) ? "" : "&$linkvars")
    ."\" title=\"".team_name($tid)."\">".team_name($tid)." (".team_tag($tid).")</a>";
}

function region_link($rid) {
  global $leaguetag;
  global $linkvars;

  return "<a href=\"?league=".$leaguetag."&mod=regions-region$rid".(empty($linkvars) ? "" : "&$linkvars")
    ."\" title=\"".locale_string("region$rid")."\">".locale_string("region$rid")."</a>";
}

function team_link_short($tid) {
  global $leaguetag;
  global $linkvars;

  return "<a href=\"?league=".$leaguetag."&mod=teams-profiles-team".$tid.(empty($linkvars) ? "" : "&$linkvars")
    ."\" title=\"".team_name($tid)."\">".team_tag($tid)."</a>";
}


?>
