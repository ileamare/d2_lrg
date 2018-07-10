<?php
function join_matches($matches) {
  $output = array();
  foreach($matches as $match) {
    $output[] = match_link($match);
  }
  return implode($output, ", ");

}
?>
