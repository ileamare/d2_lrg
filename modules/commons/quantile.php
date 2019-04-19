<?php

/**
 * LRG Library - quantile library v 2.4.0
 * Originally ported from D2-LRG-lrg2 codebase
 * Collection of functions to calculate quantiles, median values and find find dispersion
 * @author Darien "leamare" Fawkes
 * @license GNU GPL 3.0
 */

function quantile($arr, $val) {
  if(!is_array($arr)) return false;
  if(!sizeof($arr)) return 0;
  sort($arr);

  $count = count($arr);
  $quantile_val = floor(($count-1)*$val);
  if($count*$val == $quantile_val+1) {
    $low = $arr[$quantile_val];
    $high = $arr[$quantile_val+1];
    $quantile = round(($low+$high)/2);
  } else if ($count*$val < $quantile_val+1) {
    $quantile = $arr[$quantile_val];
  } else {
    $quantile = $arr[$quantile_val+1];
  }
  return $quantile;
}

function calculate_median($arr) {
  return quantile($arr, 0.5);
}

function expected($arr) {
  sort($arr);
  $count = count($arr);
  $sum = array_reduce($arr, function($carry, $item) {
    $carry += $item;
    return $carry;
  }, 0);
  return ($sum/$count);
}

function dispersion($arr) {
  $expected = expected($arr);
  $d = [];
  foreach ($arr as $v) {
    $d[] = pow($v-$expected, 2);
  }
  return expected($d);
}

function sq_dev($arr) {
  return pow(dispersion($arr), 0.5);
}

function find_position($arr, $val) {
  sort($arr);
  $last = 0;
  foreach($arr as $id => $v) {
    $last = $id;
    if ($v > $val) break;
  }
  return $last;
}

?>
