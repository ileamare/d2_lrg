<?php 

if (isset($report)) {
  $output = "";

  $meta = new lrg_metadata;

  include_once("modules/view/__post_load.php");

  $modules = [];

  if(empty($mod)) $mod = "";
  
  // TODO: mod.split(-)
  // from latest: endpoint
  // if such endpoint doesn't exist: move to another
  // use mod as parameters object

  $endpoints = [];

  // overview
  // fallback

  // records
  
  // - heroes
  // averages_heroes
  // pickban
  // draft
  // hero_positions
  // hero_sides
  // hero_pairs
  // hero_triplets
} else {
  // basic response
  // list of matches + category
}

$modline = array_reverse(explode("-", $mod));

foreach ($modline as $ml) {
  if (isset($endpoints[$ml])) {
    $endp = $endpoints[$ml];
    break;
  } else {
    continue;
  }
}
if (empty($endp))
  $endp = $endpoints['fallback']();

try {
  $result = $endp($modline, $report);
} catch (Exception $e) {
  $result = [
    'error' => $e->getMessage()
  ];
}