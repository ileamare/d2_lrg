<?php 

/**
 * LRG Library - quantile library v 2.4.0-r2
 * Originally ported from D2-LRG-lrg2 codebase
 * Collection of functions to calculate quantiles, median values and find find dispersion
 * @author Darien "leamare" Fawkes
 * @license GNU GPL 3.0
 */

function quantile(array $arr, float $val): float {
  if(!is_array($arr)) return false;
  if(!sizeof($arr)) return 0;
  sort($arr);

  $count = count($arr);
  $quantile_val = floor(($count-1)*$val);
//  $quantile_dev = $quantile_val < $count ? (($count-1)*$val) - $quantile_val : 0;
  if($count*$val == $quantile_val+1) {
    $low = $arr[$quantile_val];
    $high = $arr[$quantile_val+1];
    $quantile = (($low+$high)/2);
  } else if ($count*$val < $quantile_val+1) {
    $quantile = $arr[$quantile_val];
  } else {
    $quantile = $arr[$quantile_val+1];
  }
  return $quantile;
}

function calculate_median(array $arr): float {
  return quantile($arr, 0.5);
}

function expected(array $arr): float {
  $count = count($arr);
  if (!$count) return 0;
  $sum = array_sum($arr);
  return ($sum/$count);
}

function dispersion(array $arr): float {
  $expected = expected($arr);
  $d = [];
  foreach ($arr as $v) {
    $d[] = pow($v-$expected, 2);
  }
  return expected($d);
}

function sq_dev($arr) {
  return sqrt(dispersion($arr));
}

function find_position(array $arr, float $val): float {
  sort($arr);
  reset($arr);
  $last = null;
  foreach($arr as $id => $v) {
    if ($v > $val) {
      if ($last !== null) {
        $delta = abs($val - $arr[$last])/abs($v - $arr[$last]);
        $last += $delta;
      } else {
        if ($v > 0 && $val < 0 || $v < 0 && $val > 0) $last = 0;
        else $last += abs($val - $v)/abs($v);
      }
      break;
    }
    $last = $id;
  }
  return $last;
}

?>