<?php
function join_matches($matches) {
  $output = [];
  foreach($matches as $match) {
    $output[] = match_link($match);
  }
  return implode($output, ", ");

}
?>
