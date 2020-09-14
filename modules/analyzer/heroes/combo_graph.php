<?php
$result["hero_combos_graph"] = [];

$dataset = [];
foreach($result["hero_pairs"] as $pair) { 
  $dev = (($pair['matches']-$pair['expectation'])/$pair['matches']);
  if($dev > 0)
    $dataset[] = $dev;
}

$pairs_limiters = calculate_limiters($dataset);

foreach($result["hero_pairs"] as $pair) {
  if($pair['matches']-$pair['expectation'] < $pair['matches']*$pairs_limiters['limiter_higher'])
    continue;
  $result["hero_combos_graph"][] = [
    "heroid1" => $pair['heroid1'],
    "heroid2" => $pair['heroid2'],
    "matches" => $pair['matches'],
    "wins" => $pair['wins'],
    "dev_pct" => round( ($pair['matches']-$pair['expectation'])/$pair['matches'] , 5),
  ];
}

?>
