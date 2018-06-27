<?php
# sometimes getting all the pairs will be too much
# using 3.5% to 10% of total matches as limiter
# 10% would be too much, while 3% can be not enough

$picks = [];
foreach($result["pickban"] as $hero)
    $picks[] = $hero["matches_total"];
$median = calculate_median($picks);
unset($picks);

$limiter = (int)ceil($median/6);
$limiter_graph = (int)ceil($median/4);
if($lg_settings['main']['teams']) {
    //$report['random']['teams_on_event'];
    //$limiter = (int)ceil($result['random']['matches_total']/($result['random']['teams_on_event']*2));
    $limiter_lower = (int)ceil($result['random']['matches_total']/($result['random']['teams_on_event']*4));
} else {
    $limiter_lower = (int)ceil($median/16);
}

unset($median);

$limiter = $limiter>1 ? $limiter : 1;
$limiter_lower = $limiter_lower>1 ? $limiter_lower : 1;
?>
