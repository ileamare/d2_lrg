<?php
function setcheck($parent, $val) {
  if(!is_array($parent)) return false;
  return ( isset($parent[$val]) && !empty($parent[$val]) );
}

 ?>
