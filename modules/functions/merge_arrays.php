<?php

function merge_arrays(&$a, $b) {
  foreach($b as $k => $v) {
    if(isset($a[$k]) && is_array($a[$k]) && is_array($b[$k])) {
      merge_arrays($a[$k], $b[$k]);
    } else{
      $a[$k] = $v;
    }
  }
}

?>
