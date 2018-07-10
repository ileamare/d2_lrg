<?php

$modules['heroes'] = array();

if (isset($report['averages_heroes']) )
  include("heroes/averages.php");

if (isset($report['pickban']))
  include("heroes/pickban.php");

if (isset($report['draft']))
  include("heroes/draft.php");

if (isset($report['hero_positions']))
  include("heroes/positions.php");

if (isset($report['hero_sides']))
  include("heroes/sides.php");

if (isset($report['hero_combos_graph']) && $report['settings']['heroes_combo_graph'])
  include("heroes/combo_graph.php");

if (isset($report['hero_pairs']) || isset($report['hero_triplets']))
  include("heroes/combos.php");

if (isset($report['hvh']))
  include("heroes/hvh.php");

if (isset($report['hero_summary']))
  include("heroes/summary.php");


?>
