<?php

function merge_mods(&$a, $b) {
  if(!is_array($a) || empty($a)) {
    if(empty($a) || is_array($b))
      $a = $b;
    else
      $a .= $b;
  } else {
    foreach($b as $k => $v) {
      if(isset($a[$k]) && is_array($a[$k]) && is_array($b[$k])) {
        merge_arrays($a[$k], $b[$k]);
      } else{
        $a[$k] = $v;
      }
    }
  }
}

?>
