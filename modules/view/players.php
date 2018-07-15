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

?>
