<?php

if ($lg_settings['ana']['avg_heroes']) {
  # average for heroes
  require_once("modules/analyzer/heroes/averages.php");
}

if ($lg_settings['ana']['draft_stages']) {
  # pick/ban draft stages stats
  require_once("modules/analyzer/heroes/draft.php");
}

if ($lg_settings['ana']['hero_positions']) {
  # heroes on positions
  require_once("modules/analyzer/heroes/positions.php");
}

if ($lg_settings['ana']['hero_sides']) {
  # heroes factions
  require_once("modules/analyzer/heroes/sides.php");
}

if ($lg_settings['ana']['hero_combos_graph']) {
  # heroes combo graph
  require_once("modules/analyzer/heroes/combo_graph.php");
}

if ($lg_settings['ana']['hero_pairs']) {
  # heroes pairs
  require_once("modules/analyzer/heroes/pairs.php");
}

if ($lg_settings['ana']['hero_triplets']) {
  # heroes trios
  require_once("modules/analyzer/heroes/trios.php");
}

if ($lg_settings['ana']['hero_vs_hero']) {
  # hero vs hero
  require_once("modules/analyzer/heroes/versus_hero.php");
}

if ($lg_settings['ana']['hero_summary']) {
  # heroes summary
  require_once("modules/analyzer/heroes/summary.php");
}

?>
