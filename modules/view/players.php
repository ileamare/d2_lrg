<?php

$modules['players'] = [];

if (isset($report['averages_players']) )
  include("players/haverages.php");

if (isset($report['players_summary']) )
  include("players/summary.php");

if (isset($report['pvp']) )
  include("players/pvp.php");

if (isset($report['player_positions']) )
  include("players/positions.php");

if (isset($report['players_combo_graph']) && $report['settings']['players_combo_graph'] && isset($report['players_additional']))
  include("players/party_graph.php");

?>
