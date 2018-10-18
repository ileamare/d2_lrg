<?php

/**
  *   convert_time
  * args: $time_decimal (float)
  * returns: $time_string (string)
 **/

function convert_time($time_decimal) {
  $minutes = floor($time_decimal);
  $seconds = floor(($time_decimal-$minutes)*60);
  if ($minutes > 60) {
    $hours = floor($minutes / 60);
    $minutes %= 60;
  }

  $str = "";
  if(isset($hours)) $str .= $hours.":";
  $str .= ($minutes < 10 ? "0" : "").$minutes.":".($seconds < 10 ? "0" : "").$seconds;

  return $str;
}

?>
