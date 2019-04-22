<?php

$picks = [];
foreach($result["pickban"] as $hero)
  $picks[] = $hero["matches_total"];

// using quantiles as limiters
// initial idea: 25% quantile as higher limiter, then divide percentage by two
// altho it wasn't suitable for real datasets, so I adjusted it to be
// 20%, 15% and then either 25% quantile divided by teams number or 7.5% quantile
// regions have similar limiters formula, but the code is fractured a bit
// ideally every team should also have its own set of limiters
// 0.15 quantile is pretty close to 1q
// 0.2 is a bit higher and is close to Q1
// 0.1 is close to 1.25q
// 0.025 is close to 2q

function calculate_limiters($dataset, $teams = null) {
  $median = quantile($dataset, 0.5);
  $sq_dev = sq_dev($dataset);
  //$closest = find_position($dataset, $sq_dev+$median);
  //$sq_dev_pct = (1-$closest/count($dataset));
  $closest = find_position($dataset, $sq_dev-$median);
  $sq_dev_pct = $closest/count($dataset);
  $limiter_quantile = (0.20+$sq_dev_pct)/2;

  return [
    "sq_quantile" => $sq_dev_pct,
    "limiter_quantile" => $limiter_quantile,
    "median" => $median,
    "limiter_higher" => quantile($dataset, $limiter_quantile),
    "limiter_graph" => quantile($dataset, $limiter_quantile*0.5),
    "limiter_lower" => $teams ? 
      ceil(quantile($dataset, $limiter_quantile)/$teams) :
      quantile($dataset, $limiter_quantile*0.3)
  ];
}

$limiters = calculate_limiters($picks, $result['random']['teams_on_event'] ?? null);

//compatibility
$limiter = $limiters['limiter_higher'];
$limiter_graph = $limiters['limiter_graph'];
$limiter_lower = $limiters['limiter_lower'];

echo "[ ] Limiter: $limiter\n[ ] Limiter for graphs: $limiter_graph\n[ ] Lower Limiter: $limiter_lower\n";

?>
