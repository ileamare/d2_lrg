<?php

$picks = [];
foreach($result["pickban"] as $hero)
    $picks[] = $hero["matches_total"];
$median = calculate_median($picks);

$limiter = quantile($picks, 0.2);
$limiter_graph = quantile($picks, 0.15);
//$limiter_graph = (int)ceil($median/4);
if($lg_settings['main']['teams']) {
    //$report['random']['teams_on_event'];
    //$limiter = (int)ceil($median/($result['random']['teams_on_event']));
    //$limiter_lower = ceil($result['random']['matches_total']/($result['random']['teams_on_event']*4));
    $limiter_lower = ceil($limiter/($result['random']['teams_on_event']));
    //$limiter_graph = quantile($picks, 0.15);
} else {
    $limiter_lower = quantile($picks, 0.075);
    //$limiter_graph = $limiter;//quantile($picks, 0.22);
}
//$limiter_graph = $limiter_lower*3;

// using quantiles as limiters
// initial idea: 25% quantile as higher limiter, then divide percentage by two
// altho it wasn't suitable for real datasets, so I adjusted it to be
// 20%, 15% and then either 25% quantile divided by teams number or 7.5% quantile
// regions have similar limiters formula, but the code is fractured a bit
// ideally every team should also have its own set of limiters
// 0.15 quantile is pretty close to 1q
// 0.2 is a bit higher and is close to Q1
// 0.075 is close to 2q

$limiter = $limiter>1 ? $limiter : 1;
$limiter_lower = $limiter_lower>1 ? $limiter_lower : 1;

?>
