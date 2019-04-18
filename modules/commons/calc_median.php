<?php

function quantile($arr, $val) {
    if(!is_array($arr)) return false;
    if(!sizeof($arr)) return 0;
    sort($arr);

    $count = count($arr);
    $quantile_val = round(($count-1)*$val);
    if($count % 2) {
        $quantile = $arr[$quantile_val];
    } else {
        $low = $arr[$quantile_val];
        $high = $arr[$quantile_val+1];
        $quantile = round(($low+$high)/2);
    }
    return $quantile;
}

function calculate_median($arr) {
    return quantile($arr, 0.5);
}

?>
