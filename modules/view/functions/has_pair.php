<?php
function has_pair($hid, $pairs) {
  foreach($pairs as $pair) {
    if(!isset($keys)) $keys = array_keys($pair);
    if ($pair[$keys[0]] == $hid) return true;
    if ($pair[$keys[1]] == $hid) return true;
  }
  return false;
}
?>
