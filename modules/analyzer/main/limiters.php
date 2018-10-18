<?php
# sometimes getting all the pairs will be too much
# using 3.5% to 10% of total matches as limiter
# 10% would be too much, while 3% can be not enough

$picks = [];
foreach($result["pickban"] as $hero)
    $picks[] = $hero["matches_total"];
$median = calculate_median($picks);
unset($picks);

$limiter = ceil($median/6);
//$limiter_graph = (int)ceil($median/4);
if($lg_settings['main']['teams']) {
    //$report['random']['teams_on_event'];
    $limiter = (int)ceil($median/($result['random']['teams_on_event']));
    //$limiter_lower = ceil($result['random']['matches_total']/($result['random']['teams_on_event']*4));
    $limiter_lower = ceil($limiter/2);
    $limiter_graph = $limiter_lower*2;
} else {
    $limiter_lower = ceil($median/16);
    $limiter_graph = ceil($median/4);
}

unset($median);

$limiter = $limiter>1 ? $limiter : 1;
$limiter_lower = $limiter_lower>1 ? $limiter_lower : 1;
?>
