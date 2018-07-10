<?php
function player_card($player_id) {
  global $report;
  global $meta;
  global $strings;
  $pname = player_name($player_id);
  $pinfo = $report['players_additional'][$player_id];

  $output = "<div class=\"player-card\"><div class=\"player-name\"><a href=\"http://opendota.com/players/$player_id\" target=\"_blank\" rel=\"noopener\">".$pname." (".$player_id.")</a></div>";
  if(isset($report['teams']))
    $output .= "<div class=\"player-team\">".team_name($pinfo['team'])." (".$pinfo['team'].")</div>";
  $output .= "<div class=\"player-add-info\">".
                "<div class=\"player-info-line\"><span class=\"caption\">".locale_string("matches").":</span> ".$pinfo['matches']." (".
                  $pinfo['won']." - ".($pinfo['matches'] - $pinfo['won']).")</div>".
                "<div class=\"player-info-line\"><span class=\"caption\">".locale_string("winrate").":</span> ".number_format($pinfo['won']*100/$pinfo['matches'], 2)."%</div>".
                "<div class=\"player-info-line\"><span class=\"caption\">".locale_string("gpm").":</span> ".number_format($pinfo['gpm'],1)."</div>".
                "<div class=\"player-info-line\"><span class=\"caption\">".locale_string("xpm").":</span> ".number_format($pinfo['xpm'],1)."</div>".
                "<div class=\"player-info-line\"><span class=\"caption\">".locale_string("hero_pool").":</span> ".$pinfo['hero_pool_size']."</div></div>";

  # heroes
  $output .= "<div class=\"player-heroes\"><div class=\"section-caption\">".locale_string("heroes")."</div><div class=\"section-lines\">";
  foreach($pinfo['heroes'] as $hero) {
    $output .= "<div class=\"player-info-line\"><span class=\"caption\">".hero_full($hero['heroid']).":</span> ";
    $output .= $hero['matches']." - ".number_format($hero['wins']*100/$hero['matches'], 2)."%</div>";
  }
  $output .= "</div></div>";

  # positions
  $output .= "<div class=\"player-positions\"><div class=\"section-caption\">".locale_string("player_positions")."</div><div class=\"section-lines\">";
  foreach($pinfo['positions'] as $position) {
    $output .= "<div class=\"player-info-line\"><span class=\"caption\">".($position['core'] ? locale_string("core")." " : locale_string("support")).
                  $meta['lanes'][ $position['lane'] ].":</span> ";
    $output .= $position['matches']." - ".number_format($position['wins']*100/$position['matches'], 2)."%</div>";
  }
  $output .= "</div></div>";


  return $output."</div>";
}
?>
